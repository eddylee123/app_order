<?php


namespace app\api\controller;


use app\api\library\traits\General;
use app\api\service\api\TokenService;
use think\Controller;
use think\Exception;
use think\Log;
use think\Request;

class BaseController extends Controller
{
    use General;

    /**
     * @var Request
     */
    protected $request;

    protected $OrgId = '';
    protected $User = [];
    protected $Data = [];

    public function _initialize()
    {
        //跨域请求检测
        check_cors_request();

        $this->request = Request::instance();

        try {
            $param = $this->request->param();
            $this->User = $param['userInfo'] ?? [];
            $this->OrgId = $param['userInfo']['orgId'] ?? '';

            if (!is_string($param['data'])) {
                throw new Exception('请求参数异常');
            }

            !empty($param['data']) && $this->Data = json_decode($param['data'], true);

        } catch (Exception $e) {
            app_exception($e->getMessage());
        }


        // 菜单是否无需登录
//        if (!$this->menuIsUnLogin()) {
//            $token = $this->request->header('authorization');
//
//            if (empty($token)) {
//                app_exception('登录状态已失效，请重新登录!');
//            }
//
//            // 用户Token验证
//            $this->User = TokenService::verify($token);
//        }
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed  $data   要返回的数据
     * @param int    $code   返回的 code
     * @param mixed  $msg    提示信息
     * @param string $type   返回数据格式
     * @param array  $header 发送的 Header 信息
     * @return void
     */
    protected function result($data, $code = 0, $msg = '', $type = 'json', array $header = [])
    {
        parent::result($data, $code, $msg, $type, $header);
    }
}