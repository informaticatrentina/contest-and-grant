<?php

/**
 * Contest
 * 
 * Contest class is used  for get contest entry,  create contest.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class Contest  {
  
  public $image;
  public $contestId;
  
  /**
   * getContestSubmission
   * 
   * This functiction calls getEntry method (in AggregatorManager) to get entries for a contest .
   * @param  $contestId 
   * @return (array)
   */
  public function getContestSubmission() {
    
    $aggregatorManager = new AggregatorManager();    
    return  $aggregatorManager->getEntry( 5, 1, '', 'active', $this->contestId.'[contest]', '', '', '', '', '', '', '',array(), '', 'guid, status','','');
  }
  
  /**
   * createContest
   * 
   * function is used for create contest
   * @param (string) $imagePath
   * @return (array) $response
   */
  
  public function createContest($imagePath) {
    $contestAPI = new ContestAPI();
    $response = array();
    $contestDetails = array();
    $contestDetails = $_POST;
    try {
      if (!empty($contestDetails)) {
        if(array_key_exists('contestTitle', $contestDetails) && empty($contestDetails['contestTitle'])) {
          throw new Exception('Contest title should not be empty');
        }
        if(array_key_exists('startDate', $contestDetails) && empty($contestDetails['startDate'])) {
          throw new Exception('start date should not be empty');
        } else {
          $startDateArr = explode('/', $contestDetails['startDate']);
          $startTime = mktime(0, 0, 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contestDetails['startDate'] = date('Y-m-d H:i:s', $startTime);
        }
        if(array_key_exists('endDate', $contestDetails) && empty($contestDetails['endDate'])) {
          throw new Exception('End date should not be empty');
        } else {
          $endDateArr = explode('/', $contestDetails['endDate']);
          $endTime = mktime(0, 0, 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contestDetails['endDate'] = date('Y-m-d H:i:s', $endTime);
        }
        if(array_key_exists('contestDescription', $contestDetails) && empty($contestDetails['contestTitle'])) {
          throw new Exception('Contest description should not be empty');
        }
        if (empty($imagePath)) {
          throw new Exception('Please choose an image for upload');
        }
        
        $contestDetails['imagePath'] = $imagePath;
        $contestDetails['creationDate'] = date('Y-m-d H:i:s'); 
        $response['success'] = $contestAPI->save($contestDetails);
        $response['msg'] = "You have created a contest Succesfully";
      }
    } catch (Exception $e) {
       Yii::log('', ERROR, 'Error in createContest :' . $e->getMessage());
       $response['success'] = '';
       $response['msg'] = $e->getMessage();
    }
    return $response;
  }
  
  /**
   * getContestDetail
   * 
   * This function is used for get contest and manipulate it
   */
  
  public function getContestDetail() {
    $contestAPI = new ContestAPI();
    $contestDetail = array();
   
    if (!empty($this->contestId)) {
      $contestDetail = $contestAPI->getContestDetailByContestId($this->contestId);
      $contestDetail['startDate'] = date('Y-m-d', strtotime($contestDetail['startDate']));
      $contestDetail['endDate'] = date('Y-m-d', strtotime($contestDetail['endDate']));
    } else {
      $contestDetail = $contestAPI->getContestDetail();
    }
    return $contestDetail;
  }
}
