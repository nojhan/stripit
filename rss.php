<?php
/**
 * Strip-It Rss
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

// Obtain the limit
if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
  $limit = $_GET['limit'];

  if ($limit <= 0 || $limit > $last + 1) {
    $limit = $last + 1;
  }
} else {
  $limit = $last + 1;
}
$end = $last - $limit;

// Obtain the strips
$list = array();
for ($i = $last; $i > $end; $i--) {
  $list[$i] = Cache::getStrip($i);
}

// Obtain the language
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
} else {
  $lang = Config::getLanguage();
}
$lang = getLang($lang);

// Navigation
$nav_img = Config::getUrl().'/'.Config::getIndex().'?id=';

// If necessary, obtain the forum data
$wotd = '';
if (Config::getUseFluxbb() === true) {
  $wotd = Forum::getWotdRss($lang);
}

// show the template
header('Content-Type: application/rss+xml');
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
include_once Config::getTemplateFolder().'/'.Config::getTemplateRss().'/template.rss';
