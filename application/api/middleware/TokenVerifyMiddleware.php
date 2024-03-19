<?php
/**
 * Created by PhpStorm.
 * User: Guozhi
 * Date: 2021/07/21
 * Time: 11:49
 */

// Token验证中间件
namespace app\api\middleware;

use app\api\library\traits\General;
use app\api\service\api\TokenService;
use Closure;
use think\Request;
use think\Response;


class TokenVerifyMiddleware
{
    use General;
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 菜单是否无需登录
        if (!$this->menuIsUnLogin()) {
            $token = $request->param('token/s', '');

            if (empty($admin_token)) {
                app_exception('登录状态已失效，请重新登录!');
            }

            // 用户Token验证
            TokenService::verify($token);
        }

        return $next($request);
    }
}
