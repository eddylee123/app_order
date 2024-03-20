<?php

namespace app\api\model\ord;

use think\Model;

/**
 * Class Config
 * @package app\api\model\ord
 */
class Config Extends Model
{

    // 表名
    protected $name = 'config';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_TIME';
    protected $updateTime = 'UPDATE_TIME';
    // 追加属性
    protected $append = [
    ];

    public function getConf($orgId, $field)
    {
        $rs = $this
            ->where("NAME", $field)
            ->where("ORG_CODE", $orgId)
            ->value("VAL");
        return !empty($rs) ? json_decode($rs, true) : [];
    }
}
