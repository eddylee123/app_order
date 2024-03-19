<?php

namespace app\common\model;
use think\Cache;
use think\Model;

/**
 * 分类模型
 */
class Common extends Model
{

    // protected $model;
    protected static function init()
    {
        //$model = Model;
    }

    public function nav_list($id=0)
    {
        $query = array(
            'table'=>'nav',
            'field'=>'id,url,title,name',
            'where'=>['web_show'=>'Y','status'=>'Y'],
            'order'=>'id asc',
            'limit'=>8,
            'types'=>$id ? 'find':'select',
            );
        if($id){
            $query['where']['id'] = $id;
        }
        $this->select_cache($datas , $query);
        return $datas;
    }

    /*
     *query数组的元素
     *sql多表查询
     *types sql方法
     *table数据表不带前缀
     *field字段，默认*
     *where参数
     *order排序
     *limit限制条数
     */
    public function select_cache(&$lists , $query, $time=0){
        $time = $time ? $time:config('site.cache_time');
        $query['page'] = isset($_GET['page']) ? intval($_GET['page']):1;
        $query['types'] = isset($query['types']) ? $query['types']:'select';
        $query['table'] = isset($query['table']) ? 'fa_'.$query['table']:'fa_news';
        $sql = !empty($query['sql']) ? $query['sql']:'';
        $redis_key = $sql ? $query['table'].':'.$sql:$this->sql_key($query , 1);
        //if($query['table']=='fa_wish a'){
        //echo $redis_key; exit();
        //}
        if($time && Cache::has($redis_key)){
            $lists = $query['types']!='paginate' ? json_decode(Cache::get($redis_key) , true):unserialize(Cache::get($redis_key));
        }
        else{
            if($sql){
                $lists = $this->query($sql);
            }
            else{
                $obj = $this->sql_key($query , 0);
                if(isset($query['limit'])){
                    if(strpos($query['limit'], ',')>-1){
                        $obj = $obj->limit($query['limit']);
                    }
                    else{
                        $obj = $obj->limit("0,{$query['limit']}");
                    }
                }
                if($query['types']=='paginate'){
                    $limit = isset($query['limit']) ? $query['limit']:20;
                    $lists = $obj->paginate($limit);
                }
                elseif($query['types']=='find'){
                    $lists = $obj->find();
                }
                elseif($query['types']=='count'){
                    $lists = $obj->count();
                }
                else{
                    $lists = $obj->select();
                }
            }
            if($time){
                Cache::set($redis_key , ($query['types']=='paginate' ? serialize($lists):json_encode($lists , 320)) , $time);
            }
            //obj_to_array($lists);
        }
    }

    /*
     *根据query条件生成sql语句
     */
    public function sql_key($query , $is_sql = 1){
        $order = isset($query['order']) ? $query['order']:[];
        $obj = $this->table($query['table']);
        if(isset($query['join'])){
            $obj = $obj->join($query['join']['0'] , $query['join']['1'] , !empty($query['join']['2']) ? $query['join']['2']:'');
        }
        if(isset($query['field'])){
            $obj = $obj->field($query['field']);
        }
        if(isset($query['where'])){
            $obj = $obj->where($query['where']);
        }
        if(isset($query['order'])){
            $obj = $obj->order($query['order']);
        }
        if($is_sql==1){
            if(isset($query['types'])=='paginate'){
                $limit = isset($query['limit']) ? intval($query['limit']):20;
                //var_dump($limit);exit;
                $start = $query['page']*$limit-$limit;
                $obj = $obj->limit($start , $limit);
            }
            elseif(isset($query['limit'])){
                $obj = $obj->limit($query['limit']);
            }
            $sql = $obj->buildsql(false);
            $sql = str_replace('  ' , ' ' , $sql);
            return $query['table'] . ':'.$sql;
        }
        else{
            return $obj;
        }
    }

    // field 代表要更新的字段
    // 更新步长为10
    public function delay_update($query){
        $table = isset($query['table']) ? $query['table']:'news';
        $where = isset($query['where']) ? $query['where']:['id'=>1];
        $field = isset($query['field']) ? $query['field']:'count';
        $redis_key = $table.':'.$field.':'.current($where);
        if(Cache::has($redis_key)){
            Cache::inc($redis_key);
            $count = Cache::get($redis_key);
            if($count%10==0){
                $datas[$field] = $count;
                $this->table('fa_'.$table)->where($where)->update($datas);
            }
        }
        else{
            $rows = $this->table('fa_'.$table)->field($field)->where($where)->find();
            $count = $rows[$field]+1;
            Cache::set($redis_key , $count);
        }
        return $count;
    }

    public function field_table(&$lists , $field='menu_id' , $val='title' , $table = 'news'){
        $ids =  '';
        foreach($lists as $r){
            $ids .= $ids ? ','.$r[$field]:$r[$field];
        }
        $datas = $this->table('fa_'.$table)->field("id,$val")->where('id' , 'in', $ids)->select();

        $ids_arr = [];
        foreach($datas as $r){
            $ids_arr[$r['id']] = $r[$val];
        }
        foreach($lists as $k=>$r){
            $lists[$k][$field] = isset($ids_arr[$r[$field]]) ? $ids_arr[$r[$field]]:$r[$field];
        }
    }

    public function statistics($url , $uid=0){
        $datas['url'] = $url;
        $datas['long_link'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']:$url;
        $datas['long_link'] = $url =='index/search' ? $url:$datas['long_link'];
        $datas['browser'] = getClientBrowser();
        $datas['ip'] = get_client_ip();
        $datas['from_uid'] = $uid ? $uid:0;
        $datas['ctime'] = date('Y-m-d H:i:s');
        //$this->table('fa_statistics_detail')->insert($datas);
        $this->delay_insert(json_encode($datas, 320) , 'statistics_detail' , 10);
    }

    public function delay_insert($sql_json , $table ,  $step=10){
        $table = strpos($table , 'fa_') > -1 ? $table:'fa_'.$table;
        $redis_key = $table . ':delay_insert';
        if(Cache::llen($redis_key)>=$step){
            $sql_arr = Cache::lrange($redis_key , 0 , $step-1);
            $all = [];
            foreach($sql_arr as $r){
                $all[] = json_decode($r , true);
            }
            $this->table($table)->insertAll($all);
            Cache::ltrim($redis_key , $step , -1);
        }
        Cache::rpush($redis_key , $sql_json);
    }

}
