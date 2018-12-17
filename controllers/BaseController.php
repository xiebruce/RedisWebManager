<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-13
 * Time: 18:08
 */

namespace app\controllers;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use yii\web\Controller;
use Yii;
use Redis;

class BaseController extends Controller
{
	/**
	 * Search by wildcard key
	 * @param $redis
	 * @param $wildcard
	 * @param $iterator
	 * @param $limit
	 *
	 * @return array
	 */
	public function wildCardSearchKey($redis, $wildcard, $iterator, $limit = 10){
		$redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
		$i = 0;
		$keys = [];
		while(1){
			if(count($keys)>=$limit || $i>=$limit){
				break;
			}
			$keys2 = $redis->scan($iterator,$wildcard,$limit);
			$keys = array_merge($keys,$keys2);
			if($iterator==0){
				break;
			}
			$i++;
		}
		//why array_unique was used here? because redis can't guarantee to return unique keys.
		return ['keys'=>array_unique($keys),'iterator'=>$iterator];
	}
	
	/**
	 * Check if a string is serialize string
	 * @param $data
	 *
	 * @return bool
	 */
	public function is_serialized( $data ) {
		// if it isn't a string, it isn't serialized
		if ( !is_string( $data ) )
			return false;
		$data = trim( $data );
		if ( 'N;' == $data )
			return true;
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
			return false;
		switch ( $badions[1] ) {
			case 'a' :
			case 'O' :
			case 's' :
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
					return true;
				break;
		}
		return false;
	}
	
	/**
	 * connectRedis
	 * @return Redis|string
	 */
	public function connectRedis(){
		$redis_config = Yii::$app->redis;
		$redis = new Redis();
		$connect = $redis->connect($redis_config->hostname,$redis_config->port);
		if(!$connect){
			return json_encode(['code'=>-1,'msg'=>'connection failed']);
		}
		$auth = $redis->auth($redis_config->password);
		if(!$auth){
			return json_encode(['code'=>-2,'msg'=>'authentification failed']);
		}
		return $redis;
	}
	
	/**
	 * Get redis value
	 * @param $key
	 * @param $db
	 * @return array
	 */
	public function getRedisVal($key, $db=0){
		$redis = Yii::$app->redis;
		$redis->select($db);
		$key_type = $redis->type($key);
		if($key_type=='none'){
			return false;
		}
		$method = 'get'.ucfirst($key_type).'Val';
		$value = $this->$method($key);
		//if string is a json string or a serialize object string, I will decode json or unserialize object to array, so I need to tell the use the value type
		$value_type = '';
		if($key_type == 'string'){
			$value_type = $value['value_type'];
			$value = $value['value'];
		}
		return ['value'=>$value,'key_type'=>$key_type, 'value_type'=>$value_type];
	}
	
	/**
	 * Get string type value
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getStringVal($key){
		$redis = Yii::$app->redis;
		$value = $redis->get($key);
		$value_type = '';
		if($this->is_serialized($value)){
			$value = unserialize($value);
			$value_type = gettype($value);
			if($value_type=='object' && isset($value->attributes)){
				$value = $value->attributes;
			}
		}else{
			$decode_value = json_decode($value,true);
			if(json_last_error()===JSON_ERROR_NONE){
				$value = $decode_value;
				is_array($value) && $value_type = 'json';
			}
		}
		return ['value'=>$value, 'value_type'=>$value_type];
	}
	
	/**
	 * get hash type value
	 * @param $key
	 *
	 * @return string
	 */
	public function getHashVal($key){
		$redis = Yii::$app->redis;
		$arr = $redis->hgetall($key);
		$i = 0;
		$max = count($arr) / 2;
		$str = '';
		while(1){
			if($i>=$max){
				break;
			}
			$newArr = array_slice($arr, $i*2, 2);
			$str .= $newArr[0].' => '.$newArr[1]."\n";
			$i++;
		}
		return "\n".$str;
	}
	
	/**
	 * get list value
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getListVal($key){
		$redis = Yii::$app->redis;
		$llen = $redis->llen($key);
		$list = $redis->lrange($key, 0, $llen-1);
		return $list;
	}
	
	/**
	 * get set value
	 * @param $key
	 *
	 * @return mixed
	 */
	public function getSetVal($key){
		$redis = Yii::$app->redis;
		$members = $redis->smembers($key);
		return $members;
	}
	
	/**
	 * get zset value
	 * @param $key
	 *
	 * @return string
	 */
	public function getZsetVal($key){
		$redis = Yii::$app->redis;
		$sortedSet = $redis->zrange($key, 0, -1, 'WITHSCORES');
		$i = 0;
		$max = count($sortedSet) / 2;
		$str = '';
		while(1){
			if($i>=$max){
				break;
			}
			$newArr = array_slice($sortedSet, $i*2, 2);
			$str .= $newArr[0].' => '.$newArr[1]."\n";
			$i++;
		}
		return "\n".$str;
	}
}