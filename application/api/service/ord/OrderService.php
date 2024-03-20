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

    public function lists(string $orgId, array $param)
    {
        $object = $this->mainModel;

        if (!empty($orgId)) {
            $object->where("ORG_CODE", $orgId);
        }
        if (!empty($param['PLACE_ID'])) {
            $object->where("PLACE_ID", $param['PLACE_ID']);
        }
        if (!empty($param['ORDER_NO'])) {
            $object->where("ORDER_NO", $param['ORDER_NO']);
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
        $userIds = array_column($list['data'], 'USER_ID');
        $userList = $this->userModel->whereIn('ID', $userIds)->column('ID,EMP_ID,NICKNAME,MOBILE', 'ID');

        foreach ($list['data'] as &$v) {
            $user = $userList[$v['USER_ID']] ?? [];
            $v['EMP_ID'] = $user['EMP_ID'] ?? '';
            $v['NICKNAME'] = $user['NICKNAME'] ?? '';
            $v['MOBILE'] = $user['MOBILE'] ?? '';
        }

        return $list;
    }

    public function info(int $orderId)
    {
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
            ->column('ID,CATE_ID,NAME,PRICE');
        $fileList = $this->fileModel->getFile($dishIds);

        foreach ($dishList as &$v) {
            $v['FILE'] = $fileList[$v['ID']] ?? [];
        }
        $detail['DISH'] = $dishList;

        return $detail;
    }
}