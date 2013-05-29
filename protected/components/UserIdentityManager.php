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
        throw new Exception('Please enter first name');
      }
      if (empty($userDetail['lastname'])) {
        throw new Exception('Please enter last name');
      }
      if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email');
      }
      if (empty($userDetail['password'])) {
        throw new Exception('Please enter password');
      }
       
      $user = new UserIdentityAPI();
      $response = $user->createUser(IDM_USER_ENTITY, $userDetail);    
      if (array_key_exists('user', $response) &&  $response['user']['status'] == 'OK') {
        $saveUser['msg'] = "You have successfully created your account";
        $saveUser['success'] = true;
      } else {    
        $message = 'Please try again';
        if (array_key_exists('user', $response) &&  $response['user']['status'] == 'ERR') {
          $message = $response['user']['issues'][0];
          if (strpos($message, "field 'email' not unique") !== false) {
              $message = 'Email id already in use, Please choose a different email id';
          } else {
              $message = 'Some technical problem occurred, contact administrator';
          }
        }
        $saveUser['msg'] = $message;
      }
    } catch (Exception $e) {
      $saveUser['msg'] = $e->getMessage();
      Yii::log('', ERROR, 'Error in createUser method :' . $e->getMessage());
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
    if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Please enter a valid email');
    }
    if (empty($userDetail['password'])) {
      throw new Exception('Please enter password');
    }
    try {
      $user = new UserIdentityAPI();
      $userStatus = $user->getUserDetail(IDM_USER_ENTITY, $userDetail);
      if(array_key_exists('_items', $userStatus)) {
        if (empty($userStatus['_items'])) {
          $userStatus['msg'] = "You have entered either wrong email id or password. Please try again";
        } else {
          Yii::app()->session->open();
          $user = array();
          $user['firstname'] = $userStatus['_items'][0]['firstname'];
          $user['lastname'] = $userStatus['_items'][0]['lastname'];
          $user['email'] = $userStatus['_items'][0]['email'];
          $user['creationDate'] = $userStatus['_items'][0]['created'];
          $user['etag'] = $userStatus['_items'][0]['etag'];
          Yii::app()->session['user'] = $user;
          $userStatus['success'] = true;
        }
      }  
    } catch (Exception $e) {      
      $userStatus['msg'] = $e->getMessage();
      Yii::log('', ERROR, 'Error in validateUser method :' . $e->getMessage());      
    }
    return $userStatus;
  }
}
?>
