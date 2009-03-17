<?php
/**
* Strip-It main manager
*
* @author Johann "nojhan" Dréo <nojhan@gmail.com>
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2007 Johann Dréo
*
* @package stripit 
*/

set_include_path(get_include_path() . PATH_SEPARATOR . getcwd());

require_once 'HTML/Template/Flexy.php';
require_once 'PEAR/XMLParser.php';
// trying to give some meaningful info if there's no configuration file
(@include_once 'conf/configuration.php') or
	die("Strip It isn't configured yet: conf/configuration.php file is missing.<br/>See README for details.");

class svg_parser extends PEAR_XMLParser
{
	/**
	* Strip Manager object
	*/
	var $strip_manager;
	/**
	* Stack of current xml elements the parser is in
	* @var array
	*/
	var $elem;
	/**
	* Value of current xml element
	* @var string
	*/
	var $currentvalue;

	function svg_parser($strip_manager)
	{
		$this->strip_manager = $strip_manager;
		$this->elem = array();
	}

	function startHandler($xp, $name, $attribs)
	{
		array_push($this->elem, $name);
		$this->currentvalue = "";

		switch($name)
		{
			case "cc:license":
				$this->strip_manager->license = $attribs["rdf:resource"];
				break;
		}
	}

	function endHandler($xp, $name)
	{
		$elem = array_pop($this->elem);
		switch($elem)
		{
			case "dc:title":
				// dc:title can appear multiple times, we're checking where we are
				switch($this->elem[count($this->elem) - 2])
				{
					case "dc:creator":
						$this->strip_manager->author =  $this->currentvalue;
						break;
					case "rdf:RDF":
						$this->strip_manager->title =  $this->currentvalue;
						break;
				}
				break;
			case "dc:date":
				$this->strip_manager->date = $this->currentvalue;
				break;
			case "dc:description":
				$this->strip_manager->description = $this->currentvalue;
				break;
			case "tspan":
				$this->strip_manager->text .= $this->currentvalue;
				break;
		}
	}

	function cdataHandler($xp, $cdata)
	{
		$this->currentvalue .= $cdata;
	}
}

/**
* Main manager
*
* @todo use correct XML parsing to avoid the tring dependencies among Inkscape way of generate the SVG
*/
class strip_manager
{
	/**
	* Directory where to find strips
	* @var string
	*/
	var $strips_path = "./strips";
	
	/**
	* Directory where to find strips thumbnail
	* @var string
	*/
	var $strips_th_path = "./strips/th";

	/**
	* Directory where to find translations files
	* @var string
	*/
	var $lang_path = "./conf/lang";

	/** 
	* Base url for asking for strips
	* @var string
	*/
	var $nav_base_url = 'index.php?strip=';

	/**
	* Base keyword for languages management
	* @var string
	*/
	var $lang_base_key = "lang";

	/**
	* Constructed URL for languages
	* @var string
	*/
	var $nav_lang_url = '';

	/**#@+
	* Navigation constants
	* @var integer
	*/
	/**
	* Previous
	*/
	var $nav_prev = 0;
	/**
	* Next
	*/
	var $nav_next = 1;
	/**
	* Last
	*/
	var $nav_last = 1;
	/**
	* First
	*/
	var $nav_first = 0;
	/**
	* First
	*/
	var $nav_gallery = "gallery.php";

	/**
	* Variable concerning the current strip
	* @var string
	*/
	/** 
	* URL of the displayed file
	*/
	var $img_src ='';
	/** 
	* URL of the thumbnail
	*/
	var $thumbnail ='';
	/**
	* Title
	*/
	var $title = '';
	/**
	* Author
	*/
	var $author = '';
	/**
	* Date
	*/
	var $date = '';
	/**
	* License
	*/
	var $license = '';
	/**
	* URL of the SVG source file
	*/
	var $source = '';
	/**
	* Size of the source file
	*/
	var $source_size = 0;
	/**
	* Description
	*/
	var $description;
	/**
	* Alternate text for the image
	*/
	var $text;

	/**
	* List of the strips
	* @var array
	*/
	var $strips_list = array();
	
	/** 
	* Total number of strips
	* @var integer
	*/
	var $strips_count = 0;

	/**
	* Strip ID on the website
	*
	* This ID is not extracted from the filename, but depends of the lexical order in the filelist.
	* If you do not want to see ID variations, always preserve the lexial order of the file list.
	*
	* @var integer
	*/
	var $current_id = 0;

	/**
	* Comments associated to a strip
	*/
	var $comments = '';

	/**
	* Link to the post page related to the strip on the forum
	*/
	var $forum_post_url = '';

	/**
	* Additional variables
	*
	* These ones are passed to the template engine.
	*
	* @var array
	*/
	var $elements = array();

	/**
	* Options for the template engine
	* @var array
	*/
	var $options = array(
		'templateDir'   => '.',
		'compileDir'    => 'cache',
		'forceCompile'  => 1,
		'debug'         => 0,
		'locale'        => 'fr',
		'compiler'      => 'Standard',
	);

	/**
	* General configuration variables
	*
	* Used to store the configuration options.
	* @var array
	*/
	var $general;
	/**
	* Languages configuration
	* @var array
	*/
	var $lang;

	/**
	* Constructor
	*
	* Load the {@link configuration}
	*
	* @access	public
	*/
	function strip_manager() {
		$this->general = new configuration;
		$this->_language();
	}


	/**
	* Check the asked language and search for translation files
	*
	* @access	private
	*/
	function _language() {
		if (isset($_GET[$this->lang_base_key]) && !empty($_GET[$this->lang_base_key])) {
			$lang = $_GET[$this->lang_base_key];
			$this->nav_lang_url = '&'.$this->lang_base_key.'='.$_GET[$this->lang_base_key];
		} else {
			$lang = $this->general->language;
		}

		if (file_exists($this->lang_path.'/'.$lang.'.php')) {
			require_once $this->lang_path.'/'.$lang.'.php';
			$this->lang = new language;
			$this->general->language = $lang;
		}
	}


	/**
	 * Parse the file list and generate the output
	 *
	 * @access public
	 */
	function generate() {
		if ($this->general->use_cache) {
			// use the cache system
			include_once 'cache.class.php';
			$cache = new STRIPIT_Cache();
			if( !isset($_GET['strip']) || $_GET['strip'] == '' || !is_numeric($_GET['strip']) ) {
				// we must give the last, so we must calculate the last strip :-(
				$this->strips_list_get();
				$get_strip = $this->strips_count-1;
			} else {
				$get_strip = $_GET['strip'];
			}
			
			if ($get_strip < 0) { 
				$get_strip = 0;
			}
			
			$template_folder	= $this->general->template_folder.'/'.$this->general->template_name;
			$strip_folder		= $this->strips_path;
			$cache_folder		= $this->general->cache_folder;
			$language		= $this->general->language;
			if ($cache->init($get_strip, $template_folder, $strip_folder, $cache_folder, $language)) {
				if ($cache->isInCache()) {
					// the page is in cache, show cache
					$cache->getCache();
				} else {
					// the cache must be re-generate
					ob_start();
					
					$this->start();
					$this->output();
					
					$cache_data = ob_get_contents();
					ob_end_clean();
					$cache->putInCache($cache_data);
					$cache->getCache();
				}
			} else {
				// error in the configuration cache, don't use the cache system
				$this->start();
				$this->output();
			}
		} else {
			// don't use the cache system
			$this->start();
			$this->output();
		}
	}


	/**
	* Construct the file list
	*
	* Add SVG files in the {@link $strips_path} directory and sort them lexically.
	* @access	public
	*/
	function strips_list_get() {
		// init the array
		$this->strips_list = array();
		
		// Open the given directory
		$handle = opendir($this->strips_path);

		// Browse the directory
		while($file = readdir($handle)) {
			if($file != "." && $file != "..") {
				// Get the extension of the file
				$ext = pathinfo($file);
				// If it is an SVG
				if( $ext['extension'] == 'svg' ) {
					// Add it to the list
					$this->strips_list[] = $file;
				}
			}
		}

		closedir($handle);

		// Sort the array based on file name
		sort($this->strips_list);

		$this->strips_count = count($this->strips_list);
	}


	/**
	* Parse meta-data of a given SVG
	*
	* @param integer index of the file to parse in the {@link $strips_list}
	* @access	public
	*/
	function strip_info_get( $element_asked ) {
		$this->current_id = $element_asked;

		$file = $this->strips_list[$element_asked];

		$svg = $this->strips_path.'/'.$file;

		// change the extension for {@link $img_src}
		$png = explode( '.', $file);
		$this->img_src = $this->strips_path.'/'.$png[0].'.png';
		$this->thumbnail = $this->strips_th_path.'/'.$png[0].'.png';
		if (!$this->_getThumbnail()) {
			// Error in generation of thumbnail, the thumbnail will be the original
			$this->thumbnail = $this->strips_path.'/'.$png[0].'.png';
		}

		$this->source = $svg;
		$this->source_size = filesize( $svg );
		
		if( file_exists($svg) ) {
			$data = file_get_contents( $svg );

			$parser = new svg_parser($this);
			$parser->parse($data);
		}

		// if one want to use punbb as forum
		if( $this->general->use_punbb ) {
			// lasts posts associated to the strip
			$fh = fopen( $this->general->forum.'/extern.php?action=topic&ttitle='.urlencode($this->title).'&max_subject_length='.$this->general->punbb_max_length, 'r');

			if (!$fh) {
				// TODO traduction
				$this->comments = $this->lang->forum_error;
			} else {
				$this->comments = utf8_encode(stream_get_contents($fh));
				fclose($fh);
			}
	
			// link for posting a new comment
			$this->forum_post_url = $this->general->forum . '/post.php?ttitle='.urlencode($this->title).'&fid='.$this->general->punbb_forum_id;
			$this->forum_view_url = $this->general->forum . '/redirect_stripit.php?ttitle='.urlencode($this->title);
		}
	}
	
	
	/**
	 * Return the link for the thumbnail (and create the thumbnail if neccesary)
	 *
	 * @return	bool			True if the thumbail is created, False else
	 */
	function _getThumbnail()
	{
		// check if the thumbnail exist
		if (file_exists($this->thumbnail)) {
			// the file exist, check if the original is create before the thumbnail
			clearstatcache();
			$original_time = filemtime($this->img_src);
			$thumbnail_time = filemtime($this->thumbnail);
			
			if ($original_time >= $thumbnail_time) {
				// There is a modification since the creation of thumbnail
				return $this->_genThumbnail();
			} else {
				return true;
			}
		} else {
			return $this->_genThumbnail();
		}
	}
	
	
	/**
	 * Create the thumbnail 
	 *
	 * @return	bool			True if the creation is OK, False else
	 */
	function _genThumbnail()
	{
		// check if the directory of thumbnail exists
		if (!is_dir($this->strips_th_path)) {
			return false;
		}
		
		// calculate the width and height of thumbnail
		$width = $this->general->thumb_size;
		$size = getimagesize($this->img_src);
		if ($size[0] > $width) {
			$rapport = $size[0] / $width;
			$height = $size[1] / $rapport;
		} else {
			$width = $size[0];
			$height = $size[1];
		}
		
		$img_src = imagecreatefrompng($this->img_src);
		$img_dst = imagecreatetruecolor($width, $height);
		
		// Preserve alpha transparency : thank crash <http://www.php.net/manual/fr/function.imagecopyresampled.php#85038>
		imagecolortransparent($img_dst, imagecolorallocate($img_dst, 0, 0, 0));
		imagealphablending($img_dst, false);
		imagesavealpha($img_dst, true);
		
		$res = imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
		if (!$res) {
			return false;
		}
		
		$res = imagepng($img_dst, $this->thumbnail);
		if (!$res) {
			return false;
		}
		
		return true;
	}


	/**
	* Launch {@link strips_list_get}, check the asked strip, construct naviguation menu, launch {@link strip_info_get} on it
	* @access	public
	*/
	function start() {

		$this->strips_list_get();

		$element_asked = -1;

		// If one ask for a particular strip
		if( !isset($_GET['strip']) || $_GET['strip'] == '' || !is_numeric($_GET['strip']) ) {
			$element_asked = $this->strips_count-1;

		} else {
			$element_asked = $_GET['strip'];

			// else, the last one
			if( $element_asked >= $this->strips_count ) {
				$element_asked = $this->strips_count-1;
			}

			if ( $element_asked < 0 ) {
				$element_asked = 0;
			}
		}

		// Relative navigation menu
		$this->nav_prev = $this->nav_base_url.($element_asked-1).$this->nav_lang_url;
		$this->nav_next = $this->nav_base_url.($element_asked+1).$this->nav_lang_url;
		$this->nav_last = $this->nav_base_url.($this->strips_count-1).$this->nav_lang_url;
		$this->nav_first = $this->nav_base_url."0".$this->nav_lang_url;
		$this->nav_gallery = $this->nav_gallery.'?page='.floor($element_asked / $this->general->thumbs_per_page).'&limit='.$this->general->thumbs_per_page;

		
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

		$this->strip_info_get( $element_asked );
	}


	/**
	* Generate the HTML output with the template engine
	* @access	public
	*/
	function output() {
		$output = new HTML_Template_Flexy($this->options);
		$output->compile($this->general->template_folder.'/'.$this->general->template_name.'/template.html');
		$output->outputObject($this,$this->elements);
	}
}

?>
