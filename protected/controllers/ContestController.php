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
    $contest->sort = '-creation_date';
    $contestInfo = array();
    $entryCount = '';
    $entries = array();    
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
    }
    if (array_key_exists('offset', $_GET) && isset($_GET['offset'])) { 
      $return = array('success' => true, 'msg' => '', 'data' => array());
      $contest->offset = $_GET['offset'];
      $entries = $contest->getContestSubmission();
      array_pop($entries);
      foreach ($entries as $entry) {
        $contestEntry['title'] = $entry['title'];
        $contestEntry['description'] = $entry['content']['description'];
        $contestEntry['authorName'] = $entry['author']['name'];
        $contestEntry['image'] = $entry['image'];
        $contestEntry['id'] = $entry['id'];
        array_push($return['data'], $contestEntry);
      }
      echo json_encode($return);
      exit;
    } elseif (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
      $contest->entryId = $_GET['id'];
      $entry = array();
      $entries = $contest->getContestSubmissionInfo();
      if (!empty($entries)) {
        $entry['contestTitle'] = $contestInfo['contestTitle'];
        $entry['contestSlug'] = $contestInfo['contestSlug'];
        $entry['title'] = $entries['title'];
        $entry['description'] = $entries['content']['description'];
        $entry['authorName'] = $entries['author']['name'];
        $entry['image'] = $entries['image'];
      }
      $this->render('entry', array('entry' => $entry));
    } else {
      if (!empty($contestInfo['entryStatus'])) {
        $entries = $contest->getContestSubmission();
        $entryCount = array_pop($entries);     
      } else {
        $this->redirect(BASE_URL);
      }
      $contestInfo['briefDescription'] = '';
      if (!empty($contestInfo)) {
        $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
      }
      $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount['count']));
    }
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
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $response = array();
    $contest = new Contest();
    try {
      if (!empty($_POST)) { 
        if (!empty($_FILES['image']['name'])) {
          $directory = 'uploads/contestImage/';
          $uploadBannerImage = $this->uploadImage($directory, 'image');
          if (!$uploadBannerImage['success']) {
            throw new Exception(Yii::t('contest', $uploadBannerImage['msg']));
          }
        } else {
          throw new Exception(Yii::t('contest', 'Please provide banner image'));
        }
        if (!empty($_FILES['squareImage']['name'])) {
          $directory = 'uploads/contestImage/';
          $uploadSquareImage = $this->uploadImage($directory, 'squareImage');
          if (!$uploadSquareImage['success']) {
            throw new Exception(Yii::t('contest', $uploadSquareImage['msg']));
          }
        } else {
          throw new Exception(Yii::t('contest', 'Please provide square image'));
        }

        $response = $contest->createContest($uploadBannerImage['img'], $uploadSquareImage['img']);
        if ($response['success']) {
          $this->redirect(BASE_URL . 'admin/contest/list');
        } else {
          $response['msg'] = Yii::t('contest', $response['msg']);
        }
      }
    } catch (Exception $e) {
      $response['success'] = false;
      $response['msg'] = Yii::t('contest', $e->getMessage());
    }
    $this->render('contestCreation', array('message' => $response, 'contest'=> $_POST ));
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
    $backUrl = BASE_URL;
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
        if ($staus['success']) {
          Yii::app()->session->destroy();
          Yii::app()->session->open();
          $user = array();
          $user['firstname'] = $userDetail['firstname'];
          $user['lastname'] = $userDetail['lastname'];
          $user['email'] = $userDetail['email'];
          $user['id'] = $staus['id'];
          Yii::app()->session['user'] = $user;
          if (!empty($_GET['back'])) {
            $backUrl = BASE_URL . substr($_GET['back'], 1);
            
          }
        }
      } catch (Exception $e) {       
        $staus['success'] = false;
        $staus['msg'] = $e->getMessage();
        Yii::log('', ERROR, Yii::t('contest', 'Error in actionRegisterUser method :') . $e->getMessage());
      }
    }
    $this->layout = 'userManager';
    $this->render('register', array('message' => $staus, 'back_url' => $backUrl));
  }

  /**
   * actionLogin
   * this function is used for login user
   */
  public function actionLogin() {
    $admin = array();
    if (userIsLogged()) {
      $this->redirect(BASE_URL);
    }
    $response = array();
    $user = new UserIdentityManager();
    $backUrl = BASE_URL;
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
        if ($response['success']) {
          $isAdmin = isAdminUser();
          if ($isAdmin) {
            $admin['admin'] = true;
            $admin['url'] = BASE_URL .'admin/contest/list';
          }
        }        
        if (!empty($_GET['back'])) {
            $backUrl = BASE_URL . substr($_GET['back'], 1);
        }
      } catch (Exception $e) {
        $response['success'] = false;
        $response['msg'] = $e->getMessage();
        Yii::log('', ERROR, Yii::t('contest', 'Error in actionRegisterUser method :') . $e->getMessage());
      }
    }
    $this->layout = 'userManager';
    $this->render('login', array('message' => $response, 'back_url' => $backUrl, 'user'=> $admin));
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
    $entry = array();
    $contestInfo = $contest->getContestDetail();
    if (!empty($contestInfo)) {
      $i = 0;
      foreach ($contestInfo as $info) {
        $entries = array();
        $contestDetail[$i]['startDate'] = date('Y-m-d', strtotime($info['startDate']));
        $contestDetail[$i]['endDate'] = date('Y-m-d', strtotime($info['endDate']));
        $contestDetail[$i]['imagePath'] = $info['imagePath'];
        $contestDetail[$i]['contestTitle'] = $info['contestTitle'];
        $contestDetail[$i]['contestDescription'] = substr($info['contestDescription'], 0, 20);
        $contestDetail[$i]['contestSlug'] = $info['contestSlug'];
        $contestDetail[$i]['squareImage'] = $info['squareImage'];
        $contest->contestSlug = $info['contestSlug'];
        $contest->count = 2;
        $entry = $contest->getContestSubmission();
        $contestDetail[$i]['entryCount'] = 0;
        if (!empty($entry)) {
          $contestDetail[$i]['entryCount']= $entry[0]['count'];
        }
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
        if (array_key_exists('contestSlug', $contestDetails) && $_GET['slug']!= $contestDetails['contestSlug']) {
          throw new Exception(Yii::t('contest', 'You have make some mistake'));
        } 
        if (empty($_FILES['image']['name'])) {
          $contest->contestImage = $contestDetails['image'];
        } else {
          $directory = 'uploads/contestImage/';
          $uploadBannerImage = $this->uploadImage($directory, 'image');
          if (!$uploadBannerImage['success']) {
            throw new Exception(Yii::t('contest', $uploadBannerImage['msg']));
          }
        }
        if (empty($_FILES['squareImage']['name'])) {
          $contest->squareImage = $contestDetails['squareImage'];
        } else {
          $directory = 'uploads/contestImage/';
          $uploadSquareImage = $this->uploadImage($directory, 'squareImage');
          if (!$uploadSquareImage['success']) {
            throw new Exception(Yii::t('contest', $uploadSquareImage['msg']));
          }
        }
        if(array_key_exists('contestRule', $contestDetails) && empty($contestDetails['contestRule'])) {
          throw new Exception(Yii::t('contest', 'Contest rule should not be empty'));
        }
        if(array_key_exists('showEntry', $contestDetails) && !empty($contestDetails['showEntry'])) {
          $contest->entryStatus = true;
        }
        $contest->contestSlug = $contestDetails['contestSlug'];
        $contest->contestRule = $contestDetails['contestRule'];
        $contest->contestDescription = $contestDetails['contestDescription'];
        if (empty($contest->contestImage )) {
          $contest->contestImage = $uploadBannerImage['img'];  
        }
        if (empty($contest->squareImage)) {
          $contest->squareImage = $uploadSquareImage['img'];
        }
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
        $contestDetail['squareImage'] = $contestInfo['squareImage'];
        $contestDetail['contestRule'] = $contestInfo['rule'];
        $contestDetail['entryStatus'] = $contestInfo['entryStatus'];
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
    $extention = array();
    $response['success'] = false;
    if (!empty($_FILES[$name]['name'])) {
      $extention = explode('.', $_FILES[$name]['name']);
      $imageExtension = end($extention);
      $allowedImageExtention = json_decode(ALLOWED_IMAGE_EXTENSION, true);
      if (!in_array($imageExtension, $allowedImageExtention)) {
        $response['msg'] = Yii::t('contest', 'Please upload jpg image');
      } else if ($_FILES[$name]['size'] > UPLOAD_IMAGE_SIZE_LIMIT) {
        $response['msg'] = Yii::t('contest', 'Image size should be less than 5MB');
      } else {
        $directory = 'uploads/contestImage/';
        $imageName = uploadFile($directory, $name);
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

  
  /**
   * actionEntries
   * 
   * This function is used for get entries for a contest and get contest detail
   */
  public function actionContestBrief() {
    $contest = new Contest();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $contestInfo = $contest->getContestDetail();
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
    }
    $this->render('contestBrief', array('contestInfo' => $contestInfo));
  }
  
  /**
   * entriesAdminView
   */
  public function actionEntriesAdminView() {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $contest = new Contest();
    $contest->sort = '-creation_date';
    $contestInfo = array();
    $entryCount = '';
    $entries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
    }
    $entries = $contest->getContestSubmission();
    $count = array_pop($entries);
    if (!empty($entries)) {
      $entryCount = $count['count'];
    }
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 325);
    }
    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount));
  }

}

