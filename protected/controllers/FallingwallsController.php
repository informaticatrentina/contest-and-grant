<?php

/**
 * FallingWallsController
 * 
 * FallingWallsController class is used  for manage flow of falling walls contest
 * FallingWallsController class extends base class Controller.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class FallingWallsController extends Controller {

  public $isAdmin;

  public function actionIndex() {
    $this->redirect(BASE_URL);
  }

  /**
   * contestEntries
   * function is used for get contest submission  
   */
  public function actionContestEntries() {
    try {
      $contestInfo = array();
      $entries = array();
      $entryCount = 0;
      $contestSubmission = array();
      $contest = new Contest();
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();

      $contestInfo['briefDescription'] = '';
      if (array_key_exists('contestDescription', $contestInfo) && empty($contestInfo['contestDescription'])) {
        $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
      }

      if (array_key_exists('offset', $_GET) && !empty($_GET['offset'])) {
        $this->loadEntryByAjax();
        exit;
      }
      if (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
        $this->singleContestEntry($contestInfo);
      }
      if (!$this->isAdmin) {
        if (array_key_exists('entryStatus', $contestInfo) && empty($contestInfo['entryStatus'])) {
          Yii::log('', INFO, 'Entry status is false for this contest');
          $this->redirect(BASE_URL);
        }
      }

      $fallingWallContest = new FallingWallsContest();
      $fallingWallContest->slug = $_GET['slug'];
      $entries = $fallingWallContest->loadContestEntries();
      if (array_key_exists('contest_submission', $entries) && !empty($entries['contest_submission'])) {
        $contestSubmission = $entries['contest_submission'];
      }
      if (array_key_exists('entry_count', $entries) && isset($entries['entry_count'])) {
        $entryCount = $entries['entry_count']['count'];
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, $e->getMessage());
    }
    $this->render('contestEntries', array('entries' => $contestSubmission, 'contestInfo' => $contestInfo,
        'entryCount' => $entryCount));
  }
  
  /**
   * loadEntryByAjax
   * this function is used for load entries for ajax request
   */
  public function loadEntryByAjax() {
    //check for ajax request
    if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
      Yii::log('', ERROR, 'This functionality serves only ajax request');
      $this->redirect(BASE_URL);
    }

    $return = array('success' => false, 'msg' => '', 'data' => array());
    $entries = array();
    $contestEntries = array();
    $contest = new FallingWallsContest();
    $contest->offset = $_GET['offset'];
    $contest->slug = $_GET['slug'];
    $entries = $contest->loadContestEntries();
    if (!empty($entries)) {
      $return['success'] = true;
      if (array_key_exists('contest_submission', $entries) && !empty($entries['contest_submission'])) {
        foreach ($entries['contest_submission'] as $entry) {
          $contestEntry = array();
          if (array_key_exists('videoImagePath', $entry) && !empty($entry['videoImagePath'])) {
            $contestEntry['video_image_Url'] = BASE_URL . resizeImageByPath($entry['videoImagePath'], '600', '450');
          }
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $contestEntry['title'] = $entry['title'];
          }
          if (array_key_exists('id', $entry) && !empty($entry['id'])) {
            $contestEntry['id'] = $entry['id'];
          }
          if (array_key_exists('author', $entry) && array_key_exists('name', $entry['author']) && !empty($entry['author']['name'])) {
            $contestEntry['author_name'] = $entry['author']['name'];
          }
          $contestEntries[] = $contestEntry;
        }
        $return['data'] = $contestEntries;
      }
    } else {
      $return['msg'] = Yii::t('contest', 'There are no more entries');
    }
    echo json_encode($return);
    exit;
  }
  
  /**
   * singleContestEntry
   * this function is used for load a single entry
   * @param array $contestInfo
   */
  public function singleContestEntry($contestInfo) {
    $contest = new FallingWallsContest();
    $contest->entryId = $_GET['id'];
    $contest->slug = $_GET['slug'];
    $entry = array();
    $entries = array();
    $entry = $contest->loadSingleContestEntries();
    $entry['contest_title'] = $contestInfo['contestTitle'];
    $entry['contest_slug'] = $contestInfo['contestSlug'];
    $entry['winner_status'] = $contestInfo['winnerStatus'];
    $aggregatorMgr = new AggregatorManager();
    $aggregatorMgr->contestSlug = $_GET['slug'];
    $aggregatorMgr->range = $_GET['id'] . ':' . 1;
    $aggregatorMgr->returnField = 'title,id';
    $entryForPagination = $aggregatorMgr->getEntryForPagination();
    if (!empty($entryForPagination)) {
      if (array_key_exists('after', $entryForPagination) && !empty($entryForPagination['after'])) {
        $entry['next_entry_id'] = $entryForPagination['after'][0]['id'];
        $entry['next_entry_title'] = $entryForPagination['after'][0]['title'];
      }
      if (array_key_exists('before', $entryForPagination) && !empty($entryForPagination['before'])) {
        $entry['prev_entry_id'] = $entryForPagination['before'][0]['id'];
        $entry['prev_entry_title'] = $entryForPagination['before'][0]['title'];
      }
    }
    $this->render('entry', array('entry' => $entry));
    exit;
  }

  /**
   * actionEntriesForWinner
   * function is used for load entries that are not declare as winner   * 
   */
  public function actionEntriesForWinner() { 
    try {
      $response = array();
      $nonWinnerEntries = array();
      $nonWinner = array();
      $winnerWeight = '';
      $msg = '';
      $entryCount = 0;
      $contest = new Contest();
      $contest->contestSlug = $_GET['slug'];      
          
       //check for ajax request
      if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
        $this->loadNonWinnerEntryByAjax();  
      }
      $contestInfo = $contest->getContestDetail();
      $fallingWallContest = new FallingWallsContest();
      
      if (!empty($_POST)) { 
        $response = $fallingWallContest->saveWinner();
        if (array_key_exists('success', $response) && $response['success']) {
          $this->redirect(BASE_URL . 'admin/contest/winner/'. $_GET['slug'] );
        } else {
          Yii::log('', ERROR, 'Error in actionEntriesForWinner - failed to save winner');
          $msg = Yii::t('contest', 'Some technical problem occurred, contact administrator');
        }
      }
      $nonWinner = $fallingWallContest->loadNonWinnerEntries();
      if (array_key_exists('non_winner_entry', $nonWinner) && !empty($nonWinner['non_winner_entry'])) {
        $nonWinnerEntries = $nonWinner['non_winner_entry'];
      }
      if (array_key_exists('winner_weight', $nonWinner) && !empty($nonWinner['winner_weight'])) {
        $winnerWeight = implode(',', $nonWinner['winner_weight']);
      }
      if (array_key_exists('entry_count', $nonWinner) && !empty($nonWinner['entry_count'])) {
        $entryCount = $nonWinner['entry_count'];
      }
    } catch (Exception $e) {      
      $msg = $e->getMessage();
      Yii::log('Error in actionEntriesForWinner ', ERROR, $msg);
    }
    if ($_GET['slug'] == FALLING_WALLS_CONTEST_SLUG) {
      $this->render('addWinner', array('entries' => $nonWinnerEntries, 'contest' => $contestInfo, 
       'winner_weight' => $winnerWeight,'msg' => $msg, 'entry_count' => $entryCount));
    } else {
      $this->render('helloFiemmeAddWinner', array('entries' => $nonWinnerEntries, 'contest' => $contestInfo, 
       'winner_weight' => $winnerWeight,'msg' => $msg, 'entry_count' => $entryCount));
    }  
  }
  
  /**
   * actionEntriesForWinner
   * function is used for load entries that are not declare as winner   
   */
  public function actionWinner() {
    try {
      $winners = array();
      $winnerEntries = array(); 
      $winnerWeight = '';
      $msg = '';
      $contest = new Contest();
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
      
      $fallingWallContest = new FallingWallsContest();
      $winners = $fallingWallContest->loadWinnerEntries();
      if (array_key_exists('winner_entry', $winners) && !empty($winners['winner_entry'])) {
        $winnerEntries = $winners['winner_entry'];
      }
      if (array_key_exists('winner_weight', $winners) && !empty($winners['winner_weight'])) {
        $winnerWeight = implode(',', $winners['winner_weight']);
      }
    } catch (Exception $e) {      
      $msg = $e->getMessage();
      Yii::log('Error in actionEntriesForWinner ', ERROR, $msg);
    }
    if ($_GET['slug'] == FALLING_WALLS_CONTEST_SLUG) {
      $this->render('manageWinner', array('entries' => $winnerEntries, 'contest' => $contestInfo, 
        'winner_weight' => $winnerWeight, 'msg' => $msg));
    } else {
      $this->render('helloFiemmeManageWinner', array('entries' => $winnerEntries, 'contest' => $contestInfo, 
        'winner_weight' => $winnerWeight, 'msg' => $msg));
    }
  }
  
  /**
   * actionDeleteWinner
   * function is used for delete existing winner
   */
  public function actionDeleteWinner() {
    try {
      $updateTag = array();
      $tags = array();
      if (array_key_exists('id', $_GET) && empty($_GET['id'])) {
        throw new Exception('Entry id is empty');
      }
      $aggregator = new AggregatorManager();
      $aggregator->entryId = $_GET['id'];
      $contest = new FallingWallsContest();
      $tags = $contest->getEntryTags($_GET['id']);
      foreach ($tags as $tag) {
        if ($tag['scheme'] != PRIZE_TAG_SCHEME && $tag['scheme'] != WINNER_TAG_SCHEME) {
          $updateTag[] = $tag;
        }
      }
      if (empty($updateTag)) {
        $this->redirect(BASE_URL . 'admin/contest/winner/' . $_GET['slug']);
      }
      $aggregator->tags = $updateTag;
      $response = $aggregator->updateEntry();
      if (array_key_exists('success', $response) && $response['success']) {
        Yii::log('', ERROR, 'Error in actionDeleteWinner function of fallingWallsController - failed to delete winner');
      }     
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in actionDeleteWinner of fallingWallsController ' . $e->getMessage());
    }
    $this->redirect(BASE_URL . 'admin/contest/winner/' . $_GET['slug']);
  }
  
  /**
   * actionUpdateWinner
   * function is used for update existing winner
   */
  public function actionUpdateWinner() {
    try {
      $updateTag = array();
      $tags = array();
      if (array_key_exists('id', $_POST) && empty($_POST['id'])) {
        throw new Exception('Entry id is empty');
      }
      if (array_key_exists('prize', $_POST) && empty($_POST['prize'])) {
        throw new Exception('Prize title is empty');
      }
      if (array_key_exists('weight', $_POST) && empty($_POST['weight'])) {
        throw new Exception('Prize weight is empty');
      }
      $aggregator = new AggregatorManager();
      $aggregator->entryId = $_POST['id'];
      $contest = new FallingWallsContest();
      $tags = $contest->getEntryTags($_POST['id']);
      foreach ($tags as $tag) {
        if ($tag['scheme'] != PRIZE_TAG_SCHEME && $tag['scheme'] != WINNER_TAG_SCHEME) {
          $updateTag[] = $tag;
        }
      }
      if (empty($updateTag)) {
        $this->redirect(BASE_URL . 'admin/contest/winner/' . $_GET['slug']);
      }
      $aggregator->tags = $updateTag;
      $aggregator->prize = $_POST['prize'];
      $aggregator->prizeWeight = $_POST['weight'];
      $response = $aggregator->updateEntry();
      if (array_key_exists('success', $response) && $response['success']) {
        Yii::log('', ERROR, 'Error in actionUpdateWinner function of fallingWallsController - failed to update winner');
      }     
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in actionUpdateWinner of fallingWallsController ' . $e->getMessage());
    }
    $this->redirect(BASE_URL . 'admin/contest/winner/' . $_GET['slug']);
  }

  /**
   * loadNonWinnerEntryByAjax
   * function is used for loading non winner entry
   */
  public function loadNonWinnerEntryByAjax(){
    try {
      $return = array('success' => false, 'msg' => '', 'data' => array());
      $nonWinnerEntries = array();
      $winnerWeight =  '';
      if (array_key_exists('offset', $_GET) && !empty($_GET['offset'])) {
        $fallingWallContest = new FallingWallsContest();
        $fallingWallContest->offset = $_GET['offset'];
        $nonWinner = $fallingWallContest->loadNonWinnerEntries();
        if (array_key_exists('non_winner_entry', $nonWinner) && !empty($nonWinner['non_winner_entry'])) {
           foreach ( $nonWinner['non_winner_entry'] as $entry) {
            $entry['videoImagePath'] = resizeImageByPath($entry['videoImagePath'],'100','500');
            $nonWinnerEntries[] = $entry;
          }
        }
        if (array_key_exists('winner_weight', $nonWinner) && !empty($nonWinner['winner_weight'])) {
          $winnerWeight = implode(',', $nonWinner['winner_weight']);
        }
      }
    } catch (Exception $e) {
      $return['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');
      Yii::log('', ERROR, 'Error in loadNonWinnerEntryByAjax ' . $e->getMessage());
    }
    $return['success'] = true;
    $return['data'] = array('non_winner_entry' => $nonWinnerEntries, 'winner_weight' => $winnerWeight, 'contest_slug' => $_GET['slug']);
    echo json_encode($return);
    exit; 
  }
  
  /**
   * actionContestWinner
   * function is used for loading all winner
   */
  public function actionContestWinner() {
    try {
      $winner = array();
      $contest = new Contest();
      $contest->contestSlug = $_GET['slug'];
      $contestInfo = $contest->getContestDetail();
      $contestInfo['briefDescription'] = '';
      if (array_key_exists('contestDescription', $contestInfo) && empty($contestInfo['contestDescription'])) {
        $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
      }
      $fallingWalls = new FallingWallsContest();
      $winnerInfo = $fallingWalls->loadWinnerEntries();
      if (array_key_exists('winner_entry', $winnerInfo) && !empty($winnerInfo['winner_entry'])) {
        foreach ($winnerInfo['winner_entry'] as $info) {
          $winner[] = array('image_url' => $info['videoImagePath'], 'id' => $info['id'], 'title' => $info['title'],
              'author' => $info['author']['name'], 'prize_title' => $info['prize']);
        }
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in actionContestWinner ' . $e->getMessage());
    }
    if ($_GET['slug'] == FALLING_WALLS_CONTEST_SLUG) {
      $this->render('winner', array('contestInfo' => $contestInfo, 'entries' => $winner));
    } else {
      $this->render('helloFiemmeWinner', array('contestInfo' => $contestInfo, 'entries' => $winner));  
    }
  }
  
} 