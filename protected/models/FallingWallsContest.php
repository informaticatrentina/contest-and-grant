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

  /**
   * loadContestEntries
   * function is used for get contest submission
   */
  public function loadContestEntries() {
    try {
      $contest = new contest();
      $contest->sort = '-creation_date';
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
        $contestSubmission = array();
        if (array_key_exists('links', $entry) && array_key_exists('enclosures', $entry['links']) && array_key_exists(0, $entry['links']['enclosures'])) {
          if (array_key_exists('uri', $entry['links']['enclosures'][0]) && !empty($entry['links']['enclosures'][0]['uri'])) {
            $contestSubmission['videoImagePath'] = $this->getVideoImage($entry['links']['enclosures'][0]['uri']);
            if (empty($contestSubmission['videoImagePath'])) {
              Yii::log('', ERROR, 'Failed to load thumbnail image :: url' . $entry['links']['enclosures'][0]['uri']);
              continue;
            }
            $contestSubmission['video_id'] = $this->getVideoId($entry['links']['enclosures'][0]['uri']);
            $contestSubmission['url_info'] = $this->getUrlInfo($entry['links']['enclosures'][0]['uri']);
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
        if (array_key_exists('content', $entry) && !empty($entry['content'])) {
          $contestSubmission['description'] = $entry['content']['description'];
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
      $entry['description'] = $entries['description'];
      $entry['author_name'] = $entries['author']['name'];
      $entry['video_image_url'] = $entries['videoImagePath'];
      $entry['video_id'] = $entries['video_id'];
      $entry['video_domain'] = $entries['url_info']['type'];
      $entry['url'] = BASE_URL . 'contest/entries/' . $this->slug . '/' . $_GET['id'];
    }
    return $entry;    
  }

}