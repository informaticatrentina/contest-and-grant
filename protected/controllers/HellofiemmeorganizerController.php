<?php

/**
 * HellofiemmeorganizeController
 * 
 * HellofiemmeorganizeController class is used  for manage flow of Hellofiemme organize contest
 * HellofiemmeorganizeController class extends base class Controller.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class HellofiemmeorganizerController extends Controller {

  public function actionIndex() {
    $this->redirect(BASE_URL);
  }

  /**
   * actionSubmitEntries
   * This function is used for Hello fiemme organize submit entries
   */
  public function actionSubmitEntries() {
    $contest = new Contest();
    $contestInfo = array();
    $entrySubmissionResponse = array();
    $contestCloseDate = time();
    $hasClosedContest = false;
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
    $contestInfo['briefDescription'] = '';
    if (!empty($contestInfo)) {
      $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
    }
    $entrySubmittedByUser = false;
    $postData = array();
    if (!empty(Yii::app()->session['user'])) {
      $aggregatorManager = new AggregatorManager();
      $aggregatorManager->authorSlug = Yii::app()->session['user']['id'];
      $aggregatorManager->contestSlug = $_GET['slug'];
      $entrySubmittedByUser = $aggregatorManager->isUserAlreadySubmitEntry('title');
      if (!empty($_POST)) {
        $postData = array_map('htmlPurifier', $_POST);
        if ( !$entrySubmittedByUser ) {
          $hellofiemmeContest = new HellofiemmeOrganizer();
          $hellofiemmeContest->slug = $_GET['slug'];
          $hellofiemmeContest->contestName = $contestInfo['contestTitle'];          
          $entrySubmissionResponse = $hellofiemmeContest->submitEntry();
        }
      }
    }
    $this->render('contestEntrySubmission', array('contestInfo' => $contestInfo, 'message' => $entrySubmissionResponse, 
        'isEntrySubmit' => $entrySubmittedByUser, 'postData' => $postData, 'hasClosedContest' => $hasClosedContest));
  }
}