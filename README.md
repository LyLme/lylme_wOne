# 六零同城企服 — 企业官网售后管理系统

基于 ThinkPHP 8 + Layui + Bootstrap 5 + MySQL 构建的同城企业售后服务平台，集企业官网展示、产品介绍、在线报修工单于一体。

## 技术栈

| 层级 | 技术 |
|------|------|
| 后端框架 | PHP 8.1+ / ThinkPHP 8.0 |
| 后台UI | Layui 2.9 |
| 前台UI | Bootstrap 5 + 自定义CSS |
| 数据库 | MySQL 8.0+ |
| 缓存 | Redis（可选）/ File |
| Web服务器 | Nginx |

## 项目结构

```
lylmew/
├── app/                    # 应用目录
│   ├── controller/         # 控制器
│   │   ├── admin/          # 后台控制器
│   │   └── *.php           # 前台控制器
│   ├── model/              # 数据模型
│   ├── middleware/         # 中间件
│   └── validate/           # 验证器
├── config/                 # 配置文件
├── database/               # 数据库文件
│   └── install.sql            # 完整数据库结构+初始数据
├── public/                 # 网站根目录
│   ├── index.php           # 入口文件
│   ├── static/             # 前台静态资源
│   │   ├── css/
│   │   ├── js/
│   │   ├── images/
│   │   └── uploads/
│   └── admin/              # 后台静态资源
├── route/                  # 路由定义
├── view/                   # 视图模板
│   ├── index/              # 前台模板
│   └── admin/              # 后台模板
├── vendor/                 # Composer依赖
├── runtime/                # 运行缓存/日志
├── .env                    # 环境配置
├── composer.json           # 依赖定义
└── README.md               # 本文件
```

## 快速部署

### 环境要求

- PHP >= 8.1
- MySQL >= 8.0
- Nginx >= 1.18

### PHP扩展要求

- PDO MySQL
- mbstring
- fileinfo
- openssl
- GD
- json
- xml
- curl


**7. 访问网站**

- 前台首页：http://your-domain.com
- 后台管理：http://your-domain.com/admin

## 默认管理员账户

- **用户名**：admin
- **密码**：admin123

首次登录后请立即修改密码！


## 功能清单

### 前台（8个页面）

- ✅ 首页（Banner轮播、服务入口、产品推荐、案例展示、新闻动态）
- ✅ 产品中心（分类筛选、产品搜索、产品详情、技术参数）
- ✅ 服务支持（5大服务模块、服务流程展示）
- ✅ 在线报修（表单提交、工单号生成）
- ✅ 客户案例（行业分类、案例详情）
- ✅ 新闻资讯（分类筛选、上一篇/下一篇）
- ✅ 关于我们（公司简介、发展历程、资质展示、品牌墙）
- ✅ 联系我们（百度地图、在线留言）

### 后台管理（15个模块）

- ✅ 仪表盘（访问统计、工单统计、趋势图）
- ✅ 权限管理（RBAC：管理员、角色、菜单、操作日志）
- ✅ 内容管理（栏目、文章、单页、Banner）
- ✅ 产品管理（分类、产品CRUD、批量操作）
- ✅ 案例管理（分类、案例CRUD）
- ✅ 报修工单（状态流转、处理备注、导出Excel）
- ✅ 留言管理（回复、已读/未读）
- ✅ 系统设置（站点信息、联系方式、SEO）
- ✅ 文件管理（上传管理）
- ✅ 数据备份（备份/恢复）
- ✅ 缓存管理

---

