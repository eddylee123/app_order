<?php
namespace app\api\controller\ord;

use app\api\controller\BaseController;
use app\api\service\ord\CategoryService;

class Category extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    public function listTree()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 999;
        if (empty($this->Data['MEAL_TYPE'])) {
            $this->Data['MEAL_TYPE'] = 'LUNCH';
        }
        $rs = CategoryService::instance()->listTree($this->OrgId, $this->Data['MEAL_TYPE']);

        app_response(200, $rs);
    }

    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 999;
        $rs = CategoryService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}