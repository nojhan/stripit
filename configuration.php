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
	var $url = 'http://localhost/~nojhan/stripit/trunk';
	
	/**
	* Title of the website
	*/
	var $title = 'Geekscottes';

	/**
	* Short description
	*
	* Is displayed as a subtitle
	*/
	var $description = 'Des miettes libres';

	/**
	* Default language of the interface
	*/
	var $language = 'fr-FR';

	/**
	* Webmaster's name
	*/
	var $webmaster = 'nojhan';
	
	/**
	* Webmaster's email
	*/
	var $email = 'nojhan@gmail.com';

	/**
	* Forum URL
	*/
	var $forum = 'http://www.nojhan.net/geekscottes/forum';

	/**
	* Additional URL
	*/
	var $see_also = array(
		'Bash' => 'http://bash.org',
		'BashFr' => 'http://bashfr.org',
		'LinuxFr' => 'http://linuxfr.org',
		'Gary Larson' => 'http://www.thefarside.com/',
		'Calvin et Hobbes' => 'http://www.gocomics.com/calvinandhobbes/',
		'Le Chat' => 'http://www.geluck.com',
		'XKCD' => 'http://www.xkcd.com',
		'Piled Higher and Deeper' => 'http://www.phdcomics.com',
		'Wulffmorgenthaler' => 'http://www.wulffmorgenthaler.com',
		'User friendly' => 'http://ars.userfriendly.org'
	);

	/**
	* Shop URL
	*/
	var $shop = 'http://geekscottes.spreadshirt.net/fr/FR/Shop';
	
	/**
	* HTML template to use
	*/
	var $template_html = 'template_default.html';
}

?>
