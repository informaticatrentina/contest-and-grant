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
      $response = $user->createUser(USER, $userDetail);    
      if (array_key_exists('user', $response) &&  $response['user']['status'] == 'OK') {
        $saveUser['msg'] = "You have successfully created your account";
        $saveUser['success'] = true;
      } else {       
        $saveUser['msg'] = "Please try again";
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
    if (empty($userDetail['email']) || !filter_var($userDetail['email'], FILTER_VALIDATE_EMAIL)) {
      throw new Exception('Please enter a valid email');
    }
    if (empty($userDetail['password'])) {
      throw new Exception('Please enter password');
    }
    try {
      $user = new UserIdentityAPI();
      $userStatus = $user->getUserDetail(USER, $userDetail);
      if (!$userStatus['success']) {
        $userStatus['msg'] = "Please try again";
      }
    } catch (Exception $e) {
      $userStatus['success'] = false;
      $userStatus['msg'] = $e->getMessage();
      Yii::log('', ERROR, 'Error in validateUser method :' . $e->getMessage());      
    }
    return $userStatus;
  }
}
?>
