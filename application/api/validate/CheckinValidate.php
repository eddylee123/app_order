<?php

namespace app\api\validate;

use think\Validate;

class CheckinValidate extends Validate
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
        'ROOM_ID'  => 'require|number',
        'BED_ID'  => 'require|number',
        'PERSON_ID'  => 'require|max:20',
        'PERSON_TYPE' => 'require|max:50',
        'CHECKIN_DATE' => 'require',
        'CHECKOUT_DATE' => 'require',
        'ASSIGN_KEYS'  => 'require|number',
        'RETURN_KEYS'  => 'require|number',
        'CHECKIN_REASON' => 'max:200',
        'CHECKOUT_REASON' => 'max:200',
        'REMARK' => 'max:100',
        'STATE' => 'require|max:20',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'ID.require' => 'ID必须',
        'ID.number'     => 'ID格式错误',
        'ROOM_ID.require' => '房间ID必须',
        'ROOM_ID.number'     => '房间ID格式错误',
        'BED_ID.require' => '床位ID必须',
        'BED_ID.number'     => '床位ID格式错误',
        'PERSON_ID.require' => '工号必须',
        'PERSON_ID.max'     => '工号不能超过20个字符',
        'PERSON_TYPE.require' => '人员类型必须',
        'PERSON_TYPE.max'     => '人员类型不能超过50个字符',
        'CHECKIN_DATE.require' => '入住时间必须',
        'CHECKOUT_DATE.require' => '退宿时间必须',
        'ASSIGN_KEYS.require' => '分配钥匙必须',
        'ASSIGN_KEYS.number'     => '分配钥匙格式错误',
        'RETURN_KEYS.require' => '归还钥匙数必须',
        'RETURN_KEYS.number'     => '归还钥匙数格式错误',
        'CHECKIN_REASON.max'     => '入住原因不能超过200个字符',
        'CHECKOUT_REASON.max'     => '退宿原因不能超过200个字符',
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
        'add' => ['ROOM_ID','BED_ID','PERSON_ID','PERSON_TYPE','CHECKIN_DATE','ASSIGN_KEYS'],
        'edit' => ['ID','ROOM_ID','BED_ID','PERSON_ID','PERSON_TYPE','CHECKIN_DATE','ASSIGN_KEYS'],
    ];


}
