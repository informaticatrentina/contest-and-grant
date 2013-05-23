<?php

/**
 * UserIdentityManager
 * 
 * UserIdentityManager class is used for interacting with UserIdentityAPI class.
 * UserIdentityManager class is used for get user, create user 
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
   * @param (string) $firstName
   * @param (string) $lastName
   * @param (string) $email
   * @param (string) $password
   * @return (boolean) $userStatus
   */
  
  public function createUser($firstName, $lastName, $email, $password) {
    $inputData = array();
    if (!empty($firstName)) {
      $inputData['firstName'] = $firstName;
    }
    if (!empty($lastName)) {
      $inputData['lastName'] = $lastName;
    }
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $inputData['email'] = $email;
    }
    if (!empty($password)) {
      $inputData['password'] = $password;
    }
    $inputParam = json_encode($inputData);
    try {
      $user = new UserIdentityAPI();
      $userStatus = $user->curlPost( USER, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in createUser method :' . $e->getMessage());      
    }
  }
  
  /**
   * validateUser
   * 
   * This function is used for validate user
   * @param (string) $email
   * @param (string) $password
   * @return (boolean) $userStatus
   */
   public function validateUser($email, $password) {
    $inputData = array();
    $userStatus = false;
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $inputData['email'] = $email;
    }
    if (!empty($password)) {
      $inputData['password'] = $password;
    }
    $inputParam = json_encode($inputData);
    try {
      $user = new UserIdentityAPI();
      $userStatus = $user->curlGet( USER, $inputParam);
    } catch (Exception $e) {
      Yii::log('', ERROR, 'Error in validateUser method :' . $e->getMessage());      
    }
    return $userStatus;
  }
}
?>
