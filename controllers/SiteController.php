<?php

namespace app\controllers;

use app\models\RedisKeyList;
use app\models\Sqlite;
use Yii;
use Redis;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use yii\data\Pagination;

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
	 * redis key list
	 * @return string
	 */
	public function actionIndex(){
		//Check login status
		$identity = Yii::$app->user->identity;
		if(!$identity){
			return $this->redirect('/site/login');
		}
		$username = $identity->username;
		
		//Get params
		$request = Yii::$app->request;
		$keyword = $request->get('keyword','');
		$keyword = trim($keyword);
		$page = $request->get('page');
		$queryParams = $request->queryParams;
		
		$cache = Yii::$app->cache;
		
		/*if(!$preIterator){
			//be aware that this is not the same as redis cliet-cli command, in redis-cli command, the iterator starts as 0, but in php, it starts as null, if you use 0, you can't retrive anything, the scan method will return false.
			$preIterator = null;
		}else{
			//iterator should be a numeric, otherwise, it won't be valid.
			$preIterator = intval($preIterator);
		}*/
		//Decide Wich db's data should be show
		$db = $request->get('db',0);
		
		if(!$page){
			//if page is null, it means this is the first page, we empty the table to get ready the table.
			RedisKeyList::deleteAll('db=' . $db);
			$page = 1;
		}
		//Connect redis
		$redis = $this->connectRedis();
		//Select db(default 16 db, first db is 0, last is 15)
		$redis->select($db);
		//Get how many keys are in the selected db
		$count = $redis->dbSize();
		
		$pageSize = Yii::$app->params['pageSize'] ?? 10;
		
		$obj = RedisKeyList::find();
		$totalCount = $obj->count();
		$Pagination1 = new Pagination([
			'totalCount'=>$totalCount,
			'pageSize'=>$pageSize,
		]);
		$dbPageCount = $Pagination1->pageCount;
		
		$prePageKey = 'pre_page';
		$prePage = $cache->get($prePageKey);
		//cache the current pagenum, so we can get it at next time.
		$cache->set($prePageKey, $page);
		//$page<$prePage means the user click Prev button, so we retrieve data from the sqlitedb, but we need to make sure $page is not larger than the pages that the sqlitedb already have, otherwise we set the page to the last page of the db.
		if($page < $prePage){
			if($page > $dbPageCount){
				$hostInfo = Yii::$app->request->hostInfo;
				$queryString = $request->queryString;
				$queryString = preg_replace('/(page=)\d+/', '${1}'.$dbPageCount, $queryString);
				$redirectUrl = $hostInfo . '?' . $queryString;
				return $this->redirect($redirectUrl);
			}
			$res = $obj->where(['db'=>$db])->offset($Pagination1->offset)->limit($Pagination1->limit)->asArray()->all();
			$keys = array_column($res,'keyname');
		}
		// retrieve data from redis if the user click Next button.
		else{
			$lastIteratorKey = 'redis_preterator';
			//get iterator returned from last scan
			$lastIterator = $cache->get($lastIteratorKey);
			$lastIterator = $lastIterator == 0 ? null : $lastIterator;
			//Get key list by scan() method
			$searchResult = $this->wildCardSearchKey($redis, "*{$keyword}*", $lastIterator, $pageSize);
			$keys = $searchResult['keys'];
			$iterator = $searchResult['iterator'];
			//cache the iterator so we can use it in next scan
			$cache->set($lastIteratorKey, $iterator);
			//put the keys to the sqlitedb so we can use it when user click Prev button
			if(!empty($keys)){
				$curTime = time();
				$data = [];
				foreach ($keys as $val){
					$data[] = [$val, $curTime, $db];
				}
				$insertedRows = RedisKeyList::batchInsert($data);
			}
		}
		
		//the pagination is not right if search key is not empty, cause we can't get how many keys was matched the given key, so totalCount only right when search key is empty, otherwise the totalCount is grater then real count, but since we can't get real count, so we use totalCount, this will cause some page have no keys in it.
		$pagination2 = new Pagination([
			'totalCount'=>$count,
			'pageSize'=>$pageSize,
			'params'=>$queryParams,
		]);
		
		$matchCountReal = false;
		// this $matchCount is current page match count, not the total match count, cause we can't get the total match count directly
		$matchCount = count($keys);
		// if $page is null it means there is only one page, and if $iterator equals to 0, it means that there is only one iteration, all matched keys were returned, this time, $matchCount is equals to total match count(I call it "real match count").
		if(!$page && $iterator == 0){
			$matchCountReal = true;
		}
		
		//Get server info, like:used_memory, redis_version and so on
		$info = $redis->info();
		
		//Get how many databases are configured
		$arr = $redis->config('get', 'databases');
		$databaseCount = $arr['databases'] ?? 16;
		
		/*$accepted_language = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if(preg_match('/zh-cn/')){

		}else if(preg_match('/en-us/')){

		}*/
		
		$serverIp = $_SERVER['SERVER_ADDR'];
		return $this->render('index',[
			'code'=>0,
			'keys'=>$keys,
			'keyword'=>$keyword,
			'pagination'=>$pagination2,
			'info'=>$info,
			'count'=>$count,
			'match_count'=>$matchCount,
			'match_count_real'=>$matchCountReal,
			'server_ip'=>$serverIp,
			'delete_auth'=>true,
			'databaseCount'=>$databaseCount,
			'db'=>$db,
			'username' => $username,
		]);
	}
	
	/**
	 * get reids value by key
	 * @return string
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
	 * View Redis Value
	 * @return string
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
			]);
		}
	}
	
	/**
	 * delete redis key
	 * @return string
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
	 * flushdb or flushall
	 * @return string
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
