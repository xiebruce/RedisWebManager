<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-13
 * Time: 16:57
 */

return [
	/*'class' => 'yii\redis\Connection',
	'hostname' => 'localhost',
	'port' => 6379,
	'database' => 0,
	'password' => '123',*/
	'class' => 'Predis\Client',
	'scheme' => 'tcp',
	'host'   => '127.0.0.1',
	'port'   => 6379,
	'password' => '123',
];