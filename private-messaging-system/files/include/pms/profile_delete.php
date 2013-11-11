<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


// Delete user's private messages
$db->query('DELETE FROM '.$db->prefix.'messages WHERE owner='.$id) or error('Unable to delete user\'s messages', __FILE__, __LINE__, $db->error());
