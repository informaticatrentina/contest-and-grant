<?php

/**
 * ContestAPITest
 * 
 * This class have all possible test case for ContestAPI
 * ContestAPITest class is used for test ContestAPI
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of
 *  <ahref Foundation.
 */
require_once realpath(dirname(__FILE__))."/../../components/ContestAPI.php";

Yii::import('application.components.ContestAPI');

class ContestAPITest extends CTestCase {
  
  public $fixtures = array(
      'contestDetail' => 'ContestAPI',
  );
  
  /**
   * testsave
   * 
   * This function is used for test save method of ConttestAPI
   */
  public function testsave() {
    $contestApi = new ContestAPI();
    $contestApi->startDate = '2013-05-09';
    $contestApi->endDate = '2013-05-09';
    $contestApi->creationDate = '2013-05-09';
    $contestApi->contestImage = '/upload/abc.ipg';
    $contestApi->contestTitle = 'test 4123';
    $contestApi->contestSlug = 'test_4123';
    $contestApi->contestDescription = 'tset description';
    $contestApi->squareImage = '/upload/abc.ipg';
    $contestApi->contestRule = '<p>Testing of save method for a contest</p>';
    $contestApi->entryStatus = false; 
    $result = $contestApi->save();
    $this->assertEquals(1, $result);         
      
    //test case that will try to save with out required fileds. And check that weather api handles those cases or not
    //test when start date is empty    
    $contestApi->startDate = '';
    try {
      $contestApi->save();
    } catch (Exception $e) {
      $msg = $e->getMessage();
    }
    $this->assertEquals(Yii::t('contest','Start date should not be empty'), $msg);
    
    //test when end date is empty
    $contestApi->startDate = '2013-05-09';
    $contestApi->endDate = '';
    try {
      $contestApi->save();
    } catch (Exception $e) {
      $msg = $e->getMessage();
    }
    $this->assertEquals(Yii::t('contest','End date should not be empty'), $msg);
    
    //test when contest title is empty
    $contestApi->endDate = '2013-05-09';
    $contestApi->contestTitle = '';
    try {
      $contestApi->save();
    } catch (Exception $e) {
      $msg = $e->getMessage();
    }
    $this->assertEquals(Yii::t('contest','Contest title should not be empty'), $msg);
    
    //test when contest description is empty
    $contestApi->contestTitle = 'test';
    $contestApi->contestDescription = '';
    try {
      $contestApi->save();
    } catch (Exception $e) {
      $msg = $e->getMessage();
    }
    $this->assertEquals(Yii::t('contest', 'Contest description should not be empty'), $msg);
    
    //test when contest image  is empty
    $contestApi->contestDescription = 'This is test contest';
    $contestApi->contestImage = '';
    try {
      $contestApi->save();
    } catch (Exception $e) {
      $msg = $e->getMessage();
    }
    $this->assertEquals(Yii::t('contest','Please provide contest image'), $msg);
  }
  
   /**
   * testGetContestDetail
   * 
   * This function is used for test getContestDetail method in contest api
   */
  public function testGetContestDetail() {
    $contestApi = new ContestAPI();
    $contest = $contestApi->GetContestDetail(); 
    $this->assertLessThanOrEqual(1,count($contest)); 
  }
  
  /**
   * testGetContestDetailByContestSlug
   * 
   * This function is used for test getContestDetailByContestSlug  method in contest api
   */
  public function testGetContestDetailByContestSlug() {
    $contestApi = new ContestAPI();
    $contest = array();
    //test when slug is empty
    $contestApi->contestSlug = '';
    $contest = $contestApi->getContestDetailByContestSlug();
    $this->assertEmpty($contest);

    // check when slug is not empty
    $contestApi->contestSlug = 'test_4123';
    $contest[] = $contestApi->getContestDetailByContestSlug();
    $this->assertEquals(1, count($contest));
  }
  
 }
?>
  