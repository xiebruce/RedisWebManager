<?php

namespace app\controllers;

use app\models\RedisRawCmd;
use app\models\Sqlite;
use Predis\Client;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get', 'post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionNavTabs(){
    	$this->layout = false;
    	return $this->render('nav-tabs');
    }
    
	/**
	 * Login action.
	 *
	 * @return Response|string
	 */
	public function actionLogin()
	{
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}
		
		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
			return $this->goBack();
		}
		
		$model->password = '';
		return $this->render('login', [
			'model' => $model,
		]);
	}
	
	/**
	 * Logout action.
	 *
	 * @return Response
	 */
	public function actionLogout()
	{
		Yii::$app->user->logout();
		
		return $this->redirect('/site/login');
	}
	
	/**
	 * Overview of redis server info
	 * @return string|Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionOverview(){
		//Check login status
		$isGuest = Yii::$app->user->isGuest;
		if($isGuest){
			return $this->redirect('/site/login');
		}
		
		$db = Yii::$app->request->get('db',0);
		$redis = $this->connectRedis();
		$redis->select($db);
		//Get server info, like:used_memory, redis_version and so on
		$info = $redis->info();
		$serverIp = $_SERVER['SERVER_ADDR'];
		return $this->render('/site/overview',[
			'info' => $info,
			'server_ip' => $serverIp,
		]);
	}
	
	/**
	 * Redis web client
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionRedisCli(){
		/** @var Client $redisConfig */
		$redisConfig = Yii::$app->get('redis');
		$db = Yii::$app->request->get('db', 0);
		$redis = new RedisRawCmd([
			'hostname' => $redisConfig->host,
			'port' => $redisConfig->port,
		]);
		
		if(Yii::$app->request->isAjax){
			if(isset($redisConfig->password) && $redisConfig->password) {
				$auth = $redis->auth($redisConfig->password);
			}
			$redis->select($db);
			// $pong = $redis->ping();
			$cmd = Yii::$app->request->post('cmd', '');
			$cmd = strtoupper(trim($cmd));
			$arr = explode(' ', $cmd);
			$cmdKey = $arr[0];
			if(array_key_exists($cmdKey, $redis->excludeCmd)){
				$msg = $redis->excludeCmd[$cmdKey]!='' ? $redis->excludeCmd[$cmdKey] : 'This command is not supported in the tool';
				return json_encode(['code' => -1, 'msg' => $msg]);
			}
			$res = [];
			if($cmd){
				$res = $redis->createCommand($cmd)->execute()->getResponse();
			}
			$content = VarDumper::dumpAsString($res, 10, true);
			return json_encode(['code' => 0, 'content' => $content]);
		}
		
		$password = $redisConfig->password ?? '';
		$password && $redisConfig->auth($password);
		$redisConfig->select($db);
		$arr = $redisConfig->config('get', 'databases');
		$databaseCount = $arr['databases'] ?? 16;
		
		$serverIp = $_SERVER['SERVER_ADDR'];
		return $this->render('redis-cli', [
			'commands' => json_encode($redis->commands),
			'server_ip' => $serverIp,
			'databaseCount' => $databaseCount,
			'db' => $db,
		]);
	}
	
	/**
	 * Index page
	 * @return string|Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex(){
		$isGuest = Yii::$app->user->isGuest;
		if($isGuest){
			return $this->redirect('/site/login');
		}
		
		//Decide Wich db's data should be show
		$db = Yii::$app->request->get('db',0);
		//Connect redis
		$redis = $this->connectRedis();
		//Select db(default 16 db, first db is 0, last is 15)
		$redis->select($db);
		//Get how many keys are in the selected db
		$count = $redis->dbSize();
		//Get server info, like:used_memory, redis_version and so on
		$info = $redis->info();
		// var_dump($info);exit;
		//Get how many databases are configured
		$arr = $redis->config('get', 'databases');
		$databaseCount = $arr['databases'] ?? 16;
		
		/*$accepted_language = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if(preg_match('/zh-cn/')){

		}else if(preg_match('/en-us/')){

		}*/
		$keyword = Yii::$app->request->get('keyword','');
		$serverIp = $_SERVER['SERVER_ADDR'];
		return $this->render('index',[
			'code'=>0,
			'info'=>$info,
			'count'=>$count,
			'server_ip'=>$serverIp,
			'delete_auth'=>true,
			'databaseCount'=>$databaseCount,
			'db'=>$db,
			'keyword' => $keyword,
		]);
	}
	
	/**
	 * Key list
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionGetKeyList(){
		$isGuest = Yii::$app->user->isGuest;
		if($isGuest){
			return json_encode(['code'=>-1,'msg'=>'Please login']);
		}
		
		//Get params
		$request = Yii::$app->request;
		$keyword = $request->get('keyword','');
		$keyword = trim($keyword);
		$iterator = $request->get('iterator', 0);
		
		//Decide Wich db's data should be show
		$db = $request->get('db',0);
		
		//Connect redis
		$redis = $this->connectRedis();
		//Select db(default 16 db, first db is 0, last is 15)
		$redis->select($db);
		$pageSize = Yii::$app->params['pageSize'] ?? 10;
		$data = $redis->scan($iterator, ['match'=>"*{$keyword}*", 'count'=>$pageSize]);
		$keys = $data[1];
		$iterator = $data[0];
		return json_encode(['code' => 0, 'keys' => $keys, 'iterator' => $iterator]);
	}
	
	/**
	 * getRedisVal
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionGetRedisVal(){
		if(Yii::$app->user->isGuest){
			return json_encode(['code'=>-1,'msg'=>'Please login']);
		}
		$key = Yii::$app->request->get('key');
		$db = Yii::$app->request->get('db', 0);
		$key = trim($key);
		$arr = $this->getRedisVal($key, $db);
		if(!$arr){
			//means the key was expired.
			$delCountDown = 2;
			return json_encode([
				'code'=>-1,
				'delCountDown'=>$delCountDown,
				'errMsg' => 'This key does not exists or expired.',
			]);
		}
		switch ($arr['ttl']){
			case -1:
				$ttl = 'not set';
				break;
			case -2:
				$ttl = 'expired';
				break;
			default:
				$ttl = $arr['ttl'];
		}
		// $value = print_r($arr['value'],true);
		$value = \yii\helpers\VarDumper::dumpAsString($arr['value'], 10, true);
		return json_encode([
			'code'=>0,
			'value'=>$value,
			'key_type'=>$arr['key_type'],
			'value_type'=>$arr['value_type'],
			'ttl'=>$ttl,
		]);
	}
	
	/**
	 * View redis value in a new page(expecially for long result)
	 * @return string|Response
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionViewRedisValue(){
		if(Yii::$app->user->isGuest){
			return $this->redirect('/site/login');
		}
		$keyword = Yii::$app->request->get('keyword');
		$specified_key = Yii::$app->request->get('specified_key');
		$specified_key = trim($specified_key);
		$db = Yii::$app->request->get('db', 0);
		$arr = $this->getRedisVal($specified_key, $db);
		if(!$arr){
			return $this->render('view-redis-value',[
				'code'=>-1,
				'keyword'=>$keyword,
				'specified_key'=>$specified_key,
				'key_type'=>'unknow',
				'value'=>'',
				'value_type'=>'unknow',
				'errMsg' => 'This key does not exists or expired.',
			]);
		}else{
			switch ($arr['ttl']){
				case -1:
					$ttl = 'not set';
					break;
				case -2:
					$ttl = 'expired';
					break;
				default:
					$ttl = $arr['ttl'];
			}
			return $this->render('view-redis-value',[
				'code'=>0,
				'keyword'=>$keyword,
				'specified_key'=>$specified_key,
				'key_type'=>$arr['key_type'],
				'value'=>$arr['value'],
				'value_type'=>$arr['value_type'],
				'ttl'=>$ttl,
				'server_ip' => $_SERVER['SERVER_ADDR'],
			]);
		}
	}
	
	/**
	 * Delete a reidis key
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionDelRedisKey(){
		if(Yii::$app->user->isGuest){
			return json_encode(['code'=>-1,'msg'=>'Please login']);
		}
		$keys = Yii::$app->request->post('keys');
		$db = Yii::$app->request->post('db',0);
		if(!$keys){
			return json_encode(['code'=>-2,'msg'=>'no key']);
		}
		
		$redis = $this->connectRedis();
		$redis->select($db);
		if(is_array($keys)){
			// $pipe = $redis->multi(Redis::PIPELINE);
			$pipe = $redis->pipeline();
			// $pipe = $redis->multi();
			foreach($keys as $key){
				$pipe->del($key);
			}
			$pipe_lines = $pipe->execute();
			return json_encode(['code'=>0,'msg'=>'del succeed','pipe_line_results'=>json_encode($pipe_lines)]);
		}else{
			if($redis->del($keys)){
				return json_encode(['code'=>0,'msg'=>'del succeed']);
			}
		}
	}
	
	/**
	 * Flush current db or all db, may sure you know what you are doing, it's dangerous
	 * @return string
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionFlushDb(){
		if(Yii::$app->user->isGuest){
			return json_encode(['code'=>-1,'msg'=>'Please login']);
		}
		$flush_type = Yii::$app->request->post('flush_type');
		$password = Yii::$app->request->post('password');
		$password = trim($password);
		if($password!=Yii::$app->params['flushPwd']){
			return json_encode(['code'=>-2,'msg'=>'Password error']);
		}
		$redis = $this->connectRedis();
		if($flush_type=='flush-db'){
			$db = Yii::$app->request->post('db', 0);
			$redis->select($db);
			$ret = $redis->flushDb();
			if($ret){
				return json_encode(['code'=>0,'msg'=>'Flush-DB succeed!']);
			}else{
				return json_encode(['code'=>-3,'msg'=>'Flush-DB failed!']);
			}
		}else if($flush_type=='flush-all'){
			$ret = $redis->flushAll();
			if($ret){
				return json_encode(['code'=>0,'msg'=>'Flush-All succeed!']);
			}else{
				return json_encode(['code'=>-4,'msg'=>'Flush-All failed!']);
			}
		}
	}
}
