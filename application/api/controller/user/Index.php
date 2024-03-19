<?php
namespace app\api\controller\user;

use app\admin\validate\User;
use app\api\controller\BaseController;
use app\api\service\user\UserService;

class Index extends BaseController
{

//    public function login()
//    {
//        $param['account'] = $this->request->param('username/s', '');
//        $param['password'] = $this->request->param('password/s', '');
//
//        validate(User::class)->scene('login')->check($param);
//
//        $data = UserService::instance()->login($param);
//
//        app_response($data,200);
//    }

//    public function logout()
//    {
//        $data = UserService::instance()->logout($this->User['token']);
//
//        app_response($data,200);
//    }
}