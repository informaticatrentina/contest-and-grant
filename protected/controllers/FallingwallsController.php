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
      
      if (array_key_exists('entryStatus', $contestInfo) && empty($contestInfo['entryStatus'])) {
        Yii::log('', INFO, 'Entry status is false for this contest');
        $this->redirect(BASE_URL);
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
}