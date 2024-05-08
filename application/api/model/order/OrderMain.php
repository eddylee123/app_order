<?php

namespace app\api\model\order;

use app\api\model\ord\Config;
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
        'STATE_TEXT',
        'MEAL_TEXT'
    ];

    public $stateMap = [
        'WAIT_PAY' => '未支付',
        'PAYING' => '支付中',
        'PAY_SUCCESS' => '支付成功',
        'PAY_FAIL' => '支付失败',
        'REFUND' => '退款中',
        'REFUND_SUCCESS' => '退款成功',
        'REFUND_FAIL' => '退款失败',
        'CONFIRM' => '已确认',
        'EXPIRED' => '已过期',
    ];

    public $checkMap = [
        '0' => '未核销',
        '1' => '已核销',
        '2' => '核销超时'
    ];

    public function getStateTextAttr($value, $data)
    {
        return $this->stateMap[$data['STATE']] ?? '';
    }

    public function getMealTextAttr($value, $data)
    {
        $confModel = new Config();
        $conf = $confModel->getConf('', 'MEAL_TYPE');
        if (empty($conf)) {
            return '';
        }

        return $conf[$data['MEAL_TYPE']] ?? '';
    }

}
