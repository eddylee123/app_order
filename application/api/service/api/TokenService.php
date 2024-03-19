<?php
namespace app\api\service\api;



use app\admin\model\base\BaseUser;
use app\api\service\BaseService;
use app\api\service\user\UserService;
use Firebase\JWT\JWT;
use think\Config;

class TokenService extends BaseService
{
    /**
     * Token配置
     *
     * @return array
     */
    public static function config()
    {
        return [
            'token_key' => Config::get('site.token_key'),
            'token_exp' => Config::get('site.token_exp'),
            ];
    }

    /**
     * Token生成
     * 
     * @param array $user 用户信息
     * 
     * @return string
     */
    public static function create($user)
    {
        $config = self::config();

        $key = $config['token_key'];                  //密钥
        $iat = time();                                //签发时间
        $nbf = time();                                //生效时间
        $exp = time() + $config['token_exp'];  //过期时间

        $data = [
            'account'   => $user['ACCOUNT'],
            'acc_type' => $user['ACC_TYPE'],
        ];

        $payload = [
            'iat'  => $iat,
            'nbf'  => $nbf,
            'exp'  => $exp,
            'data' => $data,
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * Token验证
     *
     * @param string $token token
     * 
     * @return Exception
     */
    public static function verify($token)
    {
        try {
            $config = self::config();
            $decode = JWT::decode($token, $config['token_key'], array('HS256'));

            $account   = $decode->data->account;
            $acc_type = $decode->data->acc_type;
        } catch (\Exception $e) {
            app_exception('登录状态已过期,请重新登录!');
        }

        $user = UserService::instance()->getUser($account);

        if (empty($user) || $acc_type != BaseUser::$accType) {//校验用户类型
            app_exception('登录状态已失效,请重新登录!');
        } else {
            if ($token != $user['token']) {
                app_exception('账号已在另一处登录!');
            } else {
                if ($user['STATUS'] == 'LOCKED') {
                    app_exception('账号已被禁用!');
                }
            }
        }

        return $user;
    }

    /**
     * Token用户id
     *
     * @param string $token token
     * 
     * @return string account
     */
    public static function getAccount($token)
    {
        try {
            $config = self::config();
            $decode = JWT::decode($token, $config['token_key'], array('HS256'));

            $account = $decode->data->account;
        } catch (\Exception $e) {
            $account = 0;
        }

        return $account;
    }
}
