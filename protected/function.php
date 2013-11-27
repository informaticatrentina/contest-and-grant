<?php

function p($value) {
  print '<pre>';
  print_r($value);
  print '</pre>';
  die;
}

function vd($value) {
  print '<pre>';
  var_dump($value);
  print '</pre>';
  die;
}

function generateRandomString($length) {
  $charset = "abcdefghijklmnopqrstuvwxyz";
  $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $charset .= "0123456789";
  $randomStr = '';
  for ($i = 0; $i < $length; $i++) {
    $randomStr .= $charset[(mt_rand(0, (strlen($charset) - 1)))];
  }
  return $randomStr;
}

/**
 * validateDate
 * 
 * This function is used for validate date
 * @param (date) $date
 * return (boolean) - true on valid date
 */
function validateDate($date) {
  if (empty($date)) {
    return false;
  }
  $dateTime = explode(' ', $date);
  $dateArr = explode('/', $dateTime[0]);
  if (count($dateArr)!= 3) {
    return false;
  }
  return checkdate($dateArr[0], $dateArr[1], $dateArr[2]);
}

/**
 * uploadFile
 * 
 * This function is used for uplaod file (image, text)
 * @param (string) $directory
 * @param (string) $name
 * @return (string) $imageUrl
 */
function uploadFile($directory, $name) {
  $image = CUploadedFile::getInstanceByName($name);
  $imageInfo = pathinfo($image->getName());
  if(array_key_exists('filename', $imageInfo)) {
    $imageName = sanitization($imageInfo['filename'] . generateRandomString(10)) . '.' . $imageInfo['extension'];
  } 
  if (empty($imageName)) {
    return '';
  }
  $imagePath = $directory . $imageName;
  $imageUrl = BASE_URL. $imagePath;
  $ret = $image->saveAs($imagePath);
  if (!$ret) {
    $imageName = '';
  }
  return $imageName;
}

/**
 * Function to resize image
 */
function resizeImageByPath($imagePath, $width, $height,  $crop = 0, $resizeBy = '') {
    if (empty($imagePath)) {
      return false;
    }
    $resizeWidth = $width;
    $resizeHeight = $height;
    if ($crop == 1) {
      $resizeWidth = $width + 50;
      $resizeHeight = $height + 50;
    }
    $imageInfo = pathinfo($imagePath);
    $resultImage = $imagePath;
    if (!empty($imageInfo)) {
      $resizeDirectoryName = $imageInfo['dirname'] .'/resize';
      if (!is_dir($resizeDirectoryName)) {
        Yii::log('', ERROR, Yii::t('contest', 'resize folder does not exists in ') . $resizeDirectoryName);
        return $resultImage;
      }
      $resizedImageName = $imageInfo['filename'] .'_r_' .$width .'_'.$height .'.' .$imageInfo['extension'];
      $resizedImageAbPath = dirname(__FILE__) . '/../' . $resizeDirectoryName . '/'. $resizedImageName;
      if (!file_exists($resizedImageAbPath)) {
        $imageResize = Yii::app()->image->load($imagePath);
        switch($resizeBy) {
          case 'none':
            $imageResize->resize($resizeWidth, $resizeHeight, Image::NONE);
            break;
          case 'height':
            $imageResize->resize($resizeWidth, $resizeHeight, Image::HEIGHT);
            break;
          case 'width':
            $imageResize->resize($resizeWidth, $resizeHeight, Image::WIDTH);
            break;
          default : 
            $imageResize->resize($resizeWidth, $resizeHeight, Image::WIDTH);
            break;
        } 
        $imageResize->save($resizedImageAbPath);
      }
      $resultImage = $resizeDirectoryName . '/' . $resizedImageName;
      if ($crop == 1) {
        $imageCrop = Yii::app()->image->load($resultImage);
        $imageCrop->crop($width, $height);
        $imageCrop->save($resizedImageAbPath);
        $resultImage = $resizeDirectoryName . '/' . $resizedImageName;
      }
    return $resultImage;
  }
}

/**
 * Function to check weather current user is logged in or not
 */
function userIsLogged() {
    $flag = false;
    if (isset(Yii::app()->session['user'])) {
        $flag = true;
    }
     return $flag;
}

/**
 * function to get tweets of fondazioneahref
 * 
 * @return tweets
 */
function getTweets($userName) {
    if (!empty($userName)) {
        $tweets = '';
        $tweets_result = file_get_contents("https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=" . $userName . "&count=2");
        $data = json_decode($tweets_result);
        foreach ($data as $tweet) {
            $time1 = strtotime($tweet->created_at);
            $present = time();
            $diff = $present - $time1;
            $days = floor($diff / 86400);
            $hours = floor(($diff - ($days * 86400)) / 3600);
            $content = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a class="footer-link3" href="http://twitter.com/$2">@$2</a>', $tweet->text);
            $tweets.= '<div class="tweetbox"><p class="tweet">' . $content . '</p><span class="tweettime">' . $days . ' giorni,  ' . $hours . ' ore fa</span></div><div style="margin-top:5px;"></div>';
        }
        return $tweets;
    }
}

/**
 * isAdminUser 
 * 
 * This function is used for check whether user is admin or not
 * @return boolean
 */
function isAdminUser() {
  $isAdmin = false;
  $adminUsers = array();
  if (defined('CONTEST_ADMIN_USERS')) {
    $adminUsers = json_decode(CONTEST_ADMIN_USERS, true);
  } 
  if (isset(Yii::app()->session['user'])) { 
    if (in_array(Yii::app()->session['user']['email'], $adminUsers)) {
      $isAdmin = true;
    }
  } 
  return $isAdmin;
}

/**
 * getFirstContest
 * 
 * This function is used for return contest  slug
 * @return (string) contest slug
 */
function getFirstContest() {
  $contestSlug = '';
  $contest = new Contest();
  $contestInfo = $contest->getContestDetail();
  if (is_array($contestInfo) && !empty($contestInfo)) {
    $contestInfo = array_shift($contestInfo);
    $contestSlug = $contestInfo['contestSlug'];
  }
  return $contestSlug;
}

/**
 * sanitization
 * 
 * This function is used for convert a string in santized string
 * @param $string
 * @return  $sanitizeStr
 */
function sanitization($string){
  $sanitizeStr = '';
  if (!empty($string)) {
    $sanitizeStr = strtolower(preg_replace("/[^a-z0-9]+/i", "_", $string));
  }
  return $sanitizeStr;
}

/**
 * setFileUploadError
 * 
 * set error message for upload file
 * @param numeric $errorCode 
 * @return string message
 */
function setFileUploadError($errorCode) {
  $msg = '';
  switch($errorCode) {
    case 1:
      $msg = 'The uploaded file exceeds the upload file size limit '.ini_get('upload_max_filesize') .  'B';
      break;
    case 3:
      $msg = 'The uploaded file was only partially uploaded';
      break;
    case 4:
      $msg = 'file was not uploaded ';
      break;
    case 6:
      $msg = 'Missing a temporary folder';
      break;
    case 7:
      $msg = 'Failed to write file to disk';
      break;
    default:
      $msg = 'Some error occured in file uploading';
      break;      
  }
  return $msg;
}
/**
 * downloadZipFile
 * this function is used for download zip file
 * @param string $file - file path
 */
function downloadZipFile($file) {
  if (file_exists($file)) {
    $pathParts = pathinfo($file);
    $ext = strtolower($pathParts['extension']);
    if ($ext != 'zip') {
      throw new Exception(Yii::t('contest', 'You have not permission for downlopad this file.'));
      Yii::log('Error in downloadZipFile ', ERROR, 'This is not zip file' . $file);
    }
    header("Content-Type: application/zip");
    header('Content-Disposition: attachment; filename="' . $pathParts['basename'] . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
  } else {
    Yii::log('Error in downloadZipFile ', ERROR, 'This zip file does not exist - ' . $file);
    throw new Exception(Yii::t('contest', 'Some error occur in downloading file. Please try again'));
  }
}

/**
 * function for get actual dimention of image
 */
function getImageDimension($imageUrl) {
  $dimension = array('width' => 600, 'height' => 450);
  if (!empty($imageUrl)) {
    list($width, $height, $type, $attr) = getimagesize($imageUrl);
    $dimension['width'] = $width;
    $dimension['height'] = $height;
  }
  return $dimension;
}

/**
 * getContestList
 * functon is used for get contest detail
 * @return array $contestList (contest title, slug)
 */
function getContestList() {
  $contest = new Contest();
  $contestList = array();
  $contestDetail = $contest->getContestDetail();
  foreach($contestDetail as $list) {
    $contestArr = array();
    $contestArr['slug'] = $list['contestSlug'];
    $contestArr['title'] = $list['contestTitle'];
    $contestList[] = $contestArr;
  }
  return $contestList;
}

/**
 * getRoles
 * function is used for getting all roles of user
 * @return array $roles
 */
function getRoles() { 
  $roles = array();
  $designation = array();
  if (!isset(Yii::app()->session['user'])) { 
    return $roles;
  } 
  if (isAdminUser()) {
    $roles[] = 'admin';
  }
  $jury = new Jury();
  $jury->emailId = Yii::app()->session['user']['email'];
  $juryInfo =   $jury->get();
  foreach ($juryInfo as $info) {
    $roles[] = 'jury_'.$info['designation'];
  }
  return  array_unique($roles);
}

/**
 * getAdminMenuList
 * function is used for get list item for admin menu
 */
function getAdminMenuList() {
  $userInfo = array();
  $roles = getRoles();  
  foreach ($roles as $role) {
    switch ($role) {
      case 'admin':
        $userInfo[] = array('url' => BASE_URL . 'admin/contest/list', 'title' => 'Admin');
        break;
      case 'jury_member':
        $userInfo[] = array('url' => BASE_URL . 'jury/contest', 'title' => 'Jury Member');
        break;      
      default:
        break;
    }
  }
  return $userInfo;
}

/**
 * setMessageTranslationLanguage
 * function is used for set language for translation, default language is it_it
 * @param $slug - slug of contest
 */
function setMessageTranslationLanguage($slug) {
  if (empty($slug)) {
    return '';
  }
  switch($slug) {
    case YOUNG_DESIGNER_CONTEST_SLUG:
      Yii::app()->language = 'en';
      break;
    default:
      Yii::app()->language = SITE_LANGUAGE;
      break;
  }
}