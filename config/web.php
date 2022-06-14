<?php
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$list = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
	$_SERVER['REMOTE_ADDR'] = $list[0];
}

if($_SERVER['REMOTE_ADDR']=='127.0.0.1'){
	$params = require __DIR__ . '/params_local.php';
	$db = require __DIR__ . '/db_local.php';
	$redis = require __DIR__ . '/redis_local.php';
}else{
	$params = require __DIR__ . '/params.php';
	$db = require __DIR__ . '/db.php';
	$redis = require __DIR__ . '/redis.php';
}

$config = [
    'id' => 'basic',
	'name' => 'RWM',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
	'defaultRoute' => '/site/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'cIGIk1X3YjifpbXPKZQrYF14r-tepqYG',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
	    'session' => [
		    // this is the name of the session cookie used for login on the frontend
		    'name' => 'RedisWebManager',
	    ],
        'db' => $db,
	    'redis' => $redis,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            
            ],
        ],
	    'assetManager' => [
		    'bundles' => [
			    'yii\bootstrap\BootstrapAsset' => [
				    'css' => [],  // 去除 bootstrap.css
				    'sourcePath' => null, // 防止在 frontend/web/asset 下生产文件
			    ],
			    'yii\bootstrap\BootstrapPluginAsset' => [
				    'js' => [],  // 去除 bootstrap.js
				    'sourcePath' => null,  // 防止在 frontend/web/asset 下生产文件
			    ],
			    'yii\web\JqueryAsset' => [
				    'js' => [],  // 去除 jquery.js
				    'sourcePath' => null,  // 防止在 frontend/web/asset 下生产文件
			    ],
		    ],
	    ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;