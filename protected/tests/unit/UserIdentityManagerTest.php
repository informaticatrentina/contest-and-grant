<?php

/**
 * UserIdentityManagerTest
 * 
 * This class have all possible test case for UserIdentityManagerTest
 * ContestAPITest class is used for test UserIdentityManager
 * Copyright (c) 2073 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
require_once realpath(dirname(__FILE__)) . "/../../components/UserIdentityManager.php";

Yii::import('application.components.UserIdentityManager');

class UserIdentityManagerTest extends CTestCase {

  /**
   * testCreateUser
   * 
   * This function is used for test createUser
   */
  public function testCreateUser() {
    $identityManager = new UserIdentityManager();
    
    //test when user create an account successfully
    $user = array(
        'firstname' => '7contest',
        'lastname' => '7grant',
        'email' => '7contest@grant.com',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->createUser($user);
    $this->assertTrue($status['success']);
    
    //test when user's firstname is empty
    $user = array(
        'firstname' => '',
        'lastname' => '7grant',
        'email' => '7contest@grant.com',
        'password' => '7contestandgrant'
    );

    $status = $identityManager->createUser($user);
    $this->assertFalse($status['success']);

    //test when user's lastname is empty
    $user = array(
        'firstname' => '7contest',
        'lastname' => '',
        'email' => '7contest@grant.com',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->createUser($user);
    $this->assertFalse($status['success']);
    
    //test when user's email is empty
    $user = array(
        'firstname' => '7contest',
        'lastname' => '7grant',
        'email' => '',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->createUser($user);
    $this->assertFalse($status['success']);

    //test when user's email is not valid
    $user = array(
        'firstname' => '7contest',
        'lastname' => '7grant',
        'email' => '7contest@',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->createUser($user);
    $this->assertFalse($status['success']);


    //test when user's password is empty
    $user = array(
        'firstname' => '7contest',
        'lastname' => '7grant',
        'email' => 'contest@grant.com7',
        'password' => ''
    );
    $status = $identityManager->createUser($user);
    $this->assertFalse($status['success']);
  }
  
  /**
   * testValidateUser
   * 
   * This function is used for test validateUser
   */
  public function testValidateUser() {
    $identityManager = new UserIdentityManager();
    
    //validate user where email is empty
    $user = array(
        'email' => '',
        'password' => 'contestandgrant7'
    );
    $status = $identityManager->validateUser($user);
    $this->assertFalse($status['success']);
    
    
    //validate user where email is not valid
    $user = array(
        'email' => 'contest@7',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->validateUser($user);
    $this->assertFalse($status['success']);
    
    //validate user where password is empty
    $user = array(
        'email' => '7contest@grant.com',
        'password' => ''
    );
    $status = $identityManager->validateUser($user);
    $this->assertFalse($status['success']);
    
    //validate user where password is wrong
    $user = array(
        'email' => '7contest@grant.com',
        'password' => '7lsgrantandcontesasdst'
    );
    $status = $identityManager->validateUser($user);    
    $this->assertEquals(Yii::t('contest', 'You have entered either wrong email id or password. Please try again'), $status['msg']);
    
    //validate user where user credential are correct 
    $user = array(
        'email' => '7contest@grant.com',
        'password' => '7contestandgrant'
    );
    $status = $identityManager->validateUser($user);
    $this->assertTrue($status['success']);
  }
}