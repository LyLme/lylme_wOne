<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\Config;
use app\model\ContactInfo;
use think\facade\View;

/**
 * 前台基础控制器
 * 所有前台控制器继承此类
 *
 * 配置来源：数据库 config 表（含兜底默认值）
 */
class FrontBase extends BaseController
{
    protected string $site_name = '';
    protected string $site_slogan = '';
    protected string $contact_phone = '';
    protected string $contact_address = '';
    protected string $contact_email = '';
    protected string $contact_hours = '';
    protected string $icp_number = '';
    protected string $wechat_qrcode = '';
    protected string $company_name_short = '';
    protected string $company_name_en = '';
    protected string $company_icon = '';
    protected bool $is_company_icon_image = false;
    protected array $nav_menus = [];
    protected array $footer_service_links = [];
    protected array $footer_quick_links = [];
    protected string $current_nav = '';
    protected array $siteConfig = [];
    protected array $contactInfoList = [];

    /**
     * 初始化：加载公共数据
     */
    protected function initialize(): void
    {
        parent::initialize();

        // 1. 全部从数据库读取配置（不再依赖 config/site.php）
        $this->siteConfig = Config::getAllConfig();

        // 关键字段兜底默认值
        $defaults = [
            'company_name'        => '',
            'company_name_short'  => '',
            'company_name_en'     => '',
            'company_slogan'      => '',
            'company_icon'        => 'fa-print',
            'contact_phone'       => '',
            'contact_address'     => '',
            'contact_email'       => '',
            'contact_hours'       => '周一至周五 8:00-18:00',
            'icp_number'          => '',
            'wechat_qrcode'       => '',
            'nav_menus'           => [],
            'nav_controller_map'  => [
                'index'   => 'home',
                'product' => 'products',
                'service' => 'services',
                'cases'   => 'cases',
                'article' => 'news',
                'about'   => 'about',
                'contact' => 'contact',
                'repair'  => 'services',
            ],
            'footer_service_links' => [],
            'footer_quick_links'  => [],
        ];
        $this->siteConfig = array_merge($defaults, $this->siteConfig);

        // 2. 获取配置项（此时 siteConfig 已包含数据库覆盖值）
        $cfg = fn(string $key, $default = '') => $this->siteConfig[$key] ?? $default;

        $this->site_name        = $cfg('site_name', $cfg('company_name', ''));
        $this->site_slogan      = $cfg('site_slogan', $cfg('company_slogan', ''));
        $this->contact_phone    = $cfg('contact_phone', '');
        $this->contact_address  = $cfg('contact_address', '');
        $this->contact_email    = $cfg('contact_email', '');
        $this->contact_hours    = $cfg('contact_hours', '周一至周五 8:00-18:00');
        $this->icp_number       = $cfg('icp_number', '');
        $this->wechat_qrcode    = $cfg('wechat_qrcode', '');
        $this->company_name_short = $this->siteConfig['company_name_short'] ?? $this->site_name;
        $this->company_name_en  = $this->siteConfig['company_name_en'] ?? '';
        $this->company_icon     = $this->siteConfig['company_icon'] ?? 'fa-print';
        $this->is_company_icon_image = (bool)preg_match('/\.(png|jpe?g|svg|webp|gif|ico)/i', $this->company_icon);

        // 3. 导航菜单
        $this->nav_menus = $this->siteConfig['nav_menus'] ?? [];
        // 如果数据库有自定义菜单，从数据库读取（暂不实现，保留扩展）

        // 4. 页脚链接
        $this->footer_service_links = $this->siteConfig['footer_service_links'] ?? [];
        $this->footer_quick_links = $this->siteConfig['footer_quick_links'] ?? [];

        // 5. 当前导航：根据控制器名自动判断
        $controllerName = strtolower($this->request->controller());
        $navMap = $this->siteConfig['nav_controller_map'] ?? [
            'index'   => 'home',
            'product' => 'products',
            'service' => 'services',
            'cases'   => 'cases',
            'article' => 'news',
            'about'   => 'about',
            'contact' => 'contact',
            'repair'  => 'services',
        ];
        $this->current_nav = $navMap[$controllerName] ?? '';

        // 6. 加载公司多种联系方式
        $contactInfoList = [];
        try {
            $contactInfoList = ContactInfo::getAll();
        } catch (\Exception $e) {
            // 表不存在时忽略
        }
        $this->contactInfoList = $contactInfoList;
        // 服务热线：使用后台“联系电话”字段，为空时回退到联系方式表中的第一个电话
        $servicePhone = $this->contact_phone ?: ContactInfo::getServicePhone();

        // 7. 共享变量到所有视图
        View::assign([
            'site_name'          => $this->site_name,
            'site_slogan'        => $this->site_slogan,
            'contact_phone'      => $this->contact_phone,
            'contact_address'    => $this->contact_address,
            'contact_email'      => $this->contact_email,
            'contact_hours'      => $this->contact_hours,
            'icp_number'         => $this->icp_number,
            'wechat_qrcode'      => $this->wechat_qrcode,
            'nav_menus'          => $this->nav_menus,
            'current_nav'        => $this->current_nav,
            'company_name_short' => $this->company_name_short,
            'company_name_en'    => $this->company_name_en,
            'company_icon'       => $this->company_icon,
            'is_company_icon_image' => $this->is_company_icon_image,
            'footer_service_links' => $this->footer_service_links,
            'footer_quick_links' => $this->footer_quick_links,
            'site_config'        => $this->siteConfig,
            'contact_info_list'  => $this->contactInfoList,
            'service_phone'      => $servicePhone,
        ]);
    }

    /**
     * 获取站点配置项（数据库优先，配置文件兜底）
     */
    protected function getSiteConfig(string $key, $default = null)
    {
        return $this->siteConfig[$key] ?? $default;
    }
}
