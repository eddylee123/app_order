<?php
namespace app\api\service;


class BaseService
{
    protected static $instances = [];

    /**
     * 初始化实例
     * BaseService constructor.
     */
    public function __construct()
    {

    }

    /**
     * 静态绑定方法
     * @return mixed
     * DateTime: 2024-02-05 10:46
     */
    final public static function instance()
    {
        $calledClass = get_called_class();
        if (!isset(self::$instances[$calledClass])) {
            self::$instances[$calledClass] = new $calledClass();
        }

        return self::$instances[$calledClass];
    }
}