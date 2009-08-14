<?php
/**
 * Class for manage the job scheduler
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 */
class Cron
{
  /**
   * This method is use for check if you must launch the job
   *
   * @access public
   * @static
   */
  public static function exec()
  {
    $cache_file = Config::getCacheFolder().'/'.Config::getCacheFilename();
    if (file_exists($cache_file) === false) {
      self::launch();
    } else {
      $cache_mtime = filemtime($cache_file);
      $cache_regenerate = time() - (Config::getCacheTime() * 60);
      if ($cache_mtime < $cache_regenerate) {
        self::launch();
      }
    }
  }
  
  
  /**
   * This method launch the job
   *
   * @access protected
   * @static
   */
  protected static function launch()
  {
    Strip::createCache();
  }
}