<?php

/**
 * FallingWallsContest
 * 
 * FallingWallsContest class is used  for show, submit entry of falling walls contest
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */
class FallingWallsContest {

  public $slug;
  public $offset = '';
  public $entryId = '';
  public $sort = '-creation_date';
  /**
   * loadContestEntries
   * function is used for get contest submission
   */
  public function loadContestEntries() {
    try {
      $contest = new contest();
      $contest->sort = $this->sort;
      $entryCount = 0;
      $entries = array();
      $countFromEntries = array();
      if ($this->offset && is_numeric($this->offset)) {
        $contest->offset = $this->offset;
      }
      if ($this->entryId) {
        $contest->entryId = $this->entryId;
      }
      if (empty($this->slug)) {
        $this->redirect(BASE_URL);
      }
      $contest->contestSlug = $this->slug;
      $entries = $contest->getContestSubmission();

      //check whether count is exist in entries array or not 
      if (!empty($entries)) {
        $countFromEntries = end($entries);
        if (array_key_exists('count', $countFromEntries)) {
          $entryCount = array_pop($entries);
        }
      }
      $contestSubmissions = array();
      foreach ($entries as $entry) {  
        $videoFlag = false;
        $contestSubmission = array();
        if (array_key_exists('links', $entry) && array_key_exists('enclosures', $entry['links'])) {
          $contestSubmission['videoImagePath'] = DEFAULT_VIDEO_THUMBNAIL;
          foreach ($entry['links']['enclosures'] as $key => $val) {
            if (array_key_exists('type', $val) && $val['type'] == 'video') {
              $contestSubmission['videoImagePath'] = $this->getVideoImage($entry['links']['enclosures'][$key]['uri']);
              if (empty($contestSubmission['videoImagePath'])) {
                Yii::log('', ERROR, 'Failed to load thumbnail image :: url' . $entry['links']['enclosures'][$key]['uri']);
                continue;
              }
              $contestSubmission['video_id'] = $this->getVideoId($entry['links']['enclosures'][$key]['uri']);
              $contestSubmission['url_info'] = $this->getUrlInfo($entry['links']['enclosures'][$key]['uri']);
            }
          }
        } 

        if (array_key_exists('author', $entry) && !empty($entry['author'])) {
          $contestSubmission['author'] = $entry['author'];
        }
        if (array_key_exists('title', $entry) && !empty($entry['title'])) {
          $contestSubmission['title'] = $entry['title'];
        }
        if (array_key_exists('id', $entry) && !empty($entry['id'])) {
          $contestSubmission['id'] = $entry['id'];
        }
        if (array_key_exists('content', $entry) && array_key_exists('description', $entry['content']) && 
          !empty($entry['content'])) {
          $contestSubmission['description'] = $entry['content']['description'];
        }
        if (array_key_exists('tags', $entry) && !empty($entry['tags'])) {
          $contestSubmission['tags'] = $entry['tags'];
        }
        $contestSubmissions[] = $contestSubmission;
      }
    } catch (Exception $e) {
      Yii::log('', ERROR, $e->getMessage());
    }
    return array('contest_submission' => $contestSubmissions, 'entry_count' => $entryCount);
  }

  /**
   * getVideoImage
   * function is  used for get thumbnail image using video id
   * @param string $videoUrl
   * @return string $videoImagePath
   */
  public function getVideoImage($videoUrl) {
    if (empty($videoUrl)) {
      return '';
    }
    $videoImageUrl = '';
    $videoId = $this->getVideoId($videoUrl);
    if (empty($videoId)) {
      throw new Exception(Yii::t('contest', 'Url does not contain id ') . $videoUrl);
    }
    $videoImageName = 'fallingWall_' . $videoId . '.jpg';
    $videoImagePath = UPLOAD_DIRECTORY . FALLING_WALL_IMAGE_DIRECTORY . $videoImageName;
    $videoImageDirPath = dirname(__FILE__) . '/../../' . $videoImagePath;
    if (!file_exists($videoImageDirPath)) {
      $urlInfo = $this->getUrlInfo($videoUrl);
      if (array_key_exists('type', $urlInfo) && !empty($urlInfo['type'])) {
        switch ($urlInfo['type']) {
          case YOUTUBE:
            $videoImageUrl = YOUTUBE_VIDEO_IMAGE_URL . $videoId . '/hqdefault.jpg';
            file_put_contents($videoImageDirPath, file_get_contents($videoImageUrl));
            break;
          case VIMEO:
            $videoImageUrl = unserialize(file_get_contents(VIMEO_VIDEO_IMAGE_URL . $videoId . '.php'));
            if (array_key_exists(0, $videoImageUrl) && array_key_exists('thumbnail_medium', $videoImageUrl[0])) {
              file_put_contents($videoImageDirPath, file_get_contents($videoImageUrl[0]['thumbnail_large']));
            } else {
              $videoImagePath = '';
            }
            break;
          default :
            Yii::log('getVideoImage ::', ERROR, 'Neither youtube nor vimeo url ::' . $videoUrl);
        }
      }
    }
    return $videoImagePath;
  }

  /**
   * getUrlInfo
   * return $urlInfo (host, scheme, type)
   */
  public function getUrlInfo($videoUrl) {
    $urlInfo = array();
    if (empty($videoUrl)) {
      return $urlInfo;
    }
    $urlInfo = parse_url($videoUrl);
    $type = '';
    if (array_key_exists('host', $urlInfo)) {
      if (strpos($urlInfo['host'], 'youtube') !== false) {
        $type = YOUTUBE;
      } else if (strpos($urlInfo['host'], 'vimeo') !== false) {
        $type = VIMEO;
      }
    }
    $urlInfo['type'] = $type;
    return $urlInfo;
  }

  /**
   * getVideoId
   * function is used to get video id from url
   * @param string $videoUrl
   * @return string $videoId
   */
  public function getVideoId($videoUrl) {
    $videoId = '';
    if (empty($videoUrl)) {
      throw new Exception(Yii::t('contest', 'Video url can not be empty'));
    }
    if (!preg_match("/^(https?:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/", trim($videoUrl))) {
      throw new Exception(Yii::t('contest', 'Please enter valid url'));
    }
    $urlInfo = $this->getUrlInfo($videoUrl);

    if (array_key_exists('type', $urlInfo) && !empty($urlInfo['type'])) {
      switch ($urlInfo['type']) {
        case YOUTUBE:
          $videoUrlPart = explode("?v=", $videoUrl);
          if (!array_key_exists(1, $videoUrlPart) || empty($videoUrlPart[1])) {
            $videoUrlPart = explode("/v/", $videoUrl);
          }
          if (array_key_exists(1, $videoUrlPart) && !empty($videoUrlPart[1])) {
            $videoUrlPart = explode("&", $videoUrlPart[1]);
            $videoId = $videoUrlPart[0];
          }
          break;
        case VIMEO:
          $videoId = substr($urlInfo['path'], 1);
          break;
      }
    }
    return $videoId;
  }

  /**
   * loadSingleContestEntries
   * load single contest entry detail
   */
  public function loadSingleContestEntries() {
    $entries = array();
    $entry = array();
    $contestEntries = $this->loadContestEntries();
    if (array_key_exists('contest_submission', $contestEntries) && array_key_exists(0, $contestEntries['contest_submission'])) {
      $entries = $contestEntries['contest_submission'][0];
    }

    if (!empty($entries)) {
      $entry['title'] = $entries['title'];
      if (array_key_exists('description', $entries)) {
        $entry['description'] = $entries['description'];
      }
      $entry['author_name'] = $entries['author']['name'];
      $entry['video_image_url'] = $entries['videoImagePath'];
      if (array_key_exists('video_id', $entries)) {
        $entry['video_id'] = $entries['video_id'];
      }
      if (array_key_exists('url_info', $entries) && array_key_exists('type', $entries['url_info'])) {
        $entry['video_domain'] = $entries['url_info']['type'];
      }
      $entry['url'] = BASE_URL . 'contest/entries/' . $this->slug . '/' . $_GET['id'];
      $entry['winner'] = $this->checkForWinner($entries['tags']);
      if (!empty($entry['winner'])) {
        foreach ($entries['tags'] as $tag) {
          if ($tag['scheme'] == PRIZE_TAG_SCHEME) {
            $entry['prize_title'] = $tag['name'];
          }
        }
      }
    }
    return $entry;    
  }

  /**
   * loadWinnerEntries
   * function is used for loading winners
   * @return array winnerEntries, winnerWeight 
   */
  public function loadWinnerEntries() {
    $entries = array();
    $winnerEntries = array();
    $winnerWeight = array();
    $aggregatorManager = new AggregatorManager();
    $aggregatorManager->returnField = 'links,author,title,id,tags';
    $aggregatorManager->contestSlug = $_GET['slug'];
    $aggregatorManager->sort = 'tag:winner';
    $aggregatorManager->tag =  $_GET['slug'] . '{'. CONTEST_TAG_SCHEME .'},winner';
    $entries = $aggregatorManager->getWinnerEntry();
    if (empty($entries)) {
      throw new Exception(Yii::t('contest', 'There is no entry in this contest'));
    }
    foreach ($entries as $entry) { 
      if (array_key_exists('links', $entry) && array_key_exists('enclosures', $entry['links']) && !empty($entry['links']['enclosures'])) {
        $entry['videoImagePath'] = DEFAULT_VIDEO_THUMBNAIL;
        foreach ($entry['links']['enclosures'] as $key => $val) {          
          if (array_key_exists('type', $val) && $val['type'] == 'video') {
            $entry['videoImagePath'] = $this->getVideoImage($entry['links']['enclosures'][$key]['uri']);
            if (!$entry['videoImagePath']) {
              Yii::log('', ERROR, 'Error in loadWinnerEntries - Failed to load thumbnail image :: url '. $entry['links']['enclosures'][$key]['uri']);
            }
          }
        }        
      }
      $winnerWt = '';
      if (array_key_exists('tags', $entry)) {
        $winnerWt = $this->checkForWinner($entry['tags']);
        if (!empty($winnerWt)) {
          foreach ($entry['tags'] as $tag) {
            if ($tag['scheme'] == PRIZE_TAG_SCHEME) {
              $entry['prize'] = $tag['name'];
            }
            if ($tag['scheme'] == WINNER_TAG_SCHEME) {
              $entry['prize_weight'] = $tag['weight'];
            }
          }
          $winnerEntries[] = $entry;
          $winnerWeight[] = $winnerWt;
        }
      }
    }
    return array('winner_entry' => $winnerEntries, 'winner_weight' => $winnerWeight);
  }
  
  /**
   * loadNonWinnerEntries
   * function ios used for load entry that does contain winner tag
   * @return array nonWinnerEntries, winnerWeight
   */
  public function loadNonWinnerEntries() {
    $entries = array();
    $nonWinnerEntries = array();
    $winnerWeight = array();
    $entryCount = 0;
    $this->slug = $_GET['slug'];
    $entries = $this->loadContestEntries();
    if (!array_key_exists('contest_submission', $entries) || empty($entries['contest_submission'])) {
      throw new Exception(Yii::t('contest', 'There is no entry in this contest'));
    }
    foreach ($entries['contest_submission'] as $entry) {
      $winnerWt = '';
      if (array_key_exists('tags', $entry)) {
        $winnerWt = $this->checkForWinner($entry['tags']);
        if (empty($winnerWt)) {
          $nonWinnerEntries[] = $entry;
        } else {          
          $winnerWeight[] =  $winnerWt;
        }       
      }
    }
    if (array_key_exists('entry_count', $entries) && array_key_exists('count', $entries['entry_count'])) {
      $entryCount =  $entries['entry_count']['count'];
    }
    return array('non_winner_entry' => $nonWinnerEntries, 'winner_weight' => $winnerWeight, 'entry_count' => $entryCount);
  }
  
  /**
   * saveWinner
   * function is used for update entry as winner
   * @return array - response of update entry
   */
  public function saveWinner() { 
    $postData = $_POST;
    if (array_key_exists('id', $postData) && empty($postData['id'])) {
      throw new Exception('Entry is empty');
    }
    if (array_key_exists('prize', $postData) && empty($postData['prize'])) {
      throw new Exception('Prize name is empty');
    }
    if (array_key_exists('weight', $postData) && empty($postData['weight'])) {
      throw new Exception('Prize weight is empty');
    }
   
    $aggregator = new AggregatorManager();
    $aggregator->prize = $postData['prize'];
    $aggregator->prizeWeight = $postData['weight'];
    $aggregator->tags = $this->getEntryTags($postData['id']);
    $aggregator->entryId = $postData['id'];
    return $aggregator->updateEntry();
  }

  /**
   * checkForWinner
   * function is used for check wether winner tag exist in entry or not
   * @param array $tags   - entry  tag
   * @retrn int $winnerWeight 
   */
  private function checkForWinner($tags) {
    $winnerWeight = '';
    if (!is_array($tags) || empty($tags)) {
      return $winnerWeight;
    } 
    foreach ($tags as $tag) {
      if ($tag['scheme'] == WINNER_TAG_SCHEME) {
        $winnerWeight = $tag['weight'];
        break;
      }
    }
    return $winnerWeight;
  }
  
  /**
   * getEntryTags
   * function is used for loading tags for an entry
   * @param string  $entryId  
   * @return array $tags  
   */
  public function getEntryTags($entryId, $slug = '') {
    $entryInfo = array();
    $tags = array();
    if (empty($entryId)) {
      return $tags;
    }
    $contest = new Contest();
    $contest->entryId = $entryId;
    if (empty($slug)) {
      $contest->contestSlug = $_GET['slug'];
    } else {
      $contest->contestSlug = $slug;
    }
    $entryInfo = $contest->getContestSubmissionInfo();
    if (array_key_exists('tags', $entryInfo) && !empty($entryInfo['tags'])) {
      $tags = $entryInfo['tags'];
    }
    return $tags;
  }
}