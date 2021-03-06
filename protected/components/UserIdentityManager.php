<?php

/**
 * UserIdentityManager
 * 
 * UserIdentityManager class is used for interacting with UserIdentityAPI class.
 * UserIdentityManager class is used for get user detail, create user 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi <rahul@incaendo.com>
 * This file is part of <Contest and Grand>.
 * This file can not be copied and/or distributed without the express permission of
 *   <ahref Foundation.
 */

class UserIdentityManager {

  /**
   * createUser
   * 
   * This function is used for create  user
   * @param (array) $userDetail
   * @return (array) $saveUser
   */
  
  public function createUser($userDetail) {
    $saveUser = array();
    $response = array();
     $saveUser['success'] = false;
    try {
      if (empty($userDetail['firstname'])) {
        throw new Exception(Yii::t('contest', 'Please enter first name'));
      }
      if (empty($userDetail['lastname'])) {
        throw new Exception(Yii::t('contest', 'Please enter last name'));
      }
      if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception(Yii::t('contest','Please enter a valid email'));
      } else {
        $userDetail['email'] = urlencode($userDetail['email']);
      }
      if (empty($userDetail['password'])) {
        throw new Exception(Yii::t('contest', 'Please enter password'));
      } else {
        $userDetail['email'] = urlencode($userDetail['email']);
      }
       
      $user = new UserIdentityAPI();
      $response = $user->createUser(IDM_USER_ENTITY, $userDetail);   
      if (array_key_exists('user', $response) &&  $response['user']['status'] == 'OK') {
        $saveUser['id'] = $response['user']['_id'];
        $saveUser['msg'] = Yii::t('contest', 'You have successfully created your account');
        $saveUser['success'] = true;
      } else {    
        $message = 'Please try again';
        if (array_key_exists('user', $response) &&  $response['user']['status'] == 'ERR') {
          $message = $response['user']['issues'][0];
          if (strpos($message, "field 'email' not unique") !== false) {
              $message = Yii::t('contest' , 'Email id already in use, Please choose a different email id');
          } else {
              $message = Yii::t('contest','Some technical problem occurred, contact administrator');
          }
        }
        $saveUser['msg'] = $message;
      }
    } catch (Exception $e) {
      $saveUser['msg'] = $e->getMessage();
      Yii::log('', ERROR, Yii::t('contest','Error in createUser method :') . $e->getMessage());
    }
    return $saveUser;
  }
  
  /**
   * validateUser
   * 
   * This function is used for validate user
   * @param (array) $userDetail
   * @return (boolean) $userStatus
   */
   public function validateUser($userDetail) {
    $inputParam = '';
    $userStatus = array();
    $userStatus['success'] = false;
    try {
      if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception(Yii::t('contest','Please enter a valid email'));
      } else {
        $userDetail['email'] = urlencode($userDetail['email']);
      }
      if (empty($userDetail['password'])) {
        throw new Exception(Yii::t('contest','Please enter password'));
      } else {
        $userDetail['password'] = urlencode($userDetail['password']);
      }
      $user = new UserIdentityAPI();
      $userStatus = $user->getUserDetail(IDM_USER_ENTITY, $userDetail);
      if (array_key_exists('success', $userStatus) && !$userStatus['success']) {
        $userStatus['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');
      } else if (array_key_exists('_items', $userStatus)) {
        if (empty($userStatus['_items'])) {
          $userStatus['msg'] = Yii::t('contest', 'You have entered either wrong email id or password. Please try again');
        } else {
          Yii::app()->session->open();
          $user = array();
          if (array_key_exists('firstname', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['firstname'])) {
            $user['firstname'] = $userStatus['_items'][0]['firstname'];
          }
          if (array_key_exists('lastname', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['lastname'])) {
             $user['lastname'] = $userStatus['_items'][0]['lastname'];
          }
          if (array_key_exists('email', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['email'])) {
            $user['email'] = $userStatus['_items'][0]['email'];
          }
          if (array_key_exists('created', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['created'])) {
            $user['creationDate'] = $userStatus['_items'][0]['created'];
          }
          if (array_key_exists('etag', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['etag'])) {
            $user['etag'] = $userStatus['_items'][0]['etag'];
          }
          if (array_key_exists('_id', $userStatus['_items'][0]) && !empty($userStatus['_items'][0]['_id'])) {
            $user['id'] = $userStatus['_items'][0]['_id'];
          }
          Yii::app()->session['user'] = $user;
          $userStatus['success'] = true;
        }
      }
    } catch (Exception $e) {      
      $userStatus['msg'] = $e->getMessage();
      Yii::log('', ERROR, Yii::t('contest', 'Error in validateUser method :') . $e->getMessage());      
    }
    return $userStatus;
  }
}
?>
