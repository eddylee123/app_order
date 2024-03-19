<?php

namespace app\api\validate;

use think\Validate;

class RoomValidate extends Validate
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
        'NO'  => 'require|max:20',
        'NAME' => 'require|max:50',
        'SEQ' => 'require|number|max:11',
        'ORG_ID' => 'require|max:20',
        'LINK_TYPE' => 'require|max:20',
        'HOUSE_TYPE' => 'require|max:20',
        'STATUS' => 'require|max:20',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'ID.require' => 'ID必须',
        'ID.number'     => 'ID格式错误',
        'NO.require' => '名称必须',
        'NO.max'     => '名称不能超过20个字符',
        'NAME.require' => '名称必须',
        'NAME.max'     => '名称不能超过50个字符',
        'SEQ.require' => '排序号必须',
        'SEQ.number'     => '排序号格式错误',
        'SEQ.max'     => '排序号不能超过11个字符',
        'ORG_ID.require' => '机构ID必须',
        'ORG_ID.max'     => '机构ID不能超过20个字符',
        'LINK_TYPE.require' => '关联属性必须',
        'LINK_TYPE.max'     => '关联属性不能超过20个字符',
        'HOUSE_TYPE.require' => '楼栋类型必须',
        'HOUSE_TYPE.max'     => '楼栋类型不能超过20个字符',
        'STATUS.require' => '状态必须',
        'STATUS.max'     => '状态不能超过20个字符',
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
        'add' => ['NO','NAME','SEQ','ORG_ID','LINK_TYPE','HOUSE_TYPE'],
        'edit' => ['ID','NO','NAME','SEQ','ORG_ID','LINK_TYPE','HOUSE_TYPE'],
    ];


}
