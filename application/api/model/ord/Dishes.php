<?php

namespace app\api\model\ord;

use think\Model;
use traits\model\SoftDelete;

/**
 * Class Menu
 * @package app\api\model\ord
 */
class Dishes Extends Model
{
    use SoftDelete;
    // 表名
    protected $name = 'dishes';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_DATE';
    protected $updateTime = 'UPDATE_DATE';
    protected $deleteTime = 'DELETE_DATE';
    // 追加属性
    protected $append = [
    ];

    public $statusMap = [
        'ON' => '上架',
        'OFF' => '下架'
    ];


}
