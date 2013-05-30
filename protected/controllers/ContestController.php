<?php

/**
 * ContestController
 * 
 * ContestController class inherit controller (base) class .
 * Actions are defined in ContestController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class ContestController extends Controller {
    
  public function beforeAction() {
    new JsTrans('js',SITE_LANGUAGE);
    return true;
  }

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
  
  public function actionError() {
        $this->render('error404');
  }
  
  /**
   * actionEntries
   * 
   * This function is used for get entries for a contest and get contest detail
   */
  public function actionEntries() { 
    $contest = new Contest();
    $contestInfo = array();
    $entrySubmissionResponse = array();
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
<<<<<<< Updated upstream
    if (!empty(Yii::app()->session['user']) && !empty($_POST)) {
      $entrySubmissionResponse = $this->entrySubmission();  
=======
    $contestInfo['briefDescription'] = '';
    if(!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
    }
    if (!empty(Yii::app()->session['user'])) {
      if (!empty($_POST)) {
        $entrySubmissionResponse = $this->entrySubmission();  
      }
>>>>>>> Stashed changes
    } 
    
    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount, 'message' => $entrySubmissionResponse ));
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
    //check if user belong to admin users or not
    $adminUsers = array();
    if (defined('CONTEST_ADMIN_USERS')) {
        $adminUsers = json_decode(CONTEST_ADMIN_USERS, true);
    }
    if (!isset(Yii::app()->session['user'])) {
        $this->redirect(BASE_URL);
    }
    if (!in_array(Yii::app()->session['user']['email'], $adminUsers)) {
        $this->redirect(BASE_URL);
    }
    $contest = new Contest(); 
    $response = array();
    if (!empty($_FILES['image']['name'])) { 
      $directory = 'uploads/contestImage/' ;
      $imageName = uploadFile($directory , 'image');
      if ($imageName) {
        $imagePath = $directory . $imageName;
        $response = $contest->createContest($imagePath);
      } else {
        $response['success'] = '';
        $response['msg'] = Yii::t('contest', 'Some error occured in image uploading');
      }
    }
    $this->render('contestCreation', array('message' => $response));
  }
  
  /**
   * actionRegisterUser
   * this function is used for rgister new user
   */
  public function actionRegisterUser() {
    if (userIsLogged()) {
        $this->redirect(BASE_URL);
    }
    $user = new UserIdentityManager();  
    $staus = array();
    if (!empty($_POST)) {
      try {
        if (empty($_POST['firstname'])) {
          throw new Exception(Yii::t('contest','Please enter first name'));
        }
        if (empty($_POST['lastname'])) {
          throw new Exception(Yii::t('contest','Please enter last name'));
        }
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          throw new Exception(Yii::t('contest','Please enter a valid email'));
        }
        if (empty($_POST['password'])) {
          throw new Exception(Yii::t('contest','Please enter password'));
        }
        $userDetail = array(
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname'],
            'email' => $_POST['email'],
            'password' => $_POST['password']
        );
        $staus = $user->createUser($userDetail); 
      } catch(Exception $e) {
        $staus['success'] = false;
        $staus['msg'] = $e->getMessage();
        Yii::log('', ERROR, Yii::t('contest', 'Error in actionRegisterUser method :') . $e->getMessage());            
      }
    }    
    $this->layout = 'userManager';
    $this->render('register', array('message' => $staus));
  }
  
  /**
   * actionLogin
   * this function is used for login user
   */
  public function actionLogin() {
    if (userIsLogged()) {
        $this->redirect(BASE_URL);
    }
    $response = array();
    $user = new UserIdentityManager();
    if (!empty($_POST)) {
      try {
        $userDetail = $_POST;
        if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
          throw new Exception(Yii::t('contest', 'Please enter a valid email'));
        }
        if (empty($userDetail['password'])) {
          throw new Exception(Yii::t('contest','Please enter password'));
        }
        $response = $user->validateUser($userDetail); 
      } catch(Exception $e) {
        $response['success'] = false;
        $response['msg'] = $e->getMessage();
        Yii::log('', ERROR, Yii::t('contest','Error in actionRegisterUser method :') . $e->getMessage());      
      }
    }
    $this->layout = 'userManager';
    $this->render('login', array('message' => $response));
  }
  
  
  /**
   * actionEntrySubmission
   * this function is used for submit entry 
   */
  public function entrySubmission() { 
    $contestSlug = $_GET['slug']; 
    $response = array();
    if(empty($_FILES['contestEntry']['name'])) {
      $response['msg'] = Yii::t('contest', 'Please provide an image for entrye');
      $response['success'] = false;
    } else { 
      $contest = new Contest();
      $directory = 'uploads/contestEntry/' ;
      $imageName = uploadFile($directory , 'contestEntry');
      $imagePath = $directory . $imageName;
      if ($imageName) {       
        $response = $contest->submitContestEntry($imagePath, $contestSlug);
      }
    }
    return $response;
  }
  
  /**
   * actionLogout
   * 
   * This function is used for logout user and destroy user session
   */
  
  public function actionLogout() {
    Yii::app()->session->destroy();
    $this->redirect(BASE_URL);
  }
} 
