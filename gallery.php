<?php
/**
* Strip-It Gallery manager (based on RSS manager)
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
* Gallery manager
*/
class gallery_manager
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
	* Base url for asking for strips in gallery
	* @var string
	*/
	var $nav_base_url = "gallery.php?page=";
	
	/**
	 * Strip manager object
	 * @var	object
	 */
	var $strip_manager;

	/**
	* Constructor
	* 
	* Use the {@link strip_manager} to do the stuff, and convert date from the iso8601 to RFC822 format.
	*/
	function gallery_manager() {

		$this->strip_manager = new strip_manager();

		$this->general = $this->strip_manager->general;
		$this->lang = $this->strip_manager->lang;	
	
	}

	/**
	* Generate the Gallery output with the template engine.
	*
	* @access	public
	*/
	function generate() {
		// if one want to use punbb as forum
		if( $this->general->use_punbb ) {
			// get the word of the day
			$fh = fopen( $this->general->forum.'/extern.php?action=new&show=1&fid='.$this->general->punbb_wotd_id, 'r');

			if (!$fh) {
				$this->general->wotd = $this->lang->forum_error;
			} else {
				$this->general->wotd = stream_get_contents($fh);
				fclose($fh);
			}
		}
		
		if ($this->general->use_cache) {
			// use the cache system
			include_once 'cache.class.php';
			$cache = new STRIPIT_Cache();
			
			// limit the number of strips in Gallery
			$limit = $this->general->thumbs_per_page;
			if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
				$limit = $_GET['limit'];

				if ($limit <= 0) {
					$limit = $this->general->thumbs_per_page;
				}
			}
			// the page to view
			if( !isset($_GET['page']) || $_GET['page'] == '' || !is_numeric($_GET['page']) ) {
				$page = 0;
			} else {
				$page = $_GET['page'];

				if ($page < 0) {
					$page = 0;
				}
			}
			
			$template_folder	= $this->general->template_folder.'/'.$this->general->template_name;
			$strip_folder		= $this->strip_manager->strips_path;
			$cache_folder		= $this->general->cache_folder;
			$language		= $this->general->language;
			if ($cache->init('gallery-'.$limit.'-'.$page, $template_folder, $strip_folder, $cache_folder, $language)) {
				if ($cache->isInCache()) {
					// the page is in cache, show cache
					$cache->getCache();
				} else {
					// the cache must be re-generate
					ob_start();
					
					$this->_genGallery();
					
					$cache_data = ob_get_contents();
					ob_end_clean();
					$cache->putInCache($cache_data);
					$cache->getCache();
				}
			} else {
				// error in the configuration cache, don't use the cache system
				$this->_genGallery();
			}
		} else {
			// don't use the cache system
			$this->_genGallery();
		}
		
	}
	
	
	/**
	 * Generate the HTML template of gallery
	 *
	 * @access	private
	 */
	function _genGallery()
	{
		$this->strip_manager->strips_list_get();
		
		// limit the number of strips in Gallery
		$limit = $this->general->thumbs_per_page;
		if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
			$limit = $_GET['limit'];

			if ($limit <= 0) {
				$limit = $this->general->thumbs_per_page;
			}
		}

		$lastpage = intval(($this->strip_manager->strips_count - 1) / $limit);

		if( !isset($_GET['page']) || $_GET['page'] == '' || !is_numeric($_GET['page']) ) {
			$element_asked = 0;
		} else {
			$element_asked = $_GET['page'];

			if ($element_asked < 0) {
				$element_asked = 0;
			}

			if ($element_asked > $lastpage) {
				$element_asked = $lastpage;
			}
		}

		$start = $element_asked * $limit;
		$end = $start + $limit;

		if ($end > $this->strip_manager->strips_count) {
			$end = $this->strip_manager->strips_count;
		}

		for( $i = $start; $i < $end;  $i++ ) {
			$this->strip_manager->strip_info_get( $i );
			
			$this->items_list[] = clone($this->strip_manager); // hack for php4/5 compat
		}

		$this->nav_prev = $this->nav_base_url . ($element_asked - 1) . "&limit=$limit" . $this->strip_manager->nav_lang_url;
		$this->nav_next = $this->nav_base_url . ($element_asked + 1) . "&limit=$limit" . $this->strip_manager->nav_lang_url;
		$this->nav_last = $this->nav_base_url . $lastpage . "&limit=$limit" . $this->strip_manager->nav_lang_url;
		$this->nav_first = $this->nav_base_url . "&limit=$limit" . $this->strip_manager->nav_lang_url;
		
		$output = new HTML_Template_Flexy($this->strip_manager->options);
		$output->compile($this->general->template_folder.'/'.$this->general->template_name.'/gallery_template.html');
		$output->outputObject($this,$this->items_list);
	}
}

/**
* Instanciation and output.
*/
$gallerym = new gallery_manager();
$gallerym->generate();

?>