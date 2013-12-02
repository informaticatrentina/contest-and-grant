<?php

/**
 * JuryadminController
 * 
 * JuryadminController class inherit controller (base) class .
 * Actions are defined in JuryController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class JuryadminController extends Controller {

  public function beforeAction($action) {
    new JsTrans('js', SITE_LANGUAGE);
    return true;
  }

 /**
   * actionActiveContest
   * function is used for get all active contest
   */
  public function actionActiveContest() {
    try {
      $roles = getRoles();
      if (!in_array('jury_admin', $roles)) {
        $this->redirect(BASE_URL);
      } 
      $currentContest = array();
      $activeContest = array();
      $jury = new Jury();
      $jury->emailId = Yii::app()->session['user']['email'];
      $jury->designation = JURY_ADMIN;
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
   * actionJuryRating
   * function is used for get all submission for a contest (order by jury rating)
   */
  public function actionJuryRating() {
    try {
      $roles = getRoles();
      if (!in_array('jury_admin', $roles)) {
        $this->redirect(BASE_URL);
      }
      $contestSubmissions = array();
      if (!array_key_exists('contest_slug', $_GET)) {
        $this->redirect(BASE_URL);
      }
      if (!array_key_exists('active_contest', Yii::app()->session['user']) || !in_array($_GET['contest_slug'], Yii::app()->session['user']['active_contest'])) {
        $this->redirect(BASE_URL);
      }
      $contestEntries = $this->getSubmissionForJuryAdmin();
      $juryController = new JuryController('jury');
      $contestSubmission = $juryController->prepareSubmission($contestEntries);
      if (array_key_exists('entry', $contestSubmission) && (!empty($contestSubmission['entry']))) {
        foreach ($contestSubmission['entry'] as $entry) {
          if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
            $count = 0;
            foreach ($entry['tags'] as $tag) {
              if ($tag['scheme'] == RATING_COUNT_SCHEME) {
                $count = $tag['weight'];
              }
            }
          }
          $contestSubmissions[] = array('id' => $entry['id'], 'title' => $entry['title'], 'author' => $entry['author'],
              'jury_rating' => $count);
        }
      }
    } catch (Exception $e) {
      Yii::log('Error in actionJuryRating ', ERROR, $e->getMessage());
    }
    $this->render('rating', array('entries' => $contestSubmissions, 'contest_slug' => $_GET['contest_slug']));
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
    $juryController = new JuryController('jury');
    $contestEntries = $juryController->prepareSubmission($entries);  
    if (array_key_exists('tags', $contestEntries['entry'][0]) && !empty($contestEntries['entry'][0]['tags'])) {
      foreach ($contestEntries['entry'][0]['tags'] as $tag) {
        if ($tag['scheme'] == RATING_COUNT_SCHEME) {
          $contestEntries['jury_rating'] = $tag['weight'];
        }
      }
    }     
    //loading submission detail  for pagination
    $pagination = array();
    $aggregatorMgr = new AggregatorManager();
    $aggregatorMgr->contestSlug = $_GET['slug'];
    $aggregatorMgr->range = $_GET['id'] . ':' . 1;
    $aggregatorMgr->returnField = 'title,id';
    $entryForPagination = $aggregatorMgr->getEntryForPagination();
    if (!empty($entryForPagination)) {
      if (array_key_exists('after', $entryForPagination) && !empty($entryForPagination['after'])) {
        $pagination['nextEntryId'] = $entryForPagination['after'][0]['id'];
        $pagination['nextEntryTitle'] = $entryForPagination['after'][0]['title'];
      }
      if (array_key_exists('before', $entryForPagination) && !empty($entryForPagination['before'])) {
        $pagination['prevEntryId'] = $entryForPagination['before'][0]['id'];
        $pagination['prevEntryTitle'] = $entryForPagination['before'][0]['title'];
      }
    }
    $this->render('viewEntry', array('entries' => $contestEntries, 'pagination'=> $pagination, 'contest_slug' => $_GET['slug']));
  }

  /**
   * actionJuryStatus
   * function is used for check whether jury has rated submission or not
   */
  public function actionJuryStatus() { 
    $juryInfo = array();
    $submissions = array();
    $jury = new Jury();
    $contest = new Contest();
    $contest->limit = 9999;
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $jury->contestSlug = $_GET['slug'];      
      $jury->designation = JURY_MEMBER;      
      $juryList = $jury->fetchAll();
      foreach ($juryList as $list) {
        $juryInfo[$list['user_id']] = array('user_id' => $list['user_id'], 'email' => $list['email_id']);
      }  
      $contestEntries = $contest->getContestSubmission();
      foreach ($contestEntries as $entries) {
        if (array_key_exists('tags', $entries) && !empty($entries['tags'])) {
          $submissions[$entries['id']] =  array('title' => $entries['title'], 'entry_id' => $entries['id']); 
          foreach ($entries['tags'] as $tag) {            
            if ($tag['scheme'] == JURY_RATING_SCHEME && !empty($tag['weight'])) {  
              $submissions[$entries['id']]['jury_id'][] = $tag['slug']; 
            }
          }
        }
      }
    }
    $this->render('viewJuryRating', array('jury' => $juryInfo, 'submissions'=> $submissions, 'contest_slug' => $_GET['slug']));
  }
  
  /**
   * getSubmissionForJuryAdmin
   * function is used for getting all submission for jury admin 
   * @return  - all submission for jury admin
   */
  public function getSubmissionForJuryAdmin() {
    $jury = new Jury();
    $inputParam = array(
        'return_fields' => 'links,author,title,id,tags',
        'sort' => '-tag:count',
        'tags' => $_GET['contest_slug'] . '{http://ahref.eu/contest/schema/},count',
        'limit' => 9999,
        'offset' => 0,
        'source' => SOURCE
    );   
    return $jury->getSortedContestSubmission($inputParam);
  }
}