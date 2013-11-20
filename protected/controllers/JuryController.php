<?php

/**
 * JuryController
 * 
 * JuryController class inherit controller (base) class .
 * Actions are defined in JuryController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class JuryController extends Controller {

  public function beforeAction($action) {
    new JsTrans('js', SITE_LANGUAGE);
    return true;
  }

  /**
   * actionManageJury
   * function is used for add, update jury member and admin
   */
  public function actionManageJury() {
    try {
      //check if user belong to admin users or not
      $isAdmin = isAdminUser();
      if (!$isAdmin) {
        $this->redirect(BASE_URL);
      }
      $juryEmail = array('jury_member' => array(), 'jury_admin' => array());
      $message = '';
      $isExistContest = false;
      $jury = new Jury();
      if (array_key_exists('contest_id', $_GET) && !empty($_GET['contest_id'])) {
        $jury->contestId = $_GET['contest_id'];
        $contest = new ContestAPI();
        $contest->contestId = $_GET['contest_id'];
        $contestDetail = $contest->getContestDetailByContestId();
        if (!empty($contestDetail)) {
          $isExistContest = true;
        }
      }
      if (!empty($_POST)) {
        $postData = $_POST;
        $juryEmail = $postData;
        if (array_key_exists('jury_member', $postData) && empty($postData['jury_member'])) {
          throw new Exception(Yii::t('contest', 'Jury member email can not be empty'));
        }
        if (array_key_exists('jury_member', $postData) && empty($postData['jury_member'])) {
          throw new Exception(Yii::t('contest', 'Jury admin email can not be empty'));
        }

        $juryMember = array_unique(array_map("trim", explode(',', $postData['jury_member'])));
        $juryAdmin = array_unique(array_map("trim", explode(',', $postData['jury_admin'])));
        $jury->creationDate = time();

        $emailList = array_merge($juryMember, $juryAdmin);
        foreach ($emailList as $email) {
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(Yii::t('contest', 'Email id is not valid') . ' : ' . $email);
          }
        }
        $jury->delete();
        //save jury admin
        foreach ($juryAdmin as $admin) {
          $jury->emailId = $admin;
          $jury->designation = JURY_ADMIN;
          $adminSaveStatus = $jury->save();
          if (!$adminSaveStatus) {
            Yii::log('Error in actionManageJury ', ERROR, Yii::t('contest', 'failed to save jury admin ')
             . $admin);
          }
        }
        //save jury member
        foreach ($juryMember as $member) {
          $jury->emailId = $member;
          $jury->designation = JURY_MEMBER;
          $memberSaveStatus = $jury->save();
          if (!$memberSaveStatus) {
            Yii::log('Error in actionManageJury ', ERROR, Yii::t('contest', 'failed to save jury member ') 
              . $member);
          }
        }
        $message = Yii::t('contest', 'Jury admin and memeber have been saved successfully');
      } else {        
        $juryInfo = $jury->get();
        foreach ($juryInfo as $info) {
          switch ($info['designation']) {
            case JURY_ADMIN:
              $juryEmail['jury_admin'][] = $info['email_id'];
              break;
            case JURY_MEMBER:
              $juryEmail['jury_member'][] = $info['email_id'];
              break;
          }
        }
        if (array_key_exists('jury_admin', $juryEmail)) {
          $juryEmail['jury_admin'] = implode(', ', $juryEmail['jury_admin']);
        }
        if (array_key_exists('jury_member', $juryEmail)) {
          $juryEmail['jury_member'] = implode(', ', $juryEmail['jury_member']);
        }
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in actionManageJury :') . $e->getMessage());
      $message = $e->getMessage();
    }
    $this->render('jury', array('jury' => $juryEmail, 'message' => $message, 'is_exist_contest'=>$isExistContest));
  } 
}