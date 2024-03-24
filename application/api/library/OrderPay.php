<?php


namespace app\api\library;


use think\Request;

class OrderPay
{
    const host = "http://10.254.30.36:8091";

    /**
     * 支付
     * @param array $param
     * DateTime: 2024-03-22 11:00
     */
    public static function pay(array $param)
    {
        $url = self::host.'/as/payment/pay';

        $body = [
            'traceId' => Request::instance()->param('traceId', ''),
            'ipAddress' => Request::instance()->param('ipAddress', ''),
            'data' => json_encode($param, JSON_UNESCAPED_UNICODE),
        ];

        $rs = curl_request($url, 'POST', $body);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }

    /**
     * 退款
     * @param array $param
     * DateTime: 2024-03-22 11:00
     */
    public static function refund(array $param)
    {
        $url = self::host.'/as/payment/refund';
        $body = [
            'traceId' => Request::instance()->param('traceId', ''),
            'ipAddress' => Request::instance()->param('ipAddress', ''),
            'data' => json_encode($param, JSON_UNESCAPED_UNICODE),
        ];

        $rs = curl_request($url, 'POST', $body);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }

    /**
     * 订单查询
     * @param array $param
     * DateTime: 2024-03-22 11:00
     */
    public static function query(array $param)
    {
        $url = self::host.'/as/payment/query';
        $body = [
            'traceId' => Request::instance()->param('traceId', ''),
            'ipAddress' => Request::instance()->param('ipAddress', ''),
            'data' => json_encode($param, JSON_UNESCAPED_UNICODE),
        ];

        $rs = curl_request($url, 'POST', $body);;

        $data = [];
        if ($rs) {
            $data = json_decode($rs, true);
        }

        return $data;
    }
}