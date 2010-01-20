<?php
/**
 * The class for the english language
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @package stripit
 * @see AbstractLang
 */
class Lang extends AbstractLang
{
  /**
   * We initialize the attributes of the abstract class with the good language
   *
   * @access public
   */
  public function __construct()
  {
    $this->language     = 'en-EN';
    
    $this->suivant      = "Next";
    $this->precedent    = "Previous";
    $this->premier      = "First";
    $this->dernier      = "Last";
    $this->accueil      = "Index";
    $this->contact      = "Contact";
    $this->rss          = "RSS";
    $this->licence      = "License";
    $this->permanent_link = "Permanent link";
    $this->boutique     = "Shop";
    $this->teeshirt     = "(t-shirts & gifts)";
    $this->propulse     = "Powered by";
    $this->descstrip    = "free software for SVG webcomics management";
    $this->source       = "source (SVG)";
    $this->source_rss   = "Click on the image for the source file in the SVG format.";
    $this->see_also     = "See also:";
    $this->forum        = "Forum";
    $this->forum_new    = "New";
    $this->forum_view   = "Show the topic";
    $this->forum_error  = "Cannot read comments";
    $this->comments     = "Comments";
    $this->wotd         = "Word of the day";
    $this->gallery      = "Gallery";
  }
}