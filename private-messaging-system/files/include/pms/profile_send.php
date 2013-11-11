<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

if ($pun_config['o_pms_enabled'] && !$pun_user['is_guest'] && $pun_user['g_pm'] == 1)
{
		$user_personal[] = '<dt>'.$lang_pms['PM'].'</dt>';
		$user_personal[] = '<dd><span class="pm">'.'<a href="message_send.php?id='.$id.'">'.$lang_pms['Quick message'].'</a>'.'</span></dd>';
}
