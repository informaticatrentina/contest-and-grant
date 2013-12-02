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

  public function beforeAction($action) {    
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      setMessageTranslationLanguage($_GET['slug']);
    }
    new JsTrans('js', Yii::app()->language);
    return true;
  }
  
  /**
   * actionIndex
   * 
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex() {
    $banners = array();
    $staticBanners = array();
    $homepageBox = array();
    if (defined('IMAGE_FOR_BANNER_SLIDE')) {
      $banner = json_decode(IMAGE_FOR_BANNER_SLIDE);
      foreach ($banner as $key => $val) {
        $banners[] = array('url' => $key, 'image' => $val);
      }
    }

    //prapare array for static banner   
    if (defined('STATIC_IMAGE_FOR_BANNER_SLIDE')) {
      $staticBanner = json_decode(STATIC_IMAGE_FOR_BANNER_SLIDE);
      foreach ($staticBanner as $key => $val) {
        $staticBanners[] = array('url' => $key, 'image' => $val);
      }
    }
    if (defined('HOME_PAGE_BOX_IMAGE')) {
      $homepageBox = json_decode(HOME_PAGE_BOX_IMAGE);
      $homepageBox = array_chunk($homepageBox, 3);
    }
    $this->render('index', array('banners' => $banners, 'staticBanners' => $staticBanners, 'homepageBoxes' => $homepageBox));
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
    $contestSlug = '';
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contestSlug = $_GET['slug'];
    }
    switch ($contestSlug) {
       case FIRST_CONTEST_SLUG : 
         $this->actionContestEntries();
         break;
       case  FALLING_WALLS_CONTEST_SLUG : 
       case HELLO_FIEMME_ORGANIZER : 
         $controller = new FallingwallsController('fallingwalls');
         $controller->actionContestEntries();
         break;
       default :
         $this->actionContestEntries();
         break;
    }
  }

  public function actionContestEntries() { 
    $contest = new Contest();
    $contest->sort = '-creation_date';
    $contestInfo = array();
    $entryCount = 0;
    $entries = array();
    $categoryDetail = array();
    $selectedCategory = '';
    $countFromEntries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
      $category = new Category();
      if (array_key_exists('contestId', $contestInfo) && !empty($contestInfo['contestId'])) {
        $category->contestId = $contestInfo['contestId'];
      }
      $categoryInfo = $category->get();
      if (!empty($categoryInfo)) {
        foreach ($categoryInfo as $cat) {
          $catInfo['category_name'] = $cat['category_name'];
          $catInfo['category_slug'] = sanitization($cat['category_name']);
          $categoryDetail[] = $catInfo;
        }
      }      
    }
    if (array_key_exists('category', $_GET) && !empty($_GET['category'])) {
      $selectedCategory = sanitization($_GET['category']); 
    }
    if (array_key_exists('offset', $_GET) && isset($_GET['offset'])) {
      //check for ajax request
      if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
        $this->actionError();
        exit;
      }
      $return = array('success' => false, 'msg' => '', 'data' => array());
      $contest->offset = $_GET['offset'];
      if (array_key_exists('category', $_GET) && !empty($_GET['category'])) {    
        $entries = $this->loadEntryCategoryWise($contest, $_GET['category']);
      } else {
        $entries = $contest->getContestSubmission();  
      }
      //check whether count is exist in entries array or not 
      if (!empty($entries)) {
        $return['success'] = true;
        $countFromEntries = end($entries);
        if (array_key_exists('count', $countFromEntries)) {
          $entryCount = array_pop($entries);
        }
        foreach ($entries as $entry) { 
          $contestEntry['title'] = $entry['title'];
          $contestEntry['description'] = $entry['content']['description'];
          $contestEntry['authorName'] = $entry['author']['name'];
          $contestEntry['image'] = ''; 
          if (array_key_exists('image', $entry) && !empty($entry['image'])) {            
            $basePath = parse_url($entry['image']);
            $contestEntry['image'] = BASE_URL . resizeImageByPath( substr($basePath['path'],1),600,450);
          }          
          $contestEntry['id'] = $entry['id'];
          if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
            foreach ($entry['tags'] as $tag) {
              if ($tag['scheme'] == 'http://ahref.eu/schema/contest/category') {
                $contestEntry['categorySlug'] = $tag['slug'];
                $contestEntry['categoryName'] = $tag['name'];
              }
            }
          }
          array_push($return['data'], $contestEntry);
        }
      } else {
        $return['msg'] = Yii::t('contest', 'There are no more entries');
      }
      echo json_encode($return);
      exit;
    } elseif (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
      $contest->entryId = $_GET['id'];
      $entry = array();
      $entries = $contest->getContestSubmissionInfo();
      $entry['winner'] = false;
      if (!empty($entries)) {
        $entry['contestTitle'] = $contestInfo['contestTitle'];
        $entry['contestSlug'] = $contestInfo['contestSlug'];
        $entry['winnerStatus'] = $contestInfo['winnerStatus'];
        $entry['title'] = $entries['title'];
        $entry['description'] = $entries['content']['description'];
        $entry['authorName'] = $entries['author']['name'];
        if (!empty($entries['image']) && filter_var($entries['image'], FILTER_VALIDATE_URL)) {
          $basePath = parse_url($entries['image']);
        }
        $entry['image'] = substr($basePath['path'],1);
        $entry['url'] = BASE_URL.'contest/entries/'.$contestInfo['contestSlug'].'/'. $_GET['id'];
          if (array_key_exists('tags', $entries) && !empty($entries['tags'])) {
          foreach ($entries['tags'] as $tag) {
            if ($tag['scheme'] == 'http://ahref.eu/contest/schema/contest/prize') {
              $entry['prizeName'] = $tag['name'];
            }
            if ($tag['scheme'] == 'http://ahref.eu/contest/schema/contest/winner') {
              $entry['winner'] = true;
            }
            if ($tag['scheme'] == 'http://ahref.eu/schema/contest/category') {
              $entry['categoryName'] = $tag['name'];
            }
          }
        }
        $aggregatorMgr = new AggregatorManager(); 
        $aggregatorMgr->contestSlug = $_GET['slug'];
        $aggregatorMgr->range = $_GET['id'] .':'. 1;
        $aggregatorMgr->returnField = 'title,id';     
        $entryForPagination = $aggregatorMgr->getEntryForPagination();
        if (!empty($entryForPagination)) {
          if (array_key_exists('after', $entryForPagination) && !empty($entryForPagination['after'])) {
            $entry['nextEntryId'] = $entryForPagination['after'][0]['id'];
            $entry['nextEntryTitle'] = $entryForPagination['after'][0]['title'];
          }          
          if (array_key_exists('before', $entryForPagination) && !empty($entryForPagination['before'])) {
            $entry['prevEntryId'] = $entryForPagination['before'][0]['id'];
            $entry['prevEntryTitle'] = $entryForPagination['before'][0]['title'];
          }          
        }
      }
      $this->render('entry', array('entry' => $entry));
    } else { 
      if (!empty($contestInfo['entryStatus'])) {
        if (!empty($selectedCategory)) {     
          $entries = $this->loadEntryCategoryWise($contest, $selectedCategory);
        } else {
          $entries = $contest->getContestSubmission();
        }       
        
        //check whether count is exist in entries array or not 
        if (!empty($entries)) {
          $countFromEntries = end($entries);
          if (array_key_exists('count', $countFromEntries)) {
            $entryCount = array_pop($entries);
          }
        }
        $contestSubmissions = array();
        foreach ($entries as $entry) {
          $contestSubmission = array();         
          if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
            foreach ($entry['tags'] as $tag) {
              if ($tag['scheme'] == 'http://ahref.eu/schema/contest/category') {
                $contestSubmission['categorySlug'] = $tag['slug'];
                $contestSubmission['categoryName'] = $tag['name'];
              }
            }
          }
          if (array_key_exists('author', $entry) && !empty($entry['author'])) {
            $contestSubmission['author'] = $entry['author'];
          }
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $contestSubmission['title'] = $entry['title'];
          }
          if (array_key_exists('id', $entry) && !empty($entry['id'])) {
            $contestSubmission['id'] = $entry['id'];
          }
          if (!empty($entry['image']) && filter_var($entry['image'], FILTER_VALIDATE_URL)) {
            $basePath = parse_url($entry['image']);
          }
          $contestSubmission['image'] = substr($basePath['path'], 1);
          $contestSubmissions[] = $contestSubmission;
        }
        $contestInfo['briefDescription'] = '';
        if (!empty($contestInfo)) {
          $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
        }   
        $this->render('contestEntries', array('entries' => $contestSubmissions, 'contestInfo' => $contestInfo, 
            'entryCount' => $entryCount['count'], 'category' => $categoryDetail, 'selectedCategory' => $selectedCategory));
      } else {
        $this->redirect(BASE_URL);
      }
    }
  }

  public function actionSubmitEntries() { 
    $contestSlug = '';
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contestSlug = $_GET['slug'];
    }
    switch ($contestSlug) {
      case FIRST_CONTEST_SLUG :
        $this->actionSubmitContestEntries();
        break;
      case FALLING_WALLS_CONTEST_SLUG :
        $this->actionSubmitContestEntries();
        break;
      case HELLO_FIEMME_ORGANIZER : 
        $helloFiemme = new HellofiemmeorganizerController('hellofiemmeorganizer');
        $helloFiemme->actionSubmitEntries();
        break;
      case YOUNG_DESIGNER_CONTEST_SLUG :
        $youngDesigner = new YoungdesignerController('youngdesigner');
        $youngDesigner->actionSubmitEntries();
        break;
      default :
        $this->actionSubmitContestEntries();
        break;
    }
  }
  public function actionSubmitContestEntries() {
    $contest = new Contest();
    $contestInfo = array();
    $entrySubmissionResponse = array();
    $contestCloseDate = time();
    $hasClosedContest = false;
    $uploadFileSize = UPLOAD_IMAGE_SIZE_LIMIT/(1024*1024) .'MB';
    $entryCount = 0;
    $entries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $contestInfo = $contest->getContestDetail();
    if(array_key_exists('closingDate', $contestInfo) && !empty($contestInfo['closingDate'])) {
      $contestCloseDate = strtotime($contestInfo['closingDate']);
    }
    if ($contestCloseDate < time()) {
      $hasClosedContest = true;
    }
    $entrySubmittedByUser = false;
    $postData = array();
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
    }
    if (!empty(Yii::app()->session['user'])) {
      $aggregatorManager = new AggregatorManager();
      $aggregatorManager->authorSlug = Yii::app()->session['user']['id'];
      $aggregatorManager->contestSlug = $_GET['slug'];
      $entrySubmittedByUser = $aggregatorManager->isUserAlreadySubmitEntry('title');
      if (!empty($_POST)) {
        if ( !$entrySubmittedByUser ) {
          $postData = $_POST;
          switch ($_GET['slug']) {
            case 'photo_contest':
              $entrySubmissionResponse = $this->entrySubmission();
              break;
            case FALLING_WALLS_CONTEST_SLUG :
              $entrySubmissionResponse = $contest->fallingWallsEntrySubmission();
              break;
            default :
              $entrySubmissionResponse['success'] = false;
              $entrySubmissionResponse['msg'] = 'Invalid access';
              break;
          }         
        }
      }
    }
    $uploadFileSize = ini_get('upload_max_filesize') . 'B';
    $this->render('contestSubmitEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount, 'message' => $entrySubmissionResponse, 
        'isEntrySubmit' => $entrySubmittedByUser, 'postData' => $postData, 'uploadFileSize' => $uploadFileSize, 'hasClosedContest' => $hasClosedContest));
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
        if (array_key_exists('contestRule', $_POST) && !empty($_POST['contestRule'])) {
          $_POST['contest_rule'] = htmlspecialchars($_POST['contestRule']);
        }
        if (!empty($_FILES['image']['name'])) {
          $directory = UPLOAD_DIRECTORY .'contestImage/';
          $uploadBannerImage = $this->uploadImage($directory, 'image');
          if (!$uploadBannerImage['success']) {
            throw new Exception($uploadBannerImage['msg']);
          }
        } else {
          throw new Exception(Yii::t('contest', 'Please provide banner image'));
        }
        if (!empty($_FILES['squareImage']['name'])) {
          $directory = UPLOAD_DIRECTORY.'contestImage/';
          $uploadSquareImage = $this->uploadImage($directory, 'squareImage');
          if (!$uploadSquareImage['success']) {
            throw new Exception($uploadSquareImage['msg']);
          }
        } else {
          throw new Exception(Yii::t('contest', 'Please provide square image'));
        }

        $response = $contest->createContest($uploadBannerImage['img'], $uploadSquareImage['img']);
        if ($response['success']) {
          $this->redirect(BASE_URL . 'admin/contest/list');
        } else {
          $response['msg'] =  $response['msg'];
        }
      }
    } catch (Exception $e) {
      $response['success'] = false;
      $response['msg'] = $e->getMessage();
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
    $this->render('login', array('message' => $response, 'back_url' => $backUrl));
  }

  /**
   * actionEntrySubmission
   * this function is used for submit entry 
   */
  public function entrySubmission() {
    $contestSlug = $_GET['slug'];
    $response = array();
    $response['success'] = false;
    //check for upload file size in ini
    if (!$this->checkIniAllowedFileSize()) {
       $response['msg'] = "Upload size define in config file is greater than size define in php.ini file";
    } else if($_FILES['contestEntry']['error'] != 0) { 
      $response['msg'] = setFileUploadError($_FILES['contestEntry']['error']);
    } else  if (empty($_FILES['contestEntry']['name'])) {
      $response['msg'] = Yii::t('contest', 'Please provide an image for entry');
    } else {
      $extention = explode('/', $_FILES['contestEntry']['type']);
      $imageExtension = end($extention);
      $allowedImageExtention = json_decode(ALLOWED_IMAGE_EXTENSION, true);
      if (!in_array($imageExtension, $allowedImageExtention)) {
        $response['msg'] = Yii::t('contest', 'Please upload jpg image');
      } else if ($_FILES['contestEntry']['size'] > UPLOAD_IMAGE_SIZE_LIMIT) {
        $response['msg'] = Yii::t('contest', 'Image size should be less than 5MB');
      } else {
        $contest = new Contest();
        $directory = UPLOAD_DIRECTORY.'contestEntry/';
        $imageName = uploadFile($directory, 'contestEntry');
        $imagePath = $directory . $imageName;
        if ($imageName) {
          $response = $contest->submitContestEntry($imagePath, $contestSlug);
        } else {
          Yii::log('', ERROR, Yii::t('contest', 'Error in entrySubmission :: Image name is empty') );
          $response['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');
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
        $contestDetail[$i]['id'] = $info['contestId'];
        $contestDetail[$i]['winnerStatus'] = $info['winnerStatus'];
        $contest->contestSlug = $info['contestSlug'];
        $contest->count = 2;
        $entry = $contest->getContestSubmission();
        $contestDetail[$i]['entryCount'] = 0;
        if (!empty($entry)) {
          $contestDetail[$i]['entryCount']= $entry[0]['count'];
        }
        $contestDetail[$i]['category_exists'] = false;
        if (array_key_exists('contestId', $info) && !empty($info['contestId'])) {
          $category = new Category();
          $category->contestId = $info['contestId'];
          $categoryInfo = $category->get();
          if (!empty($categoryInfo)) {
            $contestDetail[$i]['categoryExist'] = true;
          }
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
          $startDateTimeArr =  explode(' ', $contestDetails['startDate']);
          $startDateArr = explode('/', $startDateTimeArr[0]);
          $startTimeArr = explode(':', $startDateTimeArr[1]);
          $startTime = mktime($startTimeArr[0], $startTimeArr[1], 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contest->startDate = date('Y-m-d H:i:s', $startTime);
        }
        if (array_key_exists('endDate', $contestDetails) && empty($contestDetails['endDate'])) {
          throw new Exception(Yii::t('contest', 'End date should not be empty'));
        } else if (!validateDate($contestDetails['endDate'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid end date'));
        } else {
          $endDateTimeArr =  explode(' ', $contestDetails['endDate']);
          $endDateArr = explode('/', $endDateTimeArr[0]);
          $endTimeArr = explode(':', $endDateTimeArr[1]);
          $endTime = mktime($endTimeArr[0], $endTimeArr[1], 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contest->endDate = date('Y-m-d H:i:s', $endTime);
        }
        if (array_key_exists('jury_rating_from', $contestDetails) && empty($contestDetails['jury_rating_from'])) {
          throw new Exception(Yii::t('contest', 'Jury Rating Start date should not be empty'));
        } else if (!validateDate($contestDetails['jury_rating_from'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid jury rating start date'));
        } else {
          $startDateTimeArr =  explode(' ', $contestDetails['jury_rating_from']);
          $startDateArr = explode('/', $startDateTimeArr[0]);
          $startTimeArr = explode(':', $startDateTimeArr[1]);
          $startTime = mktime($startTimeArr[0], $startTimeArr[1], 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contest->juryRatingStartDate = date('Y-m-d H:i:s', $startTime);
        }
        if (array_key_exists('jury_rating_till', $contestDetails) && empty($contestDetails['jury_rating_till'])) {
          throw new Exception(Yii::t('contest', 'Jury rating end date should not be empty'));
        } else if (!validateDate($contestDetails['jury_rating_till'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid jury rating end date'));
        } else {
          $endDateTimeArr =  explode(' ', $contestDetails['jury_rating_till']);
          $endDateArr = explode('/', $endDateTimeArr[0]);
          $endTimeArr = explode(':', $endDateTimeArr[1]);
          $endTime = mktime($endTimeArr[0], $endTimeArr[1], 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contest->juryRatingEndDate = date('Y-m-d H:i:s', $endTime);
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
          $directory = UPLOAD_DIRECTORY. 'contestImage/';
          $uploadBannerImage = $this->uploadImage($directory, 'image');
          if (!$uploadBannerImage['success']) {
            throw new Exception(Yii::t('contest', $uploadBannerImage['msg']));
          }
        }
        if (empty($_FILES['squareImage']['name'])) {
          $contest->squareImage = $contestDetails['squareImage'];
        } else {
          $directory = UPLOAD_DIRECTORY.'contestImage/';
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
        $contest->contestDescription = trim($contestDetails['contestDescription']);
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
      $message['msg'] = $e->getMessage();
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
        $contestDetail['startDate'] = date('m/d/Y H:i', strtotime($contestInfo['startDate']));
        $contestDetail['endDate'] = date('m/d/Y H:i', strtotime($contestInfo['endDate']));
        $contestDetail['imagePath'] = $contestInfo['imagePath'];
        $contestDetail['contestDescription'] = $contestInfo['contestDescription'];
        $contestDetail['contestSlug'] = $contestInfo['contestSlug'];
        $contestDetail['squareImage'] = $contestInfo['squareImage'];
        $contestDetail['contestRule'] = htmlspecialchars($contestInfo['rule']);
        $contestDetail['entryStatus'] = $contestInfo['entryStatus'];
        $contestDetail['jury_rating_from'] = date('m/d/Y H:i', strtotime($contestInfo['jury_rating_from']));
        $contestDetail['jury_rating_till'] = date('m/d/Y H:i', strtotime($contestInfo['jury_rating_till']));     
      } catch (Exception $e) {
        $message['success'] = false;
        $message['msg'] = $e->getMessage();
        
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
        $directory = UPLOAD_DIRECTORY.'contestImage/';
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
   * actionContestBrief
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
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
    }
    $this->render('contestBrief', array('contestInfo' => $contestInfo));
  }

  /**
   * entriesAdminView
   * 
   * This function is used for get entries when
   *   admin user is logged in and entry status of
   *   contest is hide.
   */
   public function actionEntriesAdminView() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $contestSlug = '';
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contestSlug = $_GET['slug'];
    }
    switch ($contestSlug) {
       case FIRST_CONTEST_SLUG : 
         $this->actionContestEntriesAdminView();
         break;
       case  FALLING_WALLS_CONTEST_SLUG : 
         $controller = new FallingwallsController('fallingwalls');
         $controller->isAdmin = true;
         $controller->actionContestEntries();
         break;
       default :
         $this->actionContestEntries();
         break;
    }
  }

  public function actionContestEntriesAdminView() {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $contest = new Contest();
    $contest->sort = '-creation_date';
    $contestInfo = array();
    $entryCount = 0;
    $entries = array();
    $countFromEntries = array();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
    }
    $entries = $contest->getContestSubmission();
    //check whether count exist in entries at last index    
    if (!empty($entries)) {
      $countFromEntries = end($entries);
      if (array_key_exists('count', $countFromEntries)) {
        $entryCount = array_pop($entries);
      } 
      $i = 0;
      foreach ($entries as $entry) {
        if (array_key_exists('image', $entry) && !empty($entry['image']) && filter_var($entry['image'], FILTER_VALIDATE_URL)) {
          $basePath = parse_url($entry['image']);
          $entries[$i]['image'] = substr($basePath['path'], 1);
          $i++;
        }
      }
    }
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
    }
    $this->render('contestEntries', array('entries' => $entries, 'contestInfo' => $contestInfo, 'entryCount' => $entryCount['count']));
  }
  
  /**
   * loadEntryCategoryWise
   * 
   * This function will be used for load entry for a particular category
   * @param $contest - object of Contest class
   * @param $categorySlug  - category slug to load entries
   * @return array $entries
   */
  public function loadEntryCategoryWise($contest, $categorySlug) { 
    $entries = array();
    if (!empty($contest)) {
      $contest->tags =  $contest->contestSlug . '{http://ahref.eu/contest/schema/},' .  $categorySlug . '{http://ahref.eu/schema/contest/category}';
      $entries = $contest->getContestSubmissionForCategory();
    }
    return $entries;
  }
  
  /**
   * checkIniAllowedFileSize
   * 
   * check whether upload file size limit define in config file is greater then config file or not
   * @return boolean
   */
  public function checkIniAllowedFileSize() {
    $allowed = false;
    $iniFileSize = ini_get('upload_max_filesize');
    $iniFileSize  = substr($iniFileSize, 0, strlen($iniFileSize) - 1);
    $iniFileSize = $iniFileSize * 1024 * 1024;  
    if ($iniFileSize >= UPLOAD_IMAGE_SIZE_LIMIT) {
      $allowed =  true;
    }
    return $allowed;
  }
  
  /**
   * winnerStatus
   * 
   * This function is used for update status 
   */
  public function actionWinnerStatus() {
    if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
      $this->actionError();
      exit;
    }  else {
      $return = array('success' => false, 'status' => '');
      if (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
        $contest = new ContestAPI();
        $contest->contestId = $_GET['id'];
        if (array_key_exists('status', $_GET) && !empty($_GET['status'])) {
          $return['status'] = Yii::t('contest', 'Show');
          if ($_GET['status'] == (Yii::t('contest', 'Show'))) { 
            $return['status'] = Yii::t('contest', 'Hide');
            $contest->winnerStatus = true;
          } 
        }
        $isUpdate = $contest->updateContestWinnerStatus();
        if (!empty($isUpdate)) {
          $return['success'] = true;
        }
      }
    }
    echo json_encode($return);
    exit;
  }
  
  /**
   * actionDownloadSubmission
   * function for download zip file (all submission image)
   */
  public function actionDownloadSubmission() { 
    try {
      $message = '';
      $contestSubmissions = array();
      if (array_key_exists('contest_slug', $_GET) && !empty($_GET['contest_slug'])) {
        $aggManager = new AggregatorManager();
        $entries = $aggManager->getEntry(9999, 0, '', 'active', $_GET['contest_slug'].'[contest]', '', '',
                0, '', '', 1, '', array(), '', 'links,author,id,tags,title,content', '', '', SOURCE);
        if (empty($entries)) {
          Yii::log('actionDownloadSubmission ', INFO, 'There is no submission in this contest');
          $message = Yii::t('contest', 'There is no submission in this contest');
        } else {
          foreach ($entries as $entry) {
            $contestSubmission = array();
            if (array_key_exists('links', $entry) && !empty($entry['links'])) {
              if (!empty($entry['links']['enclosures'])) {
                $contestSubmission['image'] = $entry['links']['enclosures'][0]['uri'];
              }
              if (array_key_exists('author', $entry) && !empty($entry['author'])) {
                $contestSubmission['author'] = $entry['author']['name'];
              }
              if (array_key_exists('content', $entry) && !empty($entry['content'])) {
                if (array_key_exists('is_minor', $entry['content']) && $entry['content']['is_minor'] == MINOR) {
                  $contestSubmission['author'] = $entry['content']['minor_name'];
                } 
              }              
              if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
                foreach ($entry['tags'] as $tag) {
                  if ($tag['scheme'] == 'http://ahref.eu/schema/contest/category') {
                    $contestSubmission['tag'] = $tag['slug'];
                    break;
                  }
                }
              }
              if (array_key_exists('title', $entry) && !empty($entry['title'])) {
                $contestSubmission['title'] = sanitization($entry['title']);
              }
            }
            $contestSubmissions[] = $contestSubmission;
          }
          //create zip file
          $filename = $this->createZipFile($contestSubmissions);          
          if ($filename) {
            downloadZipFile($filename);
          } else {
            Yii::log('Error in actionDownloadSubmission ', ERROR,' failed to create zip file');
            $message = Yii::t('contest', 'Some technical error occur. Please contact adminstrator');
          }
        }
      } else {
        Yii::log('Error in actionDownloadSubmission ', ERROR, ' contest slug is empty');
        $message = Yii::t('contest', 'Some technical error occur. Please contact adminstrator');
      }
    } catch (Exception $e) {
       Yii::log('Error in actionDownloadSubmission ', ERROR, $e->getMessage());
       $message = Yii::t('contest', 'Some technical error occur. Please contact adminstrator');
    }  
    $this->render('downloadSubmission', array('message' => $message));
  }

  /**
   * createZipFile
   * function for create zip file
   * @param array $contestSubmissions  - submission detail
   * @return string $destination - zip file name
   */
  public function createZipFile($contestSubmissions) {
    if (empty($contestSubmissions)) {
      return '';
    }
    $zip = new ZipArchive();
    $destination = DOWNLOAD_ZIP_DIRECTORY . $_GET['contest_slug'].'_submission_'. time().'.zip';
    if ($zip->open($destination, ZIPARCHIVE::CREATE) !== true) {
      Yii::log('Error in createZipFile ', ERROR,' failed to create zip archive');
      return '';
    }

    foreach ($contestSubmissions as $submission) { 
      $filename = '';
      if (array_key_exists('image', $submission) && !empty($submission['image'])) {        
        $pathinfo = pathinfo($submission['image']);        
        if (array_key_exists('basename', $pathinfo) && !empty($pathinfo['basename'])) {
          $submission['entry_image_path'] = SUBMISSION_IMAGE. $pathinfo['basename'];
        }
        if (array_key_exists('entry_image_path', $submission) && !file_exists( $submission['entry_image_path'])) {          
          Yii::log('createZipFile :: ', INFO, 'file does not exist ' . $submission['entry_image_path']);
          continue;
        }       
        if (array_key_exists('extension', $pathinfo) && !empty($pathinfo['extension'])) {
          if (!in_array(strtolower($pathinfo['extension']), json_decode(ALLOWED_IMAGE_EXTENSION))) {
            Yii::log('createZipFile :: ', INFO, 'file extention is not allowed ' . $submission['entry_image_path']); 
            continue;
          }              
          $authorName = '';
          $tag = '';
          if (array_key_exists('author', $submission) && !empty($submission['author'])) {
            $authorName = str_replace(' ', '-',  strtolower($submission['author']));
          }
          if (array_key_exists('tag', $submission) && !empty($submission['tag'])) {
            $tag = $submission['tag']; 
          }
          $filename = $tag .'-'.$authorName.'+'.$submission['title'] . '.'.$pathinfo['extension'];
        }
      }
      if (!empty($filename)) {
        $zip->addFile($submission['entry_image_path'], $filename);
      }      
    }
    $zip->close();
    return $destination;
  }
}
