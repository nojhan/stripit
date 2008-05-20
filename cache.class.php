<?php
/**
* Strip-It cache manager
*
* @author Leblanc Simon <contact@leblanc-simon.eu>
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2008 Leblanc Simon
*
* @package stripit 
*/
class STRIPIT_Cache
{
	/**
	 * cache folder
	 * @var string
	 */
	var $cache_folder;
	
	/**
	 * template filename
	 * @var string
	 */
	var $template;
	
	/**
	 * name of strip
	 * @var string
	 */
	var $page;
	
	/**
	 * name of the strip folder
	 * @var	string
	 */
	var $strip_folder;
	
	/**
	 * Stat about the strip file
	 * @var array
	 */
	var $stats;
	
	/**
	 * cache data
	 * @var	string
	 */
	var $cache_data;
	
	/**
	 * Constructor
	 *
	 * Init the param of class
	 *
	 * @access	public
	 */
	function STRIPIT_Cache()
	{
		$this->cache_folder	= '';
		$this->template		= '';
		$this->page		= '';
		$this->strip_folder	= '';
		$this->stats		= array();
		$this->cache_data	= '';
	}
	
	
	/**
	 * Init the param of class and check the cache folder
	 *
	 * @param	string	$page		Name of the strip for HTML or number of strip for RSS
	 * @param	string	$template	Name of the template file
	 * @param	string	$strip_folder	Name of the strip folder
	 * @param	string	$cache_folder	Name of the folder where the cache file is save
	 * @param	string	$language	The language to use
	 * @param	bool	$is_rss		True if the page is for rss, False else
	 * @access	public
	 * @return	bool			True if all is OK, false else
	 */
	function init($page, $template, $strip_folder, $cache_folder, $language, $is_rss = false)
	{
		$this->_getName($page, $language, $is_rss);
		$this->template		= $template;
		$this->strip_folder	= $strip_folder;
		$this->cache_data	= '';
		
		if (!empty($cache_folder)) {
			$this->cache_folder = $this->template.'/'.$cache_folder;
		} else {
			$this->cache_folder = $this->template.'/cache';
		}
		
		return $this->_initFolder();
	}
	
	
	/**
	 * check if the page is in cache or if we must generate the page and the cache
	 *
	 * @access	public
	 * @return	bool		True if the page is in cache, false else
	 */
	function isInCache()
	{
		if (!$this->_cacheExist()) {
			return false;
		} else {
			include_once $this->cache_folder.'/'.$this->page;
			$this->_getStats();
			// a variable $stat_cache exist in the file cache
			if (!is_array($stat_cache)) {
				// the variable isn't good, generate cache
				return false;
			}
			
			$count_cache = count($stat_cache);
			$count_model = count($this->stats);
			
			if ($count_cache != $count_model) {
				// the number of file is different, generate cache
				return false;
			}
			
			$compare = array_diff_assoc($stat_cache, $this->stats);
			if (count($compare) == 0) {
				// the variable $cache_data exist in the template file
				$this->cache_data = $cache_data;
				return true;
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Init the folder name and create, if necessary, the folder for the cache
	 *
	 * @access	private
	 * @return	bool			True if it's ok, false if error
	 */
	function _initFolder($cache_folder = '')
	{	
		if (!is_dir($this->cache_folder)) {
			// the directory doesn't exist, we create it!
			if (!mkdir($this->cache_folder, 0777, true)) {
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * Generate the cache file
	 * 
	 * @param	string	$data	The contain HTML or RSS to put in cache
	 * @access	private
	 * @return	bool		True if it's OK, false else
	 */
	function putInCache($data)
	{
		$cache = $this->_genCache($data);
		
		// write in the cache, delete the contain of file, if it exists.
		$handle = fopen($this->cache_folder.'/'.$this->page, 'w');
		if (!$handle) {
			return false;
		}
		
		if (fwrite($handle, $cache) === false) {
			return false;
		}
		
		fclose($handle);
		
		$this->cache_data = $data;
		
		return true;
	}
	
	
	/**
	 * Generate the code to insert in the cache
	 *
	 * @param	string	$data	The contain HTML or RSS to put in cache
	 * @access	private
	 * @return	string		The code PHP + HTML or RSS to put in the cache file
	 */
	function _genCache($data)
	{
		$br = "\n";
		$cache = '<?php'.$br;
		$cache .= $this->_genStats();
		
		$cache .= $br.$br;
		$cache .= 'ob_start();'.$br;
		$cache .= '?>'.$br;
		
		$cache .= $data;
		
		$cache .= $br.$br;
		$cache .= '<?php'.$br;
		$cache .= '$cache_data = ob_get_contents();'.$br;
		$cache .= 'ob_end_clean();'.$br;
		$cache .= '?>'.$br;
		
		return $cache;
	}
	
	
	/**
	 * print cache
	 *
	 * @access	public
	 */
	function getCache()
	{
		echo $this->cache_data;
		//exit();
	}
	
	
	/**
	 * calculate the stats for the strips file
	 *
	 * @access	private
	 */
	function _getStats()
	{
		// Open the given directory
		$handle = opendir($this->strip_folder);

		// Browse the directory
		while($file = readdir($handle)) {
			if($file != "." && $file != "..") {
				// Get the extension of the file
				$ext = pathinfo($file);
				// If it is an SVG
				if( $ext['extension'] == 'svg' ) {
					// Add it to the list
					clearstatcache();
					$this->stats[$file] = filemtime($this->strip_folder.'/'.$file);
				}
			}
		}

		closedir($handle);
	}
	
	/**
	 * Generate the PHP variable $stat_cache
	 *
	 * @access	private
	 * @return	string	The PHP code to insert in the cache file for have the stat at the generation time
	 */
	function _genStats()
	{
		$br = "\n";
		$str = '';
		$i = 0;
		
		if (count($this->stats) == 0) {
			$this->_getStats();
		}
		$str .= '$stat_cache = array(';
		foreach($this->stats as $key => $value) {
			if ($i != 0) {
				$str .= ',';
			}
			$str .= '\''.$key.'\' => \''.$value.'\''.$br;
			$i++;
		}
		$str .= ');'.$br;
		
		return $str;
	}
	
	
	/**
	 * check if the cache file exist
	 *
	 * @access	private
	 * @return	bool		True if the cache file exist, false else
	 */
	function _cacheExist()
	{
		if (file_exists($this->cache_folder.'/'.$this->page)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Generate the filename for cache
	 *
	 * @access	private
	 */
	function _getName($page, $language, $is_rss)
	{
		if ($is_rss) {
			$this->page = $page.'_'.$language.'_cache_rss.php';
		} else {
			$this->page = $page.'_'.$language.'_cache_html.php';
		}
	}
}
?>