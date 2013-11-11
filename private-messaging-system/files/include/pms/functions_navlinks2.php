<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

if (!$pun_user['is_guest'] && $pun_config['o_pms_enabled'] && $pun_user['g_pm'] == 1)
{
	$pms_link = '<li id="navpm"><a href="message_list.php">'.$lang_pms['Messages'].'</a></li>';
	$pms_pos = (!$pun_user['is_admmod']) ? -1 : -2;

	// Insert Messages link
	array_splice($links, $pms_pos, 0, $pms_link);
}
