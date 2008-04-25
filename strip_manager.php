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
require_once 'configuration.php';

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
	* Directory where to find translations files
	* @var string
	*/
	var $lang_path = "./lang";

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
	* Variable concerning the current strip
	* @var string
	*/
	/** 
	* URL of the displayed file
	*/
	var $img_src ='';
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
	*/
	function strip_manager() {
		$this->general = new configuration;
		$this->language();
	}


	/**
	* Check the asked language and search for translation files
	*/
	function language() {
		if (isset($_GET[$this->lang_base_key]) && !empty($_GET[$this->lang_base_key])) {
			$lang = $_GET[$this->lang_base_key];
			$this->nav_lang_url = $this->lang_base_key.$_GET[$this->lang_base_key];
		} else {
			$lang = $this->general->language;
		}

		if (file_exists($this->lang_path.'/'.$lang.'.php')) {
			require_once $this->lang_path.'/'.$lang.'.php';
			$this->lang = new language;
		}
	}


	/**
	* Parse the file list and generate the output
	*/
	function generate() {
		$this->start();
		$this->output();
	}


	/**
	* Construct the file list
	*
	* Add SVG files in the {@link $strips_path} directory and sort them lexically.
	*/
	function strips_list_get() {
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
	*/
	function strip_info_get( $element_asked ) {
		$this->current_id = $element_asked;

		$file = $this->strips_list[$element_asked];

		$svg = $this->strips_path.'/'.$file;

		// change the extension for {@link $img_src}
		$png = explode( '.', $file);
		$this->img_src = $this->strips_path.'/'.$png[0].'.png';

		$this->source = $svg;
		
		if( file_exists($svg) ) {
			$data = file_get_contents( $svg );
		
			// note modifieurs : i = indépendant de la casse, s = . comprends les \n

			// DC titles = document title, then author
			preg_match_all('/<dc:title>(.*?)<\/dc:title>/i', $data, $matches);
			$this->title = $matches[1][0];
			$this->author = html_entity_decode( $matches[1][1] );

			// Date
			preg_match('/<dc:date>(.*?)<\/dc:date>/i', $data, $matches);
			$this->date = $matches[1];

			// License URL
			preg_match_all('/rdf:resource="(.*?)" \/>/i', $data, $matches);
			$this->license = $matches[1][1];

			// Description
			preg_match_all('/<dc:description>(.*?)<\/dc:description>/is', $data, $matches);
			$this->description = str_replace( "\n", '<br/>', html_entity_decode( $matches[1][0] ) );
			//$this->description = html_entity_decode( $matches[1][0] );

			// All the texts inside the SVG
			preg_match_all('/">(.*?)<\/tspan>/i',$data,$matches);
			$this->text = html_entity_decode( implode( $matches[1], "\n" ) );
		}

		// if one want to use punbb as forum
		if( $this->general->use_punbb ) {
			// lasts posts associated to the strip
			$fh = fopen( $this->general->forum.'/extern.php?action=topic&tid=1', 'r');

			if (!$fh) {
				// TODO traduction
				$this->comments = $this->lang-forum_error;
			} else {
				$this->comments = stream_get_contents($fh);
				fclose($fh);
			}
	
			// link for posting a new comment
			$this->forum_post_url = $this->general->forum . '/post.php?ttitle='.$this->title.'&fid='.$this->general->punbb_forum_id;
			
		}
	}


	/**
	* Launch {@link strips_list_get}, check the asked strip, construct naviguation menu, launch {@link strip_info_get} on it
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

		
		// if one want to use punbb as forum
		if( $this->general->use_punbb ) {
			// get the word of the day
			$fh = fopen( $this->general->forum.'/extern.php?action=new&show=1&fid='.$this->general->punbb_wotd_id, 'r');

			if (!$fh) {
				$this->general->wotd = $this->lang-forum_error;
			} else {
				$this->general->wotd = stream_get_contents($fh);
				fclose($fh);
			}
		}

		$this->strip_info_get( $element_asked );
	}


	/**
	* Generate the HTML output with the template engine
	*/
	function output() {
		$output = new HTML_Template_Flexy($this->options);
		$output->compile($this->general->template_html);
		$output->outputObject($this,$this->elements);
	}
}

?>
