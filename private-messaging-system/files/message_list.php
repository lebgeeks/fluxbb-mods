<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/parser.php';


if (!$pun_config['o_pms_enabled'] || $pun_user['g_pm'] == 0)
	message($lang_common['No permission']);

if ($pun_user['is_guest'])
	message($lang_common['Not logged in']);

// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

// Load the topic.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';

// Load the misc.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/misc.php';

// Inbox or Sent?
if (isset($_GET['box']))
	$box = intval($_GET['box']);
else
	$box = 0;

$box != 1 ? $box = 0 : $box = 1;
$box != 1 ? $status = 0 : null;
$box == 0 ? $name = $lang_pms['Inbox'] : $name = $lang_pms['Outbox'];
//$name plus the link to the other box
$page_name = $name;

// Delete multiple messages
if (isset($_POST['delete_messages']) || isset($_POST['delete_messages_comply']))
{
	if (isset($_POST['delete_messages_comply']))
	{
		// Check this is legit
		if ($pun_user['is_admmod'])
			confirm_referrer('message_list.php');

		if (@preg_match('/[^0-9,]/', $_POST['messages']))
			message($lang_common['Bad request']);

		// Delete messages
		$db->query('DELETE FROM '.$db->prefix.'messages WHERE id IN('.$_POST['messages'].') AND owner='.$pun_user['id']) or error('Unable to delete messages.', __FILE__, __LINE__, $db->error());

		redirect('message_list.php?box='.intval($_POST['box']), $lang_pms['Deleted redirect']);
	}
	else
	{
		$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_pms['Multidelete']);
		$idlist = is_array($_POST['delete_messages']) ? array_map("intval", $_POST['delete_messages']) : array();
		define('PUN_ACTIVE_PAGE', 'pm');
		require PUN_ROOT.'header.php';
?>
<div class="blockform">
	<h2><span><?php echo $lang_pms['Multidelete'] ?></span></h2>
	<div class="box">
		<form method="post" action="message_list.php">
			<div class="inform">
				<input type="hidden" name="messages" value="<?php echo implode(',', array_values($idlist)) ?>" />
				<input type="hidden" name="box" value="<?php echo intval($_POST['box']) ?>" />
				<fieldset>
					<legend><?php echo $lang_misc['Confirm delete legend'] ?></legend>
					<div class="infldset">
						<p><?php echo $lang_pms['Delete messages comply'] ?></p>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="delete_messages_comply" value="<?php echo $lang_pms['Delete'] ?>" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php
		require PUN_ROOT.'footer.php';
	}
}

// Mark all messages as read
else if (isset($_GET['action']) && $_GET['action'] == 'markall')
{
	$db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE owner='.$pun_user['id']) or error('Unable to update message status', __FILE__, __LINE__, $db->error());
	$p = (!isset($_GET['p']) || $_GET['p'] <= 1) ? 1 : intval($_GET['p']);
	redirect('message_list.php?box='.$box.'&amp;p='.$p, $lang_pms['Read redirect']);
}

else if (isset($_GET['email_pm']))
{
	if (intval($_GET['email_pm']) == 1)
	{
		$result = $db->query('SELECT email_pm FROM '.$db->prefix.'users WHERE id='.$pun_user['id'].' AND email_pm=1') or error('Unable to fetch pm email info', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message($lang_pms['E-mail already enabled']);

		$db->query('UPDATE '.$db->prefix.'users SET email_pm=1 WHERE id='.$pun_user['id']) or error('Unable to update email_pm data', __FILE__, __LINE__, $db->error());

		redirect('message_list.php', $lang_pms['E-mail enabled redirect']);
	}
	else
	{
		$db->query('UPDATE '.$db->prefix.'users SET email_pm=0 WHERE id='.$pun_user['id']) or error('Unable to update pm email data', __FILE__, __LINE__, $db->error());

		redirect('message_list.php', $lang_pms['E-mail disabled redirect']);
	}
}


if ($pun_config['o_pms_email'] == '1' && !(isset($_GET['action']) && $_GET['action'] == 'multidelete'))
{
	if ($pun_user['email_pm'])
		// I apologize for the variable naming here. It's a mix of subscription and action I guess :-)
		$subscraction = '<p class="subscribelink clearb">'.$lang_pms['E-mail is enabled'].' - <a href="message_list.php?email_pm=0">'.$lang_pms['Disable'].'</a></p>'."\n";
	else
		$subscraction = '<p class="subscribelink clearb"><a href="message_list.php?email_pm=1">'.$lang_pms['Enable e-mail'].'</a></p>'."\n";
}
else
	$subscraction = '<div class="clearer"></div>'."\n";



$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_pms['Private Messages'].' - '.$name);

// Get message count
$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE status='.$box.' AND owner='.$pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
list($num_messages) = $db->fetch_row($result);

// What page are we on?
$num_pages = ceil($num_messages / $pun_config['o_pms_mess_per_page']);
$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
$start_from = $pun_config['o_pms_mess_per_page'] * ($p - 1);
$limit = $start_from.','.$pun_config['o_pms_mess_per_page'];

define('PUN_ACTIVE_PAGE', 'pm');
require PUN_ROOT.'header.php';

?>
<div class="block2col">
	<div class="blockmenu">
		<h2><span><?php echo $lang_pms['Private Messages'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li <?php if ($box == 0) echo 'class="isactive"' ?>><a href="message_list.php?box=0"><?php echo $lang_pms['Inbox'] ?></a></li>
					<li<?php if ($box == 1) echo ' class="isactive"' ?>><a href="message_list.php?box=1"><?php echo $lang_pms['Outbox'] ?></a></li>
				</ul>
			</div>
		</div>
	</div>

<?php
// Generate paging links
$paging_links = '<span class="pages-label">'.$lang_common['Pages'].' </span>'.paginate($num_pages, $p, 'message_list.php?box='.$box);

if (isset($_GET['action']) && $_GET['action'] == 'multidelete')
	$post_link = "\t\t\t".'<p class="postlink conr"><input type="hidden" name="box" value="'.$box.'" /><input type="submit" value="'.$lang_pms['Delete'].'" /></p>'."\n";
else
	$post_link = "\t\t\t".'<p class="postlink conr"><a href="message_send.php">'.$lang_pms['New message'].'</a></p>'."\n";

?>
<div style="margin-left: 14em">
<form method="post" action="message_list.php">
<div class="linkst" style="MARGIN-TOP: 0px;PADDING-TOP: 0px;clear: none">
	<div class="inbox crumbsplus" style="overflow: hidden">
		<ul class="crumbs" style="clear: none">
			<li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li>
			<li><span>»&#160;</span><a href="message_list.php"><strong><?php echo pun_htmlspecialchars($lang_pms['Private Messages']) ?></strong></a></li>
			<li><span>»&#160;</span><strong><?php echo pun_htmlspecialchars($page_name) ?></strong></li>
		</ul>
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
<?php echo $post_link ?>
		</div>
		<div class="clearer"></div>
	</div>
</div>

<?php

// Are we viewing a PM?
if (isset($_GET['id']))
{
	// Yes! Lets get the details	
	$id = intval($_GET['id']);

	// Set user
	$result = $db->query('SELECT status, owner FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to get message status', __FILE__, __LINE__, $db->error());
	list($status, $owner) = $db->fetch_row($result);
	$status == 0 ? $where = 'u.id=m.sender_id' : $where = 'u.id=m.owner';

	$result = $db->query('SELECT m.id AS mid, m.subject, m.sender_ip, m.message, m.smileys, m.posted, m.showed, u.id, u.group_id as g_id, g.g_user_title, u.username, u.registered, u.email, u.title, u.url, u.location, u.email_setting, u.num_posts, u.admin_note, u.signature, o.user_id AS is_online FROM '.$db->prefix.'messages AS m,'.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.idle=0) LEFT JOIN '.$db->prefix.'groups AS g ON u.group_id = g.g_id WHERE '.$where.' AND m.id='.$id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
	$cur_post = $db->fetch_assoc($result);

	if ($owner != $pun_user['id'])
		message($lang_common['No permission']);

	if ($cur_post['showed'] == 0)
		$db->query('UPDATE '.$db->prefix.'messages SET showed=1 WHERE id='.$id) or error('Unable to update message info', __FILE__, __LINE__, $db->error());

	$user_avatar = '';
	$user_info = array();
	$user_contacts = array();
	$post_actions = array();
	$is_online = '';
	$signature = '';

	if ($cur_post['id'] > 0)
	{
		if ($pun_user['g_view_users'] == '1')
			$username = '<a href="profile.php?id='.$cur_post['id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';
		else
			$username = pun_htmlspecialchars($cur_post['username']);

		$user_title = get_title($cur_post);

		if ($pun_config['o_censoring'] == '1')
			$user_title = censor_words($user_title);

		// Format the online indicator
		$is_online = ($cur_post['is_online'] == $cur_post['id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : '<span>'.$lang_topic['Offline'].'</span>';

		if ($pun_config['o_avatars'] == '1' && $pun_user['show_avatars'] != '0')
		{
			if (isset($user_avatar_cache[$cur_post['id']]))
				$user_avatar = $user_avatar_cache[$cur_post['id']];
			else
				$user_avatar = $user_avatar_cache[$cur_post['id']] = generate_avatar_markup($cur_post['id']);
		}

		// We only show location, register date, post count and the contact links if "Show user info" is enabled
		if ($pun_config['o_show_user_info'] == '1')
		{
			if ($cur_post['location'] != '')
			{
				if ($pun_config['o_censoring'] == '1')
					$cur_post['location'] = censor_words($cur_post['location']);

				$user_info[] = '<dd><span>'.$lang_topic['From'].' '.pun_htmlspecialchars($cur_post['location']).'</span></dd>';
			}

			$user_info[] = '<dd><span>'.$lang_topic['Registered'].' '.format_time($cur_post['registered'], true).'</span></dd>';

			if ($pun_config['o_show_post_count'] == '1' || $pun_user['is_admmod'])
				$user_info[] = '<dd><span>'.$lang_topic['Posts'].' '.forum_number_format($cur_post['num_posts']).'</span></dd>';

			// Now let's deal with the contact links (Email and URL)
			if ((($cur_post['email_setting'] == '0' && !$pun_user['is_guest']) || $pun_user['is_admmod']) && $pun_user['g_send_email'] == '1')
				$user_contacts[] = '<span class="email"><a href="mailto:'.$cur_post['email'].'">'.$lang_common['Email'].'</a></span>';
			else if ($cur_post['email_setting'] == '1' && !$pun_user['is_guest'] && $pun_user['g_send_email'] == '1')
				$user_contacts[] = '<span class="email"><a href="misc.php?email='.$cur_post['id'].'">'.$lang_common['Email'].'</a></span>';
			require PUN_ROOT.'include/pms/viewtopic_PM-link.php';

			if ($cur_post['url'] != '')
			{
				if ($pun_config['o_censoring'] == '1')
					$cur_post['url'] = censor_words($cur_post['url']);

				$user_contacts[] = '<span class="website"><a href="'.pun_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a></span>';
			}
		}

		// Moderator and Admin stuff
		if ($pun_user['is_admmod'])
		{
			$user_info[] = '<dd><span><a href="moderate.php?get_host='.$cur_post['sender_ip'].'" title="'.$cur_post['sender_ip'].'">'.$lang_topic['IP address logged'].'</a></span></dd>';

			if ($cur_post['admin_note'] != '')
				$user_info[] = '<dd><span>'.$lang_topic['Note'].' <strong>'.pun_htmlspecialchars($cur_post['admin_note']).'</strong></span></dd>';
		}

		// Generation post action array (reply, delete etc.)
		if (!$status)
			$post_actions[] = '<li class="postquote"><span><a href="message_send.php?id='.$cur_post['id'].'&amp;reply='.$cur_post['mid'].'">'.$lang_pms['Reply'].'</a></span></li>';

		$post_actions[] = '<li class="postdelete"><span><a href="message_delete.php?id='.$cur_post['mid'].'&amp;box='.intval($_GET['box']).'&amp;p='.intval($_GET['p']).'">'.$lang_pms['Delete'].'</a></span></li>';

		if (!$status)
			$post_actions[] = '<li class="postquote"><span><a href="message_send.php?id='.$cur_post['id'].'&amp;quote='.$cur_post['mid'].'">'.$lang_pms['Quote'].'</a></span></li>';
	}
	// If the sender has been deleted
	else
	{
		$result = $db->query('SELECT id, sender, message, posted FROM '.$db->prefix.'messages WHERE id='.$id) or error('Unable to fetch message and user info', __FILE__, __LINE__, $db->error());
		$cur_post = $db->fetch_assoc($result);

		$username = pun_htmlspecialchars($cur_post['sender']);
		$user_title = $lang_pms['Deleted user'];

		$post_actions[] = '<li class="postdelete"><span><a href="message_delete.php?id='.$cur_post['id'].'&amp;box='.intval($_GET['box']).'&amp;p='.intval($_GET['p']).'">'.$lang_pms['Delete'].'</a></span></li>';

		$is_online = '<span>'.$lang_topic['Offline'].'</span>';
	}

	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$cur_post['smileys'] = isset($cur_post['smileys']) ? $cur_post['smileys'] : $pun_user['show_smilies'];
	$cur_post['message'] = parse_message($cur_post['message'], (!$cur_post['smileys']));

	// Do signature parsing/caching
	if ($pun_config['o_signatures'] == '1' && $cur_post['signature'] != '' && $pun_user['show_sig'] != '0')
	{
		$signature = parse_signature($cur_post['signature']);
	}

?>

	<div id="punviewtopic">
	<div id="p<?php echo $cur_post['id'] ?>" class="blockpost">
		<h2><span><?php echo format_time($cur_post['posted']) ?></span></h2>
		<div class="box">
			<div class="inbox">
				<div class="postbody">
					<div class="postleft">
						<dl>
							<dt><strong><?php echo $username ?></strong></dt>
							<dd class="usertitle"><strong><?php echo $user_title ?></strong></dd>
	<?php if ($user_avatar != '') echo "\t\t\t\t\t\t".'<dd class="postavatar">'.$user_avatar.'</dd>'."\n"; ?>
	<?php if (count($user_info)) echo "\t\t\t\t\t\t".implode("\n\t\t\t\t\t\t", $user_info)."\n"; ?>
	<?php if (count($user_contacts)) echo "\t\t\t\t\t\t".'<dd class="usercontacts">'.implode(' ', $user_contacts).'</dd>'."\n"; ?>
						</dl>
					</div>
					

					<div class="postright">
						<div class="postmsg">
							<?php echo $cur_post['message']."\n" ?>
						</div>
<?php if ($signature != '') echo "\t\t\t\t\t".'<div class="postsignature postmsg"><hr />'.$signature.'</div>'."\n"; ?>
					</div>				
				</div>
			</div>
			

			<div class="inbox">
				<div class="postfoot clearb">
					<div class="postfootleft"><?php if ($cur_post['id'] > 1) echo '<p>'.$is_online.'</p>'; ?></div>
	<?php if (count($post_actions)) echo "\t\t\t\t".'<div class="postfootright">'."\n\t\t\t\t\t".'<ul>'."\n\t\t\t\t\t\t".implode("\n\t\t\t\t\t\t", $post_actions)."\n\t\t\t\t\t".'</ul>'."\n\t\t\t\t".'</div>'."\n" ?>
				</div>
			</div>
		</div>
	</div>
	</div>
	<div class="clearer"></div>
<?php	
}

?>
<div class="blocktable">
	<h2><span><?php echo $name ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
<?php
		if ($pun_user['g_pm_limit'] != 0 && $pun_user['g_id'] > PUN_ADMIN)
		{
			// Get total message count
			$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']) or error('Unable to count messages', __FILE__, __LINE__, $db->error());
			list($tot_messages) = $db->fetch_row($result);
			$proc = ceil($tot_messages / $pun_user['g_pm_limit'] * 100);
			$status = ' - '.$lang_pms['Status'].' '.$proc.'%';
		}
		else 
			$status = '';
?>
					<th class="tcl"><?php echo $lang_pms['Subject'] ?><?php echo $status ?></th>
					<th class="tc2"><?php if ($box == 0) echo $lang_pms['Sender']; else echo $lang_pms['Receiver']; ?></th>
<?php if (isset($_GET['action']) && $_GET['action'] == 'multidelete') { ?>
					<th class="tcr"><?php echo $lang_pms['Date'] ?></th>
					<th class="tc3"><?php echo $lang_pms['Delete'] ?></th>
<?php } else { ?>
					<th class="tcr"><?php echo $lang_pms['Date'] ?></th>
<?php } ?>
				</tr>
			</thead>
			<tbody>
<?php

// Fetch messages
$result = $db->query('SELECT * FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id'].' AND status='.$box.' ORDER BY posted DESC LIMIT '.$limit) or error('Unable to fetch messages list for forum', __FILE__, __LINE__, $db->error());
$new_messages = false;
$messages_exist = false;

// If there are messages in this folder.
if ($db->num_rows($result))
{
	$messages_exist = true;
	$message_count = 0;
	while ($cur_mess = $db->fetch_assoc($result))
	{
		++$message_count;
		$item_status = ($message_count % 2 == 0) ? 'roweven' : 'rowodd';
		$icon_type = 'icon';

		if ($cur_mess['showed'] == '0')
		{
			$item_status .= ' inew';
			$icon_type = 'icon icon-new';
		}

		($new_messages == false && $cur_mess['showed'] == '0') ? $new_messages = true : null;

		$subject = '<a href="message_list.php?id='.$cur_mess['id'].'&amp;p='.$p.'&amp;box='.$box.'">'.pun_htmlspecialchars($cur_mess['subject']).'</a>';
		if (isset($_GET['id']))
			if ($cur_mess['id'] == $_GET['id'])
				$subject = "<strong>$subject</strong>";

?>
				<tr class="<?php echo $item_status ?>">
					<td class="tcl">
						<div class="intd">
							<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo forum_number_format($message_count + $start_from) ?></div></div>
							<div class="tclcon">
								<div>
									<?php echo $subject."\n" ?>
								</div>
							</div>
						</div>
					</td>
					<td class="tc2" style="white-space: nowrap; overflow: hidden"><a href="profile.php?id=<?php echo $cur_mess['sender_id'] ?>"><?php echo pun_htmlspecialchars($cur_mess['sender']) ?></a></td>
<?php if (isset($_GET['action']) && $_GET['action'] == 'multidelete') { ?>
					<td style="white-space: nowrap"><?php echo format_time($cur_mess['posted']) ?></td>
					<td style="text-align: center"><input type="checkbox" name="delete_messages[]" value="<?php echo $cur_mess['id'] ?>" /></td>
<?php } else { ?>
					<td class="tcr" style="white-space: nowrap"><?php echo format_time($cur_mess['posted']) ?></td>
<?php } ?>
				</tr>
<?php

	}
}
else
{
	$cols = isset($_GET['action']) ? '4' : '3';
	echo "\t".'<tr><td class="tcl" colspan="'.$cols.'">'.$lang_pms['No messages'].'</td></tr>'."\n";
}
?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="postlinksb">
	<div class="inbox crumbsplus">
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
<?php echo $post_link ?>
		</div>
		<ul class="crumbs">
			<li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li>
			<li><span>»&#160;</span><a href="message_list.php"><strong><?php echo pun_htmlspecialchars($lang_pms['Private Messages']) ?></strong></a></li>
			<li><span>»&#160;</span><strong><?php echo pun_htmlspecialchars($page_name) ?></strong></li>
		</ul>
<?php echo $subscraction ?>
		<div class="clearer"></div>
	</div>
</div>

</form>

<div class="clearer"></div>
</div>
</div>
<?php
if (isset($_GET['id']))
	$forum_id = $id;

$footer_style = 'message_list';
require PUN_ROOT.'footer.php';