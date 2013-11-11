<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

if ($pun_config['o_pms_enabled'] && !$pun_user['is_guest'] && $pun_user['g_pm'] == 1)
{
	$pid = isset($cur_post['poster_id']) ? $cur_post['poster_id'] : $cur_post['id'];
	$user_contacts[] = '<span class="pm"><a href="message_send.php?id='.$pid.'&amp;tid='.$id.'">'.$lang_pms['PM'].'</a></span>';
}
