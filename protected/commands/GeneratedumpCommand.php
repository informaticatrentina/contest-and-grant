<?php

/**
 * GeneratedumpCommand
 * This is used for create dump file of user's generated data
 * This class extends base class 'CConsoleCommand' of framework
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grant>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
class GeneratedumpCommand extends CConsoleCommand {

  public function run($args) {
    try {
      $compressFileName = 'dump_' . time() . '.tar';
      if (array_key_exists(0, $args) && !empty($args[0])) {
        $compressFileName = $args[0];
      }
      if (strpos($compressFileName, '.tar') === false || $compressFileName == '.tar') {
        throw new Exception('Compress file name should be *.tar.gz format');
      }
      $directory = array();
      if (defined('USER_GENERATED_DIRECTORY')) {
        $directory = json_decode(USER_GENERATED_DIRECTORY);
      }
      if (empty($directory)) {
        throw new Exception('No directory selected for dump');
      }
      $tar = new PharData($compressFileName);
      foreach ($directory as $directoryName) {
        if (is_dir($directoryName) === true) {
          $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryName),
                  RecursiveIteratorIterator::SELF_FIRST);
          foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
              continue;
            }
            $tar->addEmptyDir($directoryName);
            if (is_dir($file) === true) {
              $tar->addEmptyDir($file);
            } else if (is_file($file) === true) {
              $tar->addFromString($file, file_get_contents($file));
            }
          }
        } else if (is_file($directoryName) === true) {
          $tar->addFromString($directoryName, file_get_contents($directoryName));
        }
      }
      $tar->compress(Phar::GZ);
      unlink($compressFileName);
    } catch (Exception $e) {
      print_r($e->getMessage() . "\n");
      Yii::log('GeneratedumpCommand ', 'ERROR', $e->getMessage());
    }
  }

}
?>