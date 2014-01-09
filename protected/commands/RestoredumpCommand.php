<?php

/**
 * RestoredumpCommand
 * This is used for restore dump file of user's generated data
 * This class extends base class 'CConsoleCommand' of framework
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grant>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
class RestoredumpCommand extends CConsoleCommand {

  public function run($args) {
    try {
      $compressFileName = '';
      if (array_key_exists(0, $args) && !empty($args[0])) {
        $compressFileName = $args[0];
      } else {
        $files = glob('*.tar.gz');
        $fileCreationTime = 0;
        foreach ($files as $file) {
          if (filectime($file) > $fileCreationTime) {
            $fileCreationTime = filectime($file);
            $compressFileName = $file;
          }
        }
      }
      if (!file_exists($compressFileName)) {
        throw new Exception( 'file does not exist - ' . $compressFileName);
      }
      $finfoObject = finfo_open(FILEINFO_MIME_TYPE);
      if (strpos(finfo_file($finfoObject, $compressFileName), 'gzip') === FALSE) {
        throw new Exception('Only tar.gz file type is allowed - ' . $compressFileName);
      };
      $fileInfo = pathinfo($compressFileName);
      if (array_key_exists('filename', $fileInfo) && file_exists($fileInfo['filename'])) {
        unlink($fileInfo['filename']);  
      }
      
      // decompress from gzip
      $decompressFile = new PharData($compressFileName);
      $decompressFile->decompress();
      if (!array_key_exists('filename', $fileInfo) || !file_exists($fileInfo['filename'])) {
        throw new Exception('failed to decompress file - ' . $compressFileName);
      }      
      //extract tar
      $phar = new PharData($fileInfo['filename']);
      $phar->extractTo(realpath(dirname(__FILE__) . '/../../'), null, true);
      unlink($fileInfo['filename']);      
    } catch (Exception $e) {
      print_r($e->getMessage()."\n");
      Yii::log('RestoredumpCommand ', 'ERROR', $e->getMessage());
    }
  }
}


?>
