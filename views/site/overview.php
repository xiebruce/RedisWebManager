<?php
/**
 * Created by PhpStorm.
 * User: Bruce Xie
 * Date: 2018-12-22
 * Time: 03:22
 */
	
$title = 'Redis Manager';
if($server_ip=='127.0.0.1'){
	$this->title = '[local]-'.$title;
}else{
	$this->title = '[remote]-'.$title;
}

\yii\helpers\VarDumper::dump($info, 10, true);