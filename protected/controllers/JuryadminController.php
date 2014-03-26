<?php

/**
 * JuryadminController
 * 
 * JuryadminController class inherit controller (base) class .
 * Actions are defined in JuryController.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class JuryadminController extends Controller {

  public function beforeAction($action) {
    new JsTrans('js', SITE_LANGUAGE);
    return true;
  }

 /**
   * actionActiveContest
   * function is used for get all active contest
   */
  public function actionActiveContest() {
    try {
      $roles = getRoles();
      if (!in_array('jury_admin', $roles)) {
        $this->redirect(BASE_URL);
      } 
      $currentContest = array();
      $activeContest = array();
      $jury = new Jury();
      $jury->emailId = Yii::app()->session['user']['email'];
      $jury->designation = JURY_ADMIN;
      $juryInfo = $jury->fetchAll();        
      foreach ($juryInfo as $info) {
        if (time() >= strtotime($info['jury_rating_from']) && time() <= strtotime($info['jury_rating_till'])) {
          $currentContest[] =  $info['contestSlug'];
          $activeContest[] = array('slug' => $info['contestSlug'], 'contest_name' => $info['contestTitle']);
        }
      }    
      $_SESSION['user']['active_contest'] = $currentContest;
    } catch (Exception $e) {
      Yii::log('Error in actionActiveContest ', ERROR, $e->getMessage());
    }
    $this->render('activeContest', array('active_contest' => $activeContest));
  }

  /**
   * actionJuryRating
   * function is used for get all submission for a contest (order by jury rating)
   */
  public function actionJuryRating() {
    try {
      $roles = getRoles();
      if (!in_array('jury_admin', $roles)) {
        $this->redirect(BASE_URL);
      }
      $contestSubmissions = array();
      if (!array_key_exists('contest_slug', $_GET)) {
        $this->redirect(BASE_URL);
      }
      if (!array_key_exists('active_contest', Yii::app()->session['user']) || !in_array($_GET['contest_slug'], Yii::app()->session['user']['active_contest'])) {
        $this->redirect(BASE_URL);
      }
      $contestEntries = $this->getSubmissionForJuryAdmin();
      $juryController = new JuryController('jury');
      $contestSubmission = $juryController->prepareSubmission($contestEntries);
      if (array_key_exists('entry', $contestSubmission) && (!empty($contestSubmission['entry']))) {
        foreach ($contestSubmission['entry'] as $entry) {
          if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
            $count = 0;
            foreach ($entry['tags'] as $tag) {
              if ($tag['scheme'] == RATING_COUNT_SCHEME) {
                $count = $tag['weight'];
              }
            }
          }
          $entryTitle = '';
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $entryTitle = $entry['title'];
          }
          $contestSubmissions[] = array('id' => $entry['id'], 'title' => $entryTitle, 'author' => $entry['author'],
              'jury_rating' => $count);
        }
      }
    } catch (Exception $e) {
      Yii::log('Error in actionJuryRating ', ERROR, $e->getMessage());
    }
    $this->render('rating', array('entries' => $contestSubmissions, 'contest_slug' => $_GET['contest_slug']));
  }

  /**
   * actionViewEntry
   * function is used for view entry detail
   */
  public function actionViewEntry() {
    $contest = new Contest();
    if (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
      $contest->entryId = $_GET['id'];      
    }
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];      
    }
    $entries = array($contest->getContestSubmissionInfo());
    $juryController = new JuryController('jury');
    $contestEntries = $juryController->prepareSubmission($entries);  
    if (array_key_exists('tags', $contestEntries['entry'][0]) && !empty($contestEntries['entry'][0]['tags'])) {
      foreach ($contestEntries['entry'][0]['tags'] as $tag) {
        if ($tag['scheme'] == RATING_COUNT_SCHEME) {
          $contestEntries['jury_rating'] = $tag['weight'];
        }
      }
    }     
    //loading submission detail  for pagination
    $pagination = array();
    $aggregatorMgr = new AggregatorManager();
    $aggregatorMgr->contestSlug = $_GET['slug'];
    $aggregatorMgr->range = $_GET['id'] . ':' . 1;
    $aggregatorMgr->returnField = 'title,id';
    $entryForPagination = $aggregatorMgr->getEntryForPagination();
    if (!empty($entryForPagination)) {
      if (array_key_exists('after', $entryForPagination) && !empty($entryForPagination['after'])) {
        $pagination['nextEntryId'] = $entryForPagination['after'][0]['id'];
        if (array_key_exists('title', $entryForPagination['after'][0])) {
          $pagination['nextEntryTitle'] = $entryForPagination['after'][0]['title'];
        }
      }
      if (array_key_exists('before', $entryForPagination) && !empty($entryForPagination['before'])) {
        $pagination['prevEntryId'] = $entryForPagination['before'][0]['id'];
        if (array_key_exists('title', $entryForPagination['before'][0])) {
          $pagination['prevEntryTitle'] = $entryForPagination['before'][0]['title'];
        }
      }
    }
    $this->render('viewEntry', array('entries' => $contestEntries, 'pagination'=> $pagination, 'contest_slug' => $_GET['slug']));
  }

  /**
   * actionJuryStatus
   * function is used for check whether jury has rated submission or not
   */
  public function actionJuryStatus() { 
    $juryInfo = array();
    $submissions = array();
    $jury = new Jury();
    $contest = new Contest();
    $contest->limit = 9999;
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
      $jury->contestSlug = $_GET['slug'];      
      $jury->designation = JURY_MEMBER;      
      $juryList = $jury->fetchAll();
      foreach ($juryList as $list) {
        $juryInfo[$list['user_id']] = array('user_id' => $list['user_id'], 'email' => $list['email_id']);
      }  
      $contestEntries = $contest->getContestSubmission();
      foreach ($contestEntries as $entries) {
        if (array_key_exists('tags', $entries) && !empty($entries['tags'])) {
          $entryTitle = '';
          if (array_key_exists('title', $entries) && !empty($entries['title'])) {
            $entryTitle = $entries['title'];
          }
          $submissions[$entries['id']] =  array('title' => $entryTitle, 'entry_id' => $entries['id']); 
          foreach ($entries['tags'] as $tag) {            
            if ($tag['scheme'] == JURY_RATING_SCHEME && !empty($tag['weight'])) {  
              $submissions[$entries['id']]['jury_id'][] = $tag['slug']; 
            }
          }
        }
      }
    }
    $this->render('viewJuryRating', array('jury' => $juryInfo, 'submissions'=> $submissions, 'contest_slug' => $_GET['slug']));
  }
  
  /**
   * getSubmissionForJuryAdmin
   * function is used for getting all submission for jury admin 
   * @return  - all submission for jury admin
   */
  public function getSubmissionForJuryAdmin() {
    $jury = new Jury();
    $inputParam = array(
        'return_fields' => 'links,author,title,id,tags',
        'sort' => '-tag:count',
        'tags' => $_GET['contest_slug'] . '{http://ahref.eu/contest/schema/}',
        'limit' => 9999,
        'offset' => 0,
        'source' => SOURCE
    );   
    return $jury->getSortedContestSubmission($inputParam);
  }
  
  /**
   * actionDownloadSubmission
   * function is used for download submission
   * @param $downloadFromAdminPage (true if this function is calling from admin page)
   */
  public function actionDownloadSubmission($downloadFromAdminPage = false) {
    try {
      $message = '';
      if ($downloadFromAdminPage) {
        $aggManager = new AggregatorManager();
        $entries = $aggManager->getEntry(9999, 0, '', 'active', $_GET['contest_slug'].'[contest]', '', '',
           0, '', '', 1, '', array(), '', 'links,author,id,tags,title,content', '', '', SOURCE);
      } else {
        if (!array_key_exists('active_contest', Yii::app()->session['user']) || !in_array($_GET['contest_slug'], Yii::app()->session['user']['active_contest'])) {
          $this->redirect(BASE_URL);
        }
        $entries = $this->getSubmissionForJuryAdmin();
      }
      $juryController = new JuryController('jury');
      $entries = $juryController->prepareSubmission($entries);
      if (empty($entries)) {
        Yii::log('actionDownloadSubmission ', INFO, 'There is no rated submission in this contest');
        $message = Yii::t('contest', 'There is no rated submission in this contest');
      } else {
        foreach ($entries['entry'] as $entry) {
          $author = '';
          if (array_key_exists('author', $entry) && !empty($entry['author'])) {
            $author = $entry['author']['name'];
          }
          $title = '';
          if (array_key_exists('title', $entry) && !empty($entry['title'])) {
            $title = $entry['title'];
          }
          $voting = 0;
          foreach ($entry['tags'] as $tag) {
            if ($tag['scheme'] == RATING_COUNT_SCHEME) {
              $voting = $tag['weight'];
            }
          }
          $juryEntriesDir = 'downloads/juryEntries/';
          if (!is_writable('downloads')) {
            throw new Exception(Yii::t('contest', 'downloads directory is not writable'));
          }
          if (is_dir($juryEntriesDir) === false) {
            if (!mkdir($juryEntriesDir)) {
              throw new Exception(Yii::t('contest', 'Failed to create directory'). ' juryEntries');
            }
          }
          $dir = $juryEntriesDir . sanitization($author);
          if (is_dir($dir) === false) {
            if (!mkdir($dir)) {
              throw new Exception(Yii::t('contest', 'Failed to create directory'). ' '. sanitization($author));
            }
          }
          if (array_key_exists('pdf_file_path', $entry) && !empty($entry['pdf_file_path'])) {
            foreach ($entry['pdf_file_path'] as $link) {
              $linkInfo = parse_url($link);
              $pathInfo = pathinfo($linkInfo['path']);
              if (!file_exists(realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'])) {
                throw new Exception(Yii::t('contest', 'file does not exist ') .' "'. realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'].'"');
              }
              if (!copy(realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'], $dir . '/' . $pathInfo['basename'])) {
                throw new Exception(Yii::t('contest', 'failed to copy file') .' "'.$linkInfo['path'].'"');
              }
            }
          }
          if (array_key_exists('image_path', $entry) && !empty($entry['image_path'])) {
            foreach ($entry['image_path'] as $link) {
              $linkInfo = parse_url($link);
              $pathInfo = pathinfo($linkInfo['path']);
              if (!file_exists(realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'])) {
                throw new Exception(Yii::t('contest', 'file does not exist ') .' "'. realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'].'"');
              }
              if (!copy(realpath(dirname(__FILE__) . '/../../') . $linkInfo['path'], $dir . '/' . $pathInfo['basename'])) {
                throw new Exception(Yii::t('contest', 'failed to copy file') .' "'.$linkInfo['path'].'"');
              }
            }
          }
          $filename = 'invio.txt';
          $filePath = $dir . '/' . $filename;
          $fileOpen = fopen($filePath, 'w');
          $fileContent = 'Autore: ' . $author . "\n" . 'Titolo: ' . $title . "\n" . 'Voting: ' . $voting;
          if (array_key_exists('video_links', $entry)) {
            foreach ($entry['video_links'] as $link) {
              $fileContent = $fileContent . "\n" . 'Link to video: ' . $link;
            }
          }
          fwrite($fileOpen, $fileContent);
          fclose($fileOpen);
        }
        $destination = $this->createZipFile($juryEntriesDir);
        if (!empty($destination)) {
          deleteFile($juryEntriesDir);
        }
        downloadZipFile($destination);
      }
    } catch (Exception $e) {
      Yii::log('Error in actionDownloadSubmission ', ERROR, $e->getMessage());
      $message = Yii::t('contest', 'Some technical error occur. Please contact adminstrator');
    }
    $this->render('//contest/downloadSubmission', array('message' => $message));
  }

  /**
   * createZipFile
   * function is used for create zip file
   * @param $file - file path
   * @return $fileName - zip file name
   */
  public function createZipFile($file) {
    $zip = new ZipArchive();
    $fileName = DOWNLOAD_ZIP_DIRECTORY . $_GET['contest_slug'] . '_submission_' . time() . '.zip';
    if ($zip->open($fileName, ZIPARCHIVE::CREATE) !== true) {
      Yii::log('Error in createZipFile ', ERROR, ' failed to create zip archive');
      return '';
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file));
    foreach ($iterator as $key => $value) {
      //remove system config file (. , .. file)
      if ($iterator->getFilename() == '.' || $iterator->getFilename() == '..') {
        continue;
      }
      if ($iterator->getSubPathname() != '.') {
        $zip->addFile(realpath($key), $iterator->getSubPathname());
      }
    }
    $zip->close();
    return $fileName;
  }
}