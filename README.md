# 方块人快乐小窝（McHappyHut）

> Minecraft 游戏社区门户网站 + 后台管理系统

[![PHP](https://img.shields.io/badge/PHP-7+-777BB4?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?logo=mysql)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-green)](./LICENSE)

---

## 功能特性

- **用户系统** — 注册 / 登录 / 注销 / 个人主页 / 资料编辑 / 邮箱验证码
- **内容展示** — 公告 / 活动 / 社区帖子 / 服务器 / 行为准则 / 帮助中心 / 关于我们 / 赞助 / 时间轴 / 人物志 / 团体 / 微信
- **反馈工单** — 反馈表单 + 建议 + 完整工单系统（含回复 & 邮件通知）
- **后台管理** — 仪表盘统计 / 用户管理 / 角色权限管理 / 分类设置
- **UI 体验** — 毛玻璃效果 / 自动暗色主题 / 响应式布局 / CSS 动画
- **3D 皮肤查看器** — 贡献者页面集成 Three.js + skinview3d
- **富文本编辑** — 全站使用 Quill 富文本编辑器

---

## 技术栈

| 层级 | 技术 |
|------|------|
| 语言 | PHP 7+ |
| 数据库 | MySQL 5.7+（PDO + mysqli 双连接） |
| 前端 | 原生 JS + CSS3 变量 / Flex / Grid / 动画 |
| 编辑器 | Quill 富文本编辑器 |
| 3D | Three.js + skinview3d.bundle.js |
| 图标 | Font Awesome 6.x CDN |
| 邮件 | 原生 SMTP（fsockopen），无第三方依赖 |
| 密码 | bcrypt（password_hash） |

---

## 快速开始

### 环境要求

- PHP 7.0+
- MySQL 5.7+
- PHP 扩展：`mysqli`、`PDO`、`fileinfo`、`openssl`
- 可写目录：`uploads/`（用户上传）

### 安装步骤

```bash
# 1. 克隆项目
git clone <repository-url>
cd mchappyhut

# 2. 导入数据库（在 phpMyAdmin 或命令行中执行）
# 将提供的 SQL 文件导入 MySQL

# 3. 配置数据库连接
# 编辑 config.php，设置 DB_HOST / DB_USER / DB_PASS / DB_NAME

# 4. 配置 SMTP 邮件
# 编辑 config.php 中的 SMTP_* 常量（详见下方配置说明）

# 5. 确保 uploads/ 目录可写
chmod 755 uploads/   # Linux
# Windows 下确保 IIS/IUSR 或 NETWORK SERVICE 有写入权限

# 6. 部署到 Web 服务器
# 将项目目录设为 Apache/Nginx 的 DocumentRoot 或虚拟主机目录
```

### Web 服务器配置

**Apache**：确保已启用 `mod_rewrite`，`.htaccess`（如需要）：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Nginx**：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/mchappyhut;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 项目结构

```
mchappyhut/
├── index.php                   # 首页（Hero + 轮播 + CTA + 社交栏）
├── config.php                  # 全局配置 & 工具函数
├── header.php                  # 公共头部（导航栏 + 主题）
├── footer.php                  # 公共底部
├── upload.php                  # 图片上传 API
├── skinview3d.bundle.js        # MC 3D 皮肤查看器
├── three.min.js                # Three.js 引擎
├── assets/                     # 静态资源图片
├── uploads/                    # 用户上传目录（需可写）
└── modules/
    ├── user/                   # 用户模块（6 文件）
    │   ├── login.php           #   登录
    │   ├── register.php        #   注册
    │   ├── logout.php          #   注销
    │   ├── profile.php         #   个人主页
    │   ├── edit_profile.php    #   编辑资料
    │   ├── send_code.php       #   发送验证码 API
    │   └── forgot_password.php #   忘记密码
    ├── feedback/               # 反馈工单模块（17 文件）
    │   ├── index.php           #   反馈首页（入口导航）
    │   ├── create.php          #   创建反馈表单
    │   ├── detail.php          #   反馈详情
    │   ├── form_detail.php     #   管理员查看表单
    │   ├── form_edit.php       #   编辑表单状态
    │   ├── admin_forms.php     #   管理反馈列表
    │   ├── ticket_create.php   #   创建工单
    │   ├── ticket_detail.php   #   工单详情 & 回复
    │   ├── list.php            #   我的工单列表
    │   ├── admin_tickets.php   #   管理工单列表
    │   ├── send_ticket_code.php #  工单验证码 API
    │   ├── send_work_mail.php  #   工单邮件通知函数
    │   ├── send_ticket_notify.php # 通知函数（备用）
    │   └── ...                 #   其他辅助文件
    ├── announcements/          # 公告模块（4 文件）
    ├── events/                 # 活动模块（4 文件）
    ├── community/              # 社区帖子模块（4 文件，含评论）
    ├── servers/                # 服务器模块（4 文件）
    ├── rules/                  # 行为准则模块（4 文件）
    ├── help/                   # 帮助中心模块（4 文件）
    ├── about/                  # 关于我们模块（6 文件，含皮肤查看器）
    ├── sponsor/                # 赞助模块（2 文件）
    ├── timeline/               # 时间轴模块（4 文件）
    ├── figures/                # 人物志模块（4 文件）
    ├── groups/                 # 团体模块（4 文件）
    ├── wechat/                 # 微信模块（2 文件）
    └── admin/                  # 后台管理模块（4 文件）
        ├── dashboard.php       #   仪表盘（统计 + 快捷入口）
        ├── users.php           #   用户管理（搜索 / 分页 / 改角色）
        ├── update_role.php     #   角色修改 API
        └── categories.php      #   分类管理（社交链接等）
```

---

## 配置说明

### 数据库配置

在 `config.php` 中设置（实际常量通过外部方式定义）：

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'mchappyhut');
```

### SMTP 邮件配置

项目使用两套独立邮箱：

| 用途 | 邮箱 | SMTP |
|------|------|------|
| 注册验证码 | `mcnoreply@ururc.org` | mail.ururc.org:25 |
| 工单验证 & 通知 | `pumpkin@ururc.org` | mail.ururc.org:25 |

在 `config.php` 中修改验证码邮件配置：

```php
define('SMTP_HOST', 'mail.ururc.org');
define('SMTP_PORT', 25);
define('SMTP_USER', 'mcnoreply@ururc.org');
define('SMTP_PASS', 'your_password');
define('SMTP_SECURE', '');
define('MAIL_FROM', 'mcnoreply@ururc.org');
define('MAIL_FROM_NAME', '方块人快乐小窝');
```

工单邮箱在 `modules/feedback/send_ticket_code.php` 和 `send_work_mail.php` 中独立配置。

---

## 角色权限

| 角色 | 常量 | 说明 |
|------|------|------|
| 超级管理员 | `super_admin` | 全部权限，可管理 admin |
| 管理员 | `admin` | 管理权限，不可操作同级别 |
| 团体负责人 | `group_leader` | 团体管理 |
| 高级冒险家 | `senior_adventurer` | 高级用户 |
| 冒险家 | `adventurer` | **默认注册角色** |
| 受限用户 | `restricted` | 受限操作 |

后台入口：`/modules/admin/dashboard.php`

---

## 数据库表

共 20 张表：`users`、`announcements`、`events`、`community_posts`、`community_comments`、`servers`、`rules`、`help_articles`、`about`、`contributors`、`forms`、`suggestions`、`tickets`、`ticket_replies`、`timeline_events`、`figures`、`groups`、`categories`、`sponsors`、`wechat_qr`

> 完整表结构请参阅 [`PROJECT_DOCUMENTATION.md`](./PROJECT_DOCUMENTATION.md) 第 23 章

---

## 邮件发送

使用原生 SMTP 协议（`fsockopen`），无第三方依赖。发送流程：

```
TCP 连接 → HELO → AUTH LOGIN → Base64 认证
→ MAIL FROM / RCPT TO → DATA → HTML 邮件 → QUIT
```

支持三种邮件类型：注册验证码、工单验证码、工单回复通知（均含频率限制 60s）。

---

## 安全特性

- 密码 bcrypt 哈希存储（`password_hash` / `password_verify`）
- SQL 注入防护（mysqli 预编译 + PDO 参数绑定）
- XSS 防护（`htmlspecialchars` / `cleanInput`）
- 邮件验证码防机器人（数学题 + 频率限制）
- 角色权限分层控制
- 生产环境关闭错误显示（`display_errors = 0`）

---

## 更多文档

详细的功能说明、逐行代码分析、完整数据库结构请参考：

- **[PROJECT_DOCUMENTATION.md](./PROJECT_DOCUMENTATION.md)** — 完整项目文档（25 章）

---

## License

MIT © 方块人快乐小窝
