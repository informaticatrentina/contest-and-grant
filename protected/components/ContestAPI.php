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
  public $squareImage;
  public $contestRule;
  public $entryStatus = false;  
  public $winnerStatus = false;  
  public $juryRatingStartDate;
  public $juryRatingEndDate;
  public $introStatus = false;  
  public $introTitle;  
  public $introDescription;  

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
    if (empty($this->squareImage)) {
      throw new Exception(Yii::t('contest','Please provide square image'));
    }
    if (empty($this->contestRule)) {
      throw new Exception(Yii::t('contest','Contest rule should not be empty'));
    }
    
    $sql = "INSERT INTO contest (startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug, 
      squareImage, rule, entryStatus) VALUES( :startDate, :endDate, :creationDate, :imagePath, :contestTitle, :contestDescription, 
      :contestSlug, :squareImage, :rule, :entryStatus)";
    $query = $connection->createCommand($sql);
    $query->bindParam(":startDate", $this->startDate);
    $query->bindParam(":endDate", $this->endDate);
    $query->bindParam(":creationDate", $this->creationDate);
    $query->bindParam(":imagePath", $this->contestImage);
    $query->bindParam(":contestTitle", $this->contestTitle);
    $query->bindParam(":contestDescription", $this->contestDescription);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $query->bindParam(":squareImage", $this->squareImage);
    $query->bindParam(":rule", $this->contestRule);
    $query->bindParam(":entryStatus", $this->entryStatus);
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
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug, 
      squareImage,rule, entryStatus, winnerStatus, intro_title, intro_description, intro_status FROM contest";
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
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription,
      jury_rating_from, jury_rating_till, intro_title, intro_description, intro_status FROM contest where contestId = :contestId ";
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
    $sql = "SELECT contestId, startDate, endDate, creationDate, imagePath, contestTitle, contestDescription, contestSlug, 
      squareImage, rule, entryStatus, winnerStatus, jury_rating_from, jury_rating_till, intro_title, intro_description, intro_status FROM contest where contestSlug = :contestSlug ";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $contestDetails = $query->queryRow();
    if (!$contestDetails) {
      $contestDetails = array();
    }
    return $contestDetails;
  }
  
  /**
   * updateContest
   * 
   * This function is used for update contest
   * @return (boolean)
   */
  public function updateContest() {
    $connection = Yii::app()->db;
    if (empty($this->contestSlug)) {
      return false;
    } 
    $sql = "UPDATE contest SET startDate = :startDate, endDate = :endDate, imagePath = :imagePath, squareImage = :squareImage,
      contestDescription = :contestDescription, rule = :contestRule, entryStatus= :entryStatus, jury_rating_from = :juryRatingStartDate,
      jury_rating_till = :juryRatingEndDate, intro_title = :intro_title, intro_description = :intro_description,  
      intro_status = :intro_status where contestSlug = :contestSlug";
    $query = $connection->createCommand($sql);
    $query->bindParam(":startDate", $this->startDate);
    $query->bindParam(":endDate", $this->endDate);
    $query->bindParam(":imagePath", $this->contestImage);
    $query->bindParam(":contestDescription", $this->contestDescription);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $query->bindParam(":squareImage", $this->squareImage);
    $query->bindParam(":contestRule", $this->contestRule);
    $query->bindParam(":entryStatus", $this->entryStatus);
    $query->bindParam(":juryRatingStartDate", $this->juryRatingStartDate);
    $query->bindParam(":juryRatingEndDate", $this->juryRatingEndDate);
    $query->bindParam(":intro_title", $this->introTitle);
    $query->bindParam(":intro_description", $this->introDescription);
    $query->bindParam(":intro_status", $this->introStatus);
    $isUpdate = $query->execute();
    // if  $isUpdate is numeric it will return 1 else return error
    if(is_numeric($isUpdate)) {
      $isUpdate = 1;
    }
    return $isUpdate;
  }
  
  /**
   * deleteContest
   * 
   * This function is used for delete contest
   * @return (boolean)
   */
  public function deleteContest() {
    $connection = Yii::app()->db;
    if (empty($this->contestSlug)) {
      return false;
    } 
    $sql = "DELETE FROM contest where contestSlug = :contestSlug";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestSlug", $this->contestSlug);
    return  $query->execute();
  }
  
  /**
   * updateContestWinnerStatus
   * 
   * This function is used for update status of winner
   * @return - no of row updated
   */
  public function updateContestWinnerStatus() {
    $connection = Yii::app()->db;
    $sql = "UPDATE contest SET winnerStatus = :winnerStatus where contestId = :contestId";
    $query = $connection->createCommand($sql);
    $query->bindParam(":winnerStatus", $this->winnerStatus);
    $query->bindParam(":contestId", $this->contestId);
    return  $query->execute();
  }
}
?>