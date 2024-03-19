<?php


namespace app\api\controller\user;


use app\api\controller\BaseController;
use app\api\service\fee\FeeService;

class Fee extends BaseController
{
    public function __construct()
    {
        parent::_initialize();
    }

    public function lists()
    {
        $this->Data['page'] = $this->Data['page'] ?? 1;
        $this->Data['page_size'] = $this->Data['page_size'] ?? 10;

        $rs = FeeService::instance()->lists($this->OrgId, $this->Data);

        app_response(200, $rs);
    }
}