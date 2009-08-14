<?php
/**
 * Strip-It HTML gallery
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
    $limit = Config::getThumbsPerPage();
  }
} else {
  $limit = Config::getThumbsPerPage();
}

// Obtain the page
$last_page = (int) ($last / $limit);
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
  $page = $_GET['page'];
  
  if ($page < 0 || $page > $last_page) {
    $page = $last_page;
  }
} else {
  $page = $last_page;
}

// Obtain the list of strip
$begin = $page * $limit;
$end = $begin + $limit;

if ($end > $last + 1) {
  $end = $last + 1;
}

for ($i = $begin; $i < $end; $i++) {
  $list[$i] = Cache::getStrip($i);
}

// Obtain the language
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
} else {
  $lang = Config::getLanguage();
}
$lang = getLang($lang);

// Obtain the navigation
list($nav_first, $nav_last, $nav_prev, $nav_next) = getNavigationGallery($page, $last_page, $limit, $lang);
$nav_lang = '';
if (isset($_GET['lang'])) {
  $nav_lang = 'lang='.$lang.'&';
}
$nav_img = Config::getUrl().'/'.Config::getIndex().'?'.$nav_lang.'id=';

// If necessary, obtain the forum data
$wotd = '';
if (Config::getUseFluxbb() === true) {
  $wotd = Forum::getWotd($lang);
}

// show the template
include_once Config::getTemplateFolder().'/'.Config::getTemplateName().'/gallery.html';
