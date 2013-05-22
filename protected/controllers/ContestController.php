<?php

/**
 * ContestController
 * 
 * ContestController class inherit controller (base) class .
 * Actions are defined in ContestController.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class ContestController extends Controller {

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
    $entryCount = '';
    $entries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $entries = $contest->getContestSubmission();
    if (!empty($entries)) {
      $entryCount = count($entries);
    }
    $contestInfo = $contest->getContestDetail();
    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount ));
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
    $response = array();
    if (!empty($_FILES['image']['name'])) { 
      $directory = 'uploads/contestImage/' ;
      $imagePath = uploadFile($directory , 'image');
      $imageName = end(explode('/', $imagePath));
      $imageResize = Yii::app()->image->load($directory . $imageName);
      $imageResize->resize(800, 350, Image::NONE);
      $imageResize->save();
      
      if ($imagePath) {
        $response = $contest->createContest($imagePath);
      }
    }
    $this->render('contestCreation', array('message' => $response));
  }
  
  /**
   * actionRegisterUser
   * this function is used for rgister new user
   */
  public function actionRegisterUser() {
    $this->layout = 'userManager';
    $this->render('register');
  }
  
  /**
   * actionLogin
   * this function is used for rgister new user
   */
  public function actionLogin() {
    $this->layout = 'userManager';
    $this->render('login');
  }
  
  
  /**
   * actionEntrySubmission
   * this function is used for submit entry 
   */
  public function actionEntrySubmission() {
    $contestSlug = $_GET['slug'];
    $response = array();
    if (!empty($_FILES['contestEntry']['name'])) { 
      $contest = new Contest();
      $directory = 'uploads/contestEntry/' ;
      $imageUrl = uploadFile($directory , 'contestEntry');
      if ($imageUrl) {
        $response = $contest->submitContestEntry($imageUrl, $contestSlug);
      }
    } 
    $this->render('entrySubmission', array('slug' => $contestSlug, 'message' => $response));
  }
}

