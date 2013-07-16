<?php

/**
 * Category
 * 
 * Category class is used  for add category , update category
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class Category  {
  
  public $categoryId;
  public $contestSlug;
  public $categoryName;
  public $creationDate;
 
  /**
   * get
   * 
   * This function is used for get category for a contest
   * @return array $category
   */
  public function get() {
    $connection = Yii::app()->db;
    if (empty($this->contestSlug)) {
      return array();
    }
    $sql = "SELECT category_id, category_name FROM category where contest_slug = :contestSlug";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contestSlug", $this->contestSlug);
    $category = $query->queryAll();
    if (!$category) {
      $category = array();
    }
    return $category;
  }
  
  /**
   * save
   * 
   * This function is used for save category for a contest
   * @return array $category
   */
  public function save() {
    $connection = Yii::app()->db;
    if (empty($this->categoryName)) {
      throw new Exception(Yii::t('contest', 'Category name can not be empty'));
    }
    
    $sql = "INSERT INTO category (contest_slug, category_name, creation_date)
       VALUES( :contest_slug, :category_name, :creation_date)";
    $query = $connection->createCommand($sql);
    $query->bindParam(":contest_slug", $this->contestSlug);
    $query->bindParam(":category_name", $this->categoryName);
    $query->bindParam(":creation_date", $this->creationDate);
    $response = $query->execute();
    return $response;
  }
  
  /**
   * getCategory
   * 
   * This function is used for get category by id for a contest
   * @return array $category
   */
  public function getCategory() {
    $connection = Yii::app()->db;
    if (empty($this->categoryId)) {
      return array();
    }
    
    $sql = "SELECT category_name FROM category where category_id = :categoryId";
    $query = $connection->createCommand($sql);
    $query->bindParam(":categoryId", $this->categoryId);
    $category = $query->queryRow();
    return $category;
  }
}
  