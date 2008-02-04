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
require_once 'configuration.php';

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
	* Constructor
	* 
	* Use the {@link strip_manager} to do the stuff, and convert date from the iso8601 to RFC822 format.
	*/
	function rss_manager() {

		//$this->general = new configuration;

		$sm = new strip_manager();

		$this->general = $sm->general;
		$this->lang = $sm->lang;	
	
		$sm->strips_list_get();

		for( $i = $sm->strips_count-1; $i >= 0;  $i-- ) { // reverser order
			$sm->strip_info_get( $i );
			
			// conversion iso8601 -> RFC822
			$sm->date = date('r', strtotime($sm->date));

			$this->items_list[] = clone($sm); // hack for php4/5 compat
		}
	}

	/**
	* Generate the RSS output with the template engine.
	*/
	function generate() {
		$sm = new strip_manager;
		$output = new HTML_Template_Flexy($sm->options);
		$output->compile('template.rss');
		$output->outputObject($this,$this->items_list);
	}
}

/**
* Instanciation and output.
*/
$rssm = new rss_manager();
$rssm->generate();

?>
