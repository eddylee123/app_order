<?php

// 公共助手函数

use Symfony\Component\VarExporter\VarExporter;
use \think\Db;

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = \think\Config::get('upload.cdnurl');
        $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = isset($ids[$v['field']]) ? $model->where($primary, 'in', $ids[$v['field']])->column("{$primary}","{$v['column']}") : [];
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 使用短标签打印或返回数组结构
     * @param mixed   $data
     * @param boolean $return 是否返回数据
     * @return string
     */
    function var_export_short($data, $return = true)
    {
        return var_export($data, $return);
        $replaced = [];
        $count = 0;

        //判断是否是对象
        if (is_resource($data) || is_object($data)) {
            return var_export($data, $return);
        }

        //判断是否有特殊的键名
        $specialKey = false;
        array_walk_recursive($data, function (&$value, &$key) use (&$specialKey) {
            if (is_string($key) && (stripos($key, "\n") !== false || stripos($key, "array (") !== false)) {
                $specialKey = true;
            }
        });
        if ($specialKey) {
            return var_export($data, $return);
        }
        array_walk_recursive($data, function (&$value, &$key) use (&$replaced, &$count, &$stringcheck) {
            if (is_object($value) || is_resource($value)) {
                $replaced[$count] = var_export($value, true);
                $value = "##<{$count}>##";
            } else {
                if (is_string($value) && (stripos($value, "\n") !== false || stripos($value, "array (") !== false)) {
                    $index = array_search($value, $replaced);
                    if ($index === false) {
                        $replaced[$count] = var_export($value, true);
                        $value = "##<{$count}>##";
                    } else {
                        $value = "##<{$index}>##";
                    }
                }
            }
            $count++;
        });

        $dump = var_export($data, true);

        $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
        $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
        $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties
        $dump = preg_replace('#\)$#', "]", $dump); //End

        if ($replaced) {
            $dump = preg_replace_callback("/'##<(\d+)>##'/", function ($matches) use ($replaced) {
                return isset($replaced[$matches[1]]) ? $replaced[$matches[1]] : "''";
            }, $dump);
        }

        if ($return === true) {
            return $dump;
        } else {
            echo $dump;
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('check_nav_active')) {
    /**
     * 检测会员中心导航是否高亮
     */
    function check_nav_active($url, $classname = 'active')
    {
        $auth = \app\common\library\Auth::instance();
        $requestUrl = $auth->getRequestUri();
        $url = ltrim($url, '/');
        return $requestUrl === str_replace(".", "/", $url) ? $classname : '';
    }
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN']) {
            $info = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr = explode(',', config('fastadmin.cors_request_domain'));
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                exit;
            }
        }
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清理XSS
     */
    function xss_clean($content, $is_image = false)
    {
        return \app\common\library\Security::instance()->xss_clean($content, $is_image);
    }
}

function logs_write($str , $line=__LINE__, $filename='bigdata'){
    if (empty($str)) {
        return '';
    }
    if(is_array($str)){
        if(defined("JSON_UNESCAPED_UNICODE")){
            $str1 = json_encode($str , JSON_UNESCAPED_UNICODE);
        }
        else{
            $str1 = json_encode($str);
        }
    }
    else{
        $str1 = $str;
    }
    $dir = '/pro/log/'.date('Ym').'/';
    is_dir($dir) OR mkdir($dir, 0777, true);
    $file = fopen( $dir. $filename . date('d') .'.log', 'a+'); // a模式就是一种追加模式，如果是w模式则会删除之前的内容再添加
    //fwrite($file, date('H:i:s'). ' '. $str1 . ",line$line\r\n");
    fwrite($file, $str1 . "\r\n");
    fclose($file);
    unset($file);
}


function replace_write($str , $filename='bigdata'){
    if (empty($str)) {
        return '';
    }
    if(is_array($str)){
        if(defined("JSON_UNESCAPED_UNICODE")){
            $str1 = json_encode($str , JSON_UNESCAPED_UNICODE);
        }
        else{
            $str1 = json_encode($str);
        }
    }
    else{
        $str1 = $str;
    }
    $dir = '/pro/log/'.date('Ym').'/';
    is_dir($dir) OR mkdir($dir, 0777, true);
    $file = fopen( $dir. $filename .'.sql', 'w+'); // a模式就是一种追加模式，如果是w模式则会删除之前的内容再添加
    //fwrite($file, date('H:i:s'). ' '. $str1 . ",line$line\r\n");
    fwrite($file, $str1 . "\r\n");
    fclose($file);
    unset($file);
}

function clear_write($filename='bigdata'){
    $dir = '/pro/log/'.date('Ym').'/';
    is_dir($dir) OR mkdir($dir, 0777, true);
    $file = fopen( $dir. $filename . date('d') .'.log', 'w+'); // a模式就是一种追加模式，如果是w模式则会删除之前的内容再添加
    //fwrite($file, date('H:i:s'). ' '. $str1 . ",line$line\r\n");
    fwrite($file, '');
    fclose($file);
    unset($file);
}

function alone_redis(){
    $redis_master = config('redis_master');
    //连接本地的 Redis 服务
    $redis = new \Redis();
    //连接redis     地址    端口   连接超时时间  连接成功返回true  失败返false
    $redis->connect($redis_master['hostname'], $redis_master['hostport']);
    //连接redis密码认证，成功返回true 失败返回false
    if($redis_master['password']){
        $status = $redis->auth($redis_master['password']);
    }
    //查看连接状态 连接正常返回+PONG
    if($redis->ping() !='+PONG'){
        exit('redis连接失败');
    }
    return $redis;
}

function encryptDecrypt($key , $input,$decrypt=false){
    $ext = '1234567890abcdef';
    if(!$decrypt){
        $data = openssl_encrypt($input, 'AES-256-CBC', $key , OPENSSL_RAW_DATA, $ext);
        $data = base64_encode($data);
        return $data;
    }
    else{
        $decrypted = openssl_decrypt(base64_decode($input), 'AES-256-CBC', $key , OPENSSL_RAW_DATA,$ext);
        return $decrypted;
    }
}

function key_val_row($arr , $key=''){
    $arr1 = json_decode(json_encode($arr) , true);
    $result = [];
    if($key){
        foreach($arr1 as $r){
            $result[$r[$key]] = $r;
        }
    }
    else{
        foreach($arr1 as $r){
            $rows = array_values($r);
            $result[$rows['0']] = $rows['1'];
        }
    }
    return $result;
}

function obj_to_array(&$arr){
    $arr = json_decode(json_encode($arr) , true);
}

if(!function_exists('position_ad')){
    function position_ad($position_id=1){
        $model = \think\Db::table('fa_position');
        $date =time();
        $lists = $model->alias('a')
            ->field('a.width,a.height,b.weigh,b.id,b.url,b.title,b.image')
            ->join('position_ad b' , 'a.id = b.position_id' , 'left')
            ->where("a.id = '$position_id' and b.status_switch=1 and $date between b.start_time and b.end_time")
            ->order('b.weigh desc')
            ->select();
        //echo '<pre>';print_r($lists);exit;
        $count = count($lists);
        if($count>1){
            $uid = 0;
            $list_new = [];
            foreach($lists as $k=>$r){
                $r['width'] = !empty($r['width']) ? $r['width'].'px':'100%';
                $r['height'] = !empty($r['height']) ? $r['height'].'px':'auto';
                $list_new[] = $r;
            }
            $lists = $list_new;
            $count = count($lists);
        }
        $width = !empty($lists['0']['width']) ? $lists['0']['width'].'px':'100%';
        $height = !empty($lists['0']['height']) ? $lists['0']['height'].'px':'auto';
        if($count==1 && !empty($lists['0']['id'])){
            return "<a href='{$lists["0"]["url"]}' target='_blank' title='{$lists["0"]["title"]}'><img src='{$lists["0"]["image"]}' style='width:{$width};height:{$height}' alt='{$lists["0"]["title"]}' />";
        }
        elseif($count>1){
            return $lists;
        }
        else{
            return "<a href='ad/index' style='width:$width;height:$height;line-height:$height;display:block;vertical-align:middle;background:#efefef;font-weigh:bold;font-size:28px;' class='text-center'>广告招商</a>";
        }
    }
}

if(!function_exists('get_position')){
    function get_position($key=''){
        $model = \think\Db::table('fa_position');
        $arr = $model->field('id,title')->select();
        $arr = key_val_row($arr);
        if(empty($key)){
            return [''=>'请选择'] + $arr;
        }
        elseif(empty($arr[$key])){
            return $key;
        }
        else{
            return $arr[$key];
        }
    }
}

if(!function_exists('tongyiurl')){
    function tongyiurl($str , $cat=''){
        switch($cat){
        default:
            $url = $cat ? '/'.$cat.'/'.$str : '/'.$str;
        }
        return $url;
    }
}

function get_top($id){
    $model = \think\Db::name('nav');
    $nav = $model->field('catid')->where("id='$id'")->find();
    //echo $model->getLastSql(); exit();
    return $nav['catid'];
}
function get_down($id){
    $model = \think\Db::name('nav');
    $nav = $model->field('id')->where("catid='$id'")->select();
    $rows = $nav ? array_column($nav, 'id')+[''=>$id] : [$id];
    return implode(',' , $rows);
}

function nav_info($id , $field='*'){
    $nav = Db::name('nav')->field($field)->where("id='$id'")->find();
    return $nav;
}

function table_row($table , $field='*' , $where='' , $is_empty=false){
    $lists = Db::query("select $field from " . config('database.prefix') . $table . ' ' . $where );
    if(!$is_empty){
        return key_val_row($lists);
    }
    else{
        return [''=>'请选择'] + key_val_row($lists);
    }
}

function table_list($table , $where='' , $field='*'){
    $lists = Db::query("select $field from " . PREFIX . $table . ' ' . $where );
    return $lists;
}

function heyuedizhi($key=''){
    $arr = [
        //'USDT-ERC20'
        2=>'0xdac17f958d2ee523a2206206994597c13d831ec7',
        //'USDT-BEP20'
        3=>'0xdac17f958d2ee523a2206206994597c13d831ec7',
        //'USDT-TRC20'
        1=>'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
        ];
}


//type= ['check' , 'create'];
function check_create($type='check' , $oneCode='' , $secret=''){

    require_once APP_PATH . '../vendor/google/PHPGangsta/GoogleAuthenticator.php';

    $ga = new \PHPGangsta_GoogleAuthenticator();
    if($type=='create'){
        // 1.创建秘钥
        $secret = $ga->createSecret();
        //echo "Secret is: ".$secret."\n\n";
        //echo "密匙" . $secret . "<br>";

        // 2. 生成秘钥对应的二维码
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('Blog', $secret);
        //$qrCodeUrl = $ga->getQRCodeGoogleUrl('Blog', '');
        //echo "Google Charts URL for the QR-Code: ".$qrCodeUrl."<br>";
        $array = [
            'url'=>$qrCodeUrl, 
            'secret'=> $secret,
            ];
        return $array;
    }
    else{
        // 3.生成秘钥对应的动态码
        //$oneCode = $ga->getCode($secret);

        //echo "Checking Code '$oneCode' and Secret '$secret'<br>";
        //echo "被检查的动态码： '$oneCode' and 密码是  '$secret'<br>";

        // 4.检查传递的秘钥与动态码是否匹配。最后一个参数应该是时间。值是30秒的倍数。
        // 但是谷歌验证器 app  的动态码好像是30s 变化一次，这个我没注意
        $checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
        if ($checkResult) {
            return true;
        } else {
            return false;
        }

    }
}

function field_table(&$lists , $field='config_id' , $val='activity_title' , $table = 'bounty_group_config', $key_id='id'){
    $ids =  implode(',' , array_column($lists , $field));
    $datas = \think\Db::name($table)->field("$key_id,$val")->where($key_id , 'in', $ids)->select();
    obj_to_array($datas);
    $ids_arr = key_val_row($datas);
    foreach($lists as $k=>$r){
        $lists[$k][$field] = isset($ids_arr[$r[$field]]) ? $ids_arr[$r[$field]]:$r[$field];
    }
}

function member_type($catid){
    $datas = \think\Db::name('member_type')->field("id, catname")->where("catid" ,$catid)->select();
    $ids_arr = key_val_row($datas);
    return $ids_arr;
}

function create_icon($str){
    if(stripos($str ,  'CentOS')>-1 || stripos($str ,  'linux')>-1){
        $str = str_replace(['CentOS' ,  'linux'] ,  '<img src="/assets/img/li1.ico" class="w20" />' ,  $str); 
    }

    if(stripos($str ,  'Windows Server')>-1 ){
        $str = str_replace(['Windows Server'] ,  '<img src="/assets/img/windows_server.ico" class="w20" />' ,  $str); 
    }
    return $str;
}

function config_cat($cat=''){
    return  config('site.'.$cat); 
}

function admin_nickname($is_empty=0){
    $datas = \think\Db::name('admin')->field("username, nickname")->where("status='normal' and username !='test'")->select();
    $ids_arr = key_val_row($datas);
    if($is_empty){
        return [''=>'请选择'] + $ids_arr;
    }
    else{
        return $ids_arr;
    }
}

function where_to_sql1($where1){
    $func = new ReflectionFunction($where1);
    $where = $func->getStaticVariables();
    $sql_arr = []; 
    if(!empty($where['where'])){
        foreach($where['where'] as $v){
            switch($v['1']){
            case 'BETWEEN TIME':
                $sql_arr[] = "`{$v['0']}` between '" . trim($v['2']['0'])."' and '" . trim($v['2']['1']) . "'"; 
                break;
            case 'LIKE':
                $sql_arr[] = "`{$v['0']}` like '" . trim($v['2']) . "'"; 
                break;
            default:
                $sql_arr[] = "`{$v['0']}` = '" . trim($v['2']) . "'"; 
                break;
            }
        }
    }
    if($sql_arr){
        $sql = ' where ' . implode(' and ' ,  $sql_arr);
        return $sql; 
    }
    else{
        return '';
    }
}

function rand_str($length){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $randStr = str_shuffle($str);//打乱字符串
    return substr($randStr,0,$length);//substr(string,start,length);返回字符串的一部分
}

/**
 * 在字符串首尾拼接字符
 *
 * @param string $str  字符串
 * @param string $char 要拼接的字符
 *
 * @return string
 */
function str_join($str, $char = ',')
{
    $str = $char . $str . $char;

    return $str;
}

/**
 * 去除字符串首尾字符
 *
 * @param string $str  字符串
 * @param string $char 要去除的字符
 *
 * @return string
 */
function str_trim($str, $char = ',')
{
    $str = trim($str, $char);

    return $str;
}


/**
 * 获取年龄
 * @param $birthday
 * @return false|mixed|string
 * DateTime: 2024-03-14 16:58
 */
function get_age($birthday)
{
    list($year, $month, $day) = explode("-", $birthday);
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff = date("d") - $day;
    if ($day_diff < 0 || $month_diff < 0)
        $year_diff--;
    return $year_diff;
}

/**
 * 获取日期差
 * @param $date1
 * @param $date2
 * @return float|int
 * DateTime: 2024-03-14 16:58
 */
function get_day($date1, $date2)
{
    return ceil((strtotime($date2) - strtotime($date1)) / 86400);
}

/**
 * 请求
 * @param $url
 * @param string $method
 * @param string $data
 * @param array $header
 * @param bool $json
 * @return bool|string
 * DateTime: 2024-03-22 11:06
 */
function curl_request($url, $method = '', $data = '', $header = [], $json=true)
{
    $data_string = json_encode($data);

    $action = curl_init();
    curl_setopt($action, CURLOPT_URL, $url);
    curl_setopt($action, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($action, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($action, CURLOPT_HEADER, 0);
    curl_setopt($action, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($action, CURLOPT_SSL_VERIFYHOST, 0);

    $httpHeader = [
        'Content-Length: ' . strlen($data_string)
    ];
    if ($json) {
        $httpHeader = array_merge($httpHeader, ['Content-Type: application/json']);
    } else {
        $httpHeader = array_merge($httpHeader, ['Content-Type: multipart/form-data']);
    }
    if ($header) {
        $httpHeader = array_merge($httpHeader, $header);
    }
    if (strtoupper($method) == 'POST') {
        curl_setopt($action, CURLOPT_POST, 1);
        curl_setopt($action, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($action, CURLOPT_HTTPHEADER, $httpHeader);
    }
    $result = curl_exec($action);
    curl_close($action);

    return $result;
}

