<?php

use think\exception\HttpResponseException;
use think\Request;
use think\Response;

/**
 * 失败返回
 * @param $msg
 * @param null $data
 * @param int $code
 * DateTime: 2024-03-12 22:26
 */
function app_exception($msg, $data = [], $code = 0)
{
    data_response($code, $data, $msg);
}

/**
 * 成功返回
 * @param $code
 * @param null $data
 * @param string $msg
 * DateTime: 2024-03-12 22:26
 */
function app_response($code, $data = [], $msg = '')
{
    data_response($code, $data, $msg);
}

function data_response($code, $data, $msg, $type = 'json', array $header = [])
{
    $request = Request::instance();
    $result = [
        'traceId' => $request->param('traceId', ''),
        'success'  => $code == 200,
        'host' => $request->ip(),
        'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
    ];
    if (!$result['success']) {
        $result['errorCode'] = $code;
        $result['errorMessage'] = $msg;
    }

    if (isset($header['statuscode'])) {
        $code = $header['statuscode'];
        unset($header['statuscode']);
    } else {
        //未设置状态码,根据code值判断
        $code = $code >= 1000 || $code < 200 ? 200 : $code;
    }
    $response = Response::create($result, $type, $code)->header($header);
    throw new HttpResponseException($response);
}

/**
 * 递归分类
 * @param $category
 * @param int $pid
 * @return array
 * DateTime: 2024-03-21 9:41
 */
function getTree($category, $pid=0)
{
    $data = [];
    foreach ($category as $item){
        if($item['PID'] == $pid){
            $arr['ID'] = $item['ID'];
            $arr['NAME'] = $item['NAME'];
            $cate = getTree($category, $item['ID']);
            if(!empty($cate)){
                $arr['child'] = $cate;
            }
            $data[] = $arr;
            unset($arr);
        }
    }
    return $data;
}

/**
 * 生成订单号
 * @param int $len
 * @return string
 * DateTime: 2024-03-21 21:55
 */
function getOrderNo($len = 20)
{
    return date('ymd') . str_pad(mt_rand(1, 99999), $len, '0', STR_PAD_LEFT);
}