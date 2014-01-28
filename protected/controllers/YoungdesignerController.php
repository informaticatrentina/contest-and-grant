<?php

/**
 * YoungdesignerController 
 * YoungdesignerController class inherit controller (base) class .
 * Actions are defined in YoungdesignerController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class YoungdesignerController extends Controller {

  /**
   * actionSubmitEntries
   * function is used for save contest submission 
   */
  public function actionSubmitEntries() {
    try {
      $contest = new Contest();
      $entrySubmissionResponse = array();
      $contestCloseDate = time();
      $entrySubmittedByUser = false;
      $hasClosedContest = false;
      if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
        $contest->contestSlug = $_GET['slug'];
      }
      $contestInfo = $contest->getContestDetail();
      if (array_key_exists('closingDate', $contestInfo) && !empty($contestInfo['closingDate'])) {
        $contestCloseDate = strtotime($contestInfo['closingDate']);
      }
      if ($contestCloseDate < time()) {
        $hasClosedContest = true;
      }
      $contestInfo['briefDescription'] = '';
      if (!empty($contestInfo)) {
        $contestInfo['briefDescription'] = substr($contestInfo['contestDescription'], 0, 512);
      }

      $postData = array();
      if (!empty(Yii::app()->session['user'])) {
        $aggregatorManager = new AggregatorManager();
        $aggregatorManager->authorSlug = Yii::app()->session['user']['id'];
        $aggregatorManager->contestSlug = $_GET['slug'];
        $entrySubmittedByUser = $aggregatorManager->isUserAlreadySubmitEntry('title');
        if (!empty($_POST)) {
          $postData = array_map('htmlPurifier', $_POST);
          if (!$entrySubmittedByUser) {
            $youngDesigner = new YoungDesigner();
            $youngDesigner->slug = $_GET['slug'];
            $youngDesigner->contestName = $contestInfo['contestTitle'];
            $entrySubmissionResponse = $youngDesigner->saveEntry();
          }
        }
      }
    } catch (Exception $e) {
      Yii::log('Error in actionSubmitEntries : young designer ', ERROR, $e->getMessage());
    }
    $this->render('contestEntrySubmission', array('contestInfo' => $contestInfo, 'message' => $entrySubmissionResponse,
        'isEntrySubmit' => $entrySubmittedByUser, 'postData' => $postData, 'hasClosedContest' => $hasClosedContest));
  }

}

?>
