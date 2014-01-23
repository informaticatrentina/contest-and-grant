<?php

/**
 * YoungDesigner 
 * YoungDesigner class is used for submit, get submission of yuoung designer contest
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class YoungDesigner  {
  
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
      if (array_key_exists('title', $postData) && empty($postData['title'])) {
        throw new Exception(Yii::t('contest', 'Entry title should not be empty'));
      }    
      if (!array_key_exists('confirmation_checkbox', $postData)) {
        throw new Exception(Yii::t('contest', 'Please checked checkbox'));
      }
      if (array_key_exists('submitter_info', $postData) && empty($postData['submitter_info'])) {
        throw new Exception(Yii::t('contest', 'Please upload your cv'));
      } else {
        $fileName['cv'] = $this->uploadPdfFile($_FILES['cv'], 'cv');
      }
      if (array_key_exists('pdf_file', $_FILES) && !empty($_FILES['pdf_file']['name'])) {
        $fileName[1] = $this->uploadPdfFile($_FILES['pdf_file'], 'pdf_file');
        $uploadedPdfFile = true;
      }      
      if (array_key_exists('additional_pdf_file_1', $_FILES) && !empty($_FILES['additional_pdf_file_1']['name'])) {
        $fileName[2] = $this->uploadPdfFile($_FILES['additional_pdf_file_1'], 'additional_pdf_file_1');
      }
      if (array_key_exists('additional_pdf_file_2', $_FILES) && !empty($_FILES['additional_pdf_file_2']['name'])) {
        $fileName[3] = $this->uploadPdfFile($_FILES['additional_pdf_file_2'], 'additional_pdf_file_2');
      }
      if (array_key_exists('additional_pdf_file_3', $_FILES) && !empty($_FILES['additional_pdf_file_3']['name'])) {
        $fileName[4] = $this->uploadPdfFile($_FILES['additional_pdf_file_3'], 'additional_pdf_file_3');
      }
      if (array_key_exists('video_link', $postData) && !empty($postData['video_link'])) {
        if (!preg_match("/^(https?:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/", $postData['video_link'])) {
          throw new Exception(Yii::t('contest', 'Please enter valid url'));
        } else {
          $pathinfo = parse_url($postData['video_link']);
          if (!array_key_exists('scheme', $pathinfo)) {
            $postData['video_link'] = 'http://' . $postData['video_link'];
            $pathinfo = parse_url($postData['video_link']);
          }
          if (array_key_exists('host', $pathinfo) && strpos($pathinfo['host'], 'youtube') === false ) {
            throw new Exception(Yii::t('contest', 'Please enter youtube vedio url'));
          }
          if (!array_key_exists('path', $pathinfo) && !array_key_exists('query', $pathinfo)) {
            throw new Exception(Yii::t('contest', 'Please enter proper video url'));
          }
        }
      } else if (!$uploadedPdfFile) {
        throw new Exception(Yii::t('contest', 'Please either upload pdf file or add youtube video url'));
      }
     
      $aggregatorManager = new AggregatorManager();     
      // prepare data according to aggregator API input (array)
      $inputParam = array(
	  'content' => array('description' => ''),	
          'title' => $postData['title'],
          'status' => 'active',
          'author' => array('name' => Yii::app()->session['user']['firstname'] . ' ' . Yii::app()->session['user']['lastname'],
                            'slug' => Yii::app()->session['user']['id']),
          'tags' => array(array('name' => $this->contestName, 'slug' => $this->slug, 'scheme' => 'http://ahref.eu/contest/schema/')),
          'creation_date' => time(),
          'source' => SOURCE
      ); 
      foreach ($fileName as $fileOrder => $name) {
        $inputParam['links']['enclosures'][] = array('type' => 'pdf/'.$fileOrder, 'uri' => BASE_URL . UPLOAD_DIRECTORY. 'contestEntry/'. $name);
      }
      if (array_key_exists('video_link', $postData) && !empty($postData['video_link'])) {
        $inputParam['links']['enclosures'][] =  array('type' => 'video', 'uri' => $postData['video_link']);
      }
      $response = $aggregatorManager->saveContestEntry($inputParam);
      if (array_key_exists('success', $response) && $response['success']) {
        $response['msg'] = Yii::t('contest', 'You have successfully submitted an entry');
      } else {
        Yii::log('Error in saveEntry of young designer ', ERROR, print_r($response, true) .'where user id '. 
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
