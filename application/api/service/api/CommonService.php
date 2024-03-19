<?php
namespace app\api\service\api;


use app\api\service\BaseService;

class CommonService extends BaseService
{
    public static function unloginList()
    {
        return [
            'user/index/login'
        ];
    }
}