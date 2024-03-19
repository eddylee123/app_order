<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
//可选参数用[]
//Route::rule('nav/[:id]','index/index/nav');
Route::rule('nav/:id','index/index/nav');
Route::rule('detail/:id','index/index/detail');
Route::rule('search/:keywords','index/index/search');
Route::rule('mianshi/[:keywords]','index/index/mianshi');
Route::rule('bookmark/[:keywords]','index/index/bookmark');
return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [
    //'nav/:id'=>'index/index/nav',
    ],
    //变量规则
    '__pattern__' => [
    ],
//        域名绑定到模块
//        '__domain__'  => [
//            'admin' => 'admin',
//            'api'   => 'api',
//        ],
];

