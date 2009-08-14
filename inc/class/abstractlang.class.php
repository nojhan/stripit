<?php
/**
 * Abstract class for i18n
 *
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @copyright 2009 Johann Dréo, Simon Leblanc
 * @abstract
 * @package stripit
 */

abstract class AbstractLang
{
  /**
   * The locale of the class
   * @var string
   * @access  protected
   */
  protected $language     = 'fr-FR';
  
  protected $suivant      = "Suivant";
  protected $precedent    = "Précedent";
  protected $premier      = "Premier";
  protected $dernier      = "Dernier";
  protected $accueil      = "Accueil";
  protected $contact      = "Contact";
  protected $rss          = "RSS";
  protected $licence      = "Licence";
  protected $boutique     = "Boutique";
  protected $teeshirt     = "(t-shirts & cadeaux)";
  protected $propulse     = "Propulsé par";
  protected $descstrip    = "logiciel libre de gestion de webcomics en SVG";
  protected $source       = "source (SVG)";
  protected $source_rss   = "Cliquez sur l'image pour le fichier source au format SVG.";
  protected $see_also     = "Voir aussi :";
  protected $forum        = "Forum";
  protected $forum_new    = "Nouveau";
  protected $forum_view   = "Voir le sujet";
  protected $forum_error  = "Impossible de lire les commentaires";
  protected $comments     = "Commentaires";
  protected $wotd         = "Dernier message du webmaster";
  protected $gallery      = "Galerie";
  
  /**
   * Overload the method __toString for show the locale of the class
   * @access  public
   * @return  string  The locale of the class
   */
  public function __toString() { return $this->language; }
  
  /*
   All getter for access to protected attributes
   */
  public function getSuivant() { return $this->suivant; }
  public function getPrecedent() { return $this->precedent; }
  public function getPremier() { return $this->premier; }
  public function getDernier() { return $this->dernier; }
  public function getAccueil() { return $this->accueil; }
  public function getContact() { return $this->contact; }
  public function getRss() { return $this->rss; }
  public function getLicence() { return $this->licence; }
  public function getBoutique() { return $this->boutique; }
  public function getTeeshirt() { return $this->teeshirt; }
  public function getPropulse() { return $this->propulse; }
  public function getDescstrip() { return $this->descstrip; }
  public function getSource() { return $this->source; }
  public function getSourceRss() { return $this->source_rss; }
  public function getSeeAlso() { return $this->see_also; }
  public function getForum() { return $this->forum; }
  public function getForumNew() { return $this->forum_new; }
  public function getForumView() { return $this->forum_view; }
  public function getForumError() { return $this->forum_error; }
  public function getComments() { return $this->comments; }
  public function getWotd() { return $this->wotd; }
  public function getGallery() { return $this->gallery; }
}