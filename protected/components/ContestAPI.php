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
  public $contestId;
  public $startDate;
  public $endDate;
  public $creationDate;
  public $contestTitle;
  public $contestSlug;
  public $contestDescription;
  public $contestImage;
  
  /**
   * save
   * 
   * This function is used for inserting contest details    
   * @return (int) $response
   */
  public function save() {
    
    $response = '';
    $connection = Yii::app()->db;
    if (empty($this->startDate)) {
      throw new Exception(Yii::t('contest','Start date should not be empty'));
    }
    if (empty($this->endDate)) {
      throw new Exception(Yii::t('contest','End date should not be empty'));
    }
    if (empty($this->creationDate)) {
      throw new Exception(Yii::t('contest','Creation date should not be empty'));
    }
    if (empty($this->contestTitle)) {
      throw new Exception(Yii::t('contest','Contest title should not be empty'));
    }
    if (empty($this->contestDescription)) {
      throw new Exception(Yii::t('contest', 'Contest description should not be empty'));
    }
    if (empty($this->contestImage)) {
      throw new Exception(Yii::t('contest','Please provide contest image'));
    }
    
    $sql = "INSERT INTO contest (startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug) 
        VALUES( :startDate, :endDate, :creationDate, :imagePath, :contestTitle, :contestDescription, :contestSlug)";
    $query = $connection->createCommand($sql);
    $query->bindParam(":startDate", $this->startDate);
    $query->bindParam(":endDate", $this->endDate);
    $query->bindParam(":creationDate", $this->creationDate);
    $query->bindParam(":imagePath", $this->contestImage);
    $query->bindParam(":contestTitle", $this->contestTitle);
    $query->bindParam(":contestDescription", $this->contestDescription);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $response = $query->execute();    
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
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug FROM contest";
    $query = $connection->createCommand($sql);
    $contestDetails = $query->queryAll();
    return $contestDetails;
  }
  
  /**
   * getContestDetailByContestId
   * 
   * This function is used for get contest detail on the basis of contest id
   * @return (array) $contestDetails
   */
  
  public function getContestDetailByContestId() {
    $connection = Yii::app()->db;
    if (empty($this->contestId)) {
      return array();
    }    
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription FROM 
      contest where contestId = :contestId ";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestId", $this->contestId);
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
   * @return (array) $contestDetails
   */
  public function getContestDetailByContestSlug() {
    $connection = Yii::app()->db;
    if (empty($this->contestSlug)) {
      return array();
    }    
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription FROM 
      contest where contestSlug = :contestSlug ";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $contestDetails = $query->queryRow();
    if (!$contestDetails) {
      $contestDetails = array();
    }
    return $contestDetails;
  }
}
?>





