<?php

/**
 * UserIdentityAPI
 * 
 * UserIdentityAPI class is called for create, update, search userIdentityManager class. 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grand>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
         
class UserIdentityAPI {

  private $baseUrl;
  private $response;
  private $url;
  
  function __construct() {
    $this->baseUrl = IDENTITY_MANAGER_API_URL;
  }
    
  /**
   * getUserDetail
   * 
   * This function is used for curl request on server using Get method
   * @param (array) $params
   * @param (string) $function
   * @param $id - for getting user detail on the basis of email id  
   * @return (array) $userDetail
   */
  function getUserDetail($function, $params = array(), $id = false, $email = false) {
    $userDetail = array();
    try {
      $userParam = array();
      if (array_key_exists('email', $params) && !empty($params['email'])) {
        $userParam['email'] = $params['email'];
      }
      if (array_key_exists('password', $params) && !empty($params['password'])) {
        $userParam['password'] = $params['password'];
      }
      if (array_key_exists('id', $params) && !empty($params['id'])) {
        if (is_array($params['id'])) {
          foreach ($params['id'] as $userId) {
            $userParam['$or'][] = array('_id'=> $userId);
          }
        } else {
          $userParam['_id'] = $params['id'];
        }
      }
      $projection = '';
      if ($id) {
        $projection = '&projection={"_id":1}';
      } else if ($email) {
        $projection = '&projection={"email":1}';
      }
      if (!empty($userParam)) {
        $userParam = 'where=' . json_encode($userParam) . $projection;
        $this->url = $this->baseUrl . $function .'/?'. $userParam;
      }
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->url);
      curl_setopt($ch, CURLOPT_HEADER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
            "Authorization: Basic " . base64_encode(IDM_API_KEY . ':')
      ));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
      
      $this->response = curl_exec($ch);
      $headers = curl_getinfo($ch);
      curl_close($ch);

      //Manage uncorrect response 
      if ($headers['http_code'] != 200) {
        throw new Exception(Yii::t('contest', 'Identitity Manager API returning httpcode: ') . $headers['http_code']);
      } elseif (!$this->response) {
        throw new Exception(Yii::t('contest', 'Identitity Manager API is not responding or Curl failed'));
      } elseif (strlen($this->response) == 0) {
        throw new Exception(Yii::t('contest', 'Zero length response not permitted'));
      }
      $userDetail = json_decode(strstr($this->response, "{"), true);
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in curlGet :' . $e->getMessage());
      $userDetail['success'] = false;
      $userDetail['msg'] = $e->getMessage();
      $userDetail['data'] = '';
    }
    return $userDetail;
  }
  
  /**
   * createUser
   * 
   * @param (array) $params
   * @param (string) $function
   * @return (array) $return
   */
  function createUser($function, $params = array()) {
    $return = array();
    try {
      if (!empty($params)) {
        $data = 'user=' . json_encode($params);
        $this->url = $this->baseUrl . $function .'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
              "Authorization: Basic " . base64_encode(IDM_API_KEY . ':')
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
        
        $this->response = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        //Manage uncorrect response 
        if ($headers['http_code'] != 200) {
          throw new Exception(Yii::t('contest', 'Identitity Manager returning httpcode: ') . $headers['http_code']);
        } elseif (!$this->response) {
          throw new Exception(Yii::t('contest', 'Identitity Manager  is not responding or Curl failed'));
        } elseif (strlen($this->response) == 0) {
          throw new Exception(Yii::t('contest', 'Zero length response not permitted'));
        }
        $return = json_decode(strstr($this->response, "{"), true);
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in createUser :') . $e->getMessage());
      $return['success'] = false;
      $return['msg'] = $e->getMessage();
      $return['data'] = '';
    }
    return $return;
  }

}