<?php


namespace app\api\service\ord;


use app\api\library\OrderPay;
use app\api\model\ord\Category;
use app\api\model\ord\Config;
use app\api\model\ord\Dishes;
use app\api\model\ord\File;
use app\api\model\ord\User;
use app\api\model\order\OrderDetail;
use app\api\model\order\OrderMain;
use app\api\service\BaseService;
use think\Db;
use think\Exception;

class OrderService extends BaseService
{
    protected $mainModel;
    protected $detailModel;
    protected $configModel;
    protected $dishesModel;
    protected $fileModel;
    protected $cateModel;
    protected $userModel;

    public function __construct()
    {
        $this->mainModel = new OrderMain();
        $this->detailModel = new OrderDetail();
        $this->configModel = new Config();
        $this->dishesModel = new Dishes();
        $this->fileModel = new File();
        $this->cateModel = new Category();
        $this->userModel = new User();
    }

    public function lists(string $orgId, array $param, int $userId = 0)
    {
        $object = $this->mainModel;

        if (!empty($orgId)) {
            $object->where("ORG_CODE", $orgId);
        }
        if (!empty($userId)) {
            $object->where("USER_ID", $userId);
        }
        if (!empty($param['PLACE_ID'])) {
            $object->where("PLACE_ID", $param['PLACE_ID']);
        }
        if (!empty($param['ORDER_NO'])) {
            $object->where("ORDER_NO", 'like', $param['ORDER_NO']."%");
        }
        if (!empty($param['STATE'])) {
            $object->where("STATE", $param['STATE']);
        }
        if (!empty($param['START_TIME'])) {
            $object->where("CREATE_DATE", '>=', $param['START_TIME']);
        }
        if (!empty($param['END_TIME'])) {
            $object->where("CREATE_DATE", '<=', $param['END_TIME']);
        }

        $list = $object
            ->order('CREATE_DATE', 'DESC')
            ->paginate(['list_rows' => $param['page_size'], 'query' => $param['page']])
            ->toArray();
        $deList = $userList = [];
        if (!empty($list['data'])) {
            //用户信息
            $userIds = array_column($list['data'], 'USER_ID');
            $userList = $this->userModel->whereIn('ID', $userIds)->column('ID,EMP_ID,NICKNAME,MOBILE', 'ID');
            //菜单基础信息
            $orderIds = implode(",", array_column($list['data'], 'ID'));
            $sql = "SELECT de.ID,de.ORD_ID,de.DISH_NAME,fi.FILE_PATH,fi.FILE_TYPE FROM ord_order_detail de 
                LEFT JOIN ord_dishes di ON di.ID=de.DISH_ID 
                LEFT JOIN ord_dishes_file df ON df.DISHES_ID=di.ID 
                LEFT JOIN ord_file fi ON fi.ID=df.FILE_ID 
                WHERE de.ORD_ID IN ({$orderIds})";
//                GROUP BY de.ID;
            $res = Db::query($sql);

            $deList = [];
            foreach ($res as $val) {
                $deList[$val['ORD_ID']][] = $val;
            }
        }

        foreach ($list['data'] as &$v) {
            $user = $userList[$v['USER_ID']] ?? [];

            $v['EMP_ID'] = $user['EMP_ID'] ?? '';
            $v['NICKNAME'] = $user['NICKNAME'] ?? '';
            $v['MOBILE'] = $user['MOBILE'] ?? '';
            $v['DETAIL'] = $deList[$v['ID']] ?? [];
        }

        return $list;
    }

    public function info(int $orderId)
    {
        $order = $this->mainModel
            ->field('ORDER_NO,STATE,PAY_AMT,ORDER_AMT,MEAL_TYPE,CODE,CHECK,CREATE_DATE')
            ->find($orderId);

        $detail = $this->detailModel
            ->field('ID,DISH_ID,UNIT_PRICE,DISH_NAME,NUM,TOTAL_AMT')
            ->where('ORD_ID', $orderId)
            ->select();
        if (empty($detail)) {
            return [];
        }
        $detail = collection($detail)->toArray();
        $dishIds = array_column($detail, 'DISH_ID');
        $fileList = $this->fileModel->getFile($dishIds);

        foreach ($detail as &$v) {
            $v['FILE'] = $fileList[$v['DISH_ID']] ?? [];
        }

        $data['ORDER'] = $order;
        $data['DETAIL'] = $detail;

        return $data;
    }

    public function settle(int $userId, array $param)
    {
        //去重
        $exist = $this->mainModel
            ->where('USER_ID', $userId)
            ->whereIn('STATE', ['WAIT_PAY','PAYING'])
            ->value('ID');
        if ($exist) {
            app_exception('当前存在未支付订单，请勿重复提交');
        }
        $dishIds = array_column($param['DISH'],'ID');
        $dish = $this->dishesModel
            ->whereIn('ID', $dishIds)
            ->column('*', 'ID');

        $detail = [];
        foreach ($param['DISH'] as $v) {

            if (empty($dish[$v['ID']])) {
                app_exception('菜单信息异常');
            }
            $dishOrd = $dish[$v['ID']];

            $detail[] = [
                'DISH_ID' => $dishOrd['ID'],
                'UNIT_PRICE' => $dishOrd['PRICE'],
                'DISH_NAME' => $dishOrd['NAME'],
                'NUM' => $v['NUM'],
                'TOTAL_AMT' => $dishOrd['PRICE'] * $v['NUM']
            ];
        }
        $payAmt = array_sum(array_column($detail, 'TOTAL_AMT'));

//        $mealTime = [
//            'BREAKFAST' =>['06:00:00','10:59:59'],
//            'LUNCH' =>['11:00:00','16:59:59'],
//            'DINNER' =>['17:00:00','21:59:59'],
//            'SNACK' =>['22:00:00','23:59:59'],
//        ];
//        $mealType = '未知';
//        $timestamp = time();
//        foreach ($mealTime as $k=>$v) {
//            [$start, $end] = $v;
//            $startTime = strtotime(date("Y-m-d ".$start, $timestamp));
//            $endTime = strtotime(date("Y-m-d ".$end, $timestamp));
//            if ($startTime <= $timestamp && $timestamp <= $endTime) {
//                $mealType = $k;
//                break;
//            }
//        }
        $time = date("Y-m-d H:i:s");
        $dish1= reset($dish);
        $main = [
            'ORDER_NO' => get_order_no(),
            'ORG_CODE' => $dish1['ORG_CODE'],
            'PLACE_ID' => $dish1['PLACE_ID'],
            'USER_ID' => $userId,
            'PAYMENT_ID' => '',
            'STATE' => 'WAIT_PAY',
            'PAY_AMT' => $payAmt,
            'ORDER_AMT' => $payAmt,
            'REMARK' => $param['REMARK'] ?? '',
            'MEAL_TYPE' => $param['MEAL_TYPE'],
            'CODE' => rand_str(32),
            'CREATE_DATE' => $time
        ];

        $ordId = $this->mainModel->insertGetId($main);
        if (!$ordId) {
            app_exception('结算失败，请稍后再试');
        }

        foreach ($detail as &$val) {
            $val['ORD_ID'] = $ordId;
        }

        $rs0 = $this->detailModel->saveAll($detail);
        if (!$rs0) {
            app_exception('系统异常，请稍后再试');
        }
        return [
            'ORDER_ID' => $ordId,
            'ORDER_NO' => $main['ORDER_NO'],
            'PAY_AMT' => $main['PAY_AMT'],
            'ORDER_AMT' => $main['ORDER_AMT'],
        ];
    }

    public function pay(array $param)
    {
        //订单信息
        $main = $this->mainModel->find($param['ORDER_ID']);
        if (empty($main)) {
            app_exception('订单信息异常');
        }

        return $this->payHandle($main, $param);
    }

    public function payOrder(array $param)
    {
        //订单信息
        $main = $this->mainModel->where('ORDER_NO', $param['ORDER_NO'])->find();
        if (empty($main)) {
            app_exception('订单信息异常');
        }

        return $this->payHandle($main, $param);
    }

    public function refund(array $param)
    {
        $main = $this->mainModel->find($param['ORDER_ID']);
        if (empty($main)) {
            app_exception('订单信息异常');
        }
        if ($main['STATE'] == 'REFUND') {
            app_exception('订单退款中，请勿重复操作');
        }
        if ($main['STATE'] != 'PAY_SUCCESS') {
            app_exception('订单未支付成功，无法退款');
        }
        if ($param['REFUND_AMT'] > $main['PAY_AMT']) {
            app_exception('退款金额异常');
        }
        $data = [
            'paymentId' => $main['PAYMENT_ID'],
            'refundAmt' => $param['REFUND_AMT'],
            'sourceTag' => OrderPay::refundTag,
            'refundReason' => $param['REASON'],
        ];
        try {
            $resp = OrderPay::refund($data);
            if ($resp['success'] != true) {
                app_exception('退款请求失败');
            }
            //退款更新
            $update = [
                'STATE' => 'REFUND',
                'REFUND_AMT' => $param['REFUND_AMT'],
                'REMARK' => $param['REASON'],
            ];
            $rs0 = $main->save($update);

            return true;
        } catch (Exception $e) {
            app_exception($e->getMessage());
            return false;
        }

    }

    public function query(string $orderNo)
    {
        $main = $this->mainModel
            ->field('ORDER_NO,STATE,PAY_AMT,REFUND_AMT,PAY_DATE,REFUND_DATE,CREATE_DATE SETTLE_DATE,MEAL_TYPE')
            ->where('ORDER_NO', $orderNo)
            ->find();
        if (!$main) {
            app_exception('订单信息异常');
        }

        return $main;
    }

    protected function payHandle(&$main,array $param)
    {
        if ($main['STATE'] != 'WAIT_PAY') {
            app_exception('系统异常，暂无待支付订单');
        }
        //支付超时判断
        $conf = $this->configModel->getConf('', 'PAY_CONFIG');
        $payConf = [
            "PAY_END_SEC" => $conf['PAY_END_SEC'] ?? 600,
            "REFUND_END_SEC" => $conf['REFUND_END_SEC'] ?? 600
        ];
        if ((time() - strtotime($main['CREATE_DATE'])) > $payConf['PAY_END_SEC']) {
            app_exception('订单支付超时，请重新下单');
        }
        //用户信息
        $data = [
            'openId' => $param['openId'],
            'title' => 'order',
            'body' => $main['ORDER_NO'],
            'payAmt' => $main['PAY_AMT'],
            'ipAddress' => $param['IP'],
            'expireSeconds' => $payConf['PAY_END_SEC'],
            'channel' => $param['CHANNEL'],
            'tradeType' => $param['TRADE_TYPE'],
            'sourceTag' => OrderPay::payTag,
        ];

        try {
            $resp = OrderPay::pay($data);
            if ($resp['success'] != true) {
                app_exception('支付请求失败');
            }
            $res = json_decode($resp['data'], true);
            if (!empty($res['credential'])) {
                $main->save(['STATE'=>'PAYING']);
                return json_decode($res['credential'], true);
            }

        } catch (Exception $e) {
            app_exception($e->getMessage());
        }

        return [];
    }

}