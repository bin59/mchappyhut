# 方块人快乐小窝 - 完整项目文档

> Minecraft 社区门户网站 + 后台管理系统
> 最后更新：2026-08-02

---

## 目录

1. [项目概述](#1-项目概述)
2. [系统架构](#2-系统架构)
3. [根目录文件](#3-根目录文件)
4. [核心配置 - config.php](#4-核心配置---configphp)
5. [公共头部 - header.php](#5-公共头部---headerphp)
6. [公共底部 - footer.php](#6-公共底部---footerphp)
7. [文件上传 - upload.php](#7-文件上传---uploadphp)
8. [用户模块 - modules/user/](#8-用户模块---modulesuser)
9. [反馈工单模块 - modules/feedback/](#9-反馈工单模块---modulesfeedback)
10. [公告模块 - modules/announcements/](#10-公告模块---modulesannouncements)
11. [活动模块 - modules/events/](#11-活动模块---modulesevents)
12. [社区帖子模块 - modules/community/](#12-社区帖子模块---modulescommunity)
13. [服务器模块 - modules/servers/](#13-服务器模块---modulesservers)
14. [行为准则模块 - modules/rules/](#14-行为准则模块---modulesrules)
15. [帮助中心模块 - modules/help/](#15-帮助中心模块---moduleshelp)
16. [关于我们模块 - modules/about/](#16-关于我们模块---modulesabout)
17. [赞助模块 - modules/sponsor/](#17-赞助模块---modulessponsor)
18. [时间轴模块 - modules/timeline/](#18-时间轴模块---modulestimeline)
19. [人物志模块 - modules/figures/](#19-人物志模块---modulesfigures)
20. [团体模块 - modules/groups/](#20-团体模块---modulesgroups)
21. [微信模块 - modules/wechat/](#21-微信模块---moduleswechat)
22. [后台管理模块 - modules/admin/](#22-后台管理模块---modulesadmin)
23. [数据库表结构](#23-数据库表结构)
24. [邮件发送系统](#24-邮件发送系统)
25. [角色权限系统](#25-角色权限系统)

---

## 1. 项目概述

**方块人快乐小窝（McHappyHut）** 是一个 Minecraft 游戏社区门户网站，采用纯 PHP + MySQL 架构，不依赖任何框架。项目实现了：

- **用户系统**：注册、登录、个人主页、资料编辑
- **内容展示**：公告、活动、社区帖子、服务器列表、行为准则、帮助中心、关于我们、赞助、时间轴、人物志、团体、微信联系
- **反馈工单系统**：支持反馈表单、建议和工单三大类型，含邮箱通知
- **后台管理系统**：仪表盘统计、用户管理、角色管理、分类管理等
- **现代化UI**：毛玻璃效果、暗色主题、响应式布局、动画过渡

**技术栈**：PHP 7+、MySQL、原生 JavaScript、Quill 富文本编辑器、Three.js / skinview3d（3D皮肤查看）

---

## 2. 系统架构

```
项目根目录 /
├── config.php                  # 全局配置（数据库、SMTP、角色、工具函数）
├── header.php                  # 公共头部（导航栏、主题、CSS变量）
├── footer.php                  # 公共底部（页脚）
├── index.php                   # 首页（英雄区、轮播、反馈CTA、社交栏）
├── upload.php                  # 图片上传API（返回JSON）
├── skinview3d.bundle.js        # Minecraft 3D皮肤查看器库
├── three.min.js                # Three.js 3D引擎
├── assets/                     # 静态资源（图片）
├── uploads/                    # 用户上传文件目录
└── modules/
    ├── user/                   # 用户模块（6个文件）
    ├── feedback/               # 反馈工单模块（17个文件）
    ├── announcements/          # 公告模块（4个文件）
    ├── events/                 # 活动模块（4个文件）
    ├── community/              # 社区帖子模块（4个文件）
    ├── servers/                # 服务器模块（4个文件）
    ├── rules/                  # 行为准则模块（4个文件）
    ├── help/                   # 帮助中心模块（4个文件）
    ├── about/                  # 关于我们模块（6个文件）
    ├── sponsor/                # 赞助模块（2个文件）
    ├── timeline/               # 时间轴模块（4个文件）
    ├── figures/                # 人物志模块（4个文件）
    ├── groups/                 # 团体模块（4个文件）
    ├── wechat/                 # 微信模块（2个文件）
    └── admin/                  # 后台管理模块（4个文件）
```

**URL 路由规则**：所有功能通过 `/modules/{模块名}/{页面}.php` 直接访问，无 URL 重写。

---

## 3. 根目录文件

### 3.1 index.php - 首页

**完整流程**：

| 行号 | 功能说明 |
|------|----------|
| 1-4 | 引入 config.php、header.php |
| 5 | 查询 `announcements` 表，取最新3条精选(is_featured=1)+置顶公告 |
| 6 | 查询 `events` 表，取未来最近的3个活动 |
| 7 | 查询 `community_posts` 表，取最新3篇帖子（JOIN users 获取作者信息） |
| 8-49 | **英雄区（Hero）**：全屏背景 + logo + 标语 + 导航按钮，使用 CSS `fadeIn` 动画 |
| 50-120 | **公告轮播（Announcement Carousel）**：水平横向滚动轮播（overflow-x:auto），每张卡片显示封面+标签+标题+摘要，悬停放大。管理员可见"发布公告"按钮 |
| 121-195 | **活动轮播（Event Carousel）**：同上结构的活动卡片轮播，显示日期徽章+标题+副标题+摘要。管理员可见"发布活动"按钮 |
| 196-260 | **社区帖子展示**：3列网格布局，每张卡片显示标题+摘要+作者头像+作者名+评论数+时间 |
| 261-310 | **反馈CTA（Call To Action）**：绿色渐变背景区域，引导用户提交反馈/建议 |
| 311-335 | **社交链接栏**：底部展示社区链接（QQ群、Discord等，通过 `$conn` 查询 `categories` 表中 `type='social'` 的记录动态渲染） |
| 336 | 引入 footer.php |

---

## 4. 核心配置 - config.php

> 所有模块的第一行都必须 `require_once __DIR__ . '/../../config.php';`（或相应路径）

### 4.1 错误报告（第1-3行）

```php
error_reporting(E_ALL);
ini_set('display_errors', 0);     // 生产环境关闭显示
ini_set('log_errors', 1);          // 记录到PHP错误日志
```

### 4.2 会话管理（第5行）

```php
if (session_status() === PHP_SESSION_NONE) session_start();
```

### 4.3 路径常量（第7-8行）

| 常量 | 值 | 说明 |
|------|-----|------|
| `BASE_URL` | 动态检测 `HTTP_HOST` + 路径 | 例：`http://localhost/mchappyhut` |

### 4.4 数据库连接（第9-22行）

**双连接机制**：
- **`$conn`**（mysqli）：用于所有带参数绑定的预编译查询（`prepare`/`bind_param`/`execute`）
- **`$db`**（PDO）：用于简单的查询（`query`/`fetch`），主要在后台管理页面使用

```php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset("utf8mb4");

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
```

**数据库常量**（未在代码中显式定义，需在服务器环境或外部配置）：
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

### 4.5 SMTP 邮件配置（第124-130行）

| 常量 | 当前值 | 用途 |
|------|--------|------|
| `SMTP_HOST` | `mail.ururc.org` | SMTP 服务器地址 |
| `SMTP_PORT` | `25` | SMTP 端口（可选 25/465/587） |
| `SMTP_USER` | `mcnoreply@ururc.org` | 发件邮箱账号 |
| `SMTP_PASS` | `dQ79JsqWJ2FaHOrA` | 发件邮箱密码 |
| `SMTP_SECURE` | `''`（空） | 加密方式（tls/ssl/空） |
| `MAIL_FROM` | `mcnoreply@ururc.org` | 发件人地址 |
| `MAIL_FROM_NAME` | `方块人快乐小窝` | 发件人显示名称 |

### 4.6 角色权限常量（第132-140行）

```php
define('ROLE_SUPER_ADMIN', 'super_admin');    // 超级管理员 - 最高权限
define('ROLE_ADMIN', 'admin');                 // 管理员 - 管理权限
define('ROLE_GROUP_LEADER', 'group_leader');   // 团体负责人 - 团体管理
define('ROLE_SENIOR', 'senior_adventurer');    // 高级冒险家
define('ROLE_ADVENTURER', 'adventurer');       // 冒险家 - 默认注册角色
define('ROLE_RESTRICTED', 'restricted');       // 受限用户
```

### 4.7 工具函数（第145行后）

| 函数 | 签名 | 说明 |
|------|------|------|
| `isLoggedIn()` | `(): bool` | 检查 `$_SESSION['user_id']` 是否存在 |
| `currentUser()` | `(): array` | 从 `users` 表查询当前登录用户，存储到 `$_SESSION['user']` 并返回。有缓存机制 |
| `requireLogin()` | `(): void` | 未登录则 `redirect()` 到登录页 |
| `requireAdmin()` | `(): void` | 角色不是 `super_admin` 或 `admin` 则重定向到首页 |
| `redirect($url)` | `(string): void` | 执行 `header('Location: ...')` 并 `exit` |
| `uploadFile($file)` | `(array): array` | 处理文件上传到 `uploads/` 目录，返回 `['success'=>bool, 'url'=>string, 'message'=>string]`。限制 5MB，允许 jpg/jpeg/png/gif/webp |
| `cleanInput($data)` | `(string): string` | `htmlspecialchars(trim())` 包装 |
| `timeAgo($datetime)` | `(string): string` | 将时间戳转为"x分钟前/x小时前/x天前"格式 |

---

## 5. 公共头部 - header.php

### 5.1 CSS 变量（主题系统）

定义在 `<style>` 块中，支持亮色/暗色双主题：

| CSS 变量 | 亮色值 | 说明 |
|----------|--------|------|
| `--bg` | `#F5F5F0` | 页面背景 |
| `--surface` | `#FFFFFF` | 卡片/面板背景 |
| `--surface-glass` | `rgba(255,255,255,0.72)` | 毛玻璃卡片 |
| `--surface-alt` | `#EDEDE8` | 次要背景 |
| `--text` | `#1C1F18` | 主文字色 |
| `--text-secondary` | `#5C6058` | 次要文字色 |
| `--text-tertiary` | `#8A8E86` | 辅助文字色 |
| `--border` | `#D4D4CC` | 边框色 |
| `--border-light` | `#E8E8E2` | 浅边框色 |
| `--mc-green` | `#4F8A30` | Minecraft 绿色（主题色） |
| `--mc-gold` | `#D4942B` | Minecraft 金色 |
| `--mc-gold-soft` | `#E8B84B` | 柔和金色 |
| `--nav-height` | `70px` | 导航栏高度 |
| `--shadow-sm` | `0 2px 8px rgba(0,0,0,0.06)` | 浅阴影 |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.10)` | 深阴影 |

**暗色主题**通过 `@media (prefers-color-scheme: dark)` 自动切换，所有变量值反转为暗色调。

### 5.2 基础样式重置

- `box-sizing: border-box` 全局应用
- `body` 使用 `--bg` 背景色 + `--text` 文字色
- 自定义滚动条：8px 宽，Minecraft 绿色滑块
- Smooth scroll behavior

### 5.3 导航栏（Navbar）

- **结构**：`<nav>` 标签，`position: fixed` 固定在页面顶部，`z-index: 1000`
- **高度**：70px（`--nav-height`）
- **布局**：flex 两端对齐
- **左侧**：logo 图片（40x40）+ 品牌名 + 导航链接组
- **导航链接**：首页 / 公告 / 活动 / 社区 / 服务器 / 规则 / 帮助 / 关于
- **右侧**：
  - 已登录：头像按钮（下拉菜单：个人主页、编辑资料、后台管理（仅管理员）、退出登录）
  - 未登录：登录 / 注册按钮
- **移动端**：hamburger 汉堡菜单，点击展开全屏移动导航面板
- **滚动效果**：`window.scrollY > 10` 时添加背景模糊 + 边框

### 5.4 全局按钮样式

```css
.btn-auth {
  background: linear-gradient(135deg, #4F8A30, #6DB840);
  color: #fff;
  font-weight: 700;
  border-radius: 12px;
  transition: all 0.2s;
}
.btn-auth:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,138,48,0.3); }
```

### 5.5 Font Awesome 图标

通过 CDN 引入 Font Awesome 6.x：`<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">`

---

## 6. 公共底部 - footer.php

### 6.1 结构

- **主区域**：3列网格布局（max-width: 1200px）
  - 第1列：方块人快乐小窝简介 + 标语
  - 第2列：快捷链接（首页、公告、活动、社区、服务器、规则、帮助、关于、赞助）
  - 第3列：联系方式（待扩展）
- **分隔线**
- **底部**：版权声明 + "Powered by McHappyHut"

### 6.2 响应式

移动端（`max-width: 768px`）：3列变为1列堆叠布局。

---

## 7. 文件上传 - upload.php

### 7.1 功能

独立的图片上传 API 端点，返回 JSON 响应。主要用于 Quill 富文本编辑器的图片插入。

### 7.2 详细流程

| 行号 | 说明 |
|------|------|
| 1-5 | 设置 JSON 响应头，只接受 POST |
| 6-12 | 检查文件上传错误码（UPLOAD_ERR_OK），失败则返回错误 JSON |
| 13-18 | 验证文件类型：`$allowed = ['jpg','jpeg','png','gif','webp']`，通过 `pathinfo` 获取扩展名 |
| 19-22 | 限制文件大小：5MB（5 * 1024 * 1024） |
| 23-28 | 生成唯一文件名：`time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext` |
| 29-32 | `move_uploaded_file()` 移动到 `uploads/` 目录 |
| 33 | 返回 JSON：`{"success":true, "url":"uploads/xxx.png", "message":"上传成功"}` |

**使用场景**：Quill 编辑器中的图片工具栏按钮通过 `fetch` 调用此 API。

---

## 8. 用户模块 - modules/user/

> 共 6 个文件，实现完整的用户注册登录体系。

### 8.1 login.php - 用户登录

**流程**：

| 行号 | 说明 |
|------|------|
| 1-3 | 引入 config，已登录则重定向首页 |
| 5-28 | **POST 处理**：接收 `email`+`password` → `password_verify()` 验证 → 写入 `$_SESSION['user_id']` → 重定向首页 |
| 12 | 数据库查询：`SELECT id, password FROM users WHERE email = ?` |
| 18 | 密码验证：`password_verify($password, $user['password'])` |
| 19 | 登录成功：`$_SESSION['user_id'] = $user['id']` |
| 30-81 | **UI**：左右分栏布局，左侧品牌展示（logo+标语+背景图），右侧登录表单（邮箱+密码+登录按钮+注册链接） |

**CSS 亮点**：
- 登录按钮 hover 上移动画
- 移动端 flex-direction 切换为 column
- 错误提示红底白字

### 8.2 register.php - 用户注册

**流程**：

| 行号 | 说明 |
|------|------|
| 1-3 | 引入 config，已登录重定向 |
| 8-45 | **POST 处理** |
| 9-17 | 收集 username、email、password、confirm、6位验证码（从 code1~code6 拼接） |
| 19-20 | **验证码校验**：`$_SESSION['reg_code']` 对比，大小写不敏感（`strtoupper`） |
| 21-26 | 基础校验：空值检查、密码一致性、最小6位 |
| 28-32 | 查重：`SELECT id FROM users WHERE email = ? OR username = ?` |
| 34-36 | 创建用户：`INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'adventurer')`，密码 `password_hash()` 加密 |
| 38-39 | 成功清除 `reg_code` session |
| 47-212 | **UI**：左右分栏，右侧注册表单 |
| 73-101 | 表单字段：用户名、邮箱、密码、确认密码、6格验证码输入（每格1字符，自动跳转）+ 获取验证码按钮 + 数学验证码防机器人 |
| 121-200 | **JavaScript 逻辑** |
| 123-134 | `focusNext()`：验证码格子自动跳转和退格支持 |
| 137-200 | 发送验证码流程：①点击"获取验证码" → ②弹出数学题（随机加法 1~10） → ③答对后 fetch POST 到 `send_code.php` → ④60秒倒计时冷却 |
| 170 | API 调用：`fetch('send_code.php', { method:'POST', body:'email='+email })` |

### 8.3 send_code.php - 发送验证码 API

**流程**：

| 行号 | 说明 |
|------|------|
| 1-8 | 设置 JSON 响应头，仅接受 POST |
| 10-14 | 验证邮箱格式：`filter_var($email, FILTER_VALIDATE_EMAIL)` |
| 17-20 | **频率限制**：`$_SESSION['last_mail_time']`，60秒内禁止重发 |
| 23-25 | 生成6位验证码：`strtoupper(substr(md5(uniqid(mt_rand(),true)), 0, 6))`，存入 `$_SESSION['reg_code']` |
| 27-85 | 构建 HTML 邮件：绿色渐变头部 + 居中大码验证码块（32px 字体，虚线边框） + 底部提示 |
| 88-141 | **`smtp_mail_html()` 函数**：使用 `fsockopen()` 原生 SMTP 协议发送邮件 |
| 89-99 | 连接 SMTP 服务器：`fsockopen(SMTP_HOST, SMTP_PORT)` |
| 101-103 | HELO 握手 |
| 105-116 | AUTH LOGIN 认证：base64 编码用户名和密码 |
| 118-123 | MAIL FROM / RCPT TO / DATA 命令 |
| 126-133 | 邮件头构建：`Content-Type: text/html; charset=UTF-8`，中文主题 Base64 编码 |
| 135-138 | 发送邮件体 + `QUIT` 断开 |
| 140 | 返回判断：响应码 `250` 为成功 |
| 143-148 | 调用发送函数，返回 JSON 结果给前端 |

### 8.4 profile.php - 个人主页

**流程**：

| 行号 | 说明 |
|------|------|
| 1-3 | 引入 config |
| 5-20 | **路由逻辑**：有 `?id=` → 查看指定用户；无 id → 需登录查看自己的主页 |
| 7-10 | 查询 `users` 表：`SELECT * FROM users WHERE id = ?` |
| 22-25 | 查询用户帖子：`SELECT * FROM community_posts WHERE user_id = ? ORDER BY created_at DESC` |
| 32-88 | **UI** |
| 35-47 | **封面区**：全宽 260-420px 高，使用 `cover` 字段（或渐变色默认图），底部渐变遮罩。仅本人可见编辑按钮 |
| 50-87 | **头像+信息区**：大圆形头像（150x150，白色边框+阴影），用户名+角色标签（颜色按角色区分）+ 简介 + 注册日期 + 帖子数 + 用户ID |
| 44, 57 | 编辑按钮：仅 `isLoggedIn() && $profileUser['id'] == currentUser()['id']` 可见 |
| 91-128 | **帖子列表区**：响应式网格布局（auto-fill, minmax(320px, 1fr)），每张卡片显示标题+摘要（3行截断）+评论数+时间，hover 上移+边框变绿 |

### 8.5 edit_profile.php - 编辑个人资料

**流程**：

| 行号 | 说明 |
|------|------|
| 1-4 | 引入 config + `requireLogin()` + `currentUser()` |
| 7-27 | **POST 处理** |
| 8-10 | 接收 username、bio |
| 13-17 | **头像处理**：优先文件上传（`uploadFile()`），其次 URL 输入，保留旧值 |
| 19-23 | **封面图处理**：同理 |
| 25 | 用户名校验：不能为空 |
| 26-28 | 查重：`SELECT id FROM users WHERE username = ? AND id != ?` |
| 31 | 更新：`UPDATE users SET username=?, bio=?, avatar=?, cover=? WHERE id=?` |
| 33-50 | **UI 表单**：用户名、简介 textarea、头像（上传+URL）、封面图（上传+URL）、保存按钮 |

### 8.6 logout.php - 退出登录

```php
session_destroy();
redirect(BASE_URL . '/index.php');
```

---

## 9. 反馈工单模块 - modules/feedback/

> 共 17 个文件，实现了完整的工单反馈系统（反馈表单 + 建议 + 工单），含邮箱通知。

### 9.1 整体结构

| 子模块 | 前端页面 | 管理员页面 | 邮件通知 |
|--------|----------|------------|----------|
| 反馈表单 (forms) | index.php, create.php, detail.php | admin_forms.php, form_detail.php, form_edit.php | 无 |
| 建议 (suggestions) | index.php 内嵌表单 | admin_suggestions.php | 无 |
| 工单 (tickets) | ticket_create.php, ticket_detail.php, list.php | admin_tickets.php | send_work_mail.php |

### 9.2 index.php - 反馈首页

- 三个卡片入口：反馈表单 / 建议 / 工单
- 全屏背景 + 入场动画
- 移动端 3列变1列

### 9.3 forms/create.php - 创建反馈表单

| 关键行 | 说明 |
|--------|------|
| 1-3 | 引入 config，需登录 |
| 5-27 | POST 处理：接收 title、content、type → `INSERT INTO forms (user_id, title, content, type)` |
| 30-85 | UI：标题+内容+类型下拉（bug/feature/other）+ 提交按钮 |

### 9.4 forms/detail.php - 反馈表单详情

| 关键行 | 说明 |
|--------|------|
| 6 | 查询：`SELECT f.*, u.username, u.avatar FROM forms f JOIN users u ON f.user_id = u.id WHERE f.id = ?` |
| 22-65 | UI：作者信息+标题+类型标签+状态+内容+时间。管理员可见编辑/状态按钮 |

### 9.5 forms/form_edit.php - 编辑反馈表单

| 关键行 | 说明 |
|--------|------|
| 1-6 | 管理员验证，查询表单 |
| 8-18 | POST：更新 `title, content, type, status` |
| 21-55 | UI：标题/内容/类型/状态下拉（pending/processing/resolved/closed） |

### 9.6 forms/form_detail.php - 管理员查看表单详情

| 关键行 | 说明 |
|--------|------|
| 1-5 | 管理员验证 |
| 10 | 查询：`SELECT f.*, u.username, u.avatar, u.email FROM forms f JOIN users u ON f.user_id = u.id WHERE f.id = ?` |

### 9.7 forms/admin_forms.php - 管理反馈表单列表

| 关键行 | 说明 |
|--------|------|
| 1-3 | 管理员验证 |
| 5-9 | 搜索：`WHERE title LIKE ? OR content LIKE ?` |
| 11 | 分页：每页10条，`LIMIT ?, ?` |
| 25-99 | UI：搜索框+表格（ID/用户/标题/类型/状态/时间/操作），分页导航 |

### 9.8 suggestions/index.php（含内嵌表单）

- 用户可直接在建议页面提交建议
- POST 处理：`INSERT INTO suggestions (user_id, content)` → 重定向
- 已登录用户显示输入框+提交按钮
- 管理员可见 `admin_suggestions.php` 入口

### 9.9 suggestions/admin_suggestions.php

| 关键行 | 说明 |
|--------|------|
| 1-3 | 管理员验证 |
| 5 | 查询：`SELECT s.*, u.username FROM suggestions s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC` |
| 15-60 | UI：卡片列表（用户头像+用户名+内容+时间），管理员可删除 |

### 9.10 tickets/ticket_create.php - 创建工单

| 关键行 | 说明 |
|--------|------|
| 1-3 | 引入 config，需登录 |
| 5-10 | 邮箱验证码检查：`$_SESSION['ticket_code']` |
| 12-25 | POST 处理：title、category、priority、content，`INSERT INTO tickets` |

### 9.11 tickets/ticket_detail.php - 工单详情

| 关键行 | 说明 |
|--------|------|
| 1-6 | 查询工单：`SELECT t.*, u.username, u.avatar FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?` |
| 8-35 | **回复功能**：POST 回复 → `INSERT INTO ticket_replies` + 调用 `sendWorkMail()` 发送通知邮件 |
| 36-100 | UI：工单详情+状态标签+优先级+分类+回复列表（每楼显示头像+用户名+时间+内容） |

### 9.12 tickets/list.php - 我的工单列表

| 关键行 | 说明 |
|--------|------|
| 1-4 | 需登录 |
| 6 | 查询：`SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC` |
| 15-50 | UI：卡片列表（标题+状态+优先级+分类+时间） |

### 9.13 tickets/admin_tickets.php - 管理所有工单

- 管理员查看所有工单（不限用户）
- 带搜索和状态筛选功能
- 显示用户头像、用户名、邮箱

### 9.14 send_ticket_code.php - 发送工单验证码

| 关键行 | 说明 |
|--------|------|
| 1-5 | 只接受 POST |
| 7-13 | 邮箱验证+频率限制（60秒） |
| 15-22 | 生成6位验证码 → `$_SESSION['ticket_code']` |
| 24-85 | 使用独立 SMTP 配置（`pumpkin@ururc.org`），发送 HTML 验证码邮件（绿色主题，不同于注册验证码的样式） |

**工单 SMTP 配置**（第24-31行）：
```php
$ticketSmtp = [
    'host' => 'mail.ururc.org',
    'port' => 25,
    'user' => 'pumpkin@ururc.org',
    'pass' => 'H7N7By2skN8FxX74',
    'secure' => '',
    'from' => 'pumpkin@ururc.org',
    'fromName' => '方块人工单系统'
];
```

### 9.15 send_work_mail.php - 工单通知邮件函数

| 关键行 | 说明 |
|--------|------|
| 1 | `function sendWorkMail($to, $subject, $htmlBody)` |
| 3-7 | 使用 `pumpkin@ururc.org` 独立SMTP配置 |
| 9-45 | `fsockopen()` 原生 SMTP 发送（与 send_code.php 相同逻辑） |

**调用位置**：`ticket_detail.php` 第8行回复工单后调用。

### 9.16 send_ticket_notify.php - 工单通知函数（备用）

使用 `config.php` 全局 SMTP 常量（`SMTP_HOST` 等），与主验证邮箱共享配置。目前未被实际调用（工单通知走 `send_work_mail.php`）。

### 9.17 manage.php / edit.php / forms.php / tickets.php / detail.php

这些文件提供补充的管理/编辑功能，服务于上述主要页面。

---

## 10. 公告模块 - modules/announcements/

> 4个文件，标准 CRUD 模式。

### 10.1 index.php - 公告列表

| 关键行 | 说明 |
|--------|------|
| 1-4 | 查询：`SELECT * FROM announcements ORDER BY is_featured DESC, created_at DESC` |
| 8-70 | UI：横幅+卡片网格（auto-fill），每张卡片显示封面+标题+标签+摘要+时间，hover动画 |

### 10.2 detail.php - 公告详情

| 关键行 | 说明 |
|--------|------|
| 5 | 查询：`SELECT a.*, u.username, u.avatar, u.id AS author_id FROM announcements a JOIN users u ON a.user_id = u.id WHERE a.id = ?` |
| 10-50 | **封面英雄区**：全宽图+渐变遮罩+标题浮动 | 无封面时显示简洁标题区 |
| 51-100 | **内容区**：作者信息卡片 + 富文本正文 + 底部标签+时间 |

### 10.3 edit.php - 公告新增/编辑

| 关键行 | 说明 |
|--------|------|
| 1-4 | 管理员验证 |
| 6-14 | POST 处理：`INSERT/UPDATE announcements (title, subtitle, content, cover, tag, is_featured, user_id)` |
| 16-90 | 表单：Quill 富文本编辑器、标题、副标题、标签、封面（上传+URL）、精选复选框 |

### 10.4 delete.php - 公告删除

```php
$stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
redirect(BASE_URL . '/modules/announcements/');
```

---

## 11. 活动模块 - modules/events/

> 4个文件，与公告模块结构相同。

### 11.1 index.php - 活动列表

| 关键行 | 说明 |
|--------|------|
| 1-4 | 查询：`SELECT * FROM events ORDER BY event_date DESC` |
| UI | 卡片网格，每张卡片显示日期徽章+封面+标题+副标题+摘要 |

### 11.2 detail.php - 活动详情

| 关键行 | 说明 |
|--------|------|
| 5 | 查询：`SELECT e.*, u.username, u.avatar FROM events e JOIN users u ON e.user_id = u.id WHERE e.id = ?` |
| UI | 顶部大日期徽章 + 活动详情（时间、地点、组织者） + 富文本内容 |

### 11.3 edit.php - 活动新增/编辑

| 关键行 | 说明 |
|--------|------|
| POST | 字段：title, subtitle, content, cover, event_date, event_time, location, organizer, user_id |

### 11.4 delete.php - 活动删除

```php
DELETE FROM events WHERE id = ?
```

---

## 12. 社区帖子模块 - modules/community/

> 4个文件，支持评论系统。

### 12.1 index.php - 帖子列表

| 关键行 | 说明 |
|--------|------|
| 1-3 | 查询：`SELECT p.*, u.username, u.avatar FROM community_posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC` |
| UI | 卡片网格（3列），每张卡片：标题+副标题+摘要（3行截断）+ 作者头像+用户名+评论数+时间 |

### 12.2 detail.php - 帖子详情 + 评论区

| 关键行 | 说明 |
|--------|------|
| 4 | 主查询：`SELECT p.*, u.username, u.avatar FROM community_posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?` |
| 8-12 | **评论提交处理**：`INSERT INTO community_comments (post_id, user_id, content)` |
| 14 | 评论查询：`SELECT c.*, u.username, u.avatar FROM community_comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC` |
| 20-95 | UI：帖子标题+内容+作者信息+时间 + 评论列表（每楼显示用户头像+用户名+时间+内容） + 评论输入框（需登录） |

### 12.3 edit.php - 帖子新增/编辑

| 关键行 | 说明 |
|--------|------|
| 1-4 | 需登录 |
| 6-14 | POST：`INSERT/UPDATE community_posts (title, subtitle, content, cover, tag, user_id)` |
| 表单 | Quill 富文本编辑器 + 标题 + 副标题 + 封面 + 标签 |

### 12.4 delete.php - 帖子删除

```php
DELETE FROM community_posts WHERE id = ?
```

---

## 13. 服务器模块 - modules/servers/

> 4个文件，服务器信息展示。

### 13.1 index.php - 服务器列表

| 关键行 | 说明 |
|--------|------|
| 查询 | `SELECT * FROM servers ORDER BY name ASC` |
| UI | 左侧列表 + 右侧终端风格详情面板（JS 动态切换） |

### 13.2 detail.php - 服务器详情

| 关键行 | 说明 |
|--------|------|
| 查询 | `SELECT * FROM servers WHERE id = ?` |
| UI | 封面+头像+状态指示灯（绿=在线/红=离线）+ 地址（可复制）+ 端口 + 游戏类别 + 版本 + 模组 + 客户端说明 + 人数上限 + 描述 |

### 13.3 edit.php - 服务器新增/编辑

| 关键行 | 说明 |
|--------|------|
| POST | 字段：name, address, port, status(online/offline), game_type, version, mod_info, client_notes, max_players, description, join_url, avatar, cover |

### 13.4 delete.php - 服务器删除

```php
DELETE FROM servers WHERE id = ?
```

---

## 14. 行为准则模块 - modules/rules/

> 4个文件，类似公告模块的结构。

### 14.1 index.php

- 序号排列规则（`sort_order` 字段）
- 每条规则卡片：序号+标题+副标题+标签+时间

### 14.2 detail.php

- 使用 `DOMDocument` 解析正文中 `<h2>`/`<h3>` 标签自动生成左侧目录导航
- 支持面包屑导航
- 查询关联 `users` 表获取发布者信息

### 14.3 edit.php

- Quill 富文本编辑器
- 字段：title, subtitle, tag, content, cover, sort_order

### 14.4 delete.php

```php
DELETE FROM rules WHERE id = ?
```

---

## 15. 帮助中心模块 - modules/help/

> 4个文件，帮助文档系统。

### 15.1 index.php

- 3列网格卡片布局
- 每张卡片：图标+标题+内容摘要（纯文本截取）
- 移动端变1列

### 15.2 detail.php

- 绿色渐变标题横幅
- 毛玻璃内容区
- 富文本渲染

### 15.3 edit.php

- Quill 富文本编辑器
- 字段：title, content, sort_order

### 15.4 delete.php

```php
DELETE FROM help_articles WHERE id = ?
```

---

## 16. 关于我们模块 - modules/about/

> 6个文件，含 3D Minecraft 皮肤查看器。

### 16.1 index.php - 关于我们主页

- 全屏背景图（bj1.png）
- 毛玻璃内容区：显示 `about` 表富文本
- 贡献者展示区：圆形头像+姓名+副标题，仿终端风格

### 16.2 edit.php - 编辑关于内容

- Quill 编辑器
- 读取/写入 `about` 表单条记录

### 16.3 contributor_detail.php - 贡献者详情

- **3D 皮肤查看器**：使用 Three.js + skinview3d.bundle.js 渲染 Minecraft 角色皮肤
- 皮肤图片通过 `skin_proxy.php` 代理加载（解决跨域问题）
- 左侧人物信息卡（头像+姓名+副标题+封面）
- 右侧详细介绍

### 16.4 contributor_edit.php - 贡献者新增/编辑

- 字段：name, subtitle, avatar, cover, skin_file, description, sort_order
- 皮肤文件仅支持上传（不支持URL）

### 16.5 contributor_delete.php

```php
DELETE FROM contributors WHERE id = ?
```

### 16.6 skin_proxy.php - 皮肤图片代理

- 通过 cURL 或 `file_get_contents()` 获取外部皮肤图片
- 验证 MIME 类型后转为 base64 Data URI
- 失败时返回64x64透明 PNG

---

## 17. 赞助模块 - modules/sponsor/

> 2个文件。

### 17.1 index.php - 赞助前台

- 展示赞助码（收款码图片）+ 赞助说明
- 赞助人员名单（头像+姓名+金额）
- 入场动画效果

### 17.2 admin.php - 赞助管理

- 赞助码配置（图片上传+说明更新）
- 赞助人员 CRUD（姓名、头像、金额、留言、排序）
- 赞助人员列表展示

---

## 18. 时间轴模块 - modules/timeline/

> 4个文件。

### 18.1 index.php - 时间轴列表

| 关键行 | 说明 |
|--------|------|
| 分页 | 每页20条，`LIMIT ?, 20` |
| UI | 垂直时间轴线 + 事件卡片（封面+标题+副标题+摘要+记录者+服务器+时间） |
| 缩放控制 | 0.5x / 1x / 1.5x 缩放按钮切换 `transform: scale()` |

### 18.2 detail.php

- 标题+副标题+封面+记录者+事件时间+关联服务器+富文本内容

### 18.3 edit.php

- Quill 编辑器
- 字段：title, subtitle, content, cover, event_date, server_id, user_id

### 18.4 delete.php

```php
DELETE FROM timeline_events WHERE id = ?
```

---

## 19. 人物志模块 - modules/figures/

> 4个文件。

### 19.1 index.php - 人物志列表

- 顶部横幅（ag5.png）
- 卡片网格（按 sort_order），每张卡片：封面背景+头像叠加+姓名+副标题

### 19.2 detail.php - 人物详情

- 全宽横幅（cover 字段）
- 左侧 sticky：人物信息卡（头像+姓名+副标题）
- 右侧：富文本正文

### 19.3 edit.php - 人物新增/编辑

- Quill 编辑器
- 图片通过 fetch 异步上传
- 字段：name, subtitle, avatar, cover, description, sort_order

### 19.4 delete.php

```php
DELETE FROM figures WHERE id = ?
```

---

## 20. 团体模块 - modules/groups/

> 4个文件。

### 20.1 index.php - 团体列表

- 顶部横幅（ag6.png）
- 卡片网格（按时间倒序）
- 每张卡片：封面/渐变色+名称+副标题+负责人头像+类型标签+时间

### 20.2 detail.php - 团体详情

- 封面+名称+副标题+负责人（头像+名称）+类型标签+创建时间+描述

### 20.3 edit.php - 团体新增/编辑

- 字段：name, subtitle, cover, type, leader_name, leader_avatar, description

### 20.4 delete.php

```php
DELETE FROM groups WHERE id = ?
```

---

## 21. 微信模块 - modules/wechat/

> 2个文件。

### 21.1 index.php - 联系我们

- 微信绿(#07C160)主题全屏背景
- 白色居中卡片：微信图标+标题"方块人微信小窝"+二维码图片

### 21.2 admin.php - 二维码管理

- 上传或URL设置二维码图片
- 删除二维码
- 当前二维码预览

---

## 22. 后台管理模块 - modules/admin/

> 4个文件，管理员专用。

### 22.1 dashboard.php - 管理仪表盘

| 关键行 | 说明 |
|--------|------|
| 1-3 | `requireAdmin()` 验证 |
| 5-15 | **6项统计**：`SELECT COUNT(*) FROM users/community_posts/announcements/events/servers/tickets` |
| 17-50 | UI：统计卡片网格（图标+数字+标签+渐变背景色） |
| 51-85 | **快捷操作**：6个按钮（发布公告、发布活动、添加服务器、添加规则、帮助文档、管理用户） |
| 86-120 | 数据趋势图占位区（预留扩展） |
| 121-130 | 响应式：移动端卡片变为2列 |

### 22.2 users.php - 用户管理

| 关键行 | 说明 |
|--------|------|
| 1-5 | 管理员验证 |
| 7-12 | **搜索**：`WHERE username LIKE ? OR email LIKE ?` |
| 15-20 | **分页**：每页15条，`LIMIT ?, 15`，计算总页数 |
| 22-30 | 查询：`SELECT id, username, email, avatar, role, created_at FROM users ORDER BY id DESC` |
| 32-125 | **UI 表格** |
| 35-55 | 顶部：标题 + 搜索框（GET 方式提交） + 总用户数 |
| 57-105 | 表格列：头像 + 用户名 + 邮箱 + 角色标签（颜色区分）+ 注册日期 + "修改角色"按钮 |
| 107-120 | **角色修改弹窗**：模态框，角色下拉选项根据权限动态生成 |
| 121-130 | 分页导航（上一页/下一页 + 总页数） |
| 权限控制 | 超级管理员可设置任何角色；普通管理员不可操作超级管理员和其他管理员 |

### 22.3 update_role.php - 修改用户角色

| 关键行 | 说明 |
|--------|------|
| 1-5 | 管理员验证，仅POST |
| 7-11 | 接收 `user_id` + `role` |
| 13-28 | **权限层级检查** |
| 14-18 | 超级管理员：可设置任何角色 |
| 19-27 | 管理员：不能操作超级管理员（`role != 'super_admin'`），不能操作其他管理员（`role != 'admin'`），只能设置 `group_leader` 及以下 |
| 30 | 更新：`UPDATE users SET role = ? WHERE id = ?` |
| 32-42 | Room2 检查：工单系统角色联动（若原角色为 group_leader → 更新 tickets 表相关逻辑） |
| 44 | redirect 回 `users.php` |

### 22.4 categories.php - 分类管理

| 关键行 | 说明 |
|--------|------|
| 1-3 | 管理员验证 |
| 5-19 | **添加分类**：POST 接收 `name`+`sort_order`+`type` → `INSERT INTO categories` |
| 21-28 | **编辑分类**：`UPDATE categories SET name=?, sort_order=? WHERE id=?` |
| 30-35 | **删除分类**：`DELETE FROM categories WHERE id=?` |
| 37-51 | **UI**：分类列表表格（名称、排序、类型、操作按钮），内联编辑表单 |

---

## 23. 数据库表结构

根据代码分析，数据库包含以下主要表：

### 23.1 users - 用户表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK AUTO_INCREMENT | 用户ID |
| username | VARCHAR | 用户名 |
| email | VARCHAR | 邮箱（唯一） |
| password | VARCHAR | bcrypt 哈希密码 (password_hash) |
| avatar | VARCHAR | 头像URL |
| cover | VARCHAR | 个人主页背景图URL |
| bio | TEXT | 个人简介 |
| role | ENUM | super_admin/admin/group_leader/senior_adventurer/adventurer/restricted |
| created_at | DATETIME | 注册时间 |

### 23.2 announcements - 公告表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| subtitle | VARCHAR | 副标题 |
| content | TEXT(HTML) | 内容（Quill 富文本） |
| cover | VARCHAR | 封面图URL |
| tag | VARCHAR | 标签 |
| is_featured | TINYINT | 是否精选（0/1） |
| user_id | INT FK→users.id | 发布者 |
| created_at | DATETIME | 发布时间 |

### 23.3 events - 活动表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| subtitle | VARCHAR | 副标题 |
| content | TEXT(HTML) | 内容 |
| cover | VARCHAR | 封面URL |
| tag | VARCHAR | 标签 |
| event_date | DATE | 活动日期 |
| event_time | TIME | 活动时间 |
| location | VARCHAR | 活动地点 |
| organizer | VARCHAR | 组织者 |
| user_id | INT FK | 发布者 |
| created_at | DATETIME | 创建时间 |

### 23.4 community_posts - 社区帖子表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| subtitle | VARCHAR | 副标题 |
| content | TEXT(HTML) | 内容 |
| cover | VARCHAR | 封面URL |
| tag | VARCHAR | 标签 |
| user_id | INT FK | 发布者 |
| created_at | DATETIME | 发布时间 |

### 23.5 community_comments - 帖子评论表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| post_id | INT FK→community_posts.id | 所属帖子 |
| user_id | INT FK→users.id | 评论者 |
| content | TEXT | 评论内容 |
| created_at | DATETIME | 评论时间 |

### 23.6 servers - 服务器表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| name | VARCHAR | 服务器名称 |
| address | VARCHAR | 服务器地址（IP/域名） |
| port | INT | 端口号 |
| status | VARCHAR | online/offline |
| game_type | VARCHAR | 游戏类型（生存/创造/小游戏等） |
| version | VARCHAR | MC 版本 |
| mod_info | TEXT | 模组信息 |
| client_notes | TEXT | 客户端说明 |
| max_players | INT | 最大玩家数 |
| description | TEXT(HTML) | 描述（富文本） |
| join_url | VARCHAR | 加入链接 |
| avatar | VARCHAR | 头像URL |
| cover | VARCHAR | 封面URL |
| created_at | DATETIME | 添加时间 |

### 23.7 rules - 行为准则表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| subtitle | VARCHAR | 副标题 |
| content | TEXT(HTML) | 内容 |
| cover | VARCHAR | 封面URL |
| tag | VARCHAR | 标签 |
| sort_order | INT | 排序序号 |
| user_id | INT FK | 发布者 |
| created_at | DATETIME | 发布时间 |

### 23.8 help_articles - 帮助文档表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| content | TEXT(HTML) | 内容 |
| sort_order | INT | 排序 |
| author_id | INT FK | 作者 |
| updated_at | DATETIME | 更新时间 |

### 23.9 about - 关于我们表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID（单条记录） |
| content | TEXT(HTML) | 关于内容（Quill 富文本） |

### 23.10 contributors - 贡献者表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| name | VARCHAR | 姓名 |
| subtitle | VARCHAR | 副标题 |
| avatar | VARCHAR | 头像URL |
| cover | VARCHAR | 背景图URL |
| skin_file | VARCHAR | Minecraft 皮肤文件路径 |
| description | TEXT(HTML) | 详细介绍 |
| sort_order | INT | 排序 |

### 23.11 forms - 反馈表单表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| user_id | INT FK | 提交者 |
| title | VARCHAR | 标题 |
| content | TEXT | 内容 |
| type | VARCHAR | 类型（bug/feature/other） |
| status | VARCHAR | 状态（pending/processing/resolved/closed） |
| created_at | DATETIME | 提交时间 |

### 23.12 suggestions - 建议表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| user_id | INT FK | 建议者 |
| content | TEXT | 建议内容 |
| created_at | DATETIME | 提交时间 |

### 23.13 tickets - 工单表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| user_id | INT FK | 创建者 |
| title | VARCHAR | 标题 |
| category | VARCHAR | 分类 |
| priority | VARCHAR | 优先级（low/medium/high/urgent） |
| status | VARCHAR | 状态（open/processing/resolved/closed） |
| content | TEXT | 内容 |
| created_at | DATETIME | 创建时间 |

### 23.14 ticket_replies - 工单回复表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| ticket_id | INT FK→tickets.id | 所属工单 |
| user_id | INT FK→users.id | 回复者 |
| content | TEXT | 回复内容 |
| created_at | DATETIME | 回复时间 |

### 23.15 timeline_events - 时间轴事件表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| title | VARCHAR | 标题 |
| subtitle | VARCHAR | 副标题 |
| content | TEXT(HTML) | 内容 |
| cover | VARCHAR | 封面URL |
| event_date | DATETIME | 事件时间 |
| server_id | INT FK（可选） | 关联服务器 |
| recorder_id | INT FK | 记录者 |
| created_at | DATETIME | 创建时间 |

### 23.16 figures - 人物志表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| name | VARCHAR | 姓名 |
| subtitle | VARCHAR | 副标题 |
| avatar | VARCHAR | 头像URL |
| cover | VARCHAR | 封面URL |
| description | TEXT(HTML) | 描述 |
| sort_order | INT | 排序 |

### 23.17 groups - 团体表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| name | VARCHAR | 团体名称 |
| subtitle | VARCHAR | 副标题 |
| cover | VARCHAR | 封面URL |
| type | VARCHAR | 团体类型 |
| leader_name | VARCHAR | 负责人姓名 |
| leader_avatar | VARCHAR | 负责人头像 |
| description | TEXT | 描述 |
| created_at | DATETIME | 创建时间 |

### 23.18 categories - 分类/设置表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| name | VARCHAR | 名称 |
| sort_order | INT | 排序 |
| type | VARCHAR | 类型（如 'social' 用于社交链接） |

### 23.19 sponsors - 赞助表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| 赞助码配置 | - | 二维码图片+说明 |
| 赞助人员 | - | 姓名、头像、金额、留言等 |

### 23.20 wechat_qr - 微信二维码表

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT PK | ID |
| 图片URL | VARCHAR | 微信二维码图片路径 |

---

## 24. 邮件发送系统

### 24.1 架构概览

项目使用**原生 SMTP 协议（fsockopen）**发送邮件，无第三方邮件库依赖。两套独立的邮箱配置：

| 用途 | 邮箱 | SMTP | 配置位置 |
|------|------|------|----------|
| 注册验证码 | `mcnoreply@ururc.org` | mail.ururc.org:25 | config.php 常量 |
| 工单验证+通知 | `pumpkin@ururc.org` | mail.ururc.org:25 | send_ticket_code.php / send_work_mail.php 内联 |

### 24.2 SMTP 发送流程（通用）

```
1. fsockopen(SMTP_HOST, SMTP_PORT)          → 建立 TCP 连接
2. HELO {hostname}                          → SMTP 握手
3. AUTH LOGIN                               → 开始认证
4. base64_encode(username)                  → 发送用户名
5. base64_encode(password)                  → 发送密码
6. 检查响应码 == 235                        → 认证成功
7. MAIL FROM:<{from}>                       → 设置发件人
8. RCPT TO:<{to}>                           → 设置收件人
9. DATA                                     → 开始邮件内容
10. 发送邮件头 + HTML内容                    → 包含 Content-Type: text/html; charset=UTF-8
11. \r\n.\r\n                               → 结束标记
12. QUIT                                    → 断开连接
```

### 24.3 邮件类型

#### 注册验证码邮件（send_code.php）

- **触发**：注册页点击"获取验证码"
- **样式**：绿色渐变头部 + 白色内容区 + 大码验证码（32px + 虚线边框）
- **验证码**：6位大写字母数字混合（MD5 截取）
- **有效期**：10分钟（文字说明，实际由 session 控制）
- **频率限制**：60秒内不可重发

#### 工单验证码邮件（send_ticket_code.php）

- **触发**：创建工单时验证邮箱
- **样式**：与注册验证码不同的视觉主题
- **验证码**：4位数字
- **频率限制**：60秒

#### 工单回复通知邮件（send_work_mail.php）

- **触发**：管理员或用户回复工单时
- **收件人**：工单创建者或上一回复者
- **内容**：包含工单标题、回复内容、查看链接

---

## 25. 角色权限系统

### 25.1 角色层级（从高到低）

```
super_admin (超级管理员)
  └── 所有权限，可管理 admin
admin (管理员)
  └── 管理权限，不能操作 super_admin 和其他 admin
group_leader (团体负责人)
  └── 团体管理权限
senior_adventurer (高级冒险家)
  └── 普通用户 +
adventurer (冒险家)
  └── 默认注册角色，基础权限
restricted (受限用户)
  └── 受限操作
```

### 25.2 权限检查函数

| 函数 | 说明 |
|------|------|
| `requireLogin()` | 任意登录用户可访问 |
| `requireAdmin()` | 仅 `super_admin` 和 `admin` 可访问 |
| `isLoggedIn()` | 布尔检查 |

### 25.3 权限保护的文件

**需要管理员（requireAdmin）**：
- 所有 `*_delete.php` 文件（删除操作）
- 所有 `*_edit.php` 文件（新增/编辑操作）
- `admin/*.php` 全部文件
- `feedback/admin_*.php` 全部文件
- `sponsor/admin.php`
- `wechat/admin.php`

**需要登录（requireLogin）**：
- `feedback/create.php`（创建反馈）
- `feedback/ticket_create.php`（创建工单）
- `community/edit.php`（发帖/编辑）
- `user/edit_profile.php`（编辑资料）
- `user/profile.php`（无 id 参数时）

### 25.4 UI 权限控制

- 所有管理入口按钮（"发布公告"、"添加服务器"等）仅在 `isAdmin()` 时渲染
- 个人资料编辑按钮仅本人可见
- 角色修改弹窗选项根据当前用户角色动态过滤

---

## 附录：静态资源清单

| 文件 | 用途 |
|------|------|
| `logo.png` | 网站 Logo（导航栏+登录注册页） |
| `home1.png` | 登录/注册左侧背景图 |
| `mc1.png`, `mc2.png`, `mc3.png` | Minecraft 相关装饰图 |
| `log.png` | 备用 logo |
| `Sug1.png` | 建议/反馈相关图 |
| `1783617315484..png`, `1783617720335..png` | 临时上传图片 |
| `assets/` | 11个 PNG + 1个 JPG 装饰资源 |
| `skinview3d.bundle.js` | Minecraft 3D 皮肤查看器（约200KB） |
| `three.min.js` | Three.js 3D 渲染引擎（约600KB） |
| `uploads/` | 用户上传文件目录（49张PNG + 20张JPG） |

---

> **文档生成时间**：2026-08-02
> **项目维护者**：方块人快乐小窝开发团队
