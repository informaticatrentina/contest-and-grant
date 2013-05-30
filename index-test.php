<?php
/**
 * This is  file for test application.
 * This file will be exist on local for test cases.
 */

$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/test.php';

require_once($yii);
Yii::createWebApplication($config)->run();
