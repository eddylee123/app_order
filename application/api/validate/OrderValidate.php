<?php

namespace app\api\validate;

use think\Validate;

class OrderValidate extends Validate
{

    /**
     * 正则
     */
    protected $regex = ['format' => '[a-z0-9_\/]+'];

    /**
     * 验证规则
     */
    protected $rule = [
        'ID'  => 'require|number',
        'ORDER_ID'  => 'require|number',
        'NUM'  => 'require|number',
        'REMARK' => 'max:100',
        'PAY_AMT'  => 'require|number',
        'REFUND_AMT'  => 'require|number',
        'STATE' => 'require|max:20',
        'CHANNEL' => 'require|max:30',
        'TRADE_TYPE' => 'require|max:30',
        'REASON' => 'require|max:100',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'ID.require' => 'ID必须',
        'ID.number'     => 'ID格式错误',
        'ORDER_ID.require' => '订单ID必须',
        'ORDER_ID.number'     => '订单ID格式错误',
        'NUM.require' => '房间ID必须',
        'NUM.number'     => '房间ID格式错误',
        'REMARK.max'     => '备注不能超过100个字符',
        'STATE.require' => '状态必须',
        'STATE.max'     => '状态不能超过20个字符',
        'PAY_AMT.require' => '支付金额必须',
        'PAY_AMT.number'     => '支付金额格式错误',
        'REFUND_AMT.require' => '退款金额必须',
        'REFUND_AMT.number'     => '退款金额格式错误',
        'CHANNEL.require' => '通道号必须',
        'CHANNEL.max'     => '通道号不能超过30个字符',
        'TRADE_TYPE.require' => '类型必须',
        'TRADE_TYPE.max'     => '类型不能超过30个字符',
        'REASON.require' => '退款理由必须',
        'REASON.max'     => '退款理由不能超过100个字符',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'settle' => ['ID','NUM'],
        'pay' => ['ORDER_ID','PAY_AMT','CHANNEL','TRADE_TYPE'],
        'payOrder' => ['ORDER_NO','CHANNEL','TRADE_TYPE'],
        'refund' => ['ORDER_ID','REFUND_AMT','REASON'],
    ];


}
