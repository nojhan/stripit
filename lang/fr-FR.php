<?php
class language
{
	var $suivant	= "Suivant";
	var $precedent	= "Précedent";
	var $premier	= "Premier";
	var $dernier	= "Dernier";
	var $accueil	= "Accueil";
	var $rss	= "RSS";
	var $licence	= "Licence";
	var $boutique	= "Boutique";
	var $teeshirt	= "(t-shirts & cadeaux)";
	var $propulse	= "Propulsé par";
	var $descstrip	= "logiciel libre de gestion de webcomics en SVG";
	var $source	= "source (SVG)";
	var $source_rss = "Cliquez sur l'image pour le fichier source au format SVG.";
	var $see_also	= "Voir aussi :";
	var $forum	= "Forum";
	
	function language()
	{
		$vars = get_class_vars(__CLASS__);
		foreach ($vars as $key => $value) {
			$this->$key = $value;

		}
	}
}
?>
