<?php
/**
 * Strip-It HTML index
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @author Johann "nojhan" Dréo <nojhan@gmail.com>
 * @author Simon Leblanc <contact@leblanc-simon.eu>
 * @package stripit
 */


require_once 'inc/functions.php';

//Launch Cron
Cron::exec();

// Obtain the Config
$config = new Config();

// Obtain the cache
$cache = Cache::getCache();

// Obtain the id asked
$last = Cache::getLastId();

if (isset($_GET['id']) === true && is_numeric($id) === true) {
  $id = $_GET['id'];
  
  if ($id > $last || $id < 0) {
    $id = $last;
  }
} else {
  $id = $last;
}

// Obtain the strip
$strip = Cache::getStrip($id);

// Obtain the language
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
} else {
  $lang = Config::getLanguage();
}
$lang = getLang($lang);

// Obtain the navigation
list($nav_first, $nav_last, $nav_prev, $nav_next, $nav_gallery) = getNavigation($id, $last, $lang);
$nav_forum_post = Config::getFluxbbForum().'/post.php?ttitle='.urlencode($strip->getTitle()).'&fid='.Config::getFluxbbForumId();
$nav_forum_view = Config::getFluxbbForum().'/redirect_stripit.php?ttitle='.urlencode($strip->getTitle());

// If necessary, obtain the forum data
$comments = '';
$wotd = '';
if (Config::getUseFluxbb() === true) {
  $comments = Forum::getComments($strip, $lang);
  $wotd = Forum::getWotd($lang);
}

// show the template
if (isset($_GET['ajax']) === true) {
  // it's an ajax call, return an XML
  header('Content-type: text/xml');
  include_once Config::getTemplateFolder().'/stripit.xml';
} else {
  include_once Config::getTemplateFolder().'/'.Config::getTemplateName().'/template.html';
}