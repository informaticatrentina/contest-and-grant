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
  $dateArr = explode('/', $date);
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
  $imageName = $imageInfo['filename'] . generateRandomString(10) . '.' . $imageInfo['extension'];
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
function resizeImageByPath($imagePath, $width, $height) {
    if (empty($imagePath)) {
      return false;
    }
    $imageInfo = pathinfo($imagePath);
    $resultImage = $imagePath;
    if (!empty($imageInfo)) {
      $resizeDirectoryName = $imageInfo['dirname'] .'/resize';
      if (!is_dir($resizeDirectoryName)) {
        Yii::log('', ERROR, Yii::t('contest', 'resize folder does not exists in ' . $resizeDirectoryName));
        return $resultImage;
      }
      $resizedImageName = $imageInfo['filename'] .'_r_' .$width .'_'.$height .'.' .$imageInfo['extension'];
      $resizedImageAbPath = dirname(__FILE__) . '/../' . $resizeDirectoryName . '/'. $resizedImageName;
      if (!file_exists($resizedImageAbPath)) {
        $imageResize = Yii::app()->image->load($imagePath);
        $imageResize->resize($width, $height, Image::WIDTH);
        $imageResize->save($resizedImageAbPath);
      }
      $resultImage = $resizeDirectoryName . '/' . $resizedImageName;
    }
    return $resultImage;
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