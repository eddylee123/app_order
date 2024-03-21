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

        $rs = CategoryService::instance()->listTree();

        app_response(200, $rs);
    }
}