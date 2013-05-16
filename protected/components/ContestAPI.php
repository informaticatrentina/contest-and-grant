<?php

/**
 *  ContestAPI
 * 
 * ContestAPI class is used for get, save,update, delete contest detail
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */

class ContestAPI {

  /**
   * save
   * 
   * This function is used for inserting contest details    
   * @param (array) $contestDetails
   * @return (int) $response
   */
  public function save($contestDetails) {
    $response = '';
    if (!empty($contestDetails)) {
      $connection = Yii::app()->db;
      $sql = "INSERT INTO contestDetail (startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug) 
      VALUES( :startDate, :endDate, :creationDate, :imagePath, :contestTitle, :contestDescription, :contestSlug)";
      $query = $connection->createCommand($sql);
      $query->bindParam(":startDate", $contestDetails['startDate']);
      $query->bindParam(":endDate", $contestDetails['endDate']);
      $query->bindParam(":creationDate", $contestDetails['creationDate']);
      $query->bindParam(":imagePath", $contestDetails['imagePath']);
      $query->bindParam(":contestTitle", $contestDetails['contestTitle']);
      $query->bindParam(":contestDescription", $contestDetails['contestDescription']);
      $query->bindParam(":contestSlug", $contestDetails['contestSlug']);
      $response = $query->execute();
    }
    return $response;
  }

  /**
   * getContestDetail
   * 
   * This function is used for getting contest details.  
   * @return (array) $contestDetails
   */
  public function getContestDetail() { 
    $connection = Yii::app()->db;
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug FROM contestDetail";
    $query = $connection->createCommand($sql);
    $contestDetails = $query->queryAll();
    return $contestDetails;
  }
  
  /**
   * getContestDetailByContestId
   * 
   * This function is used for get contest detail on the basis of contest id
   * @param (int) $contestId
   * @return (array) $contestDetails
   */
  
  public function getContestDetailByContestId($contestId) {
    $connection = Yii::app()->db;
    if (empty($contestId)) {
      return array();
    }    
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription FROM 
      contestDetail where contestId = :contestId ";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestId", $contestId);
    $contestDetails = $query->queryRow();
    if (!$contestDetails) {
      $contestDetails = array();
    }
    return $contestDetails;
  }
  
  /**
   * getContestDetailByContestSlug
   * 
   * This function is used for get contest detail on the basis of contest slug
   * @param (string) $contestSlug
   * @return (array) $contestDetails
   */
  public function getContestDetailByContestSlug($contestSlug) {
    $connection = Yii::app()->db;
    if (empty($contestSlug)) {
      return array();
    }    
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription FROM 
      contestDetail where contestSlug = :contestSlug ";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestSlug", $contestSlug);
    $contestDetails = $query->queryRow();
    if (!$contestDetails) {
      $contestDetails = array();
    }
    return $contestDetails;
  }
}
?>





