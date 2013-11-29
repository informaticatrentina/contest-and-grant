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
 * Including local configuration file.
 */
require_once(dirname(__FILE__).'/local_config.php');
require_once(dirname(__FILE__).'/../function.php');

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
define('DEFAULT_OFFSET', 0);

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
 * define constant for minor
 */
define('ADULT', 0);
define('MINOR', 1);

/**
 * define constant for entry limit 
 */
define('ENTRY_LIMIT', 20);

//define constant for source - save with entry
define('SOURCE', 'contest-grant');
//define constant for  tag scheme
define('WINNER_TAG_SCHEME','http://ahref.eu/contest/schema/contest/winner');
define('PRIZE_TAG_SCHEME','http://ahref.eu/contest/schema/contest/prize');
define('CONTEST_TAG_SCHEME','http://ahref.eu/contest/schema/');
define('JURY_RATING_SCHEME','http://ahref.eu/contest/schema/jury-rating');
define('RATING_COUNT_SCHEME','http://ahref.eu/contest/schema/rating-count');

define('DEFAULT_VIDEO_THUMBNAIL','images/novideo_100_500.jpeg');
define('JURY_MEMBER', 'member');
define('JURY_ADMIN', 'admin');
/**
 * configuration for interaction of file
 */
return array(
  'defaultController' => 'contest',  
  'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  'runtimePath' => RUNTIME_DIRECTORY,
  'name' => SITE_TITLE,  
  'import' => array(
    'application.models.*',
    'application.components.*',
    'application.controllers.*',
    'application.extensions.JsTrans.*',
    'application.views.*'  
  ),
  'sourceLanguage' => 'en',
  'language' => SITE_LANGUAGE,    
  'preload' => array('log'),
  'components' => array(
    'log' => array(
      'class' => 'CLogRouter',
      'routes' => array(
        array(
          'class' => 'CFileLogRoute',
          'levels' => 'trace, info, error',
          'logFile' => APP_LOG_FILE_NAME
        )
      ),
    ),
   'viewRenderer' => array(
      'class' => 'ext.ETwigViewRenderer',
      'fileExtension' => '.html',
      'functions' => array(
        'getTweets' => 'getTweets'    ,
        'getFirstContest' => 'getFirstContest', 
        'isAdminUser' => 'isAdminUser',  
        'getImageDimension' => 'getImageDimension',
        'getContestList' => 'getContestList',  
        'getAdminMenuList' => 'getAdminMenuList'  
      ) 
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
        '' => 'contest/index',
        'admin/contest/add'=>'contest/createContest',  
        'admin/contest/list' => 'contest/getContest',
        'admin/contest/submission/download/<contest_slug:[\w-]+>' => 'contest/downloadSubmission',
        'admin/contest/delete/<slug:[\w-]+>' => 'contest/deleteContest',
        'admin/contest/edit/<slug:[\w-]+>' => 'contest/updateContest',
        'admin/contest/entries/<slug:[\w-]+>'=>'contest/entriesAdminView',
        'admin/category/<slug:[\w-]+>' => 'winner/manageCategory',
        'admin/category/<slug:[\w-]+>/edit' => 'winner/updateCategory',
        'admin/category/<slug:[\w-]+>/delete/<id:[\d-]+>' => 'winner/deleteCategory',
        'admin/category/winner/<id:[\w-]+>' => 'winner/manageWinner',
        'admin/category/winner/<id:[\w-]+>/edit' => 'winner/updateWinner',
        'admin/category/winner/<id:[\w-]+>/delete' => 'winner/deleteWinner',       
        'admin/category/winner/<id:[\w-]+>/add' => 'winner/addWinnerInCategory',
        'contest/winner/<slug:[\w-]+>'=>'winner/getWinnerInfo',  
        'contest/entries/<slug:[\w-]+>'=>'contest/entries',
        'contest/entries/<slug:[\w-]+>/category/<category:[\w-]+>'=>'contest/entries',
	'contest/entries/<slug:[\w-]+>/<id:[\w-]+>'=>'contest/entries',
        'contest/brief/<slug:[\w-]+>'=>'contest/contestBrief',
        'contest/submission/<slug:[\w-]+>'=>'contest/submitEntries',              
        'register' => 'contest/registerUser', 
        'login' => 'contest/login',  
        'logout' => 'contest/logout',
        'admin/winner/status' => 'contest/winnerStatus',
        'admin/contest/winner/add/<slug:[\w-]+>' => 'winner/addWinner', 
        'admin/contest/winner/<slug:[\w-]+>' => 'winner/winner',  
        'admin/contest/winner/edit/<slug:[\w-]+>' => 'winner/updateContestWinner',  
        'admin/contest/winner/delete/<slug:[\w-]+>/<id:[\w-]+>' => 'winner/deleteContestWinner',
        'admin/jury/manage/<contest_id:[\w-]+>' => 'jury/manageJury',
        'jury/contest' => 'jury/activeContest',  
        'jury/entry/<slug:[\w-]+>/<id:[\w-]+>' => 'jury/viewEntry',  
        'jury/entry/<contest_slug:[\w-]+>' => 'jury/juryRating',
        'jury/rating/save/<contest_slug:[\w-]+>' => 'jury/saveRating',
      ),
    ),
    'session' => array(
      'sessionName' => SITE_SESSION_COOKIE_NAME,
      'class' => 'ModifiedHttpSession',
      'lifetime' => SESSION_TIMEOUT_TIME 
    ),
    'errorHandler' => array(
      'errorAction' => 'contest/error',
    ),  
    'messages' => array(
      'class' => 'CGettextMessageSource',
      'useMoFile' => FALSE,
      'catalog' => 'contest'
    )
  ),    
);
        
