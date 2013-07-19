<?php

/**
 * WinnerController
 * 
 * WinnerController class inherit controller (base) class .
 * Actions are defined in WinnerController for manage winner.
 * 
 * Copyright (c) 2013 <ahref Foundati on -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class WinnerController extends Controller {

   public function beforeAction($action) {
    new JsTrans('js', SITE_LANGUAGE);
    return true;
  }
  
  public function actionError() {
    $this->render('error404');
  }

  
  /**
   * actionManageCategory
   * 
   * This function is used for manage category for contest
   */
  
  public function actionManageCategory() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $category = new Category();
    $message = array();
    $categoryDetail = array();
    $contestInfo = array();
    $contest = new ContestAPI();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {      
      $contest->contestSlug = $_GET['slug'];
    }    
    $contestInfo = $contest->getContestDetailByContestSlug();
    if (array_key_exists('contestId', $contestInfo) && !empty($contestInfo['contestId'])) {
      $category->contestId = $contestInfo['contestId'];
    }    
    if(!empty($_POST)) {
      try {
        if (array_key_exists('categoryName', $_POST) && empty($_POST['categoryName'])) {
          throw new Exception(Yii::t('contest','Category name can not be empty'));
        }
        $category->categoryName = $_POST['categoryName'];        
        $category->creationDate = date('Y-m-d H:i:s');
        $category->status = 1;
        $response = $category->save();
        if ($response) {
          $message['success'] = true;
        }
      } catch(Exception $e){
        $message['success'] = false;
        $message['msg'] = $e->getMessage();
      }      
    }     
    $categoryDetail = $category->get();
    $this->render('manageCategory', array('categories' => $categoryDetail, 'contest' =>  $contestInfo['contestTitle'], 'message' => $message));
  }
  
  /**
   * actionManageWinner
   * 
   * This function is used for manage winner for each category
   */
  public function actionManageWinner() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $winnerEntries = array();
    $categoryInfo = array();
    $contest = array();
    
    if (!empty($_POST)) {
      $response = $this->actionUpdateCategoryEntry();
      if (!$response['success']) {
        $this->actionAddWinnerInCategory($response['msg']);
        exit;
      }
    }
    $categoryInfo = $this->getCategoryDetail();
    if (array_key_exists('category_name',$categoryInfo) && !empty($categoryInfo['category_name'])) {
      $categorySlug = sanitization($categoryInfo['category_name']);
    }
    if (!empty($categoryInfo)) {
      $winnerEntries = $this->getWinner($categoryInfo['contest_slug'],$categorySlug);
    }
    $this->render('manageWinner', array('category' => $categoryInfo, 'entries' => $winnerEntries));
  }
  
  /**
   * actionUpdateCategoryEntry
   * 
   * This function is used for update existing category.
   * @return $response
   */
  public function actionUpdateCategoryEntry() {
    try {
      if (!empty($_POST)) {
        $aggregatorManager = new AggregatorManager();
        if (array_key_exists('prize', $_POST) && (!empty($_POST['prize']))) {
          $aggregatorManager->prize = $_POST['prize'];
        }
        if (array_key_exists('weight', $_POST) && (!empty($_POST['weight']))) {
          $aggregatorManager->prizeWeight = $_POST['weight'];
        }
        if (array_key_exists('id', $_POST) && (!empty($_POST['id']))) {
          $aggregatorManager->entryId = $_POST['id'];
        }
        if (array_key_exists('tag', $_POST) && (!empty($_POST['tag']))) { 
          $aggregatorManager->tags = unserialize($_POST['tag']);
        }        
        if (array_key_exists('category', $_POST) && (!empty($_POST['category']))) {
          $aggregatorManager->category = $_POST['category'];
        }   
        $response = $aggregatorManager->updateEntry();
        if ($response['success']) {
          $response['msg'] = Yii::t('contest', 'You have succesfully add an entry');
        } else {
          $response['msg'] = Yii::t('contest', 'Some technical problem occurred, contact administrator');
        }
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, Yii::t('contest', 'Error in updateCategoryEntry :') . $e->getMessage());
      $response['success'] = false;
      $response['msg'] = $e->getMessage();
    }
    return $response;
  }
  
  /**
   * actionAddWinnerInCategory
   * 
   * This function is used for add entry in category
   * @param $msg
   */
  public function actionAddWinnerInCategory($msg = '') {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }   
    try {
      $entries = array();
      $categoryInfo = array();
      $prizeWeight = array();
      $winnerWeight = '';
      $contestSubmission = array();
      $categoryInfo = $this->getCategoryDetail();
      if (array_key_exists('contest_slug',$categoryInfo) && !empty($categoryInfo['contest_slug'])) {
        $contestSlug = $categoryInfo['contest_slug'];
      }      
      if (array_key_exists('category_name',$categoryInfo) && !empty($categoryInfo['category_name'])) {
        $categorySlug = sanitization($categoryInfo['category_name']);
      }
      if (!empty($contestSlug) && !empty($categorySlug)) {
        $contest = new Contest();
        $contest->tags = $contestSlug . '{http://ahref.eu/contest/schema/},' . $categorySlug . '{http://ahref.eu/schema/contest/category}';
        $contest->sort = '-creation_date';
        $contestSubmission = $contest->getContestSubmissionForCategory();
        if (!empty($contestSubmission)) {
          foreach ($contestSubmission as $submission) {
            $entry = array();          

            //check whether winner already exist or not
            if (array_key_exists('tags', $submission) && !empty($submission['tags'])) {
              $weight = $this->checkWinner($submission['tags']);
              if (!empty($weight)) {
                $prizeWeight[] = $weight;
                continue;
              }
            }
            if (array_key_exists('title', $submission) && !empty($submission['title'])) {
              $entry['title'] = $submission['title'];
            }
            if (array_key_exists('id', $submission) && !empty($submission['id'])) {
              $entry['id'] = $submission['id'];
            }
            if (array_key_exists('author', $submission) && !empty($submission['author'])) {
              $entry['author'] = $submission['author']['name'];
            }
            if (array_key_exists('tags', $submission) && !empty($submission['tags'])) {
              $entry['tag'] = serialize($submission['tags']);
            }
            if (array_key_exists('image', $submission) && !empty($submission['image'])) {
              if (!empty($submission['image']) && filter_var($submission['image'], FILTER_VALIDATE_URL)) {
                $basePath = parse_url($submission['image']);
                if (!empty($basePath['path'])) {
                  $entry['image'] = substr($basePath['path'], 1);
                }
              } else {
                $entry['image'] = $submission['image'];
              }
            }
            if (!empty($entry)) {
              $entries[] = $entry;
            }
          }
        }
      }
      if(!empty($prizeWeight)) {
        $winnerWeight = implode(',',$prizeWeight); 
      }
    } catch (Exception $e) {
      $message['success'] = false;
      $message['msg'] = $e->getMessage();
    }
    $this->render('addWinner', array('category' => $categoryInfo, 'entries' => $entries, 'winnerWeight'=>$winnerWeight, 'msg' => $msg));
  }

   /**
   * actionGetWinner
   * 
   * This function is used for get winner entry
   * @param $contestSlug
   * @param $category
   * @return $winner  (winner entry)
   */
  public function getWinner($contestSlug, $categorySlug) {
    $winner = array();
    if (empty($contestSlug) || empty($categorySlug)) { 
      return array();
    }
    $aggregatorManager = new AggregatorManager();
    $aggregatorManager->returnField = 'links,author,title,id,tags';
    $aggregatorManager->contestSlug = $contestSlug;
    $aggregatorManager->sort = 'weight';
    $aggregatorManager->tag =  $contestSlug . '{http://ahref.eu/contest/schema/},winner,' . $categorySlug . '{http://ahref.eu/schema/contest/category}';
    $entries = $aggregatorManager->getWinnerEntry();
    foreach ($entries as $entry) {
      if (array_key_exists('image', $entry) && !empty($entry['image'])) {
        if (!empty($entry['image']) && filter_var($entry['image'], FILTER_VALIDATE_URL)) {
          $basePath = parse_url($entry['image']);
          if (!empty($basePath['path'])) {
            $WinnerEntry['image'] = substr($basePath['path'], 1);
          }
        } else {
          $WinnerEntry['image'] = $entry['image'];
        }
      }
      if (array_key_exists('author', $entry) && !empty($entry['author'])) {
        $WinnerEntry['author'] = $entry['author']['name'];
      }
      if (array_key_exists('title', $entry) && !empty($entry['title'])) {
        $WinnerEntry['title'] = $entry['title'];
      }
      if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
        $WinnerEntry['tags'] = $entry['tags'];
      }
      if (array_key_exists('id', $entry) && !empty($entry['id'])) {
        $WinnerEntry['id'] = $entry['id'];
      }
      $winner[] = $WinnerEntry;
    }
    return $winner;
  }
  
  /**
   * getCategoryDetail
   * 
   * This function is used for get category and contest detail
   */
  public function getCategoryDetail(){
    $categoryInfo = array();
    $category = new Category();
    if (array_key_exists('id', $_GET) && !empty($_GET['id'])) {
      $category->categoryId = $_GET['id'];
    }   
    $categoryInfo = $category->getCategory(); 
    if(!empty($categoryInfo['contest_id'])) {
      $category->contestId = $categoryInfo['contest_id'];
      $contest = $category->get(); 
      if(!empty($contest)){
        $categoryInfo['contest_name'] = $contest[0]['contestTitle'];
        $categoryInfo['contest_slug'] = $contest[0]['contestSlug'];
      }
    }   
    return $categoryInfo;
  }
  
  /**
   * checkWinner
   * 
   * This function is used for check whether an entry is winner or not
   * @param $submission
   * @return array $winnerWeight
   */
  public function checkWinner($submission) {
    $winnerWeight = '';
    if (!empty($submission)) {
      foreach ($submission as $tag) {
        if ($tag['name'] == 'winner') {
          $winnerWeight = $tag['weight'];        
        }
      }
    }
    return $winnerWeight;
  }
  
  /**
   * actionGetWinnerInfo
   * 
   * This function is used for get winner information
   */
  public function actionGetWinnerInfo() {
    $winner = array();
    $contestInfo = array();
    $categoryDetail = array();
    $contest = new ContestAPI();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {      
      $contest->contestSlug = $_GET['slug'];
    }    
    $contestInfo = $contest->getContestDetailByContestSlug();
    $category = new Category();
    if (array_key_exists('contestId', $contestInfo) && !empty($contestInfo['contestId'])) {
      $category->contestId = $contestInfo['contestId'];
    }    
    $categoryDetail = $category->get();
    if (!empty($categoryDetail)) {
      foreach($categoryDetail as $category) {
        $cat = array();
        if (array_key_exists('category_name', $category) && !empty($category['category_name'])) {
          $cat['slug'] = sanitization($category['category_name']);
          $cat['name'] = $category['category_name'];          
        }
        if(!empty($cat['slug']) && !empty($category['contestSlug'])) { 
          $winner[$cat['slug']] = $this->getWinner($category['contestSlug'], $cat['slug']);
        }      
        $categoryInfo[] = $cat;
      }
    } 
    $this->render('winner', array('contestInfo' => $contestInfo, 'categories' => $categoryInfo, 'entries' => $winner));
  }
 }


