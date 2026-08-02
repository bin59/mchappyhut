<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

$id = intval($_GET['id'] ?? 0);
$form = $conn->query("SELECT title FROM forms WHERE id = $id")->fetch_assoc();
if (!$form) die('表单不存在');

$fields = $conn->query("SELECT label FROM form_fields WHERE form_id = $id ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
$answers = $conn->query("SELECT data, created_at, user_id FROM form_answers WHERE form_id = $id ORDER BY created_at DESC");

// 准备数据
$rows = [];
$header = [];
foreach ($fields as $f) $header[] = $f['label'];
$header[] = '提交者';
$header[] = '提交时间';
$rows[] = $header;

while ($a = $answers->fetch_assoc()) {
    $d = json_decode($a['data'], true) ?: [];
    $row = [];
    foreach ($fields as $f) $row[] = $d[$f['label']] ?? '';
    $username = '';
    if ($a['user_id']) {
        $u = $conn->query("SELECT username FROM users WHERE id = " . intval($a['user_id']))->fetch_assoc();
        $username = $u['username'] ?? 'ID:'.$a['user_id'];
    }
    $row[] = $username;
    $row[] = $a['created_at'];
    $rows[] = $row;
}

// 生成 xlsx 文件（使用 ZipArchive）
$filename = $form['title'] . '_结果.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip->open($tmpFile, ZipArchive::CREATE);

// 构建共享字符串表
$strings = [];
$sheetXml = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

foreach ($rows as $row) {
    $sheetXml .= '<row>';
    foreach ($row as $cellValue) {
        $cellValue = (string) $cellValue;
        $index = array_search($cellValue, $strings, true);
        if ($index === false) {
            $index = count($strings);
            $strings[] = $cellValue;
        }
        $sheetXml .= '<c t="inlineStr"><is><t>' . htmlspecialchars($cellValue, ENT_QUOTES) . '</t></is></c>';
    }
    $sheetXml .= '</row>';
}
$sheetXml .= '</sheetData></worksheet>';

// 共享字符串表 XML
$sst = '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
foreach ($strings as $s) {
    $sst .= '<si><t>' . htmlspecialchars($s, ENT_QUOTES) . '</t></si>';
}
$sst .= '</sst>';

// 其他必需文件
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>';
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>';

$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->addFromString('xl/sharedStrings.xml', $sst);
$zip->close();

readfile($tmpFile);
unlink($tmpFile);
exit;