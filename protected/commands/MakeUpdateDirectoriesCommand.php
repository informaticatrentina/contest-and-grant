<?php

/**
 * MakeUpdateDirectoriesCommand
 * 
 * This class is used to create directories and change their owners.
 * This class extends base class 'CConsoleCommand' of framework
 * Copyright (c) 2014 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grant>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
class MakeUpdateDirectoriesCommand extends CConsoleCommand {

  public function run($args) {
    if (0 == posix_getuid()) {
      if (!empty($args)) {
        $userName = $args[0];
        if (!posix_getpwnam($userName)) {
          die("$userName user does not exist \n");
        }
        $path = Yii::app()->basePath . '/runtime';
        if (!is_dir($path)) {
          mkdir($path);
        }
        $this->recursiveChown($path, $userName);
        $assests = Yii::app()->basePath . '/../assets';
        if (!is_dir($assests)) {
          mkdir($assests);
        }
        $this->recursiveChown($assests, $userName);
        $jsTrans = Yii::app()->basePath . '/extensions/JsTrans/assets';
        $this->recursiveChown($jsTrans, $userName);
        echo "Script Completed \n";
      } else {
        echo "Please enter user name for whom permission is to be granted \n";
      }
    } else {
      echo "Invalid access \n";
    }
  }

  /**
   * recursiveChown
   * 
   * This function is used to run chown command recursively
   * 
   * @param string $path
   * @param string $owner
   * @return boolean
   */
  public function recursiveChown($path, $owner) {
    if (!file_exists($path)) {
      return false;
    }
    if (is_file($path)) {
      chown($path, $owner);
      chgrp($path, $owner);
    } elseif (is_dir($path)) {
      // get an array of the contents
      $foldersAndFiles = scandir($path);
      // Remove "." and ".." from the list
      $entries = array_slice($foldersAndFiles, 2);
      foreach ($entries as $entry) {
        $this->recursiveChown($path . "/" . $entry, $owner);
      }
      chown($path, $owner);
      chgrp($path, $owner);
    }
    return true;
  }

}