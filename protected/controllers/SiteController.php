<?php

/**
 * SiteController
 * 
 * SiteController class inherit controller (base) class .
 * Actions are defined in siteController.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class SiteController extends Controller {

 /**
  * actionIndex
  * 
  * This is the default 'index' action that is invoked
  * when an action is not explicitly requested by users.
  */
  public function actionIndex() {
    $contest = new Contest();
    $contestInfo = $contest->getContestDetail();
    $this->render('index', array('contestInfo' => $contestInfo));
  }
  
  /**
   * actionEntries
   * 
   * This function is used for get entries for a contest and get contest detail
   */
  public function actionEntries() {
    $contest = new Contest();
    $contestInfo = array();
    $entries = array();
    $contestId = $this->getArgumentFromUrl('id');
    $contest->contestId = $contestId;
    $entries = $contest->getContestSubmission();
    $contestInfo = $contest->getContestDetail();
    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo));
  }
  
  /**
   * getArgumentFromUrl
   * 
   * This function is used for get argument (value) from url 
   * @param (string) $param
   * @return $val
   */
  private function getArgumentFromUrl($param) {
    $val = '';
    if (array_key_exists($param, $_GET) && !empty($_GET[$param])) {
      $val = $_GET[$param];
    }
    return $val;
  }
  
  /**
   * actionCreateContest
   * 
   * This function is used for create contest
   */
  public function actionCreateContest() {
    $contest = new Contest(); 
    $message = '';
    $response = array();
    if (!empty($_FILES['image']['name'])) { 
      $image = CUploadedFile::getInstanceByName('image');
      $imageInfo = pathinfo($image->getName());
      $imageName = $imageInfo['filename'] . generateRandomString(10) .'.'. $imageInfo['extension']; 
      $imagePath = CONTEST_IMAGE_URL . $imageName;
      $ret = $image->saveAs('uploads/contestImage/' . $imageName);
      $imageResize = Yii::app()->image->load('uploads/contestImage/' . $imageName);
      $imageResize->resize(800, 350, Image::NONE);
      $imageResize->save();
      
      if ($ret == 1) {
        $response = $contest->createContest($imagePath);
      }
    }
    if (array_key_exists('msg', $response) && $response['msg']) {
      $message =  $response['msg'];
    }
    $this->render('contestCreation', array('message' => $message));
  }
}

