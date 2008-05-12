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
require_once 'configuration.php';

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

	var $nav_base_url = "gallery.php?page=";

	/**
	* Constructor
	* 
	* Use the {@link strip_manager} to do the stuff, and convert date from the iso8601 to RFC822 format.
	*/
	function gallery_manager() {

		//$this->general = new configuration;

		$sm = new strip_manager();

		$this->general = $sm->general;
		$this->lang = $sm->lang;	
	
		$sm->strips_list_get();
		
		// limit the number of strips in Gallery
		$limit = $this->general->thumbs_per_page;
		if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
			$limit = $_GET['limit'];

			if ($limit <= 0) {
				$limit = $this->general->thumbs_per_page;
			}
		}

		$lastpage = intval(($sm->strips_count - 1) / $limit);

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

		if ($end > $sm->strips_count) {
			$end = $sm->strips_count;
		}

		for( $i = $start; $i < $end;  $i++ ) {
			$sm->strip_info_get( $i );
			
			$this->items_list[] = clone($sm); // hack for php4/5 compat
		}

		$this->nav_prev = $this->nav_base_url . ($element_asked - 1) . "&limit=$limit";
		$this->nav_next = $this->nav_base_url . ($element_asked + 1) . "&limit=$limit";
		$this->nav_last = $this->nav_base_url . $lastpage . "&limit=$limit";
		$this->nav_first = $this->nav_base_url . "&limit=$limit";
	}

	/**
	* Generate the Gallery output with the template engine.
	*/
	function generate() {
		$sm = new strip_manager;
		$output = new HTML_Template_Flexy($sm->options);
		$output->compile('gallery_'.$sm->general->template_html);
		$output->outputObject($this,$this->items_list);
	}
}

/**
* Instanciation and output.
*/
$gallerym = new gallery_manager();
$gallerym->generate();

?>
