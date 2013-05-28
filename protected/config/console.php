<?php
/**
 * constant for database configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'candg');
define('DB_USER', 'root');
define('DB_PASS','123456');


return array(
  'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  'preload' => array('log'),
  'components' => array(
    'db' => array(
      'class' => 'CDbConnection',
      'connectionString' => 'mysql:host='.DB_HOST.';dbname='.DB_NAME,
      'username' => DB_USER,
      'password' => DB_PASS,
      'emulatePrepare' => true,
    ),
  ),
);
