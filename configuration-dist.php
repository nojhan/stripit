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
	* URL of the website
	*/
	var $url = 'http://localhost/stripit';
	
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
	var $forum = 'http://perdu.com';

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
	var $template_html = 'template_default.html';
}

?>