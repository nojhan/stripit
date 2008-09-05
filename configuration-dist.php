<?php
/**
* Strip-It configuration
*
* @author Johann "nojhan" Dréo <nojhan@gmail.com>
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2007 Johann Dréo
*
* @package stripit 
*/

/**
* Configuration 
*/
class configuration
{
	/**
	* Software version
	*/
	var $version = 'pre-0.5 (2008-05-12)';

	/**
	* URL of the website
	*/
	var $url = 'http://localhost/~nojhan/stripit/trunk';
	
	/**
	* Title of the website
	*/
	var $title = 'Stripit';

	/**
	* Short description
	*
	* Is displayed as a subtitle
	*/
	var $description = 'Ce serait mieux avec des strips libres !';

	/**
	* Default language of the interface
	*/
	var $language = 'fr-FR';

	/**
	* Webmaster's name
	*/
	var $webmaster = 'inconnu';
	
	/**
	* Webmaster's email
	*/
	var $email = 'inconnu';

	/**
	* Forum URL
	*/
	var $forum = 'http://www.nohan.net/geekscottes/forum';

	/**
	* Use PunBB integration ?
	*/
	var $use_punbb = true;

	/**
	* PunBB's forum ID to use for strips comment
	*/
	var $punbb_forum_id = '3';

	/**
	* PunBB's forum ID to use for word of the day
	*/
	var $punbb_wotd_id = '5';

	/**
	* Additional URL
	*/
	var $see_also = array(
		'Geekscottes' => 'http://www.nojhan.net/geekscottes'
	);

	/**
	* Shop URL
	*/
	var $shop = 'http://perdu.com';
	
	/**
	* HTML template to use
	*/
	var $template_html = 'template_lego.html';

	/**
	* Number of thumbnails per gallery page
	*/
	var $thumbs_per_page = 6;

    /**
    * Size of the thumbnails
    */
    var $thumb_size = "200px";
}

?>
