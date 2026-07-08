SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `lylmew_admin_log`;
CREATE TABLE `lylmew_admin_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT '0' COMMENT '管理员ID',
  `admin_name` varchar(50) DEFAULT '' COMMENT '管理员名称',
  `action` varchar(50) DEFAULT '' COMMENT '操作类型',
  `content` varchar(255) DEFAULT '' COMMENT '操作内容',
  `ip` varchar(45) DEFAULT '' COMMENT 'IP地址',
  `user_agent` varchar(255) DEFAULT '' COMMENT '浏览器UA',
  `create_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员操作日志表';

DROP TABLE IF EXISTS `lylmew_admin_user`;
CREATE TABLE `lylmew_admin_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码hash',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1启用 0禁用',
  `role` tinyint(1) DEFAULT '1' COMMENT '角色：0超级管理员 1普通管理员 2编辑',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员用户表';

INSERT INTO `lylmew_admin_user` (`id`, `username`, `password`, `nickname`, `avatar`, `status`, `role`, `last_login_time`, `last_login_ip`, `create_time`, `update_time`) VALUES
(1, 'admin', '$2y$10$LcVuvcDhGsNw0KVxCMo96.WSuv8s4CFNsBAwYDAjLJvDcVThsDMPC', '超级管理员', '', 1, 0, '2026-07-06 20:08:42', '127.0.0.1', '2026-07-05 20:25:28', '2026-07-05 20:25:28'),
(2, 'lylme', '$2y$10$PAe.PThuoht9yqUJItaZ.ea12NKQWrBjAwP5ACIiHnCn8G7fArOvm', '六零', '', 0, 2, NULL, '', '2026-07-06 18:46:37', '2026-07-06 18:46:37');

DROP TABLE IF EXISTS `lylmew_article`;
CREATE TABLE `lylmew_article` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `category_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `type` varchar(20) NOT NULL DEFAULT 'news' COMMENT '类型: news=新闻, case=案例',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(500) DEFAULT '' COMMENT '列表封面图',
  `subtitle` varchar(200) NOT NULL DEFAULT '' COMMENT '副标题',
  `summary` varchar(500) DEFAULT '' COMMENT '摘要',
  `content` longtext COMMENT '内容',
  `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签(逗号分隔)',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=下线,1=发布',
  `seo_title` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表(新闻/案例)';

INSERT INTO `lylmew_article` (`id`, `category_id`, `type`, `title`, `image`, `subtitle`, `summary`, `content`, `cover_img`, `tags`, `view_count`, `sort`, `status`, `seo_title`, `seo_keywords`, `seo_description`, `create_time`, `update_time`) VALUES
(1, 28, 'news', '办公电脑日常维护保养小技巧', '', '', '让你的办公电脑始终保持流畅运行的10个实用技巧，延长设备使用寿命。', '<h3>硬件篇</h3><ol><li><b>定期清灰：</b>每6个月打开机箱清洁灰尘，特别是CPU风扇和电源风扇</li><li><b>散热检查：</b>确保电脑通风口不被遮挡，温度过高是蓝屏死机的主要原因</li><li><b>硬盘健康：</b>每月检查SSD/HDD健康状态，提前备份重要数据</li><li><b>线缆整理：</b>避免线缆缠绕勒紧，防止接口松动</li></ol><h3>软件篇</h3><ol><li><b>定期重启：</b>至少每周重启一次，释放内存缓存</li><li><b>卸载无用软件：</b>安装的软件越多电脑越慢</li><li><b>清理临时文件：</b>使用磁盘清理工具定期清理</li><li><b>保持更新：</b>Windows Update和安全软件保持最新</li></ol><h3>笔记本特别篇</h3><ul><li>避免在软面上使用（影响散热）</li><li>电池每月至少一次完整充放电</li><li>键盘膜定期清洁，避免异物进入</li></ul>', '', '', 6404, 9, 1, '', '', '', '2026-05-03 22:49:09', '2026-07-06 23:16:19');
INSERT INTO `lylmew_article` (`id`, `category_id`, `type`, `title`, `image`, `subtitle`, `summary`, `content`, `cover_img`, `tags`, `view_count`, `sort`, `status`, `seo_title`, `seo_keywords`, `seo_description`, `create_time`, `update_time`) VALUES
(2, 28, 'news', '硒鼓加粉vs换新：经济账怎么算？', '', '', '详细对比硒鼓加粉和更换新硒鼓的成本、质量、风险，帮你做出明智选择。', '<h3>加粉的缺点</h3><ol><li><b>打印品质下降：</b>加粉后常出现底灰、色浅、黑线等问题</li><li><b>容易漏粉：</b>密封不严导致碳粉泄漏，污染打印机内部</li><li><b>损坏设备风险：</b>劣质碳粉可能损坏感光鼓甚至定影组件</li><li><b>打印量缩水：</b>标称可加粉2-3次，实际通常第2次就出现各种问题</li></ol><h3>换新的优势</h3><ol><li>打印品质有保障</li><li>不会损坏打印机</li><li>有售后服务</li></ol><h3>成本对比</h3><table border=\"1\"><tbody><tr><th></th><th>原装硒鼓</th><th>通用硒鼓</th><th>加粉</th></tr><tr><td>单次成本</td><td>400-600元</td><td>100-200元</td><td>50-80元</td></tr><tr><td>打印量</td><td>3000页</td><td>2600页</td><td>1500-2000页</td></tr><tr><td>单页成本</td><td>0.16元</td><td>0.06元</td><td>0.03元</td></tr><tr><td>品质风险</td><td>无</td><td>低</td><td>中</td></tr></tbody></table><h3>建议</h3><p>日常办公推荐使用品牌通用硒鼓（如XX系列），性价比和品质兼顾。加粉适合对品质不敏感的草稿打印场景。</p>', '', '', 8209, 10, 1, '', '', '', '2026-04-26 22:49:09', '2026-07-06 22:53:54'),
(4, 30, 'news', '打印纸怎么选？70g和80g有什么区别？', '', '', '从纸张克重、白度、挺度三个维度教你挑选合适的办公打印纸。', '<h3>70g vs 80g 对比</h3><table border=\"1\"><tr><th></th><th>70g/㎡</th><th>80g/㎡</th></tr><tr><td>厚度</td><td>约0.09mm</td><td>约0.10mm</td></tr><tr><td>适用</td><td>日常草稿/内部文件</td><td>合同/报告/正式文件</td></tr><tr><td>双面打印</td><td>轻微透印</td><td>基本不透</td></tr><tr><td>价格</td><td>约18-22元/包</td><td>约25-30元/包</td></tr></table><h3>选购建议</h3><ul><li><b>日常办公：</b>80g为佳，双面打印不透，手感更佳</li><li><b>大批量草稿：</b>70g够用，成本更低</li><li><b>彩色打印：</b>必须80g以上，避免透印影响色彩</li><li><b>重要文件/合同：</b>推荐100g高白度，显档次</li></ul><h3>避坑</h3><p>不要买过于便宜的杂牌纸，容易卡纸、掉粉，损坏打印机搓纸轮和定影组件。</p>', '', '', 7504, 13, 1, '', '', '', '2026-04-05 22:49:09', '2026-07-06 22:51:50'),
(5, 21, 'case', '某市政府办公设备升级改造项目', '/static/uploads/20260706/230144_eb610502.png', '', '某市政府办公设备升级改造项目', '<li class=\"ybc-li-component ybc-li-component_ol\"><span class=\"ybc-li-component_content\"><div class=\"ybc-p\"><b>本项目作为“数字政府”建设的重要一环，不仅解决了硬件瓶颈，更推动了组织流程优化与人员数字素养提升。通过设备升级，市政府实现了从“被动响应”到“主动服务”的转变，为后续智慧大厅、移动政务、AI辅助决策等场景打下坚实基础。项目实施过程中，我们注重用户体验与平滑过渡，采用“分批替换+现场培训+驻场指导”模式，确保业务不中断、员工零抵触。</b></div></span></li>', '', '', 32, 0, 1, '', '', '', '2026-07-06 21:56:20', '2026-07-06 23:38:35'),
(6, 20, 'case', '某医院打印设备维保服务案例', '/static/uploads/20260706/231821_2837f664.jpg', '', '无摘要', '<p><br></p>', '', '', 14, 0, 1, '', '', '', '2026-07-06 23:18:58', '2026-07-06 23:46:05');

DROP TABLE IF EXISTS `lylmew_article_category`;
CREATE TABLE `lylmew_article_category` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `slug` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `type` varchar(20) NOT NULL DEFAULT 'news' COMMENT '类型:news=新闻,case=案例',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分类描述',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=显示',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表';

INSERT INTO `lylmew_article_category` (`id`, `name`, `slug`, `type`, `description`, `sort`, `status`, `create_time`, `update_time`) VALUES
(20, '企业办公', '', 'case', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(21, '政府机关', '', 'case', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(22, '学校教育', '', 'case', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(23, '金融保险', '', 'case', '', 4, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(24, '医疗健康', '', 'case', '', 5, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(25, '公司新闻', '', 'news', '', 6, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(26, '产品资讯', '', 'news', '', 7, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(27, '选购指南', '', 'news', '', 8, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(28, '维护技巧', '', 'news', '', 9, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(29, '行业方案', '', 'news', '', 10, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(30, '耗材百科', '', 'news', '', 11, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(31, '办公效率', '', 'news', '', 12, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09');

DROP TABLE IF EXISTS `lylmew_banner`;
CREATE TABLE `lylmew_banner` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `subtitle` varchar(200) NOT NULL DEFAULT '' COMMENT '副标题',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `link_url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=显示',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Banner表';

DROP TABLE IF EXISTS `lylmew_case_info`;
CREATE TABLE `lylmew_case_info` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `article_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章ID',
  `client_name` varchar(100) NOT NULL DEFAULT '' COMMENT '客户名称',
  `industry` varchar(50) NOT NULL DEFAULT '' COMMENT '行业类型',
  `devices` varchar(255) DEFAULT '' COMMENT '使用设备',
  `service_date` varchar(100) DEFAULT '' COMMENT '服务日期',
  `requirement` text COMMENT '需求背景',
  `solution` text COMMENT '解决方案',
  `result` text COMMENT '服务成果',
  `images` json DEFAULT NULL COMMENT '案例图片(JSON)',
  `cover` varchar(255) DEFAULT '' COMMENT '封面/背景图',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='案例扩展信息表';

INSERT INTO `lylmew_case_info` (`id`, `article_id`, `client_name`, `industry`, `devices`, `service_date`, `requirement`, `solution`, `result`, `images`, `cover`, `create_time`, `update_time`) VALUES
(24, 5, 'x市水利局', 'government', '戴尔 Latitude 7420 笔记本 + 华为 MateStation B515 台式机 + HP LaserJet Pro MFP M428fdw 多功能打印机 + 华为 IdeaHub S 86英寸智能会议平板', '2026年7月6日', '随着政务数字化转型加速，原办公设备老化严重，存在运行卡顿、软件兼容差、信息安全风险高、协作效率低等问题。市政府亟需通过设备升级提升办公效率、保障数据安全、支撑智慧政务应用（如电子公文、视频会议、数据共享平台等）。', '硬件更新：替换老旧终端，部署高性能国产化/信创兼容设备，支持Windows 11 、麒麟OS双系统。\n网络优化：升级千兆局域网+Wi-Fi 6覆盖，确保多设备并发稳定连接。\n安全加固：启用全盘加密、USB管控、终端准入审计，符合等保2.0三级要求。\n协同赋能：配置智能会议平板+云协作平台，实现无纸化办公与远程协同。\n运维保障：提供7*24小时远程支持+月度巡检+应急响应机制。', '设备平均开机时间从45秒降至8秒，办公效率提升60%\n实现100%终端安全合规，零重大安全事件\n会议响应速度提升70%，跨部门协作满意度达95%\n年节省纸张耗材成本约18万元，碳排放减少约12吨\n形成可复制的政府办公设备升级标准模板获上级单位推广', '[\"/static/uploads/20260706/230148_abe7cc9d.jpg\"]', '', '2026-07-06 21:56:20', '2026-07-06 23:37:56'),
(25, 6, '县医院', 'medical', '复印机', '', '', '', '', '[\"/static/uploads/20260706/231826_1a6c3949.png\"]', '', '2026-07-06 23:18:58', '2026-07-06 23:45:58');

DROP TABLE IF EXISTS `lylmew_config`;
CREATE TABLE `lylmew_config` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键',
  `value` mediumtext COMMENT '配置值（支持大段JSON/HTML）',
  `title` varchar(255) DEFAULT '' COMMENT '配置项显示名称',
  `group` varchar(50) DEFAULT 'base' COMMENT '分组标识',
  `type` varchar(20) NOT NULL DEFAULT 'text' COMMENT '字段类型:text,textarea,select,image',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

INSERT INTO `lylmew_config` (`id`, `key`, `value`, `title`, `group`, `type`, `sort`, `create_time`, `update_time`) VALUES
(1, 'company_name', '太阴星云上六零科技有限公司', 'company_name', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(2, 'company_name_short', '云上六零', 'company_name_short', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(3, 'company_name_en', 'LyLme Technology', 'company_name_en', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(4, 'company_slogan', '专业办公设备服务提供商', 'company_slogan', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(5, 'company_keywords', '办公设备,打印机维修,打印机维保,耗材供应', 'company_keywords', 'seo', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(6, 'company_desc', '太阴星云上六零科技有限公司，专业办公设备服务提供商。提供设备销售、售后维修、维保签约、耗材供应等一站式服务。', 'company_desc', 'seo', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(7, 'company_founded', '2024', 'company_founded', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(8, 'company_city', '月球', 'company_city', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(9, 'company_icon', '/static/images/logo.png', 'company_icon', 'base', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(10, 'map_src', '', 'map_src', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:57'),
(11, 'contact_phone', '+3474-8888888', 'contact_phone', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(12, 'contact_address', '月球·静海基地第3居住舱C区207室', 'contact_address', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(13, 'contact_email', 'service@service.com', 'contact_email', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(14, 'contact_hours', '周一至周五 8:00-18:00', 'contact_hours', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(15, 'icp_number', '月ICP备20000000号', 'icp_number', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(16, 'wechat_qrcode', '/static/images/wxmp.jpg', 'wechat_qrcode', 'contact', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:59:28'),
(17, 'meta_description', '太阴星云上六零科技有限公司，专业办公设备服务提供商', 'meta_description', 'seo', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:54'),
(18, 'meta_keywords', '办公设备,打印机维修,打印机维保,耗材供应', 'meta_keywords', 'seo', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:52:54'),
(19, 'nav_menus', '[{\"name\":\"首页\",\"url\":\"/\",\"active\":\"home\"},{\"name\":\"产品中心\",\"url\":\"/products\",\"active\":\"products\"},{\"name\":\"服务支持\",\"url\":\"/services\",\"active\":\"services\"},{\"name\":\"客户案例\",\"url\":\"/cases\",\"active\":\"cases\"},{\"name\":\"新闻资讯\",\"url\":\"/news\",\"active\":\"news\"},{\"name\":\"关于我们\",\"url\":\"/about\",\"active\":\"about\"},{\"name\":\"联系我们\",\"url\":\"/contact\",\"active\":\"contact\"}]', 'nav_menus', 'nav', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:06'),
(20, 'nav_controller_map', '{\n    \"index\": \"home\",\n    \"product\": \"products\",\n    \"service\": \"services\",\n    \"cases\": \"cases\",\n    \"article\": \"news\",\n    \"about\": \"about\",\n    \"contact\": \"contact\",\n    \"repair\": \"services\"\n}', 'nav_controller_map', 'nav', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:06'),
(21, 'home_core_data', '[{\"number\":\"300\",\"unit\":\"+\",\"label\":\"服务客户\"},{\"number\":\"10\",\"unit\":\"年\",\"label\":\"行业经验\"},{\"number\":\"10\",\"unit\":\"+\",\"label\":\"专业工程师\"},{\"number\":\" \",\"unit\":\"北京\",\"label\":\"覆盖全城\"}]', 'home_core_data', 'home', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:57:36'),
(22, 'home_services', '[{\"icon\":\"fa-print\",\"title\":\"设备销售\",\"desc\":\"提供各类办公打印设备销售，正品保障，价格优惠\",\"url\":\"/services/device-sales\",\"color\":\"#1A5FDC\"},{\"icon\":\"fa-tools\",\"title\":\"售后维修\",\"desc\":\"专业维修团队，快速响应，上门服务\",\"url\":\"/services/after-sales\",\"color\":\"#00B4D8\"},{\"icon\":\"fa-shield-alt\",\"title\":\"维保签约\",\"desc\":\"定制化维保方案，降低设备维护成本\",\"url\":\"/services/maintenance\",\"color\":\"#10B981\"},{\"icon\":\"fa-boxes\",\"title\":\"耗材供应\",\"desc\":\"原装耗材供应，品类齐全，快速配送\",\"url\":\"/services/supplies\",\"color\":\"#F59E0B\"}]', 'home_services', 'home', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:57:36'),
(23, 'home_advantages', '[{\"icon\":\"fa-truck\",\"title\":\"上门服务\",\"desc\":\"全城提供上门服务，省时省心\"},{\"icon\":\"fa-bolt\",\"title\":\"快速响应\",\"desc\":\"接到报修10分钟内响应，24小时内上门\"},{\"icon\":\"fa-user-graduate\",\"title\":\"专业技术\",\"desc\":\"工程师均经过专业培训，持证上岗\"},{\"icon\":\"fa-check-circle\",\"title\":\"正品保障\",\"desc\":\"所有设备耗材均为原装正品，假一赔十\"}]', 'home_advantages', 'home', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:57:36'),
(24, 'home_about_text', '专注于办公设备销售、维修、维保及耗材供应的综合性服务企业。公司坐落于美丽的太阴星，多年来致力于为政府机关、医疗机构、企业单位等提供专业的办公设备解决方案。', 'home_about_text', 'home', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:57:36'),
(25, 'service_flow_steps', '[{\"icon\":\"fa-edit\",\"title\":\"客户提交\",\"desc\":\"在线提交服务需求或拨打服务热线\"},{\"icon\":\"fa-phone\",\"title\":\"客服响应\",\"desc\":\"客服10分钟内响应，了解详细需求\"},{\"icon\":\"fa-calendar-check\",\"title\":\"工程师预约\",\"desc\":\"安排专业工程师，预约上门时间\"},{\"icon\":\"fa-tools\",\"title\":\"上门服务\",\"desc\":\"工程师按时上门，专业检测维修\"},{\"icon\":\"fa-star\",\"title\":\"服务回访\",\"desc\":\"服务完成后回访，确保满意度\"}]', 'service_flow_steps', 'service', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:11'),
(26, 'about_intro', '        <p>成立于:company_founded年，是一家专注于办公设备销售、维修、维保及耗材供应的综合性服务企业。</p>\n        <p>公司坐落于美丽的:company_city，多年来致力于为政府机关、教育机构、企业单位等提供专业的办公设备解决方案。</p>\n        <p>我们拥有一支经验丰富、技术精湛的服务团队，以\"专业、高效、诚信\"为服务宗旨，赢得了广大客户的信赖与支持。</p>\n        <p>未来，我们将继续秉承\"客户至上、追求卓越\"的理念，不断提升服务品质，与您共创美好未来</p>\n    ', 'about_intro', 'about', 'text', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:15'),
(27, 'about_timeline', '[{\"year\":\"2024\",\"title\":\"公司成立\",\"desc\":\"公司正式成立，专注于办公设备销售与维修服务。\"},{\"year\":\"2024\",\"title\":\"业务拓展\",\"desc\":\"拓展维保签约服务，与多家企业建立长期合作关系。\"},{\"year\":\"2025\",\"title\":\"团队壮大\",\"desc\":\"专业工程师团队扩展至20余人，服务覆盖全境。\"},{\"year\":\"2025\",\"title\":\"数字化转型\",\"desc\":\"上线在线报修系统，实现服务流程数字化管理。\"},{\"year\":\"2026\",\"title\":\"持续创新\",\"desc\":\"不断优化服务体系，引入更多品牌合作，为客户创造价值。\"}]', 'about_timeline', 'about', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:15'),
(28, 'about_qualifications', '[{\"name\":\"营业执照\",\"icon\":\"fa-file-alt\"},{\"name\":\"质量管理体系认证\",\"icon\":\"fa-certificate\"},{\"name\":\"售后服务认证\",\"icon\":\"fa-award\"},{\"name\":\"品牌授权证书\",\"icon\":\"fa-medal\"}]', 'about_qualifications', 'about', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:15'),
(29, 'about_team', '[{\"name\":\"张经理\",\"title\":\"技术总监\",\"desc\":\"15年办公设备维修经验，拥有多项品牌技术认证。\",\"avatar\":\"\"},{\"name\":\"李工程师\",\"title\":\"高级维修工程师\",\"desc\":\"擅长各类打印机、复印机故障诊断与维修。\",\"avatar\":\"\"},{\"name\":\"王工程师\",\"title\":\"客户服务经理\",\"desc\":\"负责客户关系维护，确保服务质量与客户满意度。\",\"avatar\":\"\"},{\"name\":\"赵工程师\",\"title\":\"耗材供应主管\",\"desc\":\"负责耗材库存管理与快速配送，确保及时供应。\",\"avatar\":\"\"}]', 'about_team', 'about', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:15'),
(30, 'about_bidding', '[{\"title\":\"政府采购供应商\",\"desc\":\"入围多个地市政府采购办公设备协议供应商名录，具备参与政府采购招投标项目资质。\",\"icon\":\"fa-landmark\"},{\"title\":\"企业采购合作\",\"desc\":\"与多家大中型企业建立长期采购合作关系，具备丰富的企业招投标项目经验。\",\"icon\":\"fa-building\"},{\"title\":\"教育系统采购\",\"desc\":\"为各级学校、教育机构提供办公设备集中采购与维保专项服务。\",\"icon\":\"fa-graduation-cap\"},{\"title\":\"医疗系统合作\",\"desc\":\"具备医疗系统办公设备招投标资格，服务多家医院及卫生机构。\",\"icon\":\"fa-hospital\"}]', 'about_bidding', 'about', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:15'),
(31, 'about_partners', '[{\"name\":\"华为HUAWEI\",\"logo\":\"/static/images/partners/huawei.png\"},{\"name\":\"麒麟\",\"logo\":\"/static/images/partners/kylin.png\"},{\"name\":\"统信\",\"logo\":\"/static/images/partners/uos.png\"},{\"name\":\"长城\",\"logo\":\"/static/images/partners/great-wall.png\"},{\"name\":\"新华三\",\"logo\":\"/static/images/partners/h3c.png\"},{\"name\":\"海康威视\",\"logo\":\"/static/images/partners/hikvision.png\"},{\"name\":\"奔图\",\"logo\":\"/static/images/partners/pantum.png\"},{\"name\":\"惠普\",\"logo\":\"/static/images/partners/hp.png\"},{\"name\":\"佳能\",\"logo\":\"/static/images/partners/canon.png\"},{\"name\":\"爱普生\",\"logo\":\"/static/images/partners/epson.png\"},{\"name\":\"兄弟\",\"logo\":\"/static/images/partners/brother.png\"},{\"name\":\"联想\",\"logo\":\"/static/images/partners/lenovo.png\"},{\"name\":\"三星\",\"logo\":\"/static/images/partners/samsung.png\"},{\"name\":\"得力\",\"logo\":\"/static/images/partners/deli.png\"},{\"name\":\"理光\",\"logo\":\"/static/images/partners/ricoh.png\"},{\"name\":\"华硕\",\"logo\":\"/static/images/partners/asus.png\"},{\"name\":\"宏碁\",\"logo\":\"/static/images/partners/acer.png\"},{\"name\":\"戴尔\",\"logo\":\"/static/images/partners/dell.png\"}]', 'about_partners', 'about', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-05 21:50:38'),
(32, 'case_industries', '{\"all\":\"全部\",\"government\":\"政府机关\",\"education\":\"教育机构\",\"enterprise\":\"企业单位\",\"medical\":\"医疗机构\",\"finance\":\"金融机构\",\"other\":\"其他行业\"}', 'case_industries', 'footer', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:18'),
(33, 'footer_service_links', '[{\"name\":\"设备销售\",\"url\":\"/services/device-sales\"},{\"name\":\"售后维修\",\"url\":\"/services/after-sales\"},{\"name\":\"维保签约\",\"url\":\"/services/maintenance\"},{\"name\":\"耗材供应\",\"url\":\"/services/supplies\"},{\"name\":\"技术咨询\",\"url\":\"/services/tech-consult\"},{\"name\":\"在线报修\",\"url\":\"/repair\"}]', 'footer_service_links', 'footer', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:18'),
(34, 'footer_quick_links', '[{\"name\":\"友情链接\",\"url\":\"https://hao.lylme.com/\"}]', 'footer_quick_links', 'footer', 'textarea', 0, '2026-07-05 20:50:15', '2026-07-06 22:53:18'),
(36, 'files', '', 'files', 'base', 'text', 0, '2026-07-05 21:50:28', '2026-07-06 22:53:22'),
(37, 'service_types', '{\"device-sales\":{\"name\":\"设备销售\",\"icon\":\"fa-cogs\",\"desc\":\"提供打印机、电脑、投影仪等全品类办公设备销售，主流品牌代理，品质保障，价格优惠。\",\"color\":\"#1A5FDC\",\"features\":[\"正品行货\",\"厂价直供\",\"免费配送\",\"上门安装\",\"质量三包\"]},\"after-sales\":{\"name\":\"售后维修\",\"icon\":\"fa-wrench\",\"desc\":\"专业工程师团队提供各类办公设备维修保养服务，快速响应，上门服务，让您省心省力。\",\"color\":\"#00B4D8\",\"features\":[\"快速响应\",\"上门服务\",\"原厂配件\",\"质量保证\",\"定期巡检\"]},\"maintenance\":{\"name\":\"定期维保\",\"icon\":\"fa-calendar-check\",\"desc\":\"为企业提供办公设备定期维护保养服务，预防故障发生，延长设备寿命，降低总体拥有成本。\",\"color\":\"#10B981\",\"features\":[\"定期巡检\",\"预防维护\",\"耗材管理\",\"设备档案\",\"成本优化\"]},\"supplies\":{\"name\":\"耗材供应\",\"icon\":\"fa-boxes\",\"desc\":\"提供硒鼓、墨盒、打印纸等全系列办公耗材，原装正品，量大从优，支持定期配送。\",\"color\":\"#F59E0B\",\"features\":[\"原装正品\",\"品类齐全\",\"价格实惠\",\"快速配送\",\"定期供应\"]}}', 'service_types', 'service', 'textarea', 0, '2026-07-05 23:38:08', '2026-07-06 22:53:11'),
(38, 'service_detail_content', '{\"device-sales\":{\"intro\":\"我们代理多个国内外知名品牌办公设备，涵盖打印机、复印机、电脑、投影仪、会议平板等全品类。凭借与厂商的深度合作，我们能为客户提供最具竞争力的价格和最完善的售后服务。\",\"sections\":[{\"title\":\"产品品类\",\"items\":[{\"icon\":\"fa-print\",\"title\":\"打印设备\",\"desc\":\"激光打印机、喷墨打印机、针式打印机、标签打印机等\"},{\"icon\":\"fa-copy\",\"title\":\"复印复合机\",\"desc\":\"A3/A4黑白及彩色数码复合机，满足不同规模企业需求\"},{\"icon\":\"fa-desktop\",\"title\":\"电脑设备\",\"desc\":\"台式电脑、笔记本电脑、一体机电脑，正版系统预装\"},{\"icon\":\"fa-projector\",\"title\":\"投影设备\",\"desc\":\"商务投影仪、激光投影、会议平板，多种场景覆盖\"},{\"icon\":\"fa-network-wired\",\"title\":\"网络设备\",\"desc\":\"路由器、交换机、无线AP，企业级网络解决方案\"},{\"icon\":\"fa-shield-alt\",\"title\":\"监控安防\",\"desc\":\"网络摄像头、NVR录像机，POE供电方案\"}]},{\"title\":\"服务承诺\",\"items\":[{\"icon\":\"fa-check-circle\",\"title\":\"正品行货\",\"desc\":\"所有设备均为原厂正品，支持官方验证\"},{\"icon\":\"fa-tags\",\"title\":\"价格优惠\",\"desc\":\"一级代理价格，批量采购更享折扣\"},{\"icon\":\"fa-shipping-fast\",\"title\":\"免费配送\",\"desc\":\"市区免费送货上门，快速交付\"},{\"icon\":\"fa-headset\",\"title\":\"售后无忧\",\"desc\":\"提供安装调试和使用培训服务\"}]}]},\"after-sales\":{\"intro\":\"我们拥有经验丰富的专业维修工程师团队，配备完善的检测设备和充足的备件库存，能够快速诊断并解决各类办公设备故障，最大限度减少设备停机对您工作的影响。\",\"sections\":[{\"title\":\"维修服务范围\",\"items\":[{\"icon\":\"fa-print\",\"title\":\"打印机维修\",\"desc\":\"卡纸、打印模糊、无法识别硒鼓、定影故障等各类问题\"},{\"icon\":\"fa-copy\",\"title\":\"复印机维修\",\"desc\":\"复印效果差、卡纸频繁、扫描故障、定影器更换等\"},{\"icon\":\"fa-desktop\",\"title\":\"电脑维修\",\"desc\":\"系统故障、硬件更换、数据恢复、蓝屏死机等问题\"},{\"icon\":\"fa-projector\",\"title\":\"投影仪维修\",\"desc\":\"灯泡更换、色彩异常、无法开机、画面模糊等\"}]},{\"title\":\"服务流程\",\"items\":[{\"icon\":\"fa-phone-alt\",\"title\":\"电话报修\",\"desc\":\"拨打服务热线或在线提交报修申请\"},{\"icon\":\"fa-user-check\",\"title\":\"工程师响应\",\"desc\":\"2小时内电话回访，确认故障现象\"},{\"icon\":\"fa-tools\",\"title\":\"上门维修\",\"desc\":\"携带备件上门检测维修\"},{\"icon\":\"fa-clipboard-check\",\"title\":\"验收回访\",\"desc\":\"维修完成后48小时回访确认\"}]}]},\"maintenance\":{\"intro\":\"预防胜于治疗。我们为企业客户提供定制化的设备定期维护保养方案，通过定期巡检和保养，有效降低设备故障率，延长设备使用寿命，帮助企业降低总体办公成本。\",\"sections\":[{\"title\":\"维保方案\",\"items\":[{\"icon\":\"fa-calendar-alt\",\"title\":\"月度巡检\",\"desc\":\"每月一次全面设备检查，清洁保养\"},{\"icon\":\"fa-file-alt\",\"title\":\"设备档案\",\"desc\":\"为每台设备建立维护档案，跟踪历史记录\"},{\"icon\":\"fa-box\",\"title\":\"耗材管理\",\"desc\":\"监控耗材余量，提前提醒更换，自动配送\"},{\"icon\":\"fa-chart-line\",\"title\":\"成本报告\",\"desc\":\"定期生成打印成本分析报告，持续优化\"}]},{\"title\":\"服务优势\",\"items\":[{\"icon\":\"fa-clock\",\"title\":\"降低停机\",\"desc\":\"预防性维护可减少80%的突发故障\"},{\"icon\":\"fa-money-bill-wave\",\"title\":\"成本可控\",\"desc\":\"固定年费模式，预算更可控\"},{\"icon\":\"fa-star\",\"title\":\"延长寿命\",\"desc\":\"科学保养可延长设备寿命30%-50%\"}]}]},\"supplies\":{\"intro\":\"我们建立完善的耗材供应链体系，提供硒鼓、墨盒、碳粉、打印纸、碳带等全品类办公耗材。支持定期配送和企业集采，帮助客户降低采购成本和管理成本。\",\"sections\":[{\"title\":\"耗材品类\",\"items\":[{\"icon\":\"fa-dot-circle\",\"title\":\"硒鼓/粉盒\",\"desc\":\"原装及优质兼容硒鼓，各主流品牌型号齐全\"},{\"icon\":\"fa-tint\",\"title\":\"墨盒/墨水\",\"desc\":\"彩色/黑白墨盒，染料/颜料墨水\"},{\"icon\":\"fa-file\",\"title\":\"打印纸\",\"desc\":\"A3/A4复印纸、相纸、彩喷纸等各类纸张\"},{\"icon\":\"fa-tape\",\"title\":\"色带/碳带\",\"desc\":\"针式打印机色带、条码机碳带\"}]},{\"title\":\"采购优势\",\"items\":[{\"icon\":\"fa-truck\",\"title\":\"快速响应\",\"desc\":\"下单后24小时内送达\"},{\"icon\":\"fa-thumbs-up\",\"title\":\"品质保证\",\"desc\":\"原装耗材支持官方验证，兼容耗材严选供应商\"},{\"icon\":\"fa-percent\",\"title\":\"量大优惠\",\"desc\":\"企业集采更享阶梯价格优惠\"}]}]}}', 'service_detail_content', 'service', 'textarea', 0, '2026-07-05 23:38:08', '2026-07-06 22:53:11'),
(39, 'notification_enabled', '1', 'notification_enabled', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(40, 'notification_dingtalk_webhook', 'https://oapi.dingtalk.com/robot/send?access_token=xxxxxxxxxx', 'notification_dingtalk_webhook', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(41, 'notification_dingtalk_secret', 'SECxxxxxxxxxx', 'notification_dingtalk_secret', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(42, 'notification_email_host', 'smtp.qq.com', 'notification_email_host', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(43, 'notification_email_port', '25', 'notification_email_port', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(44, 'notification_email_user', '123456@qq.com', 'notification_email_user', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(45, 'notification_email_pass', '9Jj4xpdZ9hzxFkfB', 'notification_email_pass', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(46, 'notification_email_from', '上云六零', 'notification_email_from', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(47, 'notification_email_to', '123456@qq.com', 'notification_email_to', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(48, 'notification_wecom_webhook', 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=xxxxxxxxxx', 'notification_wecom_webhook', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(49, 'notification_template_repair', '## [报修] 新报修工单\n\n**工单号**：{order_no}\n\n**联系人**：{client_name}\n\n**电　话**：{phone}\n\n**单　位**：{company}\n\n**地　址**：{address}\n\n**故障描述**：{description}\n\n**提交时间**：{create_time}', 'notification_template_repair', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(50, 'notification_template_message', '## [留言] 新留言通知\n\n**姓　名**：{name}\n\n**电　话**：{phone}\n\n**内　容**：{content}\n\n**时　间**：{create_time}', 'notification_template_message', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(51, 'notification_channels', '[\"dingtalk\",\"wecom\"]', 'notification_channels', 'base', 'text', 0, '2026-07-06 19:12:08', '2026-07-06 22:53:22'),
(52, 'notification_email_template_repair', '<table style=\"width:100%;border-collapse:collapse;font-size:14px;\">\n  <tbody><tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;width:90px;color:#666;\">工单号</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\"><strong>{order_no}</strong></td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">联系人</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{client_name}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">电话</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{phone}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">单位</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{company}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">地址</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{address}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">故障描述</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{description}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;color:#666;\">提交时间</td><td style=\"padding:8px 12px;\">{create_time}</td></tr>\n</tbody></table>', 'notification_email_template_repair', 'base', 'textarea', 0, '2026-07-06 19:29:44', '2026-07-06 22:53:22'),
(53, 'notification_email_template_message', '<table style=\"width:100%;border-collapse:collapse;font-size:14px;\">\n  <tbody><tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;width:60px;color:#666;\">姓名</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{name}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">电话</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{phone}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;border-bottom:1px solid #eee;color:#666;\">内容</td><td style=\"padding:8px 12px;border-bottom:1px solid #eee;\">{content}</td></tr>\n  <tr><td style=\"padding:8px 12px;background:#f9f9f9;color:#666;\">时间</td><td style=\"padding:8px 12px;\">{create_time}</td></tr>\n</tbody></table>', 'notification_email_template_message', 'base', 'textarea', 0, '2026-07-06 19:29:44', '2026-07-06 22:53:22'),
(54, 'notification_email_encryption', '', 'notification_email_encryption', 'base', 'text', 0, '2026-07-06 20:32:18', '2026-07-06 22:53:22');

DROP TABLE IF EXISTS `lylmew_contact_info`;
CREATE TABLE `lylmew_contact_info` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '联系方式类型(手机号/微信/QQ/邮件等)',
  `value` varchar(200) NOT NULL DEFAULT '' COMMENT '联系方式(账号/手机号/座机)',
  `contact_person` varchar(50) DEFAULT '' COMMENT '联系人称呼',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公司联系方式表';

INSERT INTO `lylmew_contact_info` (`id`, `type`, `value`, `contact_person`, `sort`, `create_time`, `update_time`) VALUES
(66, '手机号', '13800000000', '客服部', 1, '2026-07-06 22:59:28', '2026-07-06 22:59:28'),
(67, '座机', '0755-8888888', '前台', 2, '2026-07-06 22:59:28', '2026-07-06 22:59:28'),
(68, '微信', 'lylme_tech', '商务经理', 3, '2026-07-06 22:59:28', '2026-07-06 22:59:28'),
(69, 'QQ', '88888888', '技术支持', 4, '2026-07-06 22:59:28', '2026-07-06 22:59:28'),
(70, '邮件', 'service@example.com', '', 5, '2026-07-06 22:59:28', '2026-07-06 22:59:28'),
(71, '钉钉', 'ding88888888', '钉钉客服', 6, '2026-07-06 22:59:28', '2026-07-06 22:59:28');

DROP TABLE IF EXISTS `lylmew_message`;
CREATE TABLE `lylmew_message` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `visitor_id` varchar(64) DEFAULT '' COMMENT '浏览器访客标识',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '姓名',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `contact` varchar(100) NOT NULL DEFAULT '' COMMENT '联系方式',
  `content` text COMMENT '留言内容',
  `source` varchar(50) NOT NULL DEFAULT 'contact' COMMENT '来源:contact=联系页,repair=报修页',
  `is_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读:0=未读,1=已读',
  `is_replied` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已回复:0=未回复,1=已回复',
  `reply` text COMMENT '回复内容',
  `reply_admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复管理员ID',
  `reply_time` datetime DEFAULT NULL COMMENT '回复时间',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '提交IP',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='留言表';

DROP TABLE IF EXISTS `lylmew_message_reply`;
CREATE TABLE `lylmew_message_reply` (
  `id` int(10) UNSIGNED NOT NULL,
  `message_id` int(10) UNSIGNED NOT NULL COMMENT '所属留言ID',
  `sender_type` enum('user','admin') NOT NULL DEFAULT 'user' COMMENT '发送者类型',
  `content` text NOT NULL COMMENT '回复内容',
  `admin_id` int(10) UNSIGNED DEFAULT '0' COMMENT '管理员ID（sender_type=admin时有效）',
  `create_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='留言回复会话表';

DROP TABLE IF EXISTS `lylmew_partner`;
CREATE TABLE `lylmew_partner` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '合作伙伴名称',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT 'Logo图片',
  `url` varchar(255) DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=显示',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='合作伙伴表';

INSERT INTO `lylmew_partner` (`id`, `name`, `logo`, `url`, `link_url`, `sort`, `status`, `create_time`, `update_time`) VALUES
(1, '华为', '/static/images/partners/huawei.png', '', '', 1, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(2, '麒麟', '/static/images/partners/kylin.png', '', '', 2, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(3, '统信', '/static/images/partners/uos.png', '', '', 3, 1, '2026-07-05 21:30:24', '2026-07-05 22:16:24'),
(4, '长城', '/static/images/partners/great-wall.png', '', '', 4, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(5, '新华三', '/static/images/partners/h3c.png', '', '', 5, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(6, '海康威视', '/static/images/partners/hikvision.png', '', '', 6, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(7, '奔图', '/static/images/partners/pantum.png', '', '', 7, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(8, '惠普', '/static/images/partners/hp.png', '', '', 8, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(9, '佳能', '/static/images/partners/canon.png', '', '', 9, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(10, '爱普生', '/static/images/partners/epson.png', '', '', 10, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(11, '兄弟', '/static/images/partners/brother.png', '', '', 11, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(12, '联想', '/static/images/partners/lenovo.png', '', '', 12, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(13, '三星', '/static/images/partners/samsung.png', '', '', 13, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(14, '得力', '/static/images/partners/deli.png', '', '', 14, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(15, '理光', '/static/images/partners/ricoh.png', '', '', 15, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(16, '华硕', '/static/images/partners/asus.png', '', '', 16, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(17, '宏碁', '/static/images/partners/acer.png', '', '', 17, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24'),
(18, '戴尔', '/static/images/partners/dell.png', '', '', 18, 1, '2026-07-05 21:30:24', '2026-07-05 21:30:24');

DROP TABLE IF EXISTS `lylmew_product`;
CREATE TABLE `lylmew_product` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `category_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '产品名称',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '产品型号',
  `brand` varchar(100) NOT NULL DEFAULT '' COMMENT '品牌',
  `cover_img` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `imgs` json DEFAULT NULL COMMENT '轮播图集(JSON)',
  `params` json DEFAULT NULL COMMENT '技术参数(JSON)',
  `description` text COMMENT '产品描述',
  `summary` varchar(500) DEFAULT '' COMMENT '摘要',
  `image` varchar(500) DEFAULT '' COMMENT '产品图片',
  `scene` text COMMENT '适用场景',
  `advantage` text COMMENT '政企采购优势',
  `price_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '价格类型:0=询价,1=价格区间',
  `price_min` decimal(10,2) DEFAULT NULL COMMENT '最低价格',
  `price_max` decimal(10,2) DEFAULT NULL COMMENT '最高价格',
  `is_hot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否热门:0=否,1=是',
  `is_recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐:0=否,1=是',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=下架,1=上架',
  `seo_title` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品表';

INSERT INTO `lylmew_product` (`id`, `category_id`, `name`, `model`, `brand`, `cover_img`, `imgs`, `params`, `description`, `summary`, `image`, `scene`, `advantage`, `price_type`, `price_min`, `price_max`, `is_hot`, `is_recommend`, `view_count`, `sort`, `status`, `seo_title`, `seo_keywords`, `seo_description`, `create_time`, `update_time`) VALUES
(1, 3, 'XX IJ800 彩色喷墨打印机', 'BP-IJ800', '', '', '[]', '[]', 'pb彩色喷墨打印机/b/pp家庭/学生入门级彩喷打印机。4色墨水系统，打印成本低。适合日常文档、作业、照片打印。小巧机身，轻松摆放。/p', '家庭/学生入门级彩喷打印机。4色墨水系统，打印成本低。适合日常文档、作业、照片打印。小巧机身，轻松摆放', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '399.00', '599.00', 0, 0, 3608, 5, 1, '', '', '', '2026-06-30 22:49:09', '2026-07-06 22:51:41'),
(2, 12, 'XX CX450 A3彩色数码复合机', 'BP-CX450', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '{\"屏幕\": \"10.1英寸触控屏\", \"幅面\": \"A3\", \"扫描\": \"270页/分钟双面同步\", \"进纸\": \"标配3200页\", \"分辨率\": \"1200×1200dpi\", \"打印速度\": \"彩色45页/黑白45页\"}', '<h3>XX BP-CX450 A3彩色数码复合机</h3><p>旗舰级彩色数码复合机，45页/分钟高速彩色输出。270页/分钟超高速扫描，适合海量文档数字化。10.1英寸大屏，操作如平板般流畅。</p><h4>行业方案：</h4><ul><li>设计院：XX/CAD高清输出</li><li>广告公司：彩色打样校对</li><li>学校/政府：试卷/文件批量印刷</li><li>房产中介：户型图/宣传册打印</li></ul>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 0, '0.00', '0.00', 0, 1, 2200, 14, 1, '', '', '', '2026-06-21 22:49:09', '2026-07-05 22:49:09'),
(3, 14, 'XX B660 高性能台式电脑（i7/32G/1TB）', 'BP-B660', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '{\"CPU\": \"Intel 酷睿i7-13700\", \"内存\": \"32GB DDR4 3200MHz\", \"显卡\": \"独立GTX 1650 4GB\", \"硬盘\": \"1TB NVMe SSD\", \"系统\": \"Windows 11专业版\"}', '<h3>XX BP-B660 高性能台式电脑</h3><p>为设计师、工程师打造的高性能工作站。i7处理器+32GB内存+独立显卡，轻松驾驭CAD、PS、AI等专业软件。1TB固态硬盘海量存储。</p><h4>适用人群：</h4><ul><li>建筑/机械设计师（CAD/SolidWorks）</li><li>平面设计师（PS/AI/ID）</li><li>视频剪辑师</li><li>数据分析师</li></ul>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '5699.00', '7299.00', 0, 1, 3803, 16, 1, '', '', '', '2026-06-19 22:49:09', '2026-07-06 23:00:10'),
(4, 25, 'XX A4复印纸 80g 5包/箱', 'BP-P80', '', '', '[]', '{\"克重\": \"80g/平方米\"}', 'h4XX A4复印纸 80g/㎡/h4p500张/包×5包=2500张。高白度、纸张挺括、过机顺畅不卡纸。适用激光/喷墨打印和复印。/p', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '99.00', '149.00', 1, 1, 11007, 29, 1, '', '', '', '2026-06-06 22:49:09', '2026-07-06 22:59:50'),
(5, 26, 'XX 碳带 110mm×300m 树脂基', 'BP-RB110', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '[]', '<h4>树脂基碳带 110mm×300m</h4><p>条码/标签打印机专用碳带。树脂基配方，耐刮擦耐酒精，打印清晰不掉色。适用于铜版纸、合成纸标签。</p>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '35.00', '55.00', 1, 0, 4503, 31, 1, '', '', '', '2026-06-04 22:49:09', '2026-07-06 23:00:15'),
(6, 29, 'XX RG1200 企业级千兆路由器', 'BP-RG1200', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '{\"安全\": \"防火墙/IPSec VPN\", \"端口\": \"WAN×1/LAN×4\", \"规格\": \"千兆双频Wi-Fi 6\", \"速率\": \"AX3000\", \"带机量\": \"200+终端\"}', '<h3>BP-RG1200 企业千兆路由器</h3><p>中小企业网络中枢，Wi-Fi 6 AX3000速率，支持200+终端同时在线。内置企业级防火墙和VPN功能，满足远程办公和分支机构互联需求。</p>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '599.00', '899.00', 1, 1, 4200, 35, 1, '', '', '', '2026-05-31 22:49:09', '2026-07-05 22:49:09'),
(7, 30, 'XX IPC400 400万POE网络摄像机', 'BP-IPC400', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '{\"供电\": \"POE/DC12V\", \"像素\": \"400万（2560×1440）\", \"功能\": \"人形检测/移动侦测\", \"夜视\": \"红外30米\", \"镜头\": \"2.8mm/4mm/6mm可选\", \"防护\": \"IP67防水\"}', '<h3>BP-IPC400 400万POE摄像机</h3><p>办公场所监控必备。400万超清画质，30米红外夜视，IP67防水可室内外通用。支持POE供电，一根网线同时传输数据和电力，安装简单。</p>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '299.00', '499.00', 1, 1, 4800, 37, 1, '', '', '', '2026-05-29 22:49:09', '2026-07-05 22:49:09'),
(8, 30, 'XX NVR816 8路POE硬盘录像机', 'BP-NVR816', '', '', '[\"/static/uploads/20260706/221558_2a5f737a.png\"]', '{\"压缩\": \"H.265+\", \"存储\": \"支持2块8TB硬盘\", \"解码\": \"8MP\", \"输出\": \"HDMI+VGA 4K\", \"通道\": \"8路POE\"}', '<h3>BP-NVR816 8路POE录像机</h3><p>中小型办公场所监控主机。8路POE直连，免交换机。H.265+智能编码，存储时间更长。支持手机APP远程查看。</p>', '', '/static/uploads/20260706/221542_d4ea6caf.png', NULL, NULL, 1, '699.00', '999.00', 1, 1, 3200, 38, 1, '', '', '', '2026-05-28 22:49:09', '2026-07-05 22:49:09');

DROP TABLE IF EXISTS `lylmew_product_category`;
CREATE TABLE `lylmew_product_category` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `slug` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '分类图标',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '分类描述',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0=隐藏,1=显示',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品分类表';

INSERT INTO `lylmew_product_category` (`id`, `parent_id`, `name`, `slug`, `icon`, `description`, `sort`, `status`, `create_time`, `update_time`) VALUES
(1, 0, '打印机', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(2, 1, '激光打印机', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(3, 1, '喷墨打印机', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(4, 1, '针式打印机', '', '', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(5, 1, '标签/条码打印机', '', '', '', 4, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(6, 0, '多功能一体机', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(7, 6, '黑白激光一体机', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(8, 6, '彩色激光一体机', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(9, 6, '喷墨一体机', '', '', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(10, 0, '复印机/复合机', '', '', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(11, 10, 'A3黑白复合机', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(12, 10, 'A3彩色复合机', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(13, 0, '电脑/笔记本', '', '', '', 4, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(14, 13, '台式电脑', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(15, 13, '笔记本电脑', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(16, 13, '一体机电脑', '', '', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(17, 0, '扫描仪', '', '', '', 5, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(18, 0, '投影设备', '', '', '', 6, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(19, 0, '碎纸机', '', '', '', 7, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(20, 0, '考勤门禁', '', '', '', 8, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(21, 0, '会议平板', '', '', '', 9, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(22, 0, '办公耗材', '', '', '', 10, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(23, 22, '硒鼓/粉盒', '', '', '', 1, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(24, 22, '墨盒/墨水', '', '', '', 2, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(25, 22, '打印纸', '', '', '', 3, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(26, 22, '色带/碳带', '', '', '', 4, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(27, 0, '装订/胶装设备', '', '', '', 11, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(28, 0, '验钞/点钞机', '', '', '', 12, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(29, 0, '网络设备', '', '', '', 13, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09'),
(30, 0, '监控安防', '', '', '', 14, 1, '2026-07-05 22:49:09', '2026-07-05 22:49:09');

DROP TABLE IF EXISTS `lylmew_repair_order`;
CREATE TABLE `lylmew_repair_order` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `visitor_id` varchar(64) DEFAULT '' COMMENT '浏览器访客标识',
  `order_no` varchar(30) NOT NULL DEFAULT '' COMMENT '工单编号',
  `client_name` varchar(50) NOT NULL DEFAULT '' COMMENT '客户姓名',
  `company` varchar(100) NOT NULL DEFAULT '' COMMENT '单位名称',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `device_type` varchar(50) NOT NULL DEFAULT '' COMMENT '设备类型',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '服务地址',
  `description` text COMMENT '故障描述',
  `images` text COMMENT '上传图片(JSON数组)',
  `expect_time` varchar(50) NOT NULL DEFAULT '' COMMENT '期望上门时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0=待处理,1=处理中,2=已完成,3=已关闭',
  `service_price` decimal(10,2) DEFAULT '0.00' COMMENT '服务价格',
  `remark` text COMMENT '处理备注',
  `completion_receipt` text COMMENT '完成回执',
  `cancel_reason` varchar(255) DEFAULT '' COMMENT '撤销原因',
  `pause_reason` text COMMENT '暂停原因',
  `handler_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '处理人ID',
  `handler_name` varchar(50) NOT NULL DEFAULT '' COMMENT '处理人姓名',
  `handler_note` text COMMENT '处理备注',
  `accepted_time` datetime DEFAULT NULL COMMENT '接单时间',
  `paused_time` datetime DEFAULT NULL COMMENT '暂停时间',
  `completed_time` datetime DEFAULT NULL COMMENT '完成时间',
  `handle_time` datetime DEFAULT NULL COMMENT '处理时间',
  `finish_time` datetime DEFAULT NULL COMMENT '完成时间',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT '提交IP',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报修工单表';

DROP TABLE IF EXISTS `lylmew_repair_timeline`;
CREATE TABLE `lylmew_repair_timeline` (
  `id` int(10) UNSIGNED NOT NULL,
  `repair_id` int(10) UNSIGNED NOT NULL COMMENT '工单ID',
  `action` varchar(30) NOT NULL DEFAULT '' COMMENT '操作类型',
  `title` varchar(100) DEFAULT '' COMMENT '标题',
  `content` text COMMENT '详细内容',
  `operator_id` int(10) UNSIGNED DEFAULT '0' COMMENT '操作人ID',
  `operator_name` varchar(50) DEFAULT '' COMMENT '操作人名称',
  `operator_type` varchar(20) DEFAULT 'system' COMMENT '操作人类型:system/user/admin',
  `create_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='报修工单时间轴';

INSERT INTO `lylmew_repair_timeline` (`id`, `repair_id`, `action`, `title`, `content`, `operator_id`, `operator_name`, `operator_type`, `create_time`) VALUES
(1, 1, 'created', '提交报修', '用户提交报修工单：测试', 0, '测试用户', 'user', '2026-07-07 00:05:42');

DROP TABLE IF EXISTS `lylmew_single_page`;
CREATE TABLE `lylmew_single_page` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '页面标题',
  `slug` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `key` varchar(50) DEFAULT '' COMMENT '标识键',
  `content` longtext COMMENT '页面内容',
  `seo_title` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='单页内容表';

DROP TABLE IF EXISTS `lylmew_visit_log`;
CREATE TABLE `lylmew_visit_log` (
  `id` bigint(20) UNSIGNED NOT NULL COMMENT '主键ID',
  `url` varchar(500) NOT NULL DEFAULT '' COMMENT '访问URL',
  `ip` varchar(45) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `referer` varchar(500) NOT NULL DEFAULT '' COMMENT '来源页',
  `create_time` datetime DEFAULT NULL COMMENT '访问时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问统计表';


ALTER TABLE `lylmew_admin_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_create_time` (`create_time`);

ALTER TABLE `lylmew_admin_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`);

ALTER TABLE `lylmew_article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `lylmew_article_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_slug` (`slug`);

ALTER TABLE `lylmew_banner`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lylmew_case_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article_id` (`article_id`);

ALTER TABLE `lylmew_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_key` (`key`);

ALTER TABLE `lylmew_contact_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sort` (`sort`);

ALTER TABLE `lylmew_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_source` (`source`),
  ADD KEY `idx_visitor_id` (`visitor_id`),
  ADD KEY `idx_phone` (`phone`);

ALTER TABLE `lylmew_message_reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_id` (`message_id`);

ALTER TABLE `lylmew_partner`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lylmew_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_hot` (`is_hot`);

ALTER TABLE `lylmew_product_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_slug` (`slug`);

ALTER TABLE `lylmew_repair_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_no` (`order_no`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_visitor_id` (`visitor_id`);

ALTER TABLE `lylmew_repair_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_repair_id` (`repair_id`),
  ADD KEY `idx_action` (`action`);

ALTER TABLE `lylmew_single_page`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slug` (`slug`);

ALTER TABLE `lylmew_visit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_create_time` (`create_time`),
  ADD KEY `idx_ip` (`ip`);


ALTER TABLE `lylmew_admin_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `lylmew_admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `lylmew_article`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=7;

ALTER TABLE `lylmew_article_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=32;

ALTER TABLE `lylmew_banner`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

ALTER TABLE `lylmew_case_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=26;

ALTER TABLE `lylmew_config`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=55;

ALTER TABLE `lylmew_contact_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

ALTER TABLE `lylmew_message`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

ALTER TABLE `lylmew_message_reply`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `lylmew_partner`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=19;

ALTER TABLE `lylmew_product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=72;

ALTER TABLE `lylmew_product_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID', AUTO_INCREMENT=31;

ALTER TABLE `lylmew_repair_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

ALTER TABLE `lylmew_repair_timeline`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `lylmew_single_page`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';

ALTER TABLE `lylmew_visit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
