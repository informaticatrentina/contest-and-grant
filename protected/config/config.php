<?php

/**
 * This file is used for define constant and configuration of Aggregator project 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi <rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of
  <ahref Foundation.
 */

/**
 * define constant for path 
 */
define('API_URL', 'http://10.0.0.3:5000/api/v1/8avENayecayu3p6p/');
define('BASE_URL','http://www.aggregator.com/');
define('IMAGE_URL',BASE_URL.'/images/');
define('CONTEST_IMAGE_URL',BASE_URL.'uploads/contestImage/');

/**
 * define constant for response format
 */
define('RESPONSE_FORMAT', 'json');

/**
 * define constant for entry
 */
define('ENTRY', 'entry');

/**
 * define constant for default limit (number of entry to be show)
 */
define('DEFAULT_LIMIT', 1);

/**
 * define constant for default offset 
 */
define('DEFAULT_OFFSET', 1);

/**
 * define constant for curl timeout (execution time) 
 */
define('CURL_TIMEOUT', 60);

/**
 * define log message level
 */
define('INFO', 'info');
define('ERROR', 'error');
define('DEBUG', 'trace');
define('WARNING', 'warning');

/**
 * set error reporting on 
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * constant for database configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'aggregator');
define('DB_USER', 'root');
define('DB_PASS','');


/**
 * configuration for interaction of file
 */
require_once(dirname(__FILE__).'/../function.php');
return array(
  'defaultController' => 'contest',  
  'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  'import' => array(
    'application.models.*',
    'application.components.*',
  ),
  'preload' => array('log'),
  'components' => array(
    'log' => array(
      'class' => 'CLogRouter',
      'routes' => array(
        array(
          'class' => 'CFileLogRoute',
          'levels' => 'trace, info, error',
          'logFile' => 'aggreagtor-' . date('d-M-Y').'.log'
        )
      ),
    ),
   'viewRenderer' => array(
      'class' => 'ext.ETwigViewRenderer',
      'fileExtension' => '.html'
    ),
      
    'db' => array(
      'class' => 'CDbConnection',
      'connectionString' => 'mysql:host='.DB_HOST.';dbname='.DB_NAME,
      'username' => DB_USER,
      'password' => DB_PASS,
      'emulatePrepare' => true, 
    ),  
    'image'=>array(
      'class'=>'application.extensions.image.CImageComponent',
      'driver'=>'GD',
    ),
    'urlManager' => array(
      'urlFormat' => 'path',
      'showScriptName' => false,
      'caseSensitive'=>false,  
      'rules'=> array(
        'contest/home' => 'contest/index',   
        'contest/add'=>'contest/createContest',  
        'contest/entries/<slug:[\w-]+>'=>'contest/entries'
      ),    
    ),
  ),
);