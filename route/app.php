<?php
// +----------------------------------------------------------------------
// | 路由定义
// +----------------------------------------------------------------------

use think\facade\Route;

// | 前台路由
// +----------------------------------------------------------------------

// ==================== 前台路由 ====================

Route::group('/', function () {
    // 首页
    Route::get('/', 'Index/index')->name('home');

    // 产品中心
    Route::get('products', 'Product/index')->name('products');
    Route::get('products/:slug', 'Product/category')->name('product_category')->pattern(['slug' => '[\w\-]+']);
    Route::get('product/:id', 'Product/detail')->name('product_detail')->pattern(['id' => '\d+']);
    Route::get('products/search', 'Product/search')->name('product_search');

    // 服务支持
    Route::get('services', 'Service/index')->name('services');
    Route::get('services/:slug', 'Service/detail')->name('service_detail')->pattern(['slug' => '[\w\-]+']);

    // 在线报修
    Route::get('repair', 'Repair/index')->name('repair');
    Route::post('repair/submit', 'Repair/submit')->name('repair_submit');
    Route::get('repair/myOrders', 'Repair/myOrders')->name('repair_my_orders');
    Route::get('repair/orderDetail', 'Repair/orderDetail')->name('repair_order_detail');
    Route::post('repair/editOrder', 'Repair/editOrder')->name('repair_edit_order');
    Route::post('repair/cancelOrder', 'Repair/cancelOrder')->name('repair_cancel_order');

    // 客户案例
    Route::get('cases', 'Cases/index')->name('cases');
    Route::get('case/:id', 'Cases/detail')->name('case_detail')->pattern(['id' => '\d+']);

    // 新闻资讯
    Route::get('news', 'Article/index')->name('news');
    Route::get('news/detail/:id', 'Article/detail')->name('news_detail')->pattern(['id' => '\d+']);
    Route::get('news/:slug', 'Article/category')->name('news_category')->pattern(['slug' => '[\w\-]+']);

    // 关于我们
    Route::get('about', 'About/index')->name('about');

    // 联系我们
    Route::get('contact', 'Contact/index')->name('contact');
    Route::post('contact/message', 'Contact/message')->name('contact_message');
    Route::get('contact/myMessages', 'Contact/myMessages')->name('contact_my_messages');
    Route::post('contact/followUp', 'Contact/followUp')->name('contact_follow_up');

    // 验证码
    Route::get('captcha', 'Index/captcha')->name('captcha');

    // 搜索
    Route::get('search', 'Index/search')->name('search');

    // Sitemap（不含扩展名，由 .htaccess 显式重写处理）
    Route::get('sitemap', 'Index/sitemap')->name('sitemap');
    Route::get('robots', 'Index/robots')->name('robots');
});

// ==================== 后台路由 ====================
// 支持自定义后台路径（在 .env 中配置 ADMIN_PATH，默认 admin）
$adminPath = env('APP.ADMIN_PATH', 'admin');
$adminPath = $adminPath ? trim($adminPath, '/') : 'admin';
// 安全校验：禁止使用前台路由路径作为后台入口
$reservedPaths = ['home', 'index', 'products', 'product', 'services', 'service', 'cases', 'case', 'news', 'article', 'about', 'contact', 'repair', 'search', 'captcha', 'sitemap', 'sitemap.xml', 'robots', 'robots.txt', 'login', 'logout'];
if (in_array(strtolower($adminPath), $reservedPaths)) {
    $adminPath = 'admin';
}

// 后台路由
Route::group($adminPath, function () {
    // 登录/退出
    Route::get('login', 'admin.Login/index')->name('admin_login');
    Route::post('login', 'admin.Login/doLogin')->name('admin_dologin');
    Route::get('logout', 'admin.Login/logout')->name('admin_logout');
    Route::get('captcha', 'admin.Login/captcha')->name('admin_captcha');

    // 后台首页：访问 /admin 或 /admin/ 自动跳转到 /admin/index
    Route::get('/', function() {
        $adminPath = config('app.admin_path', 'admin');
        return redirect('/' . $adminPath . '/index');
    });
    Route::get('index', 'admin.Index/index');
    Route::post('clear-cache', 'admin.Index/clearCache')->name('admin_clear_cache');

    // 站点配置
    Route::get('config', 'admin.Config/index')->name('admin_config');
    Route::post('config/save', 'admin.Config/save')->name('admin_config_save');
    Route::post('config/save-group', 'admin.Config/saveGroup')->name('admin_config_save_group');
    Route::post('config/reset', 'admin.Config/reset')->name('admin_config_reset');
    Route::post('config/upload', 'admin.Config/upload')->name('admin_config_upload');
    Route::post('config/test-notify', 'admin.Config/testNotify')->name('admin_config_test_notify');
    Route::get('config/get-default-templates', 'admin.Config/getDefaultTemplates');

    // Banner管理
    Route::get('banner', 'admin.Banner/index')->name('admin_banner');
    Route::post('banner/add', 'admin.Banner/add')->name('admin_banner_add');
    Route::post('banner/update', 'admin.Banner/update')->name('admin_banner_update');
    Route::post('banner/delete', 'admin.Banner/delete')->name('admin_banner_delete');
    Route::post('banner/toggle-status', 'admin.Banner/toggleStatus')->name('admin_banner_status');
    Route::post('banner/upload', 'admin.Banner/upload')->name('admin_banner_upload');

    // 产品分类
    Route::get('product-category', 'admin.ProductCategory/index')->name('admin_product_category');
    Route::post('product-category/add', 'admin.ProductCategory/add')->name('admin_product_category_add');
    Route::post('product-category/update', 'admin.ProductCategory/update')->name('admin_product_category_update');
    Route::post('product-category/delete', 'admin.ProductCategory/delete')->name('admin_product_category_delete');

    // 产品管理
    Route::get('product', 'admin.Product/index')->name('admin_product');
    Route::post('product/add', 'admin.Product/add')->name('admin_product_add');
    Route::post('product/update', 'admin.Product/update')->name('admin_product_update');
    Route::post('product/delete', 'admin.Product/delete')->name('admin_product_delete');
    Route::post('product/toggle-status', 'admin.Product/toggleStatus')->name('admin_product_status');
    Route::post('product/upload', 'admin.Product/upload')->name('admin_product_upload');

    // 文章分类
    Route::get('article-category', 'admin.ArticleCategory/index')->name('admin_article_category');
    Route::post('article-category/add', 'admin.ArticleCategory/add')->name('admin_article_category_add');
    Route::post('article-category/update', 'admin.ArticleCategory/update')->name('admin_article_category_update');
    Route::post('article-category/delete', 'admin.ArticleCategory/delete')->name('admin_article_category_delete');

    // 文章/新闻/案例管理
    Route::get('article', 'admin.Article/index')->name('admin_article');
    Route::post('article/add', 'admin.Article/add')->name('admin_article_add');
    Route::post('article/update', 'admin.Article/update')->name('admin_article_update');
    Route::post('article/delete', 'admin.Article/delete')->name('admin_article_delete');
    Route::post('article/toggle-status', 'admin.Article/toggleStatus')->name('admin_article_status');
    Route::post('article/upload', 'admin.Article/upload')->name('admin_article_upload');
    Route::get('article/detail', 'admin.Article/detail')->name('admin_article_detail');

    // 留言管理
    Route::get('message', 'admin.Message/index')->name('admin_message');
    Route::post('message/reply', 'admin.Message/reply')->name('admin_message_reply');
    Route::get('message/conversation', 'admin.Message/conversation')->name('admin_message_conversation');
    Route::post('message/markRead', 'admin.Message/markRead')->name('admin_message_markread');
    Route::post('message/delete', 'admin.Message/delete')->name('admin_message_delete');

    // 报修工单管理
    Route::get('repair', 'admin.Repair/index')->name('admin_repair');
    Route::get('repair/detail', 'admin.Repair/detailJson')->name('admin_repair_detail');
    Route::post('repair/accept', 'admin.Repair/accept')->name('admin_repair_accept');
    Route::post('repair/pause', 'admin.Repair/pause')->name('admin_repair_pause');
    Route::post('repair/resume', 'admin.Repair/resume')->name('admin_repair_resume');
    Route::post('repair/complete', 'admin.Repair/complete')->name('admin_repair_complete');
    Route::post('repair/updateStatus', 'admin.Repair/updateStatus')->name('admin_repair_status');
    Route::post('repair/addRemark', 'admin.Repair/addRemark')->name('admin_repair_remark');
    Route::post('repair/delete', 'admin.Repair/delete')->name('admin_repair_delete');

    // 合作伙伴管理
    Route::get('partner', 'admin.Partner/index')->name('admin_partner');
    Route::post('partner/add', 'admin.Partner/add')->name('admin_partner_add');
    Route::post('partner/update', 'admin.Partner/update')->name('admin_partner_update');
    Route::post('partner/delete', 'admin.Partner/delete')->name('admin_partner_delete');
    Route::post('partner/toggle-status', 'admin.Partner/toggleStatus')->name('admin_partner_status');
    Route::post('partner/upload', 'admin.Partner/upload')->name('admin_partner_upload');

    // 公司联系方式管理
    Route::get('contact-info/list', 'admin.ContactInfo/list')->name('admin_contact_info_list');
    Route::post('contact-info/add', 'admin.ContactInfo/add')->name('admin_contact_info_add');
    Route::post('contact-info/update', 'admin.ContactInfo/update')->name('admin_contact_info_update');
    Route::post('contact-info/delete', 'admin.ContactInfo/delete')->name('admin_contact_info_delete');
    Route::post('contact-info/batch-save', 'admin.ContactInfo/batchSave')->name('admin_contact_info_batch_save');

    // 管理员管理（多用户）
    Route::get('admin-user', 'admin.Admin/index')->name('admin_user');
    Route::post('admin-user/add', 'admin.Admin/add')->name('admin_user_add');
    Route::post('admin-user/update', 'admin.Admin/update')->name('admin_user_update');
    Route::post('admin-user/delete', 'admin.Admin/delete')->name('admin_user_delete');
    Route::get('admin-user/password', 'admin.Admin/changePassword')->name('admin_user_password');
    Route::post('admin-user/password', 'admin.Admin/changePassword')->name('admin_user_password_save');
    Route::get('admin-user/logs', 'admin.Admin/logs')->name('admin_user_logs');
});


