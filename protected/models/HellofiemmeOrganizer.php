<?php

/**
 * HellofiemmeOrganizer
 * 
 * HellofiemmeOrganizer class is used  for get contest entry,  create contest, submit contest entry.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class HellofiemmeOrganizer  {
  
  public $contestName;
  public $slug;
  public $entryId = '';
  public $sort = '';
  public $offset = 0;
  public $count = 1;
  public $tags ;



  /**
   * fallingWallsEntrySubmission
   * save entry for falling wall entry submission
   */
  public function submitEntry() {
    $postData = array_map('trim', $_POST);
    $postData = array_map('htmlPurifier', $postData); 
    try {
      $response = array('success' => false, 'msg' => '');
      if (array_key_exists('entryTitle', $postData) && empty($postData['entryTitle'])) {
        throw new Exception(Yii::t('contest', 'Entry title should not be empty'));
      }
      
      if (array_key_exists('entryDescription', $postData) && empty($postData['entryDescription'])) {
        throw new Exception(Yii::t('contest', 'Entry description should not be empty'));
      }
      if (empty($_FILES)) {
        throw new Exception(Yii::t('contest', 'Please choose a pdf file'));
      }
      $fileName = $this->uploadPdfFile();
      
      if (array_key_exists('videoUrl', $postData) && !empty($postData['videoUrl'])) {
        if (!preg_match("/^(https?:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/", trim($postData['videoUrl']))) {
          throw new Exception(Yii::t('contest', 'Please enter valid url'));
        } else {
          $pathinfo = parse_url($postData['videoUrl']);
          if (!array_key_exists('scheme', $pathinfo)) {
            $postData['videoUrl'] = 'http://' . $postData['videoUrl'];
            $pathinfo = parse_url($postData['videoUrl']);
          }
          if (array_key_exists('host', $pathinfo) && strpos($pathinfo['host'], 'youtube') === false && strpos($pathinfo['host'], 'vimeo') === false) {
            throw new Exception(Yii::t('contest', 'Please enter either youtube or vemio vedio url'));
          }
          if (!array_key_exists('path', $pathinfo) && !array_key_exists('query', $pathinfo)) {
            throw new Exception(Yii::t('contest', 'Please enter proper video url'));
          }
        }
      }
      
      if (!array_key_exists('checkBox', $postData)) {
        throw new Exception(Yii::t('contest', 'Please checked one check box'));
      }
      
      $aggregatorManager = new AggregatorManager();     
      // prepare data according to aggregator API input (array)
      $inputParam = array(
          'content' => array('description' => $postData['entryDescription']),
          'title' => $postData['entryTitle'],
          'status' => 'active',
          'author' => array('name' => Yii::app()->session['user']['firstname'] . ' ' . Yii::app()->session['user']['lastname'],
                            'slug' => Yii::app()->session['user']['id']),
          'tags' => array(array('name' => $this->contestName, 'slug' => $this->slug, 'scheme' => 'http://ahref.eu/contest/schema/')),
          'creation_date' => time(),
          'source' => SOURCE
      );
      if ($fileName) {
        $inputParam['links'] = array('enclosures' => array(array('type' => 'pdf', 
                                      'uri' => BASE_URL . UPLOAD_DIRECTORY. PDF_FILE_DIR . $fileName)));
        if (!empty($postData['videoUrl'])) {
          array_push($inputParam['links']['enclosures'], array('type' => 'video', 'uri' => $postData['videoUrl']));
        }    
      }
       
      $response = $aggregatorManager->saveContestEntry($inputParam);
      if (array_key_exists('success', $response) && $response['success']) {
        $response['msg'] = Yii::t('contest', 'You have succesfully submit an entry');
      } else {
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
   */
  public function uploadPdfFile() {
    $fileName = '';
    if($_FILES['pdf_file']['error'] != 0) { 
      throw new Exception(setFileUploadError($_FILES['pdf_file']['error']));
    } else  if (empty($_FILES['pdf_file']['name'])) {
      throw new Exception(Yii::t('contest', 'Please choose a pdf file'));
    } else {
      $extention = explode('/', $_FILES['pdf_file']['type']);
      $fileExtension = end($extention);
      if ($fileExtension != ALLOWED_FILE_TYPE_FOR_HELLOFIEMME_CONTEST) {
        throw new Exception(Yii::t('contest', 'Please upload only pdf file'));
      } else {
        $directory = UPLOAD_DIRECTORY. PDF_FILE_DIR;
        $fileName = uploadFile($directory, 'pdf_file');
        if (empty($fileName)) {
          throw new Exception(Yii::t('contest', 'Some error occured in file uploading'));
        }       
      }
    }
    return $fileName;
  }  
}
