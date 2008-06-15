<?php
/**
* Strip-It HTML index
*
* @author Johann "nojhan" Dréo <nojhan@gmail.com>
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2007 Johann Dréo
*
* @package stripit 
*/


set_include_path(get_include_path() . PATH_SEPARATOR . getcwd());

require_once 'strip_manager.php';

// instanciation and ouput
$ctl = new strip_manager;
$ctl->generate();

?>
