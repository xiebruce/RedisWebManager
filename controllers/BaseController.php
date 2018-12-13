<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-13
 * Time: 18:08
 */

namespace app\controllers;
use yii\web\Controller;
use Yii;
use Redis;

class BaseController extends Controller
{
	/**
	 * 根据通配符查找所有匹配的key
	 * @param $redisConnection
	 * @param $wildcard
	 * @param $limit
	 *
	 * @return array
	 */
	public function wildCardSearchKey($redisConnection, $wildcard, $limit){
		$i = 0;
		$keys = [];
		$iterator = null;
		while(1){
			if(count($keys)>=$limit || $i>=$limit){
				break;
			}
			$keys2 = $redisConnection->scan($iterator,$wildcard,10);
			$keys = array_merge($keys,$keys2);
			if(!$iterator){
				break;
			}
			$i++;
		}
		return ['keys'=>array_unique($keys),'iterator'=>$iterator];
	}
	
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
}