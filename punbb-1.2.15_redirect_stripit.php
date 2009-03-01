<?php
/***********************************************************************

  Copyright (C) 2002-2005  Rickard Andersson (rickard@punbb.org)

  This file is part of PunBB.

  PunBB is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  PunBB is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/
define('PUN_ROOT', './');
@include PUN_ROOT.'config.php';

// If PUN isn't defined, config.php is missing or corrupt
if (!defined('PUN'))
	exit('The file \'config.php\' doesn\'t exist or is corrupt. Please run install.php to install PunBB first.');


// Make sure PHP reports all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

// Turn off magic_quotes_runtime
set_magic_quotes_runtime(0);


// Load the functions script
require PUN_ROOT.'include/functions.php';

// Load DB abstraction layer and try to connect
require PUN_ROOT.'include/dblayer/common_db.php';

// Load cached config
@include PUN_ROOT.'cache/cache_config.php';
if (!defined('PUN_CONFIG_LOADED'))
{
    require PUN_ROOT.'include/cache.php';
    generate_config_cache();
    require PUN_ROOT.'cache/cache_config.php';
}

// Make sure we (guests) have permission to read the forums
$result = $db->query('SELECT g_read_board FROM '.$db->prefix.'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if ($db->result($result) == '0')
	exit('No permission');


// Attempt to load the common language file
@include PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/common.php';
if (!isset($lang_common))
	exit('There is no valid language pack \''.$pun_config['o_default_lang'].'\' installed. Please reinstall a language of that name.');

// Check if we are to display a maintenance message
if ($pun_config['o_maintenance'] && !defined('PUN_TURN_OFF_MAINT'))
	maintenance_message();

if (!isset($_GET['ttitle']))
	exit('No parameters supplied. See redirect_stripit.php for instructions.');


$sql = 'SELECT t.id
        FROM '.$db->prefix.'topics AS t
        WHERE t.subject="'.utf8_decode($_GET['ttitle']).'";';

$res = $db->query( $sql );

if ($res != false && $db->num_rows($res) > 0) {
    $_tid = $db->fetch_row( $res );
    $tid = intval( $_tid[0] );
} else {
    $tid = 0;
}

if( $tid > 0 ) {
    header('Location: viewtopic.php?id='.$tid);
} else {
    header('Location: index.php');
}
exit;