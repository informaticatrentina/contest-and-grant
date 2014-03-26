<?php

/**
 * Netizenlab 
 * Netizenlab class is used for submit, get submission of netizenlab contest
 * 
 * Copyright (c) 2014 <ahref Foundation -- All rights reserved.
 * Author: Pradeep<pradeep@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class Netizenlab  {
  
  public $contestName;
  public $slug;
  
  /**
   * saveEntry
   * function is used for save entry 
   */
  public function saveEntry() {    
    try {
      $fileName = array();
      $uploadedPdfFile = false;
      $postData = array_map('trim', $_POST);
      $response = array('success' => false, 'msg' => '');
      $fileSizeInMb = MAX_UPLOAD_FILE_SIZE_IN_NETIZENLAB_CONTEST/(1024*1024)  . 'MB';
      
      if (!array_key_exists('confirmation_checkbox', $postData)) {
        throw new Exception(Yii::t('contest', 'Please checked checkbox'));
      }
      if (array_key_exists('motivational_letter', $_FILES) && !empty($_FILES['motivational_letter']['name'])) {
        if ($_FILES['motivational_letter']['size'] > MAX_UPLOAD_FILE_SIZE_IN_NETIZENLAB_CONTEST) {
          throw new Exception($_FILES['motivational_letter']['name'] . ' '. 
          Yii::t('contest', 'file is greater than ') . $fileSizeInMb );  
        }
        $fileName['motivational_letter'] = $this->uploadPdfFile($_FILES['motivational_letter'], 'motivational_letter');
      } else {
        throw new Exception(Yii::t('contest', 'Please upload your motivational letter'));
      }  
            if (array_key_exists('curriculum_vitae', $_FILES) && !empty($_FILES['curriculum_vitae']['name'])) {       
        if ($_FILES['curriculum_vitae']['size'] > MAX_UPLOAD_FILE_SIZE_IN_NETIZENLAB_CONTEST) {
          throw new Exception($_FILES['curriculum_vitae']['name'] . ' '. 
          Yii::t('contest', 'file is greater than ') . $fileSizeInMb );  
        }
        $fileName['curriculum_vitae'] = $this->uploadPdfFile($_FILES['curriculum_vitae'], 'curriculum_vitae');        
      } else {
        throw new Exception(Yii::t('contest', 'Please upload your curriculum vitae'));
      }    
      $aggregatorManager = new AggregatorManager();     
      // prepare data according to aggregator API input (array)
      $inputParam = array(
	  'content' => array('description' => ''),
          'status' => 'active',
          'author' => array('name' => Yii::app()->session['user']['firstname'] . ' ' . Yii::app()->session['user']['lastname'],
                            'slug' => Yii::app()->session['user']['id']),
          'tags' => array(array('name' => $this->contestName, 'slug' => $this->slug, 'scheme' => 'http://ahref.eu/contest/schema/')),
          'creation_date' => time(),
          'source' => SOURCE
      ); 
      foreach ($fileName as $filePurpose => $name) {
        $inputParam['links']['enclosures'][] = array('type' => 'pdf/'.$filePurpose, 'uri' => BASE_URL . UPLOAD_DIRECTORY. 'contestEntry/'. $name);
      }
      $response = $aggregatorManager->saveContestEntry($inputParam);
      if (array_key_exists('success', $response) && $response['success']) {
        $response['msg'] = Yii::t('contest', 'You have successfully submitted an entry');
      } else {
        Yii::log('Error in saveEntry of Netizenlab contest ', ERROR, print_r($response, true) .'where user id '. 
                Yii::app()->session['user']['id']);
        $response['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');        
      }
    } catch (Exception $e) {
      $response['msg'] = $e->getMessage();
    }
    return $response;
  }

  
  /**
   * uploadPdfFile
   * function is used for validate and upload file
   * @param array $files
   * @param $index  - index of $_FILES
   * return $filename
   */
  public function uploadPdfFile($files, $index) {
    $fileName = '';
    if($files['error'] != 0) { 
      throw new Exception(setFileUploadError($files['error']));
    } else {
      $extention = explode('/', $files['type']);
      $fileExtension = end($extention);
      if ($fileExtension != 'pdf') {
        throw new Exception(Yii::t('contest', 'Please upload only pdf file'));
      } else {
        $fileName = uploadFile(UPLOAD_DIRECTORY.'contestEntry/', $index);
        if (empty($fileName)) {
          throw new Exception(Yii::t('contest', 'Some error occured in file uploading'));
        }       
      }
    }
    return $fileName;
  }  
}
