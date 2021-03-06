<?php
/**
 * Including local configuration file.
 */
require_once(dirname(__FILE__).'/local_config.php');

return array(
  'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
  'preload' => array('log'),
  'import' => array(
    'application.extensions.protected.components.helpers.CLI',
  ),
  'commandMap' => array(
    'emessage' => array(
      'class' => 'application.extensions.protected.commands.EMessageCommand',
    ),
  ),
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
