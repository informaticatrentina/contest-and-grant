<?php

/**
 * Jury
 * 
 * Jury class is used  for add, update, delete jury 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class Jury {

  public $id;
  public $contestId;
  public $emailId;
  public $designation;
  public $creationDate;
  public $contestSlug;
  public $userId;

  /**
   * get
   * function is used for get Jury
   */
  public function get() {
    $connection = Yii::app()->db;
    $where = array(1);
    $data = array();
    if (!empty($this->id)) {
      $where[] = 'id = :id';
      $data[':id'] = $this->id;
    }
    if (!empty($this->contestId)) {
      $where[] = 'contest_id = :contest_id';
      $data[':contest_id'] = $this->contestId;
    }
    if (!empty($this->emailId)) {
      $where[] = 'email_id = :email_id';
      $data[':email_id'] = $this->emailId;
    }
    if (!empty($this->designation)) {
      $where[] = 'designation = :designation';
      $data[':designation'] = $this->designation;
    }
    $sql = "SELECT * FROM jury WHERE " . implode(' AND ', $where);
    $command = $connection->createCommand($sql);
    foreach ($data as $key => &$val) {
      $command->bindParam($key, $val);
    }
    return $command->queryAll();
  }

  /**
   * save
   * function is used for save Jury
   */
  public function save() {
    $connection = Yii::app()->db;
    $sql = "INSERT INTO jury (contest_id, email_id, user_id, designation, creation_date) VALUES (:contestId,
      :emailId, :userId, :designation, :creationDate)";
    $command = $connection->createCommand($sql);
    $command->bindParam(":contestId", $this->contestId);
    $command->bindParam(":emailId", $this->emailId);
    $command->bindParam(":designation", $this->designation);
    $command->bindParam(":creationDate", $this->creationDate);
    $command->bindParam(":userId", $this->userId);
    return $command->execute();
  }

  /**
   * update
   * function is used for update Jury
   */
  public function update() {
    $connection = Yii::app()->db;
    $sql = "UPDATE jury SET contest_id = :contestId, email_id = :emailId, designation = :designation
      WHERE id = :id";
    $command = $connection->createCommand($sql);
    $command->bindParam(":id", $this->id);
    $command->bindParam(":contestId", $this->contestId);
    $command->bindParam(":emailId", $this->emailId);
    $command->bindParam(":designation", $this->designation);
    $command->bindParam(":creationDate", $this->creationDate);
    $isUpdate = $command->execute();
    // if  $isUpdate is numeric it will return 1 else return error
    if (is_numeric($isUpdate)) {
      $isUpdate = 1;
    }
    return $isUpdate;
  }

  /**
   * delete
   * function is used for delete Jury
   */
  public function delete() {
    if (!empty($this->id)) {
      $where[] = 'id = :id';
      $data[':id'] = $this->id;
    }
    if (!empty($this->contestId)) {
      $where[] = 'contest_id = :contestId';
      $data[':contestId'] = $this->contestId;
    }
    if (empty($where)) {
      throw new Exception(Yii::t('contest', 'Condition can not be empty for delete jury'));
    }
    $connection = Yii::app()->db;
    $command = $connection->createCommand();
    $sql = "DELETE FROM jury WHERE " . implode(' AND ', $where);
    $command = $connection->createCommand($sql);
    foreach ($data as $key => &$val) {
      $command->bindParam($key, $val);
    }
    return $command->execute();
  }  
  
  /**
   * fetchAll
   * function is used for getting all information of jury
   */
  public function fetchAll() {
    $connection = Yii::app()->db;
    $where = array(1);
    $data = array();
    if (!empty($this->id)) {
      $where[] = 'id = :id';
      $data[':id'] = $this->id;
    }
    if (!empty($this->contestId)) {
      $where[] = 'contest_id = :contest_id';
      $data[':contest_id'] = $this->contestId;
    }
    if (!empty($this->contestSlug)) {
      $where[] = 'contestSlug = :contestSlug';
      $data[':contestSlug'] = $this->contestSlug;
    }
    if (!empty($this->emailId)) {
      $where[] = 'email_id = :email_id';
      $data[':email_id'] = $this->emailId;
    }
    if (!empty($this->designation)) {
      $where[] = 'designation = :designation';
      $data[':designation'] = $this->designation;
    }
    $sql = "SELECT  co.contestId, co.contestTitle, co.contestSlug, ju.email_id, ju.designation, ju.user_id,
      co.jury_rating_from, co.jury_rating_till FROM jury ju INNER JOIN contest co on co.contestId = ju.contest_id WHERE "
      . implode(' AND ', $where);
    $command = $connection->createCommand($sql);
    foreach ($data as $key => &$val) {
      $command->bindParam($key, $val);
    }
    return $command->queryAll();
  }

  /**
   * getSortedContestSubmission
   * function is used for getting all submistion (sort on the basis of tag
   * @param array $inputData
   * @return array $entries
   */ 
  public function getSortedContestSubmission($inputData) {
    $entries = array();
    try {
      if (empty($inputData)) {
        return $entries;
      }
      $inputParam = http_build_query($inputData);
      $aggregatorAPI = new AggregatorAPI();
      $data = $aggregatorAPI->curlGet(ENTRY, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in getSortedContestSubmission method :') . $e->getMessage());
    }
    if (array_key_exists('status', $data) && $data['status'] == 'true') {
      $entries = $data['data'];
    }
    return $entries;
  }

}

?>
