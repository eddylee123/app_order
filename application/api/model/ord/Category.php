<?php

namespace app\api\model\ord;

use think\Model;

/**
 * Class Category
 * @package app\api\model\ord
 */
class Category Extends Model
{
    // 表名
    protected $name = 'category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_DATE';
    protected $updateTime = 'UPDATE_DATE';
    // 追加属性
    protected $append = [
    ];

    public $statusMap = [
        'ON' => '开启',
        'OFF' => '关闭'
    ];

    public function getCateTree()
    {
        $list = $this
            ->where('STATUS', "ON")
            ->order('SEQ', 'desc')
            ->column('ID,PID,NAME');

        return getTree($list);
    }


}
