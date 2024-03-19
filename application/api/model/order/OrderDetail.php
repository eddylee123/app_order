<?php

namespace app\api\model\order;

use think\Model;

/**
 * Class OrderDetail
 * @package app\api\model\order
 */
class OrderDetail Extends Model
{
    // 表名
    protected $name = 'order_detail';
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
