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
        'NUM'  => 'require|number',
        'REMARK' => 'max:100',
        'STATE' => 'require|max:20',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'ID.require' => 'ID必须',
        'ID.number'     => 'ID格式错误',
        'NUM.require' => '房间ID必须',
        'NUM.number'     => '房间ID格式错误',
        'REMARK.max'     => '备注不能超过100个字符',
        'STATE.require' => '状态必须',
        'STATE.max'     => '状态不能超过20个字符',
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
    ];


}
