<?php
class language
{
	var $suivant	= "Next";
	var $precedent	= "Previous";
	var $premier	= "First";
	var $dernier	= "Last";
	var $accueil	= "Index";
	var $rss	= "RSS";
	var $licence	= "License";
	var $boutique	= "Shop";
	var $teeshirt	= "(t-shirts & gifts)";
	var $propulse	= "Powered by";
	var $descstrip	= "free software for SVG webcomics management";
	var $source	= "source (SVG)";
	var $source_rss = "Click on the image for the source file in the SVG format.";
	var $see_also	= "See also:";
	var $forum	="Forum";
	
	function language()
	{
		$vars = get_class_vars(__CLASS__);
		foreach ($vars as $key => $value) {
			$this->$key = $value;
		}
	}
}
?>
