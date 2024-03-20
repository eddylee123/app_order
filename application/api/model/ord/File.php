<?php

namespace app\api\model\ord;

use think\Model;

/**
 * Class File
 * @package app\api\model\ord
 */
class File Extends Model
{
    // 表名
    protected $name = 'file';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'datetime';
    protected $dateFormat = 'Y-m-d H:i:s';
    // 定义时间戳字段名
    protected $createTime = 'CREATE_DATE';
    protected $updateTime = 'UPDATE_DATE';
    // 追加属性
    protected $append = [
    ];

    public function getFile($dishesId)
    {
        $list = $this->field('ID,FILE_PATH,FILE_TYPE')
            ->whereIn('ID', function ($query) use ($dishesId){
            $query->name('dishes_file')->where('DISHES_ID', $dishesId)->field('FILE_ID');
            })
            ->select();

        return collection($list)->toArray();
    }
}
