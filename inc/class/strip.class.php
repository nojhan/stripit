<?php
/**
 * Class for manage the strip
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 */
class Strip
{
  /**
   * The filename of the SVG strip
   * @var string
   * @access protected
   */
  protected $filename = null;
  
  /**
   * The licence
   * @var string
   * @access protected
   */
  protected $license = '';
  
  /**
   * The title
   * @var string
   * @access protected
   */
  protected $title = '';
  
  /**
   * The author
   * @var string
   * @access protected
   */
  protected $author = '';
  
  /**
   * The date of creation of the strip
   * @var string
   * @access protected
   */
  protected $date = '';
  
  /**
   * The description of the strip
   * @var string
   * @access protected
   */
  protected $description = '';
  
  /**
   * All the text contained in the SVG artwork and alternate text for image
   * @var string
   * @access protected
   */
  protected $text = '';
  
  /**
   * The size in bytes of the SVG file
   * @var integer
   * @access protected
   */
  protected $source_size = 0;
  
  
  /**
   * The constructor can be initialize and parse a SVG strip
   *
   * @param string $file The filename of the string (only filename, not the path)
   * @param boolean $parse True if you want parse the SVG file, False else
   * @access public
   */
  public function __construct($file = null, $parse = false)
  {
    if ($file !== null) {
      $this->init($file);
      if ($parse === true) {
        $this->parse();
      }
    }
  }
  
  
  /**
   * Initialize the object with the filename
   *
   * @param string $file The filename of the string (only filename, not the path)
   * @access protected
   */
  protected function init($file)
  {
    $this->setFilename($file);
  }
  
  
  /**
   * Parse the SVG file and call the setter of the object
   *
   * @access protected
   * @todo look for resolv the problem with the namespace which change with Inkscape
   */
  protected function parse()
  {
    $dom = new DOMDocument();
    $dom->load(Config::getStripFolder().'/'.$this->getFilename());
    
    // define the namespace use
    $ns_cc    = 'http://creativecommons.org/ns#';
    $ns_oldcc = 'http://web.resource.org/cc/'; // Fucking inkscape, they change namespace in rev 20897, we must control the old if the new return null
    $ns_dc    = 'http://purl.org/dc/elements/1.1/';
    $ns_rdf   = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    
    // The license
    $license = $this->searchDomItem($dom, $ns_cc, 'license', true, $ns_rdf, 'resource');
    if ($license === null) {
      $license = $this->searchDomItem($dom, $ns_oldcc, 'license', true, $ns_rdf, 'resource');
    }
    $this->setLicense($license);
    
    // The title
    $title = $this->searchDomItem($dom, $ns_dc, 'title', false, null, null, array($ns_cc => 'Work'));
    if ($title === null) {
      $title = $this->searchDomItem($dom, $ns_dc, 'title', false, null, null, array($ns_oldcc => 'Work'));
    }
    $this->setTitle($title);
    
    // The author
    $author = $this->searchDomItem($dom, $ns_dc, 'title', false, null, null, array($ns_cc => 'Agent', $ns_dc => 'creator'));
    if ($author === null) {
      $author = $this->searchDomItem($dom, $ns_dc, 'title', false, null, null, array($ns_oldcc => 'Agent', $ns_dc => 'creator'));
    }
    $this->setAuthor($author);
    
    // The date
    $date = $this->searchDomItem($dom, $ns_dc, 'date');
    $this->setDate($date);
    
    // The description
    $description = $this->searchDomItem($dom, $ns_dc, 'description');
    $this->setDescription($description);
    
    // The text
    $text = $this->searchDomItem($dom, '*', 'tspan');
    $this->setText($text);
  }
  
  
  /**
   * Create the cache for this strip
   *
   * @access protected
   * @throws Exception If the Strip object isn't initalize an exception is throwed
   * @throws Exception If the cache can't be writing an exception is throwed
   */
  protected function setCache()
  {
    if ($this->filename === null) {
      throw new Exception('This object isn\'t initialized!');
    }
    
    $cache = serialize($this);
    if (file_put_contents(Config::getCacheFolder().'/'.$this->getFilename().'.php', $cache) === 0) {
      throw new Exception('The cache file "'.Config::getCacheFolder.'/'.$this->getFilename().'.php" can\'t be writing');
    }
  }
  
  
  /**
   * Get the cache and return the Strip object cached
   *
   * @param string $file The filename of the strip (only the filename and not the path)
   * @access public
   * @static
   * @throws Exception If the cache doesn't exist, an exception is throwed
   * @return Strip The Strip object related with the cache
   */
  public static function getCache($file)
  {
    $strip_tmp = new Strip($file);
    $cache_file = Config::getCacheFolder().'/'.$strip_tmp->getFilename().'.php';
    if (file_exists($cache_file) === false) {
      throw new Exception('The cache for "'.$file.'" doesn\'t exist!');
    }
    
    $strip = file_get_contents($cache_file);
    
    return unserialize($strip);
  }
  
  
  /**
   * Create cache for one or all necessary strips
   *
   * @param string $file The filename of the strip for which we must regenerate cache or null if we must regenerate all cache file necessary
   * @access public
   * @static
   */
  public static function createCache($file = null)
  {
    if ($file === null) {
      // we must regenerate all SVG cache
      $actual_cache = Cache::getCache();
      
      Cache::setCache();
      
      $new_cache = Cache::getCache();
      
      $compare = array_diff($new_cache, $actual_cache);
      foreach ($compare as $filename => $time) {
        $strip = new Strip($filename, true);
        $strip->setCache();
      }
    } else {
      $strip = new Strip($file, true);
      $strip->setCache();
    }
  }
  
  
  /**
   * Return the filename of the SVG
   * @return string the filename of the SVG
   * @access public
   */
  public function getFilename() { return $this->filename; }
  
  /**
   * Return the license
   * @return string the license
   * @access public
   */
  public function getLicense() { return $this->license; }
  
  /**
   * Return the title
   * @return string the title
   * @access public
   */
  public function getTitle() { return $this->title; }
  
  /**
   * Return the author
   * @return string the author
   * @access public
   */
  public function getAuthor() { return $this->author; }
  
  /**
   * Return the date of creation
   * @param boolean $rfc True if you want the date in the RFC format, False if you want the date like in the SVG
   * @return string the date of creation
   * @access public
   */
  public function getDate($rfc = false) { return ($rfc === true) ? date('r', strtotime($this->date)) : $this->date; }
  
  /**
   * Return the description
   * @return string the description
   * @access public
   */
  public function getDescription() { return $this->description; }
  
  /**
   * Return the text
   * @return string the text
   * @access public
   */
  public function getText() { return htmlentities($this->text, ENT_QUOTES, 'UTF-8'); }
  
  /**
   * Return the size of the SVG
   * @return integer the size of the SVG
   * @access public
   */
  public function getSourceSize() { return $this->source_size; }
  
  
  /**
   * Return the path with filename of of the strip in PNG format
   * @return string the path with filename of file of the strip in PNG format
   * @access public
   */
  public function getFilenamePng()
  {
    $filename = pathinfo(Config::getStripFolder().'/'.$this->getFilename(), PATHINFO_FILENAME);
    return Config::getStripFolder().'/'.$filename.'.png';
  }
  
  
  /**
   * Return the path with filename of file of the strip in SVG format
   * @return string the path with filename of file of the strip in SVG format
   * @access public
   */
  public function getFilenameSrc()
  {
    return Config::getStripFolder().'/'.$this->getFilename();
  }
  
  
  /**
   * Return the path with filename of thumbnail of the strip in PNG format
   * @return string the path with filename of thumbnail of the strip in PNG format
   * @access public
   */
  public function getThumbSrc()
  {
    $original = $this->getFilenamePng();
    $dest = Config::getThumbFolder().'/'.pathinfo(Config::getStripFolder().'/'.$this->getFilename(), PATHINFO_FILENAME).'.png';
    
    if (createThumb($original, $dest) === true) {
      return $dest;
    } else {
      return $original;
    }
  }
  
  /**
   * Setter for the filename
   * @param string $file The filename
   * @throws Exception If the file doesn't exist an exception is throwed
   * @access public
   */
  protected function setFilename($file)
  {
    if (file_exists(Config::getStripFolder().'/'.$file) === false) {
      throw new Exception('The filename "'.$file.'" isn\'t a valid file!');
    }
    
    $this->filename = $file;
    $this->setSourceSize();
  }
  
  /**
   * Setter for the license
   * @param string $file The license
   * @access public
   */
  public function setLicense($license)
  {
    if (is_string($license) === false) {
      $license = (string) $license;
    }
    
    $this->license = $license;
  }
  
  /**
   * Setter for the license
   * @param string $file The license
   * @access public
   */
  protected function setTitle($title)
  {
    if (is_string($title) === false) {
      $title = (string) $title;
    }
    
    $this->title = $title;
  }
  
  /**
   * Setter for the author
   * @param string $file The author
   * @access public
   */
  protected function setAuthor($author)
  {
    if (is_string($author) === false) {
      $author = (string) $author;
    }
    
    $this->author = $author;
  }
  
  /**
   * Setter for the date
   * @param string $file The date
   * @access public
   */
  protected function setDate($date)
  {
    if (is_string($date) === false) {
      $date = (string) $date;
    }
    
    $this->date = $date;
  }
  
  /**
   * Setter for the description
   * @param string $file The description
   * @access public
   */
  protected function setDescription($description)
  {
    if (is_string($description) === false) {
      $description = (string) $description;
    }
    
    $this->description = $description;
  }
  
  /**
   * Setter for the text
   * @param string $file The text
   * @access public
   */
  protected function setText($text)
  {
    if (is_string($text) === false) {
      $text = (string) $text;
    }
    
    $this->text = $text;
  }
  
  /**
   * Setter for the source_size
   * @access public
   */
  protected function setSourceSize()
  {
    $this->source_size = filesize($this->getFilenameSrc());
  }
  
  
  /**
   * Return the value search in the SVG
   *
   * @param DOMDocument $dom The DOMDocument object of the SVG
   * @param string $namespace The namespace where you want search
   * @param string $item The item search
   * @param boolean $search_attribute True if you want the value of an attribute, False if you want the content
   * @param string $attribute_namespace The namespace of the attribute where you want search
   * @param string $attribute_name The attribute search
   * @param array $parents The list (namespace and item) of the parent for get the value of only one item which exist more than one
   * @access public
   * @return string The value of your search (null if doesn't exist)
   * @todo decompose this method to have one method by type of search
   */
  protected function searchDomItem(DOMDocument $dom, $namespace, $item, $search_attribute = false, $attribute_namespace = null, $attribute_name = null, $parents = array())
  {
    $items = $dom->getElementsByTagNameNS($namespace, $item);
    
    if ($items->length === 1) {
      // there are only one element with $namespace and $item
      $item = $items->item(0);
      
      if ($search_attribute === false) {
        // we want the content of node
        return $item->textContent;
      } else {
        // we want the value of one attribute
        $attributes = $item->attributes;
        if ($attributes === null) {
          return null;
        }
        
        $attr = $attributes->getNamedItemNS($attribute_namespace, $attribute_name);
        return $attr->nodeValue;
      }
    } elseif ($items->length > 1) {
      if (count($parents) === 0) {
        // we want a return with all content of the elements
        $return_value = '';
        for ($i = 0; $i < $items->length; $i++) {
          $return_value .= $items->item($i)->textContent.' ';
        }
        
        return $return_value;
      } else {
        // we want check the parent for return only the content of one element
        for ($i = 0; $i < $items->length; $i++) {
          $item = $items->item($i);
          $parent = $item->parentNode;
          foreach ($parents as $namespace => $local_name) {
            if ($parent->namespaceURI !== $namespace || $parent->localName !== $local_name) {
              continue 2;
            }
            $parent = $parent->parentNode;
          }
          
          return $item->textContent;
        }
        
        return null;
      }
    } else {
      return null;
    }
  }
}