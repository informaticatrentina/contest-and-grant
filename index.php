<?php

$yii=dirname(__FILE__).'/../yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/config.php';

require_once($yii);
require_once(dirname(__FILE__).'/protected/components/Controller.php');
Yii::createWebApplication($config)->run();
