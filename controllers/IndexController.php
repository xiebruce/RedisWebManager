<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-13
 * Time: 16:33
 */

namespace app\controllers;

use Yii;
use Redis;
use yii\data\Pagination;

class IndexController extends BaseController
{
	/**
	 * redis key list
	 * @return string
	 */
	public function actionIndex(){
		/*if(!$this->readonly_auth && !$this->delete_auth){
			return $this->redirect('/loginadmin/pclogin?bakurl=/tools/redis-key-list');
		}*/
		$this->layout = 'main_bootstrap';
		$keyword = Yii::$app->request->get('keyword','');
		$keyword = trim($keyword);
		$iterator = Yii::$app->request->get('iterator');
		$db = Yii::$app->request->get('db',0);
		$queryParams = Yii::$app->request->queryParams;
		if(!$iterator){
			$iterator = null;
		}else{
			$iterator = intval($iterator);
		}
		$queryParams['iterator'] = $iterator;
		
		$redis = $this->connectRedis();
		$redis->select($db);
		$count = $redis->dbSize();
		
		$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
		
		$pageSize = 10;
		$pagination = new Pagination([
			'totalCount'=>$count,
			'pageSize'=>$pageSize,
			'params'=>$queryParams,
		]);
		
		$search_result = $this->wildCardSearchKey($redis,"*$keyword*",10);
		$keys = $search_result['keys'];
		$iterator = $search_result['iterator'];
		
		$pagination->params['iterator'] = $iterator;
		
		$match_count_real = false;
		$match_count = count($keys);
		if(!$iterator){
			$match_count_real = true;
			$pagination->totalCount = $count;
		}
		
		$info = $redis->info();
		$arr = $redis->config('get', 'databases');
		$databaseCount = $arr['databases'] ?? 16;
		// var_dump($info);

		/*$accepted_language = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if(preg_match('/zh-cn/')){

		}else if(preg_match('/en-us/')){

		}*/
		$visitor_ip = Yii::$app->request->remoteIP;
		return $this->render('index',[
			'code'=>0,
			'keys'=>$keys,
			'keyword'=>$keyword,
			'pagination'=>$pagination,
			'info'=>$info,
			'count'=>$count,
			'match_count'=>$match_count,
			'match_count_real'=>$match_count_real,
			'visitor_ip'=>$visitor_ip,
			'delete_auth'=>true,
			'databaseCount'=>$databaseCount,
			'db'=>$db,
		]);
	}
	
	/**
	 * delete redis key
	 * @return string
	 */
	public function actionDelRedisKey(){
		/*if(!$this->delete_auth){
			return json_encode(['code'=>-1,'msg'=>'authentification failed']);
		}*/
		$keys = Yii::$app->request->post('keys');
		if(!$keys){
			return json_encode(['code'=>-1,'msg'=>'no key']);
		}
		
		$redis = $this->connectRedis();
		if(is_array($keys)){
			$pipe = $redis->multi(Redis::PIPELINE);
			foreach($keys as $key){
				$pipe->del($key);
			}
			$pipe_lines = $pipe->exec();
			return json_encode(['code'=>0,'msg'=>'del succeed','pipe_line_results'=>json_encode($pipe_lines)]);
		}else{
			if($redis->del($keys)){
				return json_encode(['code'=>0,'msg'=>'del succeed']);
			}
		}
	}
	
	/**
	 * get reids value by key
	 * @return string
	 */
	public function actionGetRedisVal(){
		/*if(!$this->readonly_auth && !$this->delete_auth){
			return json_encode(['code'=>-1,'msg'=>'authentification failed']);
		}*/
		$key = Yii::$app->request->post('key');
		$key = trim($key);
		$arr = $this->getRedisVal($key);
		$value = var_export($arr['value'],true);
		return json_encode(['code'=>0,'value'=>$value,'key_type'=>$arr['key_type'],'value_type'=>$arr['value_type']]);
	}
	
	/**
	 * Get redis value
	 * @param $key
	 * @return array
	 */
	public function getRedisVal($key){
		$redis = Yii::$app->redis;
		$value = $redis->get($key);
		$key_type = $redis->type($key);
		if($this->is_serialized($value)){
			$value = unserialize($value);
			$value_type = gettype($value);
			if($value_type=='object' && $value->attributes){
				$value = $value->attributes;
			}
		}else{
			$decode_value = json_decode($value,true);
			if(json_last_error()===JSON_ERROR_NONE){
				$value = $decode_value;
			}
			$value_type = gettype($value);
		}
		return ['value'=>$value,'key_type'=>$key_type,'value_type'=>$value_type];
	}
	
	/**
	 * flushdb or flushall
	 * @return string
	 */
	public function actionFlushDb(){
		/*if(!$this->delete_auth){
			return json_encode(['code'=>-1,'msg'=>'authentification failed']);
		}*/
		$flush_type = Yii::$app->request->post('flush_type');
		$password = Yii::$app->request->post('password');
		$password = trim($password);
		if($password!=Yii::$app->params['flushPwd']){
			return json_encode(['code'=>-1,'msg'=>'Password error']);
		}
		$redis = $this->connectRedis();
		if($flush_type=='flush-db'){
			$db = Yii::$app->request->post('db');
			$redis->select($db);
			$ret = $redis->flushDb();
			if($ret){
				return json_encode(['code'=>0,'msg'=>'Flush-DB succeed!']);
			}else{
				return json_encode(['code'=>-1,'msg'=>'Flush-DB failed!']);
			}
		}else if($flush_type=='flush-all'){
			$ret = $redis->flushAll();
			if($ret){
				return json_encode(['code'=>0,'msg'=>'Flush-All succeed!']);
			}else{
				return json_encode(['code'=>-1,'msg'=>'Flush-All failed!']);
			}
		}
	}
	
	/**
	 * View Redis Value
	 * @return string
	 */
	public function actionViewRedisValue(){
		$keyword = Yii::$app->request->get('keyword');
		$specified_key = Yii::$app->request->get('specified_key');
		/*if(!$this->readonly_auth && !$this->delete_auth){
			return $this->redirect('/loginadmin/pclogin?bakurl=/tools/view-redis-value?specified_key='.$specified_key.'&keyword='.$keyword);
		}*/
		$specified_key = trim($specified_key);
		$arr = $this->getRedisVal($specified_key);
		$this->layout = 'main_bootstrap';
		return $this->render('view-redis-value',[
			'keyword'=>$keyword,
			'specified_key'=>$specified_key,
			'type'=>$arr['value_type'],
			'value'=>$arr['value'],
		]);
	}
}