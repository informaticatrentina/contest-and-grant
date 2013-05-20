<?php

/**
 * This file contain configuration for unit test case.
 */

define('TEST_DBHOST', 'localhost');
define('TEST_DBNAME', 'testAggregator');
define('TEST_DBUSER', 'root');
define('TEST_DBPASS','');

return CMap::mergeArray(
  require(dirname(__FILE__) . '/config.php'), array(
    'components' => array(
      'fixture' => array(
        'class' => 'system.test.CDbFixtureManager',
      ),
      'db' => array(
        'class' => 'CDbConnection',
        'connectionString' => 'mysql:host='. TEST_DBHOST.';dbname='.TEST_DBNAME,
        'username' => TEST_DBUSER,
        'password' => TEST_DBPASS
    ),    
    ),
  )
);
