<?php
/**
* Strip-It RSS manager
*
* @author Johann "nojhan" Dréo <nojhan@gmail.com>
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2007 Johann Dréo
*
* @package stripit 
*/

set_include_path(get_include_path() . PATH_SEPARATOR . getcwd());

require_once 'strip_manager.php';
require_once 'conf/configuration.php';

// hack for passing objects by values instead of reference under PHP5 (but not PHP4)
// damn clone keyword !
if (version_compare(phpversion(), '5.0') < 0) {
	eval('function clone($object) {return $object;}');
}

/**
* RSS manager
*/
class rss_manager
{
	/**
	* Items list
	* @var array
	*/
	var $items_list = array();

	/**
	* Configuration
	* @var array
	*/
	var $general;
	
	/**
	 * Strip manager object
	 * @var	object
	 */
	var $strip_manager;

	/**
	* Constructor
	* 
	* Use the {@link strip_manager} to do the stuff, and convert date from the iso8601 to RFC822 format.
	*
	* @access	public
	*/
	function rss_manager() {

		$this->strip_manager = new strip_manager();

		$this->general = $this->strip_manager->general;
		$this->lang = $this->strip_manager->lang;
	}
	
	
	/**
	 * Init the strip manager
	 *
	 * Use the {@link strip_manager} to do the stuff, and convert date from the iso8601 to RFC822 format.
	 * 
	 * @access	private
	 */
	function _init()
	{
		$this->strip_manager->strips_list_get();
		
		// limit the number of strip in RSS
		$limit = 0;
		if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
			// check for have no infinite for
			if ($_GET['limit'] > 0 && $_GET['limit'] < $this->strip_manager->strips_count) {
				$limit = $this->strip_manager->strips_count - $_GET['limit'];
			}
		}

		for( $i = $this->strip_manager->strips_count-1; $i >= $limit;  $i-- ) { // reverser order
			$this->strip_manager->strip_info_get( $i );
			
			// conversion iso8601 -> RFC822
			$this->strip_manager->date = date('r', strtotime($this->strip_manager->date));

			$this->items_list[] = clone($this->strip_manager); // hack for php4/5 compat
		}
	}

	/**
	* Generate the RSS output with the template engine.
	*
	* @access	public
	*/
	function generate() {
		header('Content-Type: application/rss+xml');
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
		
		if ($this->general->use_cache) {
			// use the cache system
			include_once 'cache.class.php';
			$cache = new STRIPIT_Cache();
			
			$limit = 0;
			if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
				$limit = $_GET['limit'];
			}
			
			$template_folder	= $this->general->template_folder.'/'.$this->general->template_rss;
			$strip_folder		= $this->strip_manager->strips_path;
			$cache_folder		= $this->general->cache_folder;
			if ($cache->init($limit, $template_folder, $strip_folder, $cache_folder, '', true)) {
				if ($cache->isInCache()) {
					// the page is in cache, show cache
					$cache->getCache();
				} else {
					// the cache must be re-generate
					ob_start();
					
					$this->_genRss();
					
					$cache_data = ob_get_contents();
					ob_end_clean();
					$cache->putInCache($cache_data);
					$cache->getCache();
				}
			} else {
				// error in the configuration cache, don't use the cache system
				$this->_genRss();
			}
		} else {
			// don't use the cache system
			$this->_genRss();
		}
	}
	
	
	/**
	 * Generate the RSS page
	 *
	 * @access	private
	 */
	function _genRss()
	{
		$this->_init();
		
		$output = new HTML_Template_Flexy($this->strip_manager->options);
		$output->compile($this->general->template_folder.'/'.$this->general->template_rss.'/template.rss');
		$output->outputObject($this,$this->items_list);
	}
}

/**
* Instanciation and output.
*/
$rssm = new rss_manager();
$rssm->generate();

?>
