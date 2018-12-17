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
		$ttl = $redis->ttl($key);
		$ttl = self::secToYmdHis($ttl);
		$method = 'get'.ucfirst($key_type).'Val';
		$value = $this->$method($key);
		//if string is a json string or a serialize object string, I will decode json or unserialize object to array, so I need to tell the use the value type
		$value_type = '';
		if($key_type == 'string'){
			$value_type = $value['value_type'];
			$value = $value['value'];
		}
		return ['value'=>$value,'key_type'=>$key_type, 'value_type'=>$value_type, 'ttl'=>$ttl];
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
			}else if($value_type=='array'){
				//the array element could be a serialized object,
				// if it is, unserialize it in order to read it more convenient
				array_walk_recursive($value,function(&$v, $k){
					if($this->is_serialized($v)){
						$v = unserialize($v);
					}
				});
			}
		}else{
			$decode_value = json_decode($value,true);
			if(json_last_error()===JSON_ERROR_NONE){
				$value = $decode_value;
				if(is_array($value)){
					$value_type = 'json';
					//the array element could be a serialized object,
					// if it is, unserialize it in order to read it more convenient
					array_walk_recursive($value,function(&$v, $k){
						if($this->is_serialized($v)){
							$v = unserialize($v);
						}
					});
				}
			}
		}
		return ['value'=>$value, 'value_type'=>$value_type];
	}
	
	/**
	 * get hash type value
	 * @param $key
	 *
	 * @return array
	 */
	public function getHashVal($key){
		$redis = Yii::$app->redis;
		$arr = $redis->hgetall($key);
		$i = 0;
		$max = count($arr) / 2;
		$data = [];
		while(1){
			if($i>=$max){
				break;
			}
			$newArr = array_slice($arr, $i*2, 2);
			$data[$newArr[0]] = $newArr[1];
			$i++;
		}
		return $data;
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
	 * @return array
	 */
	public function getZsetVal($key){
		$redis = Yii::$app->redis;
		$arr = $redis->zrange($key, 0, -1, 'WITHSCORES');
		$i = 0;
		$max = count($arr) / 2;
		$data = [];
		while(1){
			if($i>=$max){
				break;
			}
			$newArr = array_slice($arr, $i*2, 2);
			$data[$newArr[0]] = $newArr[1];
			$i++;
		}
		return $data;
	}
	
	/**
	 * Transfer seconds to YmdHis
	 * @param      $seconds
	 * @param bool $returnArray
	 *
	 * @return string
	 */
	public static function secToYmdHis($seconds, $returnArray=false){
		$oneYearSecs = 31536000;
		$oneMonthSecs = 2592000;
		$oneDaySecs = 86400;
		$oneHourSecs = 3600;
		$oneMinSecs = 60;
		
		$arr = ['years'=>'', 'months'=>'', 'days'=>'', 'hours'=>'', 'minutes'=>'', 'seconds'=>''];
		if($seconds > $oneYearSecs){
			$arr['years'] = floor($seconds / $oneYearSecs);
			
			$left1 = $seconds - $arr['years'] * $oneYearSecs;
			$arr['months'] = floor($left1 / $oneMonthSecs);
			
			$left2 = $left1 - $arr['months'] * $oneMonthSecs;
			$arr['days'] = floor($left2 / $oneDaySecs);
			
			$left3 = $left2 - $arr['days'] * $oneDaySecs;
			$arr['hours'] = floor($left3 / $oneHourSecs);
			
			$left4 = $left3 - $arr['hours'] * $oneHourSecs;
			$arr['minutes'] = floor($left4 / $oneMinSecs);
			
			$left5 = $left4 - $arr['minutes'] * $oneMinSecs;
			$arr['seconds'] = $left5;
		}else if($seconds > $oneMonthSecs){
			$arr['months'] = floor($seconds / $oneMonthSecs);
			
			$left2 = $seconds - $arr['months'] * $oneMonthSecs;
			$arr['days'] = floor($left2 / $oneDaySecs);
			
			$left3 = $left2 - $arr['days'] * $oneDaySecs;
			$arr['hours'] = floor($left3 / $oneHourSecs);
			
			$left4 = $left3 - $arr['hours'] * $oneHourSecs;
			$arr['minutes'] = floor($left4 / $oneMinSecs);
			
			$left5 = $left4 - $arr['minutes'] * $oneMinSecs;
			$arr['seconds'] = $left5;
		}else if($seconds > $oneDaySecs){
			$arr['days'] = floor($seconds / $oneDaySecs);
			
			$left3 = $seconds - $arr['days'] * $oneDaySecs;
			$arr['hours'] = floor($left3 / $oneHourSecs);
			
			$left4 = $left3 - $arr['hours'] * $oneHourSecs;
			$arr['minutes'] = floor($left4 / $oneMinSecs);
			
			$left5 = $left4 - $arr['minutes'] * $oneMinSecs;
			$arr['seconds'] = $left5;
		}else if($seconds > $oneHourSecs){
			$arr['hours'] = floor($seconds / $oneHourSecs);
			
			$left4 = $seconds - $arr['hours'] * $oneHourSecs;
			$arr['minutes'] = floor($left4 / $oneMinSecs);
			
			$left5 = $left4 - $arr['minutes'] * $oneMinSecs;
			$arr['seconds'] = $left5;
		}else if($seconds > $oneMinSecs){
			$arr['minutes'] = floor($seconds / $oneMinSecs);
			
			$left5 = $seconds - $arr['minutes'] * $oneMinSecs;
			$arr['seconds'] = $left5;
		}else{
			$arr['seconds'] = $seconds;
		}
		
		if($returnArray){
			return $arr;
		}
		
		$str = '';
		if($arr['years']){
			$str .= $arr['years'];
			$arr['years'] > 1 ? $str.=' years ' : $str.=' year ';
		}
		if($arr['months']){
			$str .= $arr['months'];
			$arr['months'] > 1 ? $str.=' months ' : $str.=' month ';
		}
		if($arr['days']){
			$str .= $arr['days'];
			$arr['days'] > 1 ? $str.=' days ' : $str.=' day ';
		}
		if($arr['hours']){
			$str .= $arr['hours'];
			$arr['hours'] > 1 ? $str.=' hours ' : $str.=' hour ';
		}
		if($arr['minutes']){
			$str .= $arr['minutes'];
			$arr['minutes'] > 1 ? $str.=' mins ' : $str.=' min ';
		}
		if($arr['seconds']){
			$str .= $arr['seconds'];
			$arr['seconds'] > 1 ? $str.=' secs ' : $str.=' sec ';
		}
		return $str;
	}
}