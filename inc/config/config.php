<?php
/**
 * Class for manage the configuration
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 * @todo outsource the method to have only the attribute in this file (for more visibility), maybe with interface and abstract class ?
 */
class Config
{
  /**
   * Software version
   * @var string
   * @access protected
   */
  protected static $version            = '0.8b';
  
  /**
   * URL of the website
   * @var string
   * @access protected
   */
  protected static $url                = 'http://localhost/projects/stripit_php5';
  
  /**
   * Name of the index page
   * @var string
   * @access protected
   */
  protected static $index              = 'index.php';
  
  /**
   * Name of the gallery page
   * @var string
   * @access protected
   */
  protected static $gallery            = 'gallery.php';
  
  /**
   * Title of the website
   * @var string
   * @access protected
   */
  protected static $title              = 'Stripit PHP5';
  
  /**
   * Default language of the interface
   * @var string
   * @access protected
   */
  protected static $language           = 'fr-FR';
  
  /**
   * Short description - Is displayed as a subtitle
   * @var string
   * @access protected
   */
  protected static $description        = 'Ce serait mieux avec des strips libres !';
  
  /**
   * Webmaster's name
   * @var string
   * @access protected
   */
  protected static $webmaster          = 'inconnnu';
  
  /**
   * Webmaster's email
   * @var string
   * @access protected
   */
  protected static $email              = 'root@example.com';
  
  
  /**
   * Additional URL
   * @var array
   * @access protected
   */
  protected static $see_also           = array(
    'Geekscottes' => 'http://www.nojhan.net/geekscottes',
  );
  
  /**
   * Shop URL
   * @var string
   * @access protected
   */
  protected static $shop               = 'http://perdu.com';
  
  
  /**
   * The cache directory path
   * @var string
   * @access protected
   */
  protected static $cache_folder       = './cache';
  
  /**
   * The filename of cache for list of strip
   * @var string
   * @access protected
   */
  protected static $cache_filename     = 'cache.php';
  
  /**
   * The cache time before regenerated the new cache
   * @var integer
   * @access protected
   */
  protected static $cache_time         = 1440; // in minutes : 1440 = 1 day
  
  
  /**
   * HTML template folder
   * @var string
   * @access protected
   */
  protected static $template_folder    = './inc/tpl';
  
  /**
   * Name of HTML template
   * @var string
   * @access protected
   */
  protected static $template_name      = 'lego';
  
  /**
   * Name of RSS template
   * @var string
   * @access protected
   */
  protected static $template_rss       = 'rss';
  
  
  /**
   * Diretory path for the strips
   * @var string
   * @access protected
   */
  protected static $strip_folder       = './strips';
  
  
  /**
   * Number of thumbnails per gallery page
   * @var integer
   * @access protected
   */
  protected static $thumbs_per_page    = 5;
  
  /**
   * Size of the thumbnails
   * @var integer
   * @access protected
   */
  protected static $thumb_size         = 200;
  
  /**
   * Diretory path for the strips thumbnails
   * @var string
   * @access protected
   */
  protected static $thumb_folder       = './strips/th';
  
  
  /**
   * Use FluxBB integration ?
   * @var boolean
   * @access protected
   */
  protected static $use_fluxbb         = true;
  
  /**
   * FluxBB's forum ID to use for strips comment
   * @var integer
   * @access protected
   */
  protected static $fluxbb_forum_id    = 1;
  
  /**
   * FluxBB's forum ID to use for word of the day
   * @var integer
   * @access protected
   */
  protected static $fluxbb_wotd_id     = 2;
  
  /**
   * FluxBB's forum max length for the message
   * @var integer
   * @access protected
   */
  protected static $fluxbb_max_length  = 128;
  
  /**
   * Forum URL
   * @var string
   * @access protected
   */
  protected static $fluxbb_forum       = 'http://localhost/projects/fluxbb-1.2.21/upload';
  
  
  /*
   All getter for access to protected attributes
   */
  public static function getVersion() { return self::$version; }
  public static function getUrl() { return self::$url; }
  public static function getIndex() { return self::$index; }
  public static function getGallery() { return self::$gallery; }
  public static function getTitle() { return self::$title; }
  public static function getLanguage() { return self::$language; }
  public static function getDescription() { return self::$description; }
  public static function getWebmaster() { return self::$webmaster; }
  public static function getEmail() { return self::$email; }
  
  public static function getSeeAlso() { return self::$see_also; }
  public static function getShop() { return self::$shop; }
  
  public static function getUseCache() { return self::$use_cache; }
  public static function getCacheFolder() { return self::$cache_folder; }
  public static function getCacheFilename() { return self::$cache_filename; }
  public static function getCacheTime() { return self::$cache_time; }
  
  public static function getTemplateFolder() { return self::$template_folder; }
  public static function getTemplateName() { return self::$template_name; }
  public static function getTemplateRss() { return self::$template_rss; }
  
  public static function getStripFolder() { return self::$strip_folder; }
  
  public static function getThumbsPerPage() { return self::$thumbs_per_page; }
  public static function getThumbSize() { return self::$thumb_size; }
  public static function getThumbFolder() { return self::$thumb_folder; }
  
  public static function getUseFluxbb() { return self::$use_fluxbb; }
  public static function getFluxbbForumId() { return self::$fluxbb_forum_id; }
  public static function getFluxbbWotdId() { return self::$fluxbb_wotd_id; }
  public static function getFluxbbMaxLength() { return self::$fluxbb_max_length; }
  public static function getFluxbbForum() { return self::$fluxbb_forum; }
}
