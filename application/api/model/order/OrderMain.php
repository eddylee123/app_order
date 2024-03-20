<?php

namespace app\api\model\order;

use think\Model;

/**
 * Class OrderMain
 * @package app\api\model\order
 */
class OrderMain Extends Model
{
    // 表名
    protected $name = 'order_main';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_DATE';
    protected $updateTime = 'UPDATE_DATE';
    // 追加属性
    protected $append = [
        'STATE_TEXT'
    ];

    public $stateMap = [
        'WAIT_PAY' => '待支付',
        'PAYING' => '支付中',
        'PAY_SUCCESS' => '支付成功',
        'PAY_FAIL' => '支付失败',
        'REFUND' => '退款中',
        'REFUND_SUCCESS' => '退款成功',
        'REFUND_FAIL' => '退款失败',
    ];

    public function getStateTextAttr($value, $data)
    {
        return $this->stateMap[$data['STATE']] ?? '';
    }

}
