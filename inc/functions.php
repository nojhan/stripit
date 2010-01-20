<?php
/**
 * This file contains the main functions
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 */


/**
 * Overload the autoload for don't use the require or include in the main PHP file
 *
 * @param string $class_name The name of the class to load
 */
function __autoload($class_name)
{
  if ($class_name === 'Config') {
    require_once dirname(__FILE__).'/config/config.php';
  } else {
    require_once dirname(__FILE__).'/class/'.strtolower($class_name).'.class.php';
  }
}


/**
 * Return the Lang object initialized the language wanted
 * If the language wanted doesn't exist, we use the default language
 *
 * @param string $culture The i18n code of the language
 * @return Lang the Lang object initialized the language wanted
 */
function getLang($culture)
{
  $filename = dirname(__FILE__).'/lang/'.$culture.'.php';
  if (file_exists($filename) === false) {
    $filename = dirname(__FILE__).'/lang/'.Config::getLanguage().'.php';
  }
  
  require_once $filename;
  
  return new Lang();
}


/**
 * Return an array with all navigation link
 *
 * @param integer $id The id of the actual strip
 * @param integer $last The last id of the strip cache
 * @param Lang $lang The Lang object use for the actual user
 * @return array An array with the navigation link : array(first, last, previous, next, gallery)
 */
function getNavigation($id, $last, Lang $lang)
{
  $url = Config::getUrl().'/'.Config::getIndex().'?id=';
  $nav_lang = '';
  if (isset($_GET['lang'])) {
    $nav_lang = '&lang='.$lang;
  }
  
  $return[0] = $url.'0'.$nav_lang;
  
  $return[1] = $url.$last.$nav_lang;
  
  if ($id != 0) {
    $return[2] = $url.($id - 1).$nav_lang;
  } else {
    $return[2] = $url.'0'.$nav_lang;
  }
  
  if ($id != $last) {
    $return[3] = $url.($id + 1).$nav_lang;
  } else {
    $return[3] = $url.$last.$nav_lang;
  }
  
  $return[4] = Config::getUrl().'/'.Config::getGallery().'?page='.floor($id / Config::getThumbsPerPage()).'&limit='.Config::getThumbsPerPage().$nav_lang;
  
  return $return;
}


/**
 * Return the permanent link for the strip
 *
 * @param integer $id The id of the actual strip
 * @param Lang $lang The Lang object use for the actual user
 * @return string The permanent link for the actual strip
 */
function getPermanentLink($id, Lang $lang)
{
  $url = Config::getUrl().'/'.Config::getIndex().'?id=';
  $nav_lang = '';
  if (isset($_GET['lang'])) {
    $nav_lang = '&lang='.$lang;
  }
  
  return $url.$id.$nav_lang;
}


/**
 * Return an array with all navigation link
 *
 * @param integer $page The actual page number of the gallery
 * @param integer $last_page The last page of the gallery
 * @param integer $limit The number of strip in one page
 * @param Lang $lang The Lang object use for the actual user
 * @return array An array with the navigation link : array(first, last, previous, next)
 */
function getNavigationGallery($page, $last_page, $limit, Lang $lang)
{
  $url = Config::getUrl().'/'.Config::getGallery().'?limit='.$limit.'&page=';
  $nav_lang = '';
  if (isset($_GET['lang'])) {
    $nav_lang = '&lang='.$lang;
  }
  
  $return[0] = $url.'0'.$nav_lang;
  
  $return[1] = $url.$last_page.$nav_lang;
  
  if ($page != 0) {
    $return[2] = $url.($page - 1).$nav_lang;
  } else {
    $return[2] = $url.'0'.$nav_lang;
  }
  
  if ($page != $last_page) {
    $return[3] = $url.($page + 1).$nav_lang;
  } else {
    $return[3] = $url.$last_page.$nav_lang;
  }
  
  return $return;
}


/**
 * This function check if a directory exist or create it
 *
 * @param string $dir The directory path to check
 * @return boolean True if the directory exist, False if the directory doesn't exist and fail to create
 */
function checkDirAndCreate($dir)
{
  if (is_dir($dir) === false) {
    // create the directory
    return mkdir($dir, '0777', true);
  }
  
  return true;
}


/**
 * This function create, if necessary, an thumbnail of a strip
 *
 * @param string $src The original path of strip
 * @param string $dest The thumbnail path of strip
 * @throws Exception If the folder for the thumbnail doesn't exist, an exception is throwed
 * @return boolean True if the thumbnail is created, False else
 */
function createThumb($src, $dest)
{
  // check if the thumb exist
  if (file_exists($dest) === true) {
    $original_time = filemtime($src);
    $thumbnail_time = filemtime($dest);
    
    // if the thumb is created after the original, it's ok
    if ($original_time < $thumbnail_time) {
      return true;
    }
  }
  
  // check if the directory for thumbnail exist or create it
  if (checkDirAndCreate(Config::getThumbFolder()) === false) {
    throw new Exception('Impossible to create thumbs folder : "'.Config::getThumbFolder().'"');
  }
  
  // calculate the width and height of thumbnail
  $width = Config::getThumbSize();
  $size = getimagesize($src);
  if ($size[0] > $width) {
    $rapport = $size[0] / $width;
    $height = $size[1] / $rapport;
  } else {
    $width = $size[0];
    $height = $size[1];
  }
  
  $img_src = imagecreatefrompng($src);
  $img_dst = imagecreatetruecolor($width, $height);
  
  // Preserve alpha transparency : thank crash <http://www.php.net/manual/fr/function.imagecopyresampled.php#85038>
  imagecolortransparent($img_dst, imagecolorallocate($img_dst, 0, 0, 0));
  imagealphablending($img_dst, false);
  imagesavealpha($img_dst, true);
  
  $res = imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
  if (!$res) {
    return false;
  }
  
  $res = imagepng($img_dst, $dest);
  if (!$res) {
    return false;
  }
  
  return true;
}