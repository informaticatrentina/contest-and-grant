<?php
$yii = dirname(__FILE__) . '/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/console.php';
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
require_once($yii);
$app = Yii::createConsoleApplication($config)->run();
