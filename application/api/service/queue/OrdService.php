<?php
namespace app\api\service\queue;

use app\api\library\OrderPay;
use app\api\model\ord\Config;
use app\api\model\order\OrderMain;
use app\api\service\BaseService;

class OrdService extends BaseService
{
    protected $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderMain();
    }

    /**
     * 订单更新
     * @param string $body
     * @return int
     * DateTime: 2024-03-29 15:47
     */
    public function saveOrder(string $body)
    {
        logs_write_cli($body, __LINE__);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return 0;
        }
        
        $flag = isset($data['refundId']) ? 'refund' : 'pay';
        
        if ($flag == 'refund') {
            //退款
            $main = $this->orderModel->where('PAYMENT_ID', $data['orderId'])->find();
            if (!$main) {
                return 0;
            }
            if ($main['STATE'] != 'REFUND') {
                return 0;
            }
            $rs = $main->save([
                'STATE' => $data['tradeState'] == 'SUCCESS' ? 'REFUND_SUCCESS' : 'REFUND_FAIL',
                'REFUND_ID' => $data['refundId'] ?? ''
            ]);
        } else {
            //支付
            $main = $this->orderModel->where('ORDER_NO', $data['body'])->find();
            if (!$main) {
                return 0;
            }
            if ($main['STATE'] != 'WAIT_PAY') {
                return 0;
            }
            $rs = $main->save([
                'STATE' => $data['tradeState'] == 'SUCCESS' ? 'PAY_SUCCESS' : 'PAY_FAIL',
                'PAYMENT_ID' => $data['orderId'] ?? ''
            ]);
        }
        if ($rs === false) {
            return 1;
        }

        return 0;
    }

    /**
     * 订单同步
     * @return bool
     * DateTime: 2024-03-29 11:56
     */
    public function clearOrder()
    {
        //支付配置
        $conf = (new Config())->getConf('','PAY_CONFIG');
        $payConf = [
            "PAY_END_SEC" => $conf['PAY_END_SEC'] ?? 600,
            "REFUND_END_SEC" => $conf['REFUND_END_SEC'] ?? 600
        ];

        $timePay2 = time() - $payConf['PAY_END_SEC'];
        $timeRefund2 = time() - $payConf['REFUND_END_SEC'];
        //支付同步
        $this->clearPay(date("Y-m-d H:i:s", $timePay2));
        //退款同步
//        $this->clearRefund(date("Y-m-d H:i:s", $timeRefund2));

        return true;
    }

    public function clearPay(string $date)
    {
        $list = $this->orderModel
            ->whereIn('STATE', ['WAIT_PAY','PAYING'])
            ->where('PAY_DATE','<=', $date)
            ->select();

        foreach ($list as $val) {
            $update = ['STATE'=>'PAY_FAIL'];
            if (!empty($val['PAYMENT_ID'])) {
                $param = [
                    'method' => 'PAYMENT',
                    'tradeId' => $val['PAYMENT_ID']
                ];
                $resp = OrderPay::query($param);
                if ($resp['success'] != true) {
                    continue;
                }
                $res = json_decode($resp['data'], true);
                if (!empty($res['tradeState'])) {
                    $res['tradeState'] == 'SUCCESS' && $update = ['STATE'=>'PAY_SUCCESS'];
                }
            } else {
                $update['REMARK'] = '订单未支付';
            }

            $update['UPDATE_DATE'] = date("Y-m-d H:i:s");
            $rs0 = $this->orderModel
                ->where('ID', $val['ID'])
                ->update($update);
        }
        return true;
    }

    public function clearRefund(string $date)
    {
        $list = $this->orderModel
            ->whereIn('STATE', ['REFUND'])
            ->where('REFUND_DATE','<=', $date)
            ->select();

        foreach ($list as $val) {
            $state = 'REFUND_FAIL';
            if (empty($val['REFUND_ID'])) {
                $state = 'REFUND_FAIL';
            }
            $param = [
                'method' => 'REFUND',
                'tradeId' => $val['REFUND_ID']
            ];
            $resp = OrderPay::query($param);
            if ($resp['success'] != true) {
                $state = 'REFUND_FAIL';
            }
            $res = json_decode($resp['data'], true);
            if (!empty($res['tradeState'])) {
                $res['tradeState'] == 'SUCCESS' && $state = 'REFUND_SUCCESS';
            }

            $rs0 = $this->orderModel
                ->where('ID', $val['ID'])
                ->update([
                    'STATE'=>$state,
                ]);
        }
        return true;
    }
}