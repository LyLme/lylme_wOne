<p align="center">
  <a href="https://github.com/lylme/lylmew" target="_blank">
    <img src="https://img.shields.io/badge/GitHub-lylme/lylmew-181717?style=flat-square&logo=github" alt="GitHub">
  </a>
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/ThinkPHP-8.0-67C23A?style=flat-square" alt="ThinkPHP">
  <img src="https://img.shields.io/badge/Layui-2.9-1E9FFF?style=flat-square&logo=layui&logoColor=white" alt="Layui">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/license-AGPL--3.0-orange?style=flat-square" alt="license">
</p>

<h1 align="center">六零同城企服</h1>

<p align="center">企业官网 + 售后管理系统，开箱即用的同城企业数字化平台</p>

<p align="center">
  <a href="#-项目简介">项目简介</a> •
  <a href="#-演示站点">演示站点</a> •
  <a href="#-技术栈">技术栈</a> •
  <a href="#-快速开始">快速开始</a> •
  <a href="#-功能清单">功能清单</a> •
  <a href="#-项目结构">项目结构</a> •
  <a href="#-常见问题">常见问题</a>
</p>

---

## 📖 项目简介

**六零同城企服** 是一款基于 ThinkPHP 8 开发的企业级网站系统，集 **企业官网展示** + **产品服务管理** + **在线报修工单** 于一体，适用于中小型企业、IT 服务商、售后维修团队快速搭建线上服务平台。

### 核心亮点

- 🚀 **可视化安装**：浏览器访问 `/install.php` 自动引导安装，填写数据库信息即可完成
- 🔐 **自定义后台入口**：安装时可自由设定管理后台路径，提升安全性
- 🎨 **前后台分离设计**：Bootstrap 5 前台 + Layui 2.9 后台，响应式适配 PC / 平板 / 手机
- 📦 **模块化架构**：产品、案例、新闻、单页等内容模块可按需启用
- 🔧 **RBAC 权限体系**：多角色管理员、菜单级权限控制、操作日志审计
- 📊 **工单管理**：在线报修 → 受理 → 处理 → 完成，全流程追踪，支持 Excel 导出
- 🔄 **在线安装**：无需手动导入 SQL，安装过程自动建表并写入初始数据

---

## 🖥 演示站点


| 入口 | 地址 |
|------|------|
| 🏠 前台首页 | [https://lylmew_demo.lylme.com](https://lylmew_demo.lylme.com) |
| ⚙️ 后台管理 | 暂不开放 |

---

## 🛠 技术栈

| 层级 | 技术 | 版本 |
|------|------|------|
| 后端框架 | ThinkPHP | ^8.0 |
| ORM | ThinkORM | ^3.0 |
| 前台 UI | Bootstrap 5 + 原生 JS | |
| 后台 UI | Layui | 2.9 |
| 数据库 | MySQL | 8.0+ |
| 模板引擎 | ThinkView | ^2.0 |
| 验证码 | Think-Captcha | ^3.0 |
| JWT 认证 | firebase/php-jwt | ^6.10 |
| Excel 处理 | PhpSpreadsheet | ^2.0 |
| Web 服务器 | Nginx | 1.18+ / Apache |

**PHP 扩展依赖**：`pdo_mysql` `mbstring` `fileinfo` `openssl` `gd` `json` `xml` `curl`

---

## 🚀 快速开始

### 1. 环境要求

| 组件 | 最低版本 |
|------|----------|
| PHP | >= 8.1 |
| MySQL | >= 8.0 |
| Nginx | >= 1.18（推荐） |

### 2. 下载项目

```bash
git clone https://github.com/lylme/lylmew.git
cd lylmew
```

### 3. 配置 Web 服务器

将网站运行目录指向 `public/`，并配置 URL 重写。

<details>
<summary>📄 Nginx 配置参考</summary>

项目根目录提供了 `nginx.conf` 可直接参考。核心配置：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/lylmew/public;
    index index.php index.html;

    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php?s=$1 last;
        }
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

</details>

<details>
<summary>📄 Apache 配置参考</summary>

确保已启用 `mod_rewrite` 模块，项目 `public/` 目录下的 `.htaccess` 已包含重写规则，无需额外配置。

</details>

### 4. 设置目录权限

```bash
chmod -R 755 runtime/
chmod -R 755 public/static/uploads/
```

### 5. 运行安装向导

浏览器访问 `http://your-domain.com/`，检测到 `install.lock` 文件不存在，则自动跳转到安装向导

- **步骤 1**：环境检测（自动检查 PHP 版本、扩展、目录权限）
- **步骤 2**：填写数据库信息 + 自定义后台管理路径
- **步骤 3**：安装完成，显示前后台地址与默认账号密码

> ⚠️ 安装完成后会自动生成 `install.lock` 文件。如需重新安装，请先删除该文件。

### 6. 访问站点

| 入口 | 地址 |
|------|------|
| 🏠 前台首页 | `http://your-domain.com` |
| ⚙️ 后台管理 | `http://your-domain.com/admin`（或自定义路径） |

### 7. 默认账号

| 项目 | 值 |
|------|-----|
| 用户名 | `admin` |
| 密码 | `admin123` |

> 🔐 **首次登录后请立即修改密码！**

---

## ✨ 功能清单

### 前台（8 个页面）

| 页面 | 功能描述 |
|------|----------|
| 🏠 首页 | Banner 轮播、服务入口、产品推荐、案例展示、新闻动态 |
| 📦 产品中心 | 分类筛选、产品搜索、产品详情、技术参数 |
| 🛠 服务支持 | 5 大服务模块、服务流程展示 |
| 📝 在线报修 | 表单提交、自动生成工单号 |
| 📂 客户案例 | 行业分类、案例详情 |
| 📰 新闻资讯 | 分类筛选、上一篇/下一篇导航 |
| 🏢 关于我们 | 公司简介、发展历程、资质展示、品牌墙 |
| 📞 联系我们 | 百度地图、在线留言 |

### 后台管理（11 个模块）

| 模块 | 功能描述 |
|------|----------|
| 📊 仪表盘 | 访问统计、工单统计、数据趋势图 |
| 👥 权限管理 | RBAC：管理员、角色、菜单权限、操作日志 |
| 📋 内容管理 | 栏目、文章、单页、Banner |
| 📦 产品管理 | 分类、产品 CRUD、批量操作 |
| 📂 案例管理 | 分类、案例 CRUD |
| 🔧 报修工单 | 状态流转、处理备注、Excel 导出 |
| 💬 留言管理 | 回复、已读/未读状态 |
| ⚙️ 系统设置 | 站点信息、联系方式、SEO 配置 |
| 📁 文件管理 | 上传管理、资源浏览 |
| 💾 数据备份 | 数据库备份与恢复 |
| 🗑 缓存管理 | 一键清除系统缓存 |

---

## 📁 项目结构

```
lylmew/
├── app/                        # 应用核心
│   ├── controller/
│   │   ├── admin/              # 后台控制器
│   │   └── *.php               # 前台控制器
│   ├── model/                  # 数据模型层
│   ├── middleware/             # 中间件（JWT 鉴权等）
│   └── validate/               # 表单验证器
├── config/                     # 全局配置（数据库、缓存、上传等）
├── database/
│   └── install.sql             # 安装时的数据库结构 + 初始数据
├── public/                     # 🟢 Web 根目录
│   ├── index.php               # 前端入口
│   ├── install.php             # 可视化安装向导
│   ├── static/                 # 前台静态资源（CSS/JS/图片/上传）
│   └── admin/                  # 后台静态资源
├── route/
│   └── app.php                 # 路由定义（支持自定义后台路径）
├── view/
│   ├── index/                  # 前台模板
│   └── admin/                  # 后台模板
├── extend/                     # 第三方扩展类
├── runtime/                    # 运行时文件（日志、缓存、编译模板）
├── vendor/                     # Composer 依赖
├── .env                        # 环境配置（数据库、后台路径等）
├── nginx.conf                  # Nginx 配置参考
├── composer.json               # Composer 依赖描述
├── install.lock                # 安装锁定文件（安装后自动生成）
└── README.md
```

---

## ❓ 常见问题

<details>
<summary>1. 安装后访问页面 404？</summary>

检查 Web 服务器是否配置了 URL 重写（伪静态）。Nginx 可参考项目根目录的 `nginx.conf`，Apache 确保 `mod_rewrite` 已启用。
</details>

<details>
<summary>2. 忘记后台路径怎么办？</summary>

查看项目根目录 `.env` 文件中的 `ADMIN_PATH` 字段即可找回。
</details>

<details>
<summary>3. 如何更换后台入口路径？</summary>

编辑 `.env` 文件，修改 `ADMIN_PATH` 为新的路径名称（不可使用前台路由如 `news`、`product` 等），保存后生效。
</details>

<details>
<summary>4. 如何重新安装？</summary>

删除项目根目录下的 `install.lock` 文件，重新访问 `/install.php` 即可。
</details>

---

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/amazing-feature`)
3. 提交修改 (`git commit -m 'Add amazing feature'`)
4. 推送分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

---

## 📄 License

[AGPL-3.0](LICENSE) © 六零同城企服

本软件遵循 **AGPL-3.0** 开源协议，任何基于本项目的修改、衍生或通过网络提供服务的行为，均须以相同协议公开全部源代码。如有商业授权或闭源使用需求，请联系作者另行授权。

---

## 🤖 AI 声明

本项目部分代码由 AI 辅助生成。由于 AI 生成内容可能存在缺陷或错误，**作者不对 AI 生成代码的准确性、完整性和安全性承担任何责任**，使用者应自行评估风险。如您对此有顾虑，请勿使用本项目。

---

<p align="center">
  <sub>Built with ❤️ using ThinkPHP 8 · Layui · Bootstrap 5</sub>
</p>
