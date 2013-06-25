<?php

/**
 * AggregatorManager
 * 
 * AggregatorManager class is used for interacting with AggregatorAPI class.
 * AggregatorManager class is used for manipulate(get, save, delete, update) entries. 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi <rahul@incaendo.com>
 * This file is part of <Contest and Grand>.
 * This file can not be copied and/or distributed without the express permission of
 *   <ahref Foundation.
 */

class AggregatorManager {
  
  public $authorName;
  public $authorSlug;
  public $entryTitle;
  public $entryDescription;
  public $imageUrl;
  public $contestSlug;
  public $isMinor;
  public $minorName;
  public $range = '';
  public $returnField = '';
  /**
   * getEntry
   * 
   * This function is used for get entries from aggregator
   * @param (int) $limit (default 1),
   * @param (int) $offset (default 1),
   * @param (string) $id (id of entries) 
   * @param (string) $status 
   * @param $guid
   * @param (string) $tags
   * @param (string) $tagName
   * @param (int) $count (1 for all entry with count and 2 for only count) 
   * @param (date) $dateFrom
   * @param (date) $dateTo
   * @param (int) $enclosures (default 1), 
   * @param (int) $range, 
   * @param $sort (sorting parameter like date_changed, date_published   default ascending order)
   * @param (array) $cordinate 
   * @param (string) $returnField (return these field as output)
   * @param (string) $returnContent 
   * @param (string) $returnTag
   * @return (array) $entry
   */
  
  public function getEntry($limit = 1, $offset = 0, $id, $status= 'active', $tags = '', $tagsName='', $guid = '', $count= 1, $dateFrom ='', 
          $dateTo='', $enclosures = 1, $range='', $cordinate=array(), $sort, $returnField, $returnContent, $returnTag) {
    $data = array();
    $entry = array();
    $inputData = array();
    $inputParam = '';
    $aggregatorAPI = new AggregatorAPI;
    
    if (!empty($limit) && is_numeric($limit)) {
      $inputData['limit'] = $limit;
    } else {
      $inputData['limit'] = DEFAULT_LIMIT;
    }
    
    if (!empty($offset) && is_numeric($offset)) {      
      $inputData['offset'] = $offset;
    } else {
      $inputData['offset'] = DEFAULT_OFFSET;
    }
    
    if (!empty($id)) {      
      $inputData['id'] = $id;
    }
    
    if (!empty($status) && is_string($status)) {      
      $inputData['status'] = $status;
    }
    
    if (!empty($guid) && filter_var($guid, FILTER_VALIDATE_URL)) {
      $inputData['guid'] = $guid;
    }
    
    if (!empty($tags)) {      
      $inputData['tags'] = $tags;
    }
    
    if (!empty($tagsName)) {      
      $inputData['tagsname'] = $tagsName;
    }
    
    if (!empty($count) && ($count == 1 || $count == 2)) {      
      $inputData['count'] = $count;
    }
    
    if (!empty($dateFrom) && !empty($dateTo) && $dateFrom < $dateTo && $dateTo < time()) {      
      $inputData['interval'] = $dateFrom .','. $dateTo;
    }
    
    if (isset($enclosures) && is_numeric($enclosures)) {      
      $inputData['enclosures'] = $enclosures;
    }
      
    if (!empty($range) && is_numeric($range)) {
      $inputData['range'] = $range;
    }
    
    if (!empty($sort)) {
      $inputData['sort'] = $sort;
    }
    
    if (array_key_exists('NE', $cordinate) && !empty($cordinate['NE']) && (array_key_exists('SW', $cordinate) && !empty($cordinate['SW']))) {
      $inputData['NE'] = $cordinate['NE'];
      $inputData['SW'] = $cordinate['SW'];
    } else if (array_key_exists('radius', $cordinate) && !empty($cordinate['radius']) && (array_key_exists('center', $cordinate) && !empty($cordinate['center']))) {
      $inputData['radius'] = $cordinate['radius'];
      $inputData['center'] = $cordinate['center'];
    }     
    
    if (!empty($returnField)) {
      $inputData['return_fields'] = $returnField;
    }
    
    if (!empty($returnContent)) {
      $inputData['returnContent'] = $returnContent;
    }
    
    if (!empty($returnTag)) {
      $inputData['returnTag'] = $returnTag;
    }    
    
    // encode array into a query string
    $inputParam =  http_build_query($inputData);
    
    try {
      if (empty($returnField) && empty($returnContent) && empty($returnTag)) {
        throw new Exception(Yii::t('contest', 'Return fields should not be empty'));
      }
      Yii::log('', INFO, Yii::t('contest', 'Input data in getEntry : ') . $inputParam);
      $data = $aggregatorAPI->curlGet( ENTRY, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest','Error in getEntry method :') . $e->getMessage());
    }
    
    if (array_key_exists('status', $data) && $data['status']=='true') {
      $entry = $data['data'];  
    }
    return $entry;
  }
  
  /**
   * saveEnrty
   * 
   * This function is used for saved entry
   */
  
  public function saveEntry() { 
    $inputParam = array();
    $aggregatorAPI = new AggregatorAPI();
    try { 
      if (empty($this->authorName)) {
        throw new Exception(Yii::t('contest','Please login to submit an entry'));
      }
      if (empty($this->authorSlug)) {
        throw new Exception(Yii::t('contest', 'Please login to submit an entry'));
      }
      if (empty($this->entryTitle) ||  !is_string($this->entryTitle)) {
        throw new Exception(Yii::t('contest', 'Entry title should not be empty'));
      }
      if (empty($this->entryDescription)) {
        throw new Exception(Yii::t('contest','Entry title should not be empty'));
      }
      
      if (empty($this->imageUrl) || !filter_var($this->imageUrl, FILTER_VALIDATE_URL)) {
        throw new Exception(Yii::t('contest', 'Please provide an image for enrty '));
      }

      // prepare data accordind to aggregator API input (array)
      $inputParam = array(
          'content' => array('description' => $this->entryDescription, 'is_minor' => $this->isMinor, 'minor_name' => $this->minorName),
          'title' => $this->entryTitle,
          'status' => 'active',
          'author' => array('name' => $this->authorName,
                            'slug' => $this->authorSlug),
          'tags' => array(0 => array('name' => $this->contestName, 'slug' => $this->contestSlug, 'scheme' => 'http://ahref.eu/contest/schema/')),
          'links' => array('enclosures' => array(0 => array('type' => 'image/jpg', 'uri' => $this->imageUrl))),
          'creation_date' => time()
      );
      $entryStatus = $aggregatorAPI->curlPOST(ENTRY, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in saveEntry method :') . $e->getMessage());
      $entryStatus['success'] = false;
      $entryStatus['msg'] = $e->getMessage();
    }
    return $entryStatus;
  }
  
  /**
   * isUserAlreadySubmitEntry
   * 
   * This function is used for check if user has already submitted an entry
   * @return (boolean)
   */
  
  public function isUserAlreadySubmitEntry($returnField) {
    $entrySubmitByUser= false;
    $entry = array();
    $aggregatorAPI = new AggregatorAPI();
    if (empty($this->authorSlug)) {
      throw new Exception(Yii::t('contest', 'Please login to submit an entry'));
    }
    $inputData['author'] = $this->authorSlug;
    $inputData['tags'] = $this->contestSlug;
    $inputData['offset'] = DEFAULT_OFFSET;
    if (!empty($returnField)) {
      $inputData['return_fields'] = $returnField;
    }
    $inputParam = http_build_query($inputData);
    try {
      $contestEntry = $aggregatorAPI->curlGet(ENTRY, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in isUserAlreadySubmitEntry method :') . $e->getMessage());
    }

    if (array_key_exists('status', $contestEntry) && !empty($contestEntry['data'])) {
      $entrySubmitByUser = true;
    }
    return $entrySubmitByUser;
  }
  
  /**
   * getEntryForPagination
   * 
   * This function is used for get entry for pagination
   * @return array $entry
   */
  public function getEntryForPagination() {
    $inputParam = '';
    $inputData = array();
    $entry = array();
    try {
      if (empty($this->range)) {
        throw new Exception(Yii::t('contest', 'Range can not be empty for pagination'));
      }
      if (empty($this->returnField)) {
        throw new Exception(Yii::t('contest', 'Return fields should not be empty'));
      }
      
      $inputData['range'] = $this->range;
      $inputData['return_fields'] = $this->returnField;
      $inputData['tags'] = $this->contestSlug.'[contest]';
      
      // encode array into a query string
      $inputParam =  http_build_query($inputData);
      Yii::log('', INFO, Yii::t('contest', 'Input data in getEntryForPagination : ') . $inputParam);
      $aggregatorAPI = new AggregatorAPI();
      $data = $aggregatorAPI->curlGet(ENTRY, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in getEntryForPagination method :') . $e->getMessage());
    }

    if (array_key_exists('status', $data) && $data['status'] == 'true') {
      $entry = $data['data'];
    }
    return $entry;
  }

}