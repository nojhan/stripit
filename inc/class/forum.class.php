<?php
/**
 * Class for manage the forum integration
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 */
class Forum
{
  /**
   * Return the word of the day
   *
   * @param Lang $lang The language object is use when the connection with forum isn't ok
   * @access public
   * @static
   * @return string The word of the day or an error message if the connection with forum isn't ok
   */
  public static function getWotd(Lang $lang)
  {
    $url = Config::getFluxbbForum().'/extern.php?action=new&show=1&fid='.Config::getFluxbbWotdId();
    $text = file_get_contents($url);
    
    if ($text === false) {
      $text = $lang->getForumError();
    } else {
      $text = utf8_encode($text);
    }
    return $text;
  }
  
  
  /**
   * Return the word of the day in RSS format
   *
   * @param Lang $lang The language object is use when the connection with forum isn't ok
   * @access public
   * @static
   * @return string The word of the day or an error message if the connection with forum isn't ok
   */
  public static function getWotdRss($lang)
  {
    $url = Config::getFluxbbForum().'/extern.php?action=new&show=1&type=last_rss&fid='.Config::getFluxbbWotdId();
    $text = file_get_contents($url);
    
    if ($text === false) {
      $text = $lang->getForumError();
    } else {
      $text = utf8_encode($text);
    }
    return $text;
  }
  
  
  /**
   * Return the comments for a strip
   *
   * @param Strip $strip The strip for which we want obtain the comments
   * @param Lang $lang The language object is use when the connection with forum isn't ok
   * @access public
   * @static
   * @return string The comments for the strip or an error message if the connection with forum isn't ok
   */
  public static function getComments(Strip $strip, Lang $lang)
  {
    $url = Config::getFluxbbForum().'/extern.php?action=topic&ttitle='.urlencode($strip->getTitle()).'&max_subject_length='.Config::getFluxbbMaxLength();
    $text = file_get_contents($url);
    
    if ($text === false) {
      $text = $lang->getForumError();
    } else {
      $text = utf8_encode($text);
    }
    return $text;
  }
}