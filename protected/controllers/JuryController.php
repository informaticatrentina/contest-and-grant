<?php

/**
 * JuryController
 * 
 * JuryController class inherit controller (base) class .
 * Actions are defined in JuryController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class JuryController extends Controller {

  public function beforeAction($action) {
    new JsTrans('js', SITE_LANGUAGE);
    return true;
  }

  /**
   * actionManageJury
   * function is used for add, update jury member and admin
   */
  public function actionManageJury() {
    try {
      //check if user belong to admin users or not
      $isAdmin = isAdminUser();
      if (!$isAdmin) {
        $this->redirect(BASE_URL);
      }
      $juryEmail = array('jury_member' => array(), 'jury_admin' => array());
      $message = '';
      $isExistContest = false;
      $jury = new Jury();
      if (array_key_exists('contest_id', $_GET) && !empty($_GET['contest_id'])) {
        $jury->contestId = $_GET['contest_id'];
        $contest = new ContestAPI();
        $contest->contestId = $_GET['contest_id'];
        $contestDetail = $contest->getContestDetailByContestId();
        if (!empty($contestDetail)) {
          $isExistContest = true;
        }
      }
      if (!empty($_POST)) {
        $postData = $_POST;
        $juryEmail = $postData;
        if (array_key_exists('jury_member', $postData) && empty($postData['jury_member'])) {
          throw new Exception(Yii::t('contest', 'Jury member email can not be empty'));
        }
        if (array_key_exists('jury_member', $postData) && empty($postData['jury_member'])) {
          throw new Exception(Yii::t('contest', 'Jury admin email can not be empty'));
        }

        $juryMember = array_unique(array_map("trim", explode(',', $postData['jury_member'])));
        $juryAdmin = array_unique(array_map("trim", explode(',', $postData['jury_admin'])));
        $jury->creationDate = time();

        $emailList = array_merge($juryMember, $juryAdmin);
        foreach ($emailList as $email) {
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(Yii::t('contest', 'Email id is not valid') . ' : ' . $email);
          }
        }
        $jury->delete();
        //save jury admin
        foreach ($juryAdmin as $admin) {
          $adminSaveStatus = $this->saveJury($jury, $admin, JURY_ADMIN);
          if (!$adminSaveStatus) {
            Yii::log('Error in actionManageJury ', ERROR, Yii::t('contest', 'failed to save jury admin ')
             . $admin);
          }
        }
        //save jury member
        foreach ($juryMember as $member) {
          $memberSaveStatus = $this->saveJury($jury, $member, JURY_MEMBER);
          if (!$memberSaveStatus) {
            Yii::log('Error in actionManageJury ', ERROR, Yii::t('contest', 'failed to save jury member ') 
              . $member);
          }
        }
        $message = Yii::t('contest', 'Jury admin and memeber have been saved successfully');
      } else {        
        $juryInfo = $jury->get();
        foreach ($juryInfo as $info) {
          switch ($info['designation']) {
            case JURY_ADMIN:
              $juryEmail['jury_admin'][] = $info['email_id'];
              break;
            case JURY_MEMBER:
              $juryEmail['jury_member'][] = $info['email_id'];
              break;
          }
        }
        if (array_key_exists('jury_admin', $juryEmail)) {
          $juryEmail['jury_admin'] = implode(', ', $juryEmail['jury_admin']);
        }
        if (array_key_exists('jury_member', $juryEmail)) {
          $juryEmail['jury_member'] = implode(', ', $juryEmail['jury_member']);
        }
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in actionManageJury :') . $e->getMessage());
      $message = $e->getMessage();
    }
    $this->render('jury', array('jury' => $juryEmail, 'message' => $message, 'is_exist_contest'=>$isExistContest));
  } 
  
  /**
   * actionActiveContest
   * function is used for get all active contest
   */
  public function actionActiveContest() {
    try {
      $roles = getRoles();
      if (!in_array('jury_member', $roles)) {
        $this->redirect(BASE_URL);
      } 
      $currentContest = array();
      $activeContest = array();
      $jury = new Jury();
      $jury->emailId = Yii::app()->session['user']['email'];
      $jury->designation = JURY_MEMBER;
      $juryInfo = $jury->fetchAll();   
      foreach ($juryInfo as $info) {
        if (time() >= strtotime($info['jury_rating_from']) && time() <= strtotime($info['jury_rating_till'])) {
          $currentContest[] =  $info['contestSlug'];
          $activeContest[] = array('slug' => $info['contestSlug'], 'contest_name' => $info['contestTitle']);
        }
      }  
      $_SESSION['user']['active_contest'] = $currentContest;
    } catch (Exception $e) {
      Yii::log('Error in actionActiveContest ', ERROR, $e->getMessage());
    }
    $this->render('activeContest', array('active_contest' => $activeContest));
  }

  /**
   * actionGetSubmissions
   * function is used for get all submission for a contest
   */
  public function actionJuryRating() {
    try {
      $roles = getRoles();
      if (!in_array('jury_member', $roles)) {
        $this->redirect(BASE_URL);
      }
      $contestSubmissions = array();
      $contest = new Contest();
      $contest->limit = 9999;
      if (array_key_exists('contest_slug', $_GET) && !empty($_GET['contest_slug'])) {
        $contest->contestSlug = $_GET['contest_slug'];
        if (!array_key_exists('active_contest', Yii::app()->session['user']) || !in_array($_GET['contest_slug'], Yii::app()->session['user']['active_contest'])) {
          $this->redirect(BASE_URL);
        }
      }
      $contestEntries = $contest->getContestSubmission();
      $contestSubmission = $this->prepareSubmission($contestEntries);
      if (array_key_exists('entry', $contestSubmission) && (!empty($contestSubmission['entry']))) {
        foreach ($contestSubmission['entry'] as $entry) {
          $contestEntry = array();
          $contestEntry['id'] = $entry['id'];
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $contestEntry['title'] = $entry['title'];
          }
          $contestEntry['author'] = $entry['author'];
          if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
            foreach ($entry['tags'] as $tag) {
              if ($tag['scheme'] == JURY_RATING_SCHEME && $tag['slug'] == Yii::app()->session['user']['id']) {
                $contestEntry['jury_rating'] = $tag['weight'];
              }
            }
          }
          $contestSubmissions[] = $contestEntry;
        }
      }
    } catch (Exception $e) {
      Yii::log('Error in actionJuryRating ', ERROR, $e->getMessage());
    }
    $this->render('rating', array('entries' => $contestSubmissions, 'contest_slug' => $_GET['contest_slug']));
  }

  /**
   * actionSaveRating
   * function is used for save jury rating
   */
  public function actionSaveRating() {
    if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
      $this->redirect(BASE_URL);
    }
    $response = array('success' => false);
    $entryTags = array();
    if (array_key_exists('entry_id', $_GET) && !empty($_GET['entry_id'])) {
      $entryId = $_GET['entry_id'];
      $fallingWall = new FallingWallsContest();
      $entryTags = $fallingWall->getEntryTags($entryId, $_GET['contest_slug']);
    }
    $voting = '';
    if (array_key_exists('rating', $_GET) && !empty($_GET['rating'])) {
      $voting = $_GET['rating'];
    }
    $modifiedTags = $this->modifiedTags($entryTags, $voting);
    $aggregator = new AggregatorManager();
    $aggregator->tags = $modifiedTags;
    $aggregator->entryId = $entryId;
    $isUpdate = $aggregator->updateEntry();   
    if (array_key_exists('success', $isUpdate) && $isUpdate['success'] == true) {
      $response['success'] = true;
    } else {
      Yii::log('Error in actionSaveRating ', ERROR, 'Failed to save rating' );
    }
    echo json_encode($response);
    exit;
  }

  /**
   * modifiedTags
   * function is used for update existing tag
   * @param array $tags - tag to be modified
   * @param array $voting
   * @return array $updatedTags
   */
  public function modifiedTags($tags, $voting) {
    $updatedTags = array();
    $votingCountExist = false;
    $userAlreadyRating = false;
    $prevRating = 0;
    if (empty($tags)) {
      return $updatedTags;
    }    
    //check for previous rating of jury member
    foreach ($tags as $tag) {
      if ($tag['scheme'] == JURY_RATING_SCHEME && $tag['slug'] == Yii::app()->session['user']['id']) {
        $prevRating = $tag['weight'];
        break;
      }
    }
    foreach ($tags as $tag) {
      if ($tag['scheme'] == JURY_RATING_SCHEME && $tag['slug'] == Yii::app()->session['user']['id']) {
        $tag['weight'] = $voting;
        $userAlreadyRating = true;
      }
      if ($tag['scheme'] == RATING_COUNT_SCHEME) {
        $tag['weight'] = $tag['weight'] + $voting - $prevRating;      
        $votingCountExist = true;        
      }
      $updatedTags[] = $tag; 
    }
    if (!$userAlreadyRating) {
      array_push($updatedTags, array('name' => Yii::app()->session['user']['firstname'] . ' ' .
        Yii::app()->session['user']['lastname'],
        'slug' => Yii::app()->session['user']['id'],
        'scheme' => JURY_RATING_SCHEME,
        'weight' => $voting));
    }
    if (!$votingCountExist) {
      array_push($updatedTags, array('name' => 'count',
        'slug' => 'count',
        'scheme' => RATING_COUNT_SCHEME,
        'weight' => $voting));
    }
    return $updatedTags;
  }
  
  /**
   * actionViewEntry
   * function is used for view entry detail
   */
  public function actionViewEntry() {
    $contest = new Contest();
    if (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
      $contest->entryId = $_GET['id'];      
    }
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];      
    }
    $entries = array($contest->getContestSubmissionInfo());
    $contestEntries = $this->prepareSubmission($entries); 
    if (array_key_exists('tags', $contestEntries['entry'][0]) && !empty($contestEntries['entry'][0]['tags'])) {
      foreach ($contestEntries['entry'][0]['tags'] as $tag) {
        if ($tag['scheme'] == JURY_RATING_SCHEME && $tag['slug'] == Yii::app()->session['user']['id']) {
          $contestEntries['jury_rating'] = $tag['weight'];
        }
      }
    }     
    $pagination = array();
    $aggregatorMgr = new AggregatorManager();
    $aggregatorMgr->contestSlug = $_GET['slug'];
    $aggregatorMgr->range = $_GET['id'] . ':' . 1;
    $aggregatorMgr->returnField = 'title,id';
    $entryForPagination = $aggregatorMgr->getEntryForPagination();
    if (!empty($entryForPagination)) {
      if (array_key_exists('after', $entryForPagination) && !empty($entryForPagination['after'])) {
        $pagination['nextEntryId'] = $entryForPagination['after'][0]['id'];
        if (array_key_exists('title', $entryForPagination['after'][0])) {
          $pagination['nextEntryTitle'] = $entryForPagination['after'][0]['title'];
        }
      }
      if (array_key_exists('before', $entryForPagination) && !empty($entryForPagination['before'])) {
        $pagination['prevEntryId'] = $entryForPagination['before'][0]['id'];
        if (array_key_exists('title', $entryForPagination['before'][0])) {
          $pagination['prevEntryTitle'] = $entryForPagination['before'][0]['title'];
        }
      }
    }
    $this->render('viewEntry', array('entries' => $contestEntries, 'pagination'=> $pagination, 'contest_slug' => $_GET['slug']));
  }
  
  /**
   * prepareSubmission 
   * function is used for prepare contest submission in proper format 
   * @param array $contestEntries
   * @return (array)
   */
  public function prepareSubmission($contestEntries) {
    $contestSubmissions = array();
    if (empty($contestEntries)) {
      return $contestSubmissions;
    }
    $countFromEntries = end($contestEntries);
    if (array_key_exists('count', $countFromEntries)) {
      $contestSubmissions['entry_count'] = array_pop($contestEntries);
    }
    
    foreach ($contestEntries as $entry) {
      $contestSubmission = array();
      $contestSubmission['id'] = $entry['id'];
      if (array_key_exists('title', $entry) && !empty($entry['title'])) {
        $contestSubmission['title'] = $entry['title']; 
      }
      $contestSubmission['author'] = $entry['author'];
      $contestSubmission['tags'] = $entry['tags'];
      
      if (array_key_exists('content', $entry) && array_key_exists('description', $entry['content'])) {
        $contestSubmission['description'] = $entry['content']['description'];
      }
      if (array_key_exists('links', $entry) && array_key_exists('enclosures', $entry['links'])) {
          foreach ($entry['links']['enclosures'] as $enclosure) { 
            if (strpos($enclosure['type'], 'pdf') !== FALSE) {
              $enclosure['type'] = 'pdf';
            }
            if (strpos($enclosure['type'], 'image') !== FALSE) {
              $enclosure['type'] = 'image';
            }
            switch($enclosure['type']) {
              case 'image':
                $contestType[] = 'image';
                $contestSubmission['image_path'][] = $enclosure['uri'];
                break;
              case 'video':
                $contestType[] = 'video';
                $fallingWalls = new FallingWallsContest();
                $contestSubmission['video_image_path'][] = $fallingWalls->getVideoImage($enclosure['uri']);
                $contestSubmission['video_links'][] = $enclosure['uri'];
               break;
              case 'pdf':
                $contestType[] = 'pdf';
                $contestSubmission['pdf_file_path'][] = $enclosure['uri'];
                break;
            }
          }
      }
      $contestSubmissions['entry'][] = $contestSubmission;
    }
    $contestSubmissions['contest_type'] = array_unique($contestType);
    return $contestSubmissions;
  }
  
  /**
   * saveJury
   * function is used for save jury
   * @param $jury  - object of jury class
   * @param string $juryEmail
   * @param string $designation 
   * @return boolean
   */
  public function saveJury($jury, $juryEmail, $designation) {
    $userId = '';
    $user = new UserIdentityAPI();    
    $userDetail = $user->getUserDetail(IDM_USER_ENTITY, array('email' => urlencode($juryEmail)), true);
    if (array_key_exists('_items', $userDetail) && array_key_exists(0, $userDetail['_items']) && array_key_exists('_id', $userDetail['_items'][0])) {
      $userId = $userDetail['_items'][0]['_id'];
    } else {
      Yii::log('Error in saveJury', ERROR, "failed in getting detail of jury email : $juryEmail \n" . print_r($userDetail, true));
      throw new Exception(Yii::t('contest', 'Some technical problem occurred, Please check log'));
    }
    $jury->userId = $userId;
    $jury->emailId = $juryEmail;
    $jury->designation = $designation;
    return $jury->save();
  }
}