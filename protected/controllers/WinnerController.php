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
  public function actionManageCategory($response = array()) {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $category = new Category();
    $message = array();
    $categoryDetail = array();
    $contestInfo = array();
    $contestDetail = array();
    $contest = new ContestAPI();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $contestInfo = $contest->getContestDetailByContestSlug();
    if (array_key_exists('startDate', $contestInfo) && !empty($contestInfo['startDate'])) {
      $contestInfo['startDate'] = date('Y-m-d', strtotime($contestInfo['startDate']));
    }
    if (array_key_exists('endDate', $contestInfo) && !empty($contestInfo['endDate'])) {
      $contestInfo['endDate'] = date('Y-m-d', strtotime($contestInfo['endDate']));
    }
    if (!empty($contestInfo)) {
      $contestDetail['title'] = $contestInfo['contestTitle'];
      $contestDetail['slug'] = $contestInfo['contestSlug'];
    }
    if (array_key_exists('contestId', $contestInfo) && !empty($contestInfo['contestId'])) {
      $category->contestId = $contestInfo['contestId'];
    }
    if (array_key_exists('contestDetail', $_POST)) {
      try {
        if (array_key_exists('categoryName', $_POST) && empty($_POST['categoryName'])) {
          throw new Exception(Yii::t('contest','Category name can not be empty'));
        }
        $category->categoryName = trim($_POST['categoryName']);
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
    if (empty($message)) {
      if(array_key_exists('msg', $response)) {
        $message['msg'] = $response['msg'];
      }      
    }
    $this->render('manageCategory', array('categories' => $categoryDetail, 'contest' => $contestDetail, 'message' => $message));
  }

  /**
   * actionManageWinner
   * 
   * This function is used for manage winner for each category
   */
  public function actionManageWinner($winnerPage = false) {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $winnerEntries = array();
    $categoryInfo = array();
    $contest = array();
    $contestWinner = array();
    $WinnerDetail = array();
    $winnerWeight = '';
    $msg = '';

    if (!empty($_POST)) { 
      $response = $this->actionUpdateCategoryEntry();
      if (array_key_exists('success', $response) && !$response['success']) {
        if ($winnerPage) {
          $msg = $response['msg'];
        } else {
          $this->actionAddWinnerInCategory($response['msg']);
          exit;
        }
      }
    }
    $categoryInfo = $this->getCategoryDetail();
    $contestWinner = $this->prepareWinner($categoryInfo);
    if(array_key_exists('winner', $contestWinner) && (!empty($contestWinner['winner']))) {
      $WinnerDetail =  $contestWinner['winner'];
    }
    if(array_key_exists('winnerWeight', $contestWinner) && (!empty($contestWinner['winnerWeight']))) {
      $winnerWeight =  implode(',',$contestWinner['winnerWeight']);
    }
    $this->render('manageWinner', array('category' => $categoryInfo,
        'entries' => $WinnerDetail,
        'winnerWeight'=> $winnerWeight,
        'msg' => $msg));
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
        $tags = array();
        if (array_key_exists('tag', $_POST) && (!empty($_POST['tag']))) {
          $tags = unserialize($_POST['tag']);
        }
        if (array_key_exists('category', $_POST) && (!empty($_POST['category']))) {
          $aggregatorManager->category = $_POST['category'];
        }
        if (is_array($tags) && !empty($tags)) {
          foreach ($tags  as $key => $tag) {
            if (array_search('http://ahref.eu/contest/schema/contest/prize', $tag) !== false) {
              unset($tags[$key]);
            }
            if (array_search('http://ahref.eu/contest/schema/contest/winner', $tag) !== false) {
              unset($tags[$key]);
            }
          }          
        }
        $aggregatorManager->tags = $tags;
        $response = $aggregatorManager->updateEntry();
        if (array_key_exists('success', $response) && $response['success']) {
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
      $entryCount = '';
      $offset = 0;
      $winnerEntry = array();
      $categoryInfo = $this->getCategoryDetail();
      if (array_key_exists('contest_slug',$categoryInfo) && !empty($categoryInfo['contest_slug'])) {
        $contestSlug = $categoryInfo['contest_slug'];
      }
      if (array_key_exists('category_name',$categoryInfo) && !empty($categoryInfo['category_name'])) {
        $categorySlug = sanitization($categoryInfo['category_name']);
      }
      if (array_key_exists('offset',$_GET) && !empty($_GET['offset'])) {
        $offset = $_GET['offset'];
      }
      if (!empty($categorySlug)) {
        $winnerEntry = $this->prepareEntryForWinner($contestSlug, $categorySlug, $offset);
      }
      if(array_key_exists('entry',$winnerEntry) && !empty($winnerEntry['entry'])){
        $entries = $winnerEntry['entry']; 
      }
      if(array_key_exists('prize_weight',$winnerEntry) && !empty($winnerEntry['prize_weight'])){
        $prizeWeight = $winnerEntry['prize_weight'];
      }
      if(array_key_exists('count',$winnerEntry) && !empty($winnerEntry['count'])){
        $entryCount = $winnerEntry['count'];
      }
     
      if(!empty($prizeWeight)) {
        $winnerWeight = implode(',',$prizeWeight); 
      }      
    } catch (Exception $e) {
      $message['success'] = false;
      $message['msg'] = $e->getMessage();
    }  
    //check for ajax request
    if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
      $return = array('success' => false, 'msg' => '', 'data' => array());     
      if (!empty($entries)) {
        $return['success'] = true;
        $return['data']['entry'] = $entries;
        $return['data']['category'] = $categoryInfo['category_name'];
      } else {
        $return['success'] = true;
        $return['msg'] = Yii::t('contest', 'There are no more entry in this category');
      }
      echo json_encode($return);
      exit;
    }
    $this->render('addWinner', array('category' => $categoryInfo, 'entries' => $entries,  'winnerWeight'=>$winnerWeight, 
                                     'msg' => $msg, 'count' => $entryCount ));
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
    $aggregatorManager->sort = 'tag:winner';
    $aggregatorManager->tag =  $contestSlug . '{http://ahref.eu/contest/schema/},winner,' . $categorySlug . '{http://ahref.eu/schema/contest/category}';
    $entries = $aggregatorManager->getWinnerEntry();
    foreach ($entries as $entry) { 
      $WinnerEntry = array();
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
        foreach($entry['tags'] as $tag) {
          if($tag['scheme'] == 'http://ahref.eu/contest/schema/contest/prize') {
            $WinnerEntry['prizeName'] = $tag['name'];
          }
          if($tag['scheme'] == 'http://ahref.eu/contest/schema/contest/winner') {
            $WinnerEntry['prizeWeight'] = $tag['weight'];
          }
        }
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
    $categoryInfo = array();
    $contest = new ContestAPI();
    if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
      $contest->contestSlug = $_GET['slug'];
    }
    $contestInfo = $contest->getContestDetailByContestSlug();
    if (array_key_exists('startDate', $contestInfo) && !empty($contestInfo['startDate'])) {
      $contestInfo['startDate'] = date('Y-m-d', strtotime($contestInfo['startDate']));
    }
    if (array_key_exists('endDate', $contestInfo) && !empty($contestInfo['endDate'])) {
      $contestInfo['endDate'] = date('Y-m-d', strtotime($contestInfo['endDate']));
    }
    if (array_key_exists('winnerStatus', $contestInfo) && empty($contestInfo['winnerStatus']))  {
      $this->redirect(BASE_URL);
    }
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

  /**
   * actionUpdateWinner
   * 
   * This function is used for update winner
   */
  public function actionUpdateWinner() {
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    $winnerPage = true;
    $this->actionManageWinner($winnerPage);
  }

  /**
   * actionDeleteWinner
   * 
   * This function is used for delete winner
   */
  public function actionDeleteWinner() {
    $contestWinner = array();
    $updateTag = array();
    if (isset($_POST)) {
      $aggregator = new AggregatorManager();
      if (array_key_exists('id', $_POST) && !empty($_POST['id'])) {
        $aggregator->entryId = $_POST['id'];
      }
      if (array_key_exists('tag', $_POST) && !empty($_POST['tag'])) {
        $tags = unserialize($_POST['tag']);
        foreach ($tags as $tag) {
          if ($tag['scheme'] != 'http://ahref.eu/contest/schema/contest/prize' && $tag['scheme'] != 'http://ahref.eu/contest/schema/contest/winner') {
            $updateTag[] = $tag;
          }
        }
        $aggregator->tags = $updateTag;
      }
      $response = $aggregator->updateEntry();
      $this->redirect(BASE_URL.'admin/category/winner/'. $_GET['id']);
    }
  }

  /**
   * prepareWinner
   * 
   * This function is used for prepare winner (preapare array of neccessary value)
   * @param $categoryInfo
   * @return $contestWinner
   */
  public function prepareWinner($categoryInfo) {
    $contestWinner = array();
    $winnerWeight = array();
    if (array_key_exists('category_name', $categoryInfo) && !empty($categoryInfo['category_name'])) {
      $categorySlug = sanitization($categoryInfo['category_name']);
    }
    if (!empty($categoryInfo)) {
      $winnerEntries = $this->getWinner($categoryInfo['contest_slug'], $categorySlug);
    }
    foreach ($winnerEntries as $entry) {
      if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
        $winner['tag'] = serialize($entry['tags']);
        foreach ($entry['tags'] as $tag) {
          if ($tag['slug'] == 'winner') {
            $winner['winnerWeight'] = $tag['weight'];
              $winnerWeight[] =  $tag['weight'];
          }
          if ($tag['scheme'] == 'http://ahref.eu/contest/schema/contest/prize') {
            $winner['prize'] = $tag['name'];
          }
        }
      }
      $winner['image'] = $entry['image'];
      $winner['author'] = $entry['author'];
      $winner['title'] = $entry['title'];
      $winner['id'] = $entry['id'];
      $contestWinner['winner'][] = $winner;
    }
    $contestWinner['winnerWeight'] = $winnerWeight;
    return $contestWinner;
  }

  /**
   * actionUpdateCategory
   * 
   * This function is used for update category
   */
  public function actionUpdateCategory() {
    $response = array();
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    try {         
      $category = new Category();
      if (array_key_exists('slug', $_GET) && !empty($_GET['slug'])) {
        $slug = $_GET['slug'];
      }
      if (empty($_POST)) {
        $this->redirect(BASE_URL . 'admin/category/' . $slug);
      }
      if (array_key_exists('name', $_POST) && empty($_POST['name'])) {
        throw new Exception(Yii::t('contest', 'Category name can not be empty'));
      }      
      if (array_key_exists('id', $_POST) && !empty($_POST['id'])) {
        $category->categoryId = $_POST['id'];
      }
      $category->categoryName = trim($_POST['name']);
      $updateCategory = $category->update();
      if (!isset($updateCategory)) {
        throw new Exception(Yii::t('contest', 'Some technical problem occurred. Please try again'));
      }
    } catch (Exception $e) {
      $response['success'] = false;
      $response['msg'] = $e->getMessage();
    }
    $this->actionManageCategory($response);
  }
  
  /**
   * actionDeleteCategory
   * 
   * This function is used for delete an existing conteategory
   */
  public function actionDeleteCategory() {
    try {
      $isAdmin = isAdminUser();
      if (!$isAdmin) {
        $this->redirect(BASE_URL);
      }
      $response = array();
      $category = new Category();
      if (array_key_exists('id', $_GET) && (!empty($_GET['id']))) {
        $category->categoryId = $_GET['id'];
        $deleteCategory = $category->delete();
        if (!isset($deleteCategory)) {
          throw new Exception(Yii::t('contest', 'Some technical problem occurred. Please try again'));
        }
      }
    } catch (Exception $e) {
      $response['success'] = false;
      $response['msg'] = $e->getMessage();
    }
    $this->actionManageCategory($response);
  }
  
  /**
   * prepareEntryForWinner
   * 
   * This function is used for prepare entry(that is not winner)
   * @param int $offset
   * @param int $categorySlug
   * @param $contestSlug
   * @return $winnerEntry 
   */
  private function prepareEntryForWinner($contestSlug, $categorySlug, $offset = 0) {
    $winnerEntries = array();
    if (!empty($categorySlug)) {   
      $contestSubmission = array();
      $weight = '';
      $contest = new Contest();
      $contest->tags = $contestSlug . '{http://ahref.eu/contest/schema/},' . $categorySlug . '{http://ahref.eu/schema/contest/category}';
      $contest->sort = 'tags';
      $contest->offset = $offset;
      $contestSubmission = $contest->getContestSubmissionForCategory();
      if (!empty($contestSubmission)) {
        $countFromEntries = end($contestSubmission);
        if (array_key_exists('count', $countFromEntries)) {
          $winnerEntries['count'] = $countFromEntries['count'];
        }       
        foreach ($contestSubmission as $submission) {
          $entry = array();
          
          //check whether winner already exist or not
          if (array_key_exists('tags', $submission) && !empty($submission['tags'])) {
            $weight = $this->checkWinner($submission['tags']);
            if (!empty($weight)) {
              $winnerEntries['prize_weight'][] = $weight;
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
            $winnerEntries['entry'][] = $entry;
          }
        }
      }
    }
    return $winnerEntries;
  }  
  
  /**
   * actionAddWinner
   * function is used for manage add winner functionality for contest
   */
  public function actionAddWinner() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    if (!array_key_exists('slug', $_GET) || empty($_GET['slug'])) { 
      $this->redirect(BASE_URL);
    }
    switch($_GET['slug']) {
      case FALLING_WALLS_CONTEST_SLUG :  
        $controller = new FallingwallsController('fallingwalls');
        $controller->actionEntriesForWinner();
        break;
      default :
        Yii::log('Error in actionAddWinner ', ERROR, ' Unknown contest slug');
        $this->redirect(BASE_URL);
    }
  }
  
  public function actionWinner() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    if (!array_key_exists('slug', $_GET) || empty($_GET['slug'])) {
      $this->redirect(BASE_URL);
    }
    switch ($_GET['slug']) {
      case FALLING_WALLS_CONTEST_SLUG :
        $controller = new FallingwallsController('fallingwalls');
        $controller->actionWinner();
        break;
      default :
        Yii::log('Error in actionWinner ', ERROR, ' Unknown contest slug');
        $this->redirect(BASE_URL);
    }
  }
  
  
  /**
   * actionDeleteContestWinner 
   * This function is used for delete winner
   */
   public function actionDeleteContestWinner() { 
    $isAdmin = isAdminUser();
    if (!$isAdmin) {
      $this->redirect(BASE_URL);
    }
    if (!array_key_exists('slug', $_GET) || empty($_GET['slug'])) {
      $this->redirect(BASE_URL);
    }
    switch ($_GET['slug']) { 
      case FALLING_WALLS_CONTEST_SLUG :
        $controller = new FallingwallsController('fallingwalls');
        $controller->actionDeleteWinner();
        break;
      default :
        Yii::log('Error in actionDeleteContestWinner ', ERROR, ' Unknown contest slug');
        $this->redirect(BASE_URL);
    }
  }
}
