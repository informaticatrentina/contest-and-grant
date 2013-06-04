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
    new JsTrans('js', SITE_LANGUAGE);
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
//    $entries = $contest->getContestSubmission();
//    if (!empty($entries)) {
//      $entryCount = count($entries);
//    }
    $contestInfo = $contest->getContestDetail();
    $entrySubmittedByUser = false;
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
    }

    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount));
  }

  public function actionSubmitEntries() {
    $contest = new Contest();
    $contestInfo = array();
    $entrySubmissionResponse = array();
    $entryCount = '';
    $entries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $contestInfo = $contest->getContestDetail();
    $entrySubmittedByUser = false;
    $postData = array();
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
    }
    if (!empty(Yii::app()->session['user'])) {
      $aggregatorManager = new AggregatorManager();
      $aggregatorManager->authorSlug = Yii::app()->session['user']['id'];
      $entrySubmittedByUser = $aggregatorManager->isUserAlreadySubmitEntry('title');
      if (!empty($_POST) && !$entrySubmittedByUser) {
        $postData = $_POST;
        $entrySubmissionResponse = $this->entrySubmission();
      }
    }
    $this->render('contestSubmitEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount, 'message' => $entrySubmissionResponse, 'isEntrySubmit' => $entrySubmittedByUser, 'postData' => $postData));
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
    $extenstion = array();
    if (!empty($_FILES['image']['name'])) {
      $extention = explode('.', $_FILES['image']['name']);
      $imageExtension = end($extention);
      $allowedImageExtention = json_decode(ALLOWED_IMAGE_EXTENSION, true);
      if (!in_array($imageExtension, $allowedImageExtention)) {
        $response['msg'] = Yii::t('contest', 'Please upload jpg image');
      } else if ($_FILES['image']['size'] > UPLOAD_IMAGE_SIZE_LIMIT) {
        $response['msg'] = Yii::t('contest', 'Image size should be less than 5MB');
      } else {
        $directory = 'uploads/contestImage/';
        $imageName = uploadFile($directory, 'image');
        if ($imageName) {
          $imagePath = $directory . $imageName;
          $response = $contest->createContest($imagePath);
          if ($response['success']) {
            $this->redirect(BASE_URL . 'admin/contest/list');
          }
        } else {
          $response['success'] = '';
          $response['msg'] = Yii::t('contest', 'Some error occured in image uploading');
        }
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
          throw new Exception(Yii::t('contest', 'Please enter first name'));
        }
        if (empty($_POST['lastname'])) {
          throw new Exception(Yii::t('contest', 'Please enter last name'));
        }
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          throw new Exception(Yii::t('contest', 'Please enter a valid email'));
        }
        if (empty($_POST['password'])) {
          throw new Exception(Yii::t('contest', 'Please enter password'));
        }
        $userDetail = array(
            'firstname' => $_POST['firstname'],
            'lastname' => $_POST['lastname'],
            'email' => $_POST['email'],
            'password' => $_POST['password']
        );
        $staus = $user->createUser($userDetail);
      } catch (Exception $e) {
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
          throw new Exception(Yii::t('contest', 'Please enter password'));
        }
        $response = $user->validateUser($userDetail);
      } catch (Exception $e) {
        $response['success'] = false;
        $response['msg'] = $e->getMessage();
        Yii::log('', ERROR, Yii::t('contest', 'Error in actionRegisterUser method :') . $e->getMessage());
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
    if (empty($_FILES['contestEntry']['name'])) {
      $response['msg'] = Yii::t('contest', 'Please provide an image for entry');
      $response['success'] = false;
    } else {
      $extention = explode('.', $_FILES['contestEntry']['name']);
      $imageExtension = end($extention);
      $allowedImageExtention = json_decode(ALLOWED_IMAGE_EXTENSION, true);
      if (!in_array($imageExtension, $allowedImageExtention)) {
        $response['msg'] = Yii::t('contest', 'Please upload jpg image');
      } else if ($_FILES['contestEntry']['size'] > UPLOAD_IMAGE_SIZE_LIMIT) {
        $response['msg'] = Yii::t('contest', 'Image size should be less than 5MB');
      } else {
        $contest = new Contest();
        $directory = 'uploads/contestEntry/';
        $imageName = uploadFile($directory, 'contestEntry');
        $imagePath = $directory . $imageName;
        if ($imageName) {
          $response = $contest->submitContestEntry($imagePath, $contestSlug);
        }
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

  /**
   * actionGetContest
   * 
   * This function is used for getting existing contest information
   */
  public function actionGetContest() {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $contestInfo = array();
    $contestDetail = array();
    $contest = new Contest();
    $contestInfo = $contest->getContestDetail();
    if (!empty($contestInfo)) {
      $i = 0;
      foreach ($contestInfo as $info) {
        $contestDetail[$i]['startDate'] = date('Y-m-d', strtotime($info['startDate']));
        $contestDetail[$i]['endDate'] = date('Y-m-d', strtotime($info['endDate']));
        $contestDetail[$i]['imagePath'] = $info['imagePath'];
        $contestDetail[$i]['contestTitle'] = $info['contestTitle'];
        $contestDetail[$i]['contestDescription'] = substr($info['contestDescription'], 0, 20);
        $contestDetail[$i]['contestSlug'] = $info['contestSlug'];
        $i++;
      }
    }
    $this->render('contestList', array('contestInfo' => $contestDetail));
  }

  /**
   * actionUpdateContest
   * 
   * This function is used for existing contest
   * Only admin user can update a contest
   */
  public function actionUpdateContest() {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $contest = new ContestAPI();
    $contestInfo = array();
    $contestDetail = array();
    $message = array();
    try {
      if (!empty($_POST)) {
        $contestDetails = $_POST;
        if (array_key_exists('startDate', $contestDetails) && empty($contestDetails['startDate'])) {
          throw new Exception(Yii::t('contest', 'Start date should not be empty'));
        } else if (!validateDate($contestDetails['startDate'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid start date'));
        } else {
          $startDateArr = explode('/', $contestDetails['startDate']);
          $startTime = mktime(0, 0, 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contest->startDate = date('Y-m-d H:i:s', $startTime);
        }
        if (array_key_exists('endDate', $contestDetails) && empty($contestDetails['endDate'])) {
          throw new Exception(Yii::t('contest', 'End date should not be empty'));
        } else if (!validateDate($contestDetails['endDate'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid end date'));
        } else {
          $endDateArr = explode('/', $contestDetails['endDate']);
          $endTime = mktime(0, 0, 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contest->endDate = date('Y-m-d H:i:s', $endTime);
        }
        if (array_key_exists('contestDescription', $contestDetails) && empty($contestDetails['contestDescription'])) {
          throw new Exception(Yii::t('contest', 'Contest description should not be empty'));
        }
        if (array_key_exists('contestSlug', $contestDetails) && $_GET['contestSlug']!= $contestDetails['contestSlug']) {
          throw new Exception(Yii::t('contest', 'You have make some mistake'));
        } 
        if (empty($_FILES['image']['name'])) {
          throw new Exception(Yii::t('contest', 'Please choose an image for upload'));
        } else {
          $directory = 'uploads/contestImage/';
          $upload = $this->uploadImage($directory, 'image');
          if (!$upload['success']) {
            throw new Exception(Yii::t('contest', $upload['msg']));
          }
        }
        
        $contest->contestSlug = $contestDetails['contestSlug'];
        $contest->contestDescription = $contestDetails['contestDescription'];
        $contest->contestImage = $upload['img'];
        $isContestUpdate = $contest->updateContest();
        if ($isContestUpdate) {
          $this->redirect(BASE_URL . 'admin/contest/list');
        } else {
          throw new Exception(Yii::t('contest', 'Some technical problem occurred, contact administrator'));
        }
      }
    } catch (Exception $e) {
      $message['success'] = false;
      $message['msg'] = Yii::t('contest', $e->getMessage());
    }
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest = new ContestAPI();
      if(empty($contest->contestSlug)) {
        $contest->contestSlug = $_GET['slug'];  
      }      
      try {
        $contestInfo = $contest->getContestDetailByContestSlug();
        if (empty($contestInfo)) {
          Yii::log('', ERROR, Yii::t('contest','Error in getContestDetailByContestSlug'));
          throw new Exception(Yii::t('contest','Some technical problem occurred, For more detail check log file'));
        }
        $contestDetail['startDate'] = date('m/d/Y', strtotime($contestInfo['startDate']));
        $contestDetail['endDate'] = date('m/d/Y', strtotime($contestInfo['endDate']));
        $contestDetail['imagePath'] = $contestInfo['imagePath'];
        $contestDetail['contestDescription'] = $contestInfo['contestDescription'];
        $contestDetail['contestSlug'] = $contestInfo['contestSlug'];
      } catch (Exception $e) {
        $message['success'] = false;
        $message['msg'] = Yii::t('contest', $e->getMessage());

      }
      
    }
    $this->render('editContest', array('contest' => $contestDetail, 'message' => $message));
  }

  /**
   * actionDeleteContest
   * 
   * This function is used for delete an existing contest
   */
  public function actionDeleteContest() {
    $contest = new ContestAPI();
    if (array_key_exists('slug',$_GET) && (!empty($_GET['slug']))) {
      $contest->contestSlug = $_GET['slug'];
      $contest->deleteContest();
    }
    $this->redirect(BASE_URL . 'admin/contest/list');
  }

  /**
   * uploadImage
   * 
   * This function is used for upload image
   * @param (string) $directory -where image will save
   * @param (string) $name
   * @return (array) $response 
   */
  public function uploadImage($directory, $name) {
    $response = array();
    $response['success'] = false;
    if (!empty($_FILES[$name]['name'])) {
      $extention = explode('.', $_FILES['image']['name']);
      $imageExtension = end($extention);
      $allowedImageExtention = json_decode(ALLOWED_IMAGE_EXTENSION, true);
      if (!in_array($imageExtension, $allowedImageExtention)) {
        $response['msg'] = Yii::t('contest', 'Please upload jpg image');
      } else if ($_FILES['image']['size'] > UPLOAD_IMAGE_SIZE_LIMIT) {
        $response['msg'] = Yii::t('contest', 'Image size should be less than 5MB');
      } else {
        $directory = 'uploads/contestImage/';
        $imageName = uploadFile($directory, 'image');
        if ($imageName) {
          $response['img'] = $directory . $imageName;
          $response['success'] = true;
        } else {
          $response['msg'] = Yii::t('contest', 'Some error occured in image uploading');
        }
      }
    }
    return $response;
  }

}

