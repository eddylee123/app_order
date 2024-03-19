<?php

namespace app\common\model;

use think\Model;

/**
 * 会员积分日志模型
 */
class ScoreLog Extends Model
{

    // 表名
    protected $name = 'place';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'CREATE_TIME';
    protected $updateTime = 'UPDATE_TIME';
    // 追加属性
    protected $append = [
    ];
}
