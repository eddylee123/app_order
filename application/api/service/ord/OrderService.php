<?php


namespace app\api\service\ord;


use app\api\model\ord\Category;
use app\api\model\ord\Config;
use app\api\model\ord\Dishes;
use app\api\model\ord\File;
use app\api\model\ord\User;
use app\api\model\order\OrderDetail;
use app\api\model\order\OrderMain;
use app\api\service\BaseService;
use think\Db;

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
            ->order('CREATE_DATE')
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
            ->field('ORDER_NO,STATE,PAY_AMT,ORDER_AMT,MEAL_TYPE')
            ->find($orderId);


        $detail = $this->detailModel
            ->where('ORD_ID', $orderId)
            ->select();
        if (empty($detail)) {
            return [];
        }
        $detail = collection($detail)->toArray();
        $dishIds = array_column($detail, 'DISH_ID');
        $dishList = $this->dishesModel
            ->whereIn('ID', $dishIds)
            ->field('ID,CATE_ID,NAME,PRICE')
            ->select();
        $dishList = collection($dishList)->toArray();
        $fileList = $this->fileModel->getFile($dishIds);

        foreach ($dishList as &$v) {
            $v['FILE'] = $fileList[$v['ID']] ?? [];
        }
        $detail['ORDER'] = $order;
        $detail['DISH'] = $dishList;

        return $detail;
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

        $mealTime = [
            'BREAKFAST' =>['06:00:00','10:59:59'],
            'LUNCH' =>['11:00:00','16:59:59'],
            'DINNER' =>['17:00:00','21:59:59'],
            'SNACK' =>['22:00:00','23:59:59'],
        ];
        $mealType = '未知';
        $timestamp = time();
        foreach ($mealTime as $k=>$v) {
            [$start, $end] = $v;
            $startTime = strtotime(date("Y-m-d ".$start, $timestamp));
            $endTime = strtotime(date("Y-m-d ".$end, $timestamp));
            if ($startTime <= $timestamp && $timestamp <= $endTime) {
                $mealType = $k;
                break;
            }
        }
        $time = date("Y-m-d h:i:s");
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
            'MEAL_TYPE' => $mealType,
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



}