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
  public $contestSlug;
  
  /**
   * getContestSubmission
   * 
   * This functiction calls getEntry method (in AggregatorManager) to get entries for a contest.
   * @return (array)
   */
  public function getContestSubmission() {
    $contestEntries = array();
    $aggregatorManager = new AggregatorManager();    
    $contestEntries = $aggregatorManager->getEntry( 5, 1, '', 'active', $this->contestSlug.'[contest]', '', '', '', '', '', '', '',array(), '', 'links,guid,status,author,title','','');
    $i = 0;
    foreach ($contestEntries as $entry) {
      if (!empty($entry['links']['enclosures'])) { 
        $contestEntries[$i]['image'] =  $entry['links']['enclosures'][0]['uri'] ;
        $i++;
      }
    }
    return $contestEntries;
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
          throw new Exception('Start date should not be empty');
        } else if (!validateDate($contestDetails['startDate'])){
          throw new Exception('Please enter valid start date');
        } else {
          $startDateArr = explode('/', $contestDetails['startDate']);
          $startTime = mktime(0, 0, 0, $startDateArr[0], $startDateArr[1], $startDateArr[2]);
          $contestAPI->startDate = date('Y-m-d H:i:s', $startTime);
        }
        if(array_key_exists('endDate', $contestDetails) && empty($contestDetails['endDate'])) {
          throw new Exception('End date should not be empty');
        }  else if (!validateDate($contestDetails['endDate'])){
          throw new Exception('Please enter valid end date');
        } else {
          $endDateArr = explode('/', $contestDetails['endDate']);
          $endTime = mktime(0, 0, 0, $endDateArr[0], $endDateArr[1], $endDateArr[2]);
          $contestAPI->endDate = date('Y-m-d H:i:s', $endTime);
        }
        if(array_key_exists('contestDescription', $contestDetails) && empty($contestDetails['contestDescription'])) {
          throw new Exception('Contest description should not be empty');
        }
        if (empty($imagePath)) {
          throw new Exception('Please choose an image for upload');
        }
        $contestAPI->contestTitle = $contestDetails['contestTitle'];
        $contestAPI->contestDescription = $contestDetails['contestDescription'];        
        $contestAPI->creationDate = date('Y-m-d H:i:s'); 
        $contestAPI->contestSlug =  strtolower(preg_replace("/[^a-z0-9]+/i", "_", $contestDetails['contestTitle']));        
        $contestAPI->contestImage = $imagePath;
       
        
        //check for contest exist
        $exist = $contestAPI->getContestDetailByContestSlug();
        if ($exist) {
          throw new Exception('This contest title is already exist');
        }
        $response['success'] = $contestAPI->save();
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
   
    if (!empty($this->contestSlug)) {
      $contestDetail = $contestAPI->getContestDetailByContestSlug();
      if(array_key_exists('startDate', $contestDetail) && !empty($contestDetail['startDate'])) {
        $contestDetail['startDate'] = date('Y-m-d', strtotime($contestDetail['startDate']));
      }
      if(array_key_exists('endDate', $contestDetail) && !empty($contestDetail['endDate'])) {
        $contestDetail['endDate'] = date('Y-m-d', strtotime($contestDetail['endDate']));
      }
    } else {
      $contestDetail = $contestAPI->getContestDetail();
    }
    return $contestDetail;
  }
  
  /**
   * submitContestEntry
   * 
   * This function is used for submit entry
   * @param (string) $imageUrl
   * @param (string) $contestSlug
   * @return (array) $response
   */
  public function submitContestEntry($imageUrl, $contestSlug) {
    try {
      if (!empty($_POST)) {
        if (array_key_exists('entryTitle', $_POST) && (!empty($_POST['entryTitle']))) {
          $entryTitle = $_POST['entryTitle'];
        }
        $authorName = $_SESSION['name'];
        $authorSlug = strtolower(preg_replace("/[^a-z0-9]+/i", "_", $authorName));
        $aggregatorManager = new AggregatorManager();
        $response['success'] = $aggregatorManager->getEntry($authorName, $authorSlug, $entryTitle, $imageUrl, $contestSlug);
        $response['msg'] = "You have succesfully submit an entry ";
      }
    } catch (Exception $e) {
       Yii::log('', ERROR, 'Error in submitContestEntry :' . $e->getMessage());
       $response['success'] = '';
       $response['msg'] = $e->getMessage();
    }
    return $response;
  }
}
