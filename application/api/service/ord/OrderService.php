<?php


namespace app\api\service\ord;


use app\api\model\ord\Category;
use app\api\model\ord\Config;
use app\api\model\ord\Dishes;
use app\api\model\ord\File;
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

    public function __construct()
    {
        $this->mainModel = new OrderMain();
        $this->detailModel = new OrderDetail();
        $this->configModel = new Config();
        $this->dishesModel = new Dishes();
        $this->fileModel = new File();
        $this->cateModel = new Category();
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
        if (!empty($param['PAYMENT_ID'])) {
            $object->where("PAYMENT_ID", $param['PAYMENT_ID']);
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

        foreach ($list['data'] as $v) {
            !empty($v['PAY_AMT']) && $v['PAY_AMT'] = $v['PAY_AMT'] / 100;
            !empty($v['ORDER_AMT']) && $v['ORDER_AMT'] = $v['ORDER_AMT'] / 100;
            !empty($v['REFUND_AMT']) && $v['REFUND_AMT'] = $v['REFUND_AMT'] / 100;
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
            ->column('CATE_ID,NAME,PRICE');
        $fileList = $this->fileModel->whereIn('ID', function ($query) use ($dishIds){
            $query->name('dishes_file')->where('DISHES_ID', $dishIds)->field('FILE_ID');
        })->column('ID,FILE_PATH,FILE_TYPE');
        $cateIds = array_column($dishList, 'CATE_ID');
        $cateList = $this->cateModel->whereIn('ID', $cateIds)->column('NAME', 'ID');

    }
}