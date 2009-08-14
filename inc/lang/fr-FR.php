<?php
/**
 * The class for the french language
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
    $this->language     = 'fr-FR';
    
    $this->suivant      = "Suivant";
    $this->precedent    = "Précedent";
    $this->premier      = "Premier";
    $this->dernier      = "Dernier";
    $this->accueil      = "Accueil";
    $this->contact      = "Contact";
    $this->rss          = "RSS";
    $this->licence      = "Licence";
    $this->boutique     = "Boutique";
    $this->teeshirt     = "(t-shirts & cadeaux)";
    $this->propulse     = "Propulsé par";
    $this->descstrip    = "logiciel libre de gestion de webcomics en SVG";
    $this->source       = "source (SVG)";
    $this->source_rss   = "Cliquez sur l'image pour le fichier source au format SVG.";
    $this->see_also     = "Voir aussi :";
    $this->forum        = "Forum";
    $this->forum_new    = "Nouveau";
    $this->forum_view   = "Voir le sujet";
    $this->forum_error  = "Impossible de lire les commentaires";
    $this->comments     = "Commentaires";
    $this->wotd         = "Dernier message du webmaster";
    $this->gallery      = "Galerie";
  }
}