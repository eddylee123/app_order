<?php
namespace app\api\controller\conf;

use app\api\controller\BaseController;

class Category extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = DishesService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}