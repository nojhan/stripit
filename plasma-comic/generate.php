<?php
/**
* Strip-It Plasma Comic package generator
*
* @author Frédéric 'GeoVah' Forjan 
* @license http://www.gnu.org/licenses/gpl.html GPL
* @copyright 2009 Forjan Frédéric
*
* @package stripit 
*/


set_include_path(get_include_path() . PATH_SEPARATOR . getcwd());

(@include_once '../conf/configuration.php') or
        die("Strip It isn't configured yet: conf/configuration.php file is missing.<br/>See README for details.");

   @require_once("zipstream.php");

/**
 * generate the metadata conf
 */
function getMetadataContent($conf)
{
return "[Desktop Entry]\n".
"Name=". $conf->title ."\n" .
"Comment=". $conf->title . "\n" .
"Type=Service\n" .
"X-KDE-ServiceTypes=Plasma/Comic\n" .
"Icon=favicon.png\n" .
"\n" .
"X-KDE-Library=plasma_comic_krossprovider\n" .
"X-KDE-PluginInfo-Author=Frederic Forjan\n" .
"X-KDE-PluginInfo-Email=fforjab@free.fr\n" .
"X-KDE-PluginInfo-Name=".$conf->title."\n" .
"X-KDE-PluginInfo-Version=0.2\n" .
"X-KDE-PluginInfo-Website=http://stripit.sf.net\n" .
"X-KDE-PluginInfo-License=GPLv2\n" .
"X-KDE-PluginInfo-EnabledByDefault=true\n" .
"X-KDE-PlasmaComicProvider-SuffixType=Number";
}

/**
 * return the favicon content
 */
function getFaviconContent()
{
return implode("",file("../favicon.png"));
}

/**
 * generate the main.es file
 */
function getMainEsContent($conf)
{
 return "//auto-generated URL\nvar URL =  \"" . $conf->url ."/\"\n" . implode("",file("main.es.template"));
}

   

   # create a new stream object
   $conf = new configuration();
   $zipfile = new ZipStream($conf->title .".comic",array('content_type'=>'application/octet-stream'));

   
   $conf = new configuration();
   $zipfile -> add_file("favicon.png",getFaviconContent());
   
   $zipfile -> add_file("metadata.desktop",getMetadataContent($conf));
   $zipfile -> add_file("contents/code/main.es",getMainEsContent($conf));
   
   $zipfile -> finish();

?>
