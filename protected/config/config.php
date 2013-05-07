<?php

/**
 * This file is used for define constant and configuration of Aggregator project 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi <rahul@incaendo.com>
 * This file is part of <Aggregator>.
 * This file can not be copied and/or distributed without the express permission of
    <ahref Foundation.
 */

/**
 * define constant for base url
 */
define('BASE_URL','');

/**
 * define constant for response format
 */
define('RESPONSE_FORMAT','json');

/**
 * define constant for entry
 */
define('ENTRY','entry');

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
 * path for log file
 */
define('LOGGER_FILE_PATH', '');
/**
 * define log message level
 */
define('INFO', 'info');
define('ERROR', 'error');
define('DEBUG', 'trace');
define('WARNING', 'warning');

/**
 * configuration for log messages
 */
return array(
  'preload' => array('log'),
  'components' => array(
    'log' => array(
       'class' => 'CLogRouter',
       'routes' => array(
         array(
           'class' => 'CFileLogRoute',
           'levels' => 'trace, info, error',
           'logFile' => LOGGER_FILE_PATH
         )
       ),
     ),
   ),
);