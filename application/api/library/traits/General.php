<?php
namespace app\api\library\traits;

use app\api\service\api\CommonService;
use app\cache\BaseCache;
use think\Request;

trait General
{
    /**
     * 菜单是否无需登录
     *
     * @param string $menu_url 菜单url
     *
     * @return boolean
     */
    public function menuIsUnLogin($menu_url = '')
    {
        if (empty($menu_url)) {
            $menu_url = $this->menuUrl();
        }

        $unloginlist = CommonService::unloginList();
        if (in_array($menu_url, $unloginlist)) {
            return true;
        }

        return false;
    }

    /**
     * 菜单url获取
     * 应用/控制器/操作
     * eg：api/Index/index
     *
     * @return string
     */
    public function menuUrl()
    {
        return Request::instance()->pathinfo();
    }

//    public function userToken()
//    {
//
//        $key = sprintf(BaseCache::user_token, '');
//        $token_info = SettingService::instance()->tokenInfo();
//
//        $token_name  = $token_info['token_name'];
//        $admin_token = Request::header($token_name, '');
//
//        return $admin_token;
//    }
}