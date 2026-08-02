<?php
require_once __DIR__ . '/../../config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $newRole = $_POST['role'];

    // 权限检查：超级管理员可设置任何角色，管理员不能设置超级管理员
    $currentUser = currentUser();
    $targetStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $targetStmt->bind_param("i", $user_id);
    $targetStmt->execute();
    $targetUser = $targetStmt->get_result()->fetch_assoc();

    if (!$targetUser) {
        die("用户不存在");
    }

    // 超级管理员可以设置任何角色
    if ($currentUser['role'] !== 'super_admin') {
        // 管理员不能将任何人设置为超级管理员，也不能修改超级管理员
        if ($newRole === 'super_admin' || $targetUser['role'] === 'super_admin') {
            die("权限不足：无法操作超级管理员");
        }
        // 管理员只能将用户设置为管理员及以下角色（但不能设置其他管理员？可以根据需求调整）
        // 这里允许管理员设置团体负责人及以下
        $allowedRoles = ['group_leader', 'senior_adventurer', 'adventurer', 'restricted'];
        if (!in_array($newRole, $allowedRoles) && $newRole !== 'admin' && $targetUser['role'] !== 'admin') {
            // 如果目标用户当前是管理员，管理员不能修改
            if ($targetUser['role'] === 'admin') {
                die("权限不足：不能修改其他管理员");
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $user_id);
    if ($stmt->execute()) {
        redirect(BASE_URL . '/modules/admin/users.php');
    } else {
        die("更新失败");
    }
}
redirect(BASE_URL . '/modules/admin/users.php');