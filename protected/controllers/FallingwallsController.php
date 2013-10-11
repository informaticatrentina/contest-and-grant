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
      $contest->contestSlug = FALLING_WALLS_CONTEST_SLUG;
      $contestInfo = $contest->getContestDetail();

      $contestInfo['briefDescription'] = '';
      if (array_key_exists('contestDescription', $contestInfo) && empty($contestInfo['contestDescription'])) {
        $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
      }

      if (array_key_exists('offset', $_GET) && !empty($_GET['offset'])) {
        $this->loadEntryByAjax();
        exit;
      }

      if (!$this->isAdmin) {
        if (array_key_exists('entryStatus', $contestInfo) && empty($contestInfo['entryStatus'])) {
          Yii::log('', INFO, 'Entry status is false for this contest');
          $this->redirect(BASE_URL);
        }
      }

      $fallingWallContest = new FallingWallsContest();
      $fallingWallContest->slug = FALLING_WALLS_CONTEST_SLUG;
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
            $contestEntry['video_image_dimension'] = getImageDimension($contestEntry['video_image_Url']);
            $contestEntry['play_button_url'] = BASE_URL . 'images/button_play.png';
          }
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $contestEntry['title'] = $entry['title'];
          }
          if (array_key_exists('video_id', $entry) && !empty($entry['video_id'])) {
            $contestEntry['video_id'] = $entry['video_id'];
          }
          if (array_key_exists('url_info', $entry) && array_key_exists('type', $entry['url_info']) && !empty($entry['url_info']['type'])) {
            $contestEntry['video_domain'] = $entry['url_info']['type'];
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

}