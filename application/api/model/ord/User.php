<?php

namespace app\api\model\ord;

use think\Model;

/**
 * Class User
 * @package app\api\model\ord
 */
class User Extends Model
{
    // 表名
    protected $name = 'user';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_DATE';
    protected $updateTime = 'UPDATE_DATE';
    // 追加属性
    protected $append = [
    ];

}
