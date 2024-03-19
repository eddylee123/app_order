<?php
namespace app\api\service\user;

use app\api\service\BaseService;
use app\admin\model\base\BaseUser;
use app\api\service\api\TokenService;
use app\cache\BaseCache;
use fast\Arr;
use think\Config;

class UserService extends BaseService
{
    protected $redis;
    protected $userModel;

    public function __construct()
    {
        $this->redis = alone_redis();
        $this->userModel = new BaseUser();
    }

    public function login(array $param)
    {
        $account = trim($param['account']);
        $password = md5($param['password']);

        //验证账号是否存在
        $user = $this->userModel
            ->where(['ACCOUNT'=>$account,'ACC_TYPE'=>BaseUser::$accType])
            ->find();
        if (empty($user)) {
            app_exception('账号不存在');
        }
        if ($password != $user['PASSWORD']) {
            app_exception('密码错误');
        }
        if ($user['STATUS'] != 'NORMAL') {
            app_exception('账户禁用，请联系管理员');
        }

        $user->save(['LAST_LOGIN_DATE'=>date("Y-m-d H:i:s")]);
        $user['token'] = TokenService::create($user);

        $token = TokenService::config();
        $key = sprintf(BaseCache::user_token, $user['ACCOUNT']);
        $this->redis->set($key, $user->toJson(), (int)$token['token_exp']);

        return [
            'account'=>$user['ACCOUNT'],
            'token'=>$user['token'],
            ];
    }

    public function logout(string $token)
    {
        $account = TokenService::getAccount($token);

        $key = sprintf(BaseCache::user_token, $account);
        $this->redis->del($key);

        return compact('account');
    }

    public function getUser(string $account)
    {
        $key = sprintf(BaseCache::user_token, $account);
        $data = $this->redis->get($key);
        return !empty($data) ? json_decode($data, true) : [];
    }
}