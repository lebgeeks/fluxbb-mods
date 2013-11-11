<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


if ($pun_user['is_guest'] || $pun_user['g_pm'] == 0)
	message($lang_common['No permission']);

if (empty($_GET['id']))
	message($lang_common['Bad request']);
$id = intval($_GET['id']);

// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

// Load the delete.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/delete.php';

// Load the misc.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/misc.php';

// Fetch some info from the message we are deleting
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result))
	message($lang_common['Bad request']);

$cur_post = $db->fetch_assoc($result);

// Check permissions
if ($cur_post['owner'] != $pun_user['id'])
	message($lang_common['No permission']);

if (isset($_POST['delete']))
{
	// Check id
	if (empty($_GET['id']))
		message($lang_common['Bad request']);
	$id = intval($_GET['id']);
	
	if ($pun_user['is_admmod'])
		confirm_referrer('message_delete.php');

	// Delete message
	$db->query('DELETE FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to delete message', __FILE__, __LINE__, $db->error());

	// Redirect
	redirect('message_list.php?box='.$_POST['box'].'&amp;p='.$_POST['p'], $lang_pms['Del redirect']);
}
else
{
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_pms['Delete message']);

	define('PUN_ACTIVE_PAGE', 'pm');
	require PUN_ROOT.'header.php';
	require PUN_ROOT.'include/parser.php';

	$cur_post['message'] = parse_message($cur_post['message'], (!$cur_post['smileys']));
?>
<div class="blockform">
	<h2><span><?php echo $lang_pms['Delete message'] ?></span></h2>
	<div class="box">
		<form method="post" action="message_delete.php?id=<?php echo $id ?>">
			<div class="inform">
				<input type="hidden" name="box" value="<?php echo intval($_GET['box']) ?>" />
				<input type="hidden" name="p" value="<?php echo intval($_GET['p']) ?>" />
				<fieldset>
					<legend><?php echo $lang_misc['Confirm delete legend'] ?></legend>
					<div class="infldset">
						<div class="postmsg">
							<p><?php echo $lang_pms['Sender'] ?>: <strong><?php echo pun_htmlspecialchars($cur_post['sender']) ?></strong></p>
							<?php echo $cur_post['message'] ?>
						</div>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="delete" value="<?php echo $lang_delete['Delete'] ?>" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}
