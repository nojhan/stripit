<?php
/**
 * Class for manage the cache of SVG file
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 */
class Cache
{
  /**
   * The filename which use for write the php array with the cache data
   * @var string
   * @access protected
   * @static
   */
  protected static $filename  = null;
  
  /**
   * This variable contain the cache for not reload always the cache with an include
   * @var array
   * @access protected
   * @static
   */
  protected static $cache     = null;
  
  
  /**
   * This function initialize, if neccesary, the filename with the configuration
   * 
   * @access protected
   * @static
   */
  protected static function init()
  {
    if (self::$filename === null) {
      self::$filename = Config::getCacheFolder().'/'.Config::getCacheFilename();
    }
  }
  
  
  /**
   * This method obtain the cache if neccesary and return it
   *
   * @access public
   * @static
   * @return array The cache of the SVG file
   * @throws Exception If the cache isn't defined an exception is throwed
   */
  public static function getCache()
  {
    self::init();
    
    if (file_exists(self::$filename) === false) {
      return array();
    }
    
    include self::$filename;
    
    if (isset($cache) === false) {
      throw new Exception('The cache isn\'t defined!');
    }
    
    self::$cache = $cache;
    
    return $cache;
  }
  
  
  /**
   * This method allow to generate the cache file with all data for SVG file
   *
   * @access public
   * @static
   * @throws Exception If the cache can't be write, an exception is throwed
   * @todo When PHP 5.3 is ok in most hosting, use GlobIterator and not DirectoryIterator and don't do 2 foreach and ksort
   */
  public static function setCache()
  {
    self::init();
    
    $directory = new DirectoryIterator(Config::getStripFolder());
    $file = array();
    
    // get all svg file
    foreach ($directory as $file) {
      if ($file->isDot() === true || $file->isFile() === false) {
        continue;
      }
      
      $extension = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
      if (strtolower($extension) !== 'svg') {
        continue;
      }
      
     $files[$file->getBasename()] = $file->getMTime();
    }
    
    // We must use ksort for sort the array by filename (the DirectoryIterator have his self sort :-)), in PHP 5.3, we can use GlobIterator directly
    ksort($files);
    
    $cache = '<?php $cache = array(';
    foreach ($files as $name => $time) {
      $cache .= '"'.str_replace('"', '\"', $name).'" => '.$time.', ';
    }
    $cache .= ');';
    
    // if file wrinting fail, throw an Exception
    if (file_put_contents(self::$filename, $cache) === false) {
      throw new Exception('Impossible to write in the cache file : "'.self::$filename.'"');
    }
  }
  
  
  /**
   * Return the last numeric key of the cache array
   *
   * @access public
   * @static
   * @return integer the last numeric key of the cache array
   */
  public static function getLastId()
  {
    if (self::$cache === null) {
      self::getCache();
    }
    
    return (count(self::$cache) - 1);
  }
  
  
  /**
   * Return the strip with the numeric key of the cache
   *
   * @access public
   * @static
   * @return Strip The strip wanted
   */
  public static function getStrip($id = 0)
  {
    $last_id = self::getLastId();
    
    // if the id of strip isn't valid, return a new strip
    if ($last_id === -1 || $id > $last_id) {
      return new Strip();
    }
    
    $cache_iterator = new ArrayIterator(self::$cache);
    $cache_iterator->seek($id);
    
    return Strip::getCache($cache_iterator->key());
  }
}