<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


if (!$pun_config['o_pms_enabled'] || $pun_user['is_guest'] || $pun_user['g_pm'] == 0)
	message($lang_common['No permission']);

// Load the pms.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';

// Load the post.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

// Start with a clean slate
$errors = array();


// Did someone just hit "Send" or "Preview"?
if (isset($_POST['form_sent']))
{
	// Flood protection
	if (!isset($_POST['preview']) && !$pun_user['is_admmod'])
	{
		$result = $db->query('SELECT posted FROM '.$db->prefix.'messages WHERE sender_id='.$pun_user['id'].' ORDER BY id DESC LIMIT 1') or error('Unable to fetch message time for flood protection', __FILE__, __LINE__, $db->error());
		if (list($last) = $db->fetch_row($result))
		{
			if ((time() - $last) < $pun_user['g_post_flood'])
				$errors[] = $lang_pms['Flood start'].' '.$pun_user['g_post_flood'].' '.$lang_pms['Flood end'];
		}
	}

	// Smileys
	if (isset($_POST['hide_smilies']))
		$show_smilies = 0;
	else
		$show_smilies = 1;

	// Check subject
	$subject = pun_trim($_POST['req_subject']);
	if ($subject == '')
		$errors[] = $lang_post['No subject'];
	else if (pun_strlen($subject) > 70)
		$errors[] = $lang_post['Too long subject'];
	else if ($pun_config['p_subject_all_caps'] == '0' && is_all_uppercase($subject) && !$pun_user['is_admmod'])	
		$errors[] = $lang_post['All caps subject'];

	// Clean up message from POST
	$message = pun_linebreaks(pun_trim($_POST['req_message']));

	// Check message
	if ($message == '')
		$errors[] = $lang_post['No message'];
	// Here we use strlen() not pun_strlen() as we want to limit the post to PUN_MAX_POSTSIZE bytes, not characters
	else if (strlen($message) > PUN_MAX_POSTSIZE)
		$errors[] = sprintf($lang_post['Too long message'], forum_number_format(PUN_MAX_POSTSIZE));
	else if ($pun_config['p_message_all_caps'] == '0' && is_all_uppercase($message) && !$pun_user['is_admmod'])
		$errors[] = $lang_post['All caps message'];

	// Validate BBCode syntax
	if ($pun_config['p_message_bbcode'] == '1')
	{
		require PUN_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}

	// Replace four-byte characters (MySQL cannot handle them)
	$message = strip_bad_multibyte_chars($message);

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		// Get userid
		$result = $db->query('SELECT id, username, group_id FROM '.$db->prefix.'users WHERE id!=1 AND username=\''.$db->escape($_POST['req_username']).'\'') or error('Unable to get user id', __FILE__, __LINE__, $db->error());

		// Send message
		if (list($id,$user,$status) = $db->fetch_row($result))
		{
			// Check inbox status
			if ($pun_user['g_pm_limit'] != 0 && $pun_user['g_id'] > PUN_ADMIN)
			{
				// Check receiver's box
				$result = $db->query('SELECT g_pm_limit FROM '.$db->prefix.'groups WHERE g_id='.$status) or error('Unable to get group PM limit', __FILE__, __LINE__, $db->error());
				list($limit) = $db->fetch_row($result);
				$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$id) or error('Unable to get message count of the receiver', __FILE__, __LINE__, $db->error());
				list($count) = $db->fetch_row($result);
				if ($count >= $limit && $status > PUN_ADMIN)
					message($lang_pms['Inbox full']);

				// Also check user's own box
				if (isset($_POST['savemessage']) && intval($_POST['savemessage']) == 1)
				{
					$result = $db->query('SELECT count(*) FROM '.$db->prefix.'messages WHERE owner='.$pun_user['id']) or error('Unable to get message count of the sender', __FILE__, __LINE__, $db->error());
					list($count) = $db->fetch_row($result);
					if ($count >= $pun_user['g_pm_limit'])
						message($lang_pms['Sent full']);
				}
			}

			// Should we send out a notification
			if ($pun_config['o_pms_email'] == '1')
			{
				// Get the post time of receiver's previous message received
				$result = $db->query('SELECT max(posted) as max_posted from '.$db->prefix.'messages WHERE owner='.$id.' AND status=0 LIMIT 1') or error('Unable to fetch pm info', __FILE__, __LINE__, $db->error());
				$previous_pm_time = $db->result($result);

				if (empty($previous_pm_time))
					$previous_pm_time = 0;

				// Check whether the user should be notified
				$result = $db->query('SELECT u.email, u.language FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'online AS o ON u.id=o.user_id WHERE u.id='.$id.' AND COALESCE(o.logged, u.last_visit)>'.$previous_pm_time.' AND u.id!='.intval($pun_user['id']).' AND u.email_pm=1') or error('Unable to fetch pm info', __FILE__, __LINE__, $db->error());

				if ($db->num_rows($result))
				{
					list($pms_email,$pms_language) = $db->fetch_row($result);

					require_once PUN_ROOT.'include/email.php';

					$notification_emails = array();

						if (file_exists(PUN_ROOT.'lang/'.$pms_language.'/mail_templates/new_message.tpl'))
						{
							// Load the "new message" template
							$mail_tpl = trim(file_get_contents(PUN_ROOT.'lang/'.$pms_language.'/mail_templates/new_message.tpl'));

							// The first row contains the subject (it also starts with "Subject:")
							$first_crlf = strpos($mail_tpl, "\n");
							$mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
							$mail_message = trim(substr($mail_tpl, $first_crlf));

							$mail_subject = str_replace('<board_title>', $pun_config['o_board_title'], $mail_subject);
							$mail_message = str_replace('<pm_receiver>', $user, $mail_message);
							$mail_message = str_replace('<board_title>', $pun_config['o_board_title'], $mail_message);
							$mail_message = str_replace('<pm_sender>', $pun_user['username'], $mail_message);
							$mail_message = str_replace('<pm_title>', $subject, $mail_message);
							$mail_message = str_replace('<pm_url>', $pun_config['o_base_url'].'/message_list.php', $mail_message);
							$mail_message = str_replace('<disable_email_pm_url>', $pun_config['o_base_url'].'/message_list.php?email_pm=0', $mail_message);
							$mail_message = str_replace('<board_mailer>', $pun_config['o_board_title'], $mail_message);

							$notification_emails[$pms_language][0] = $mail_subject;
							$notification_emails[$pms_language][1] = $mail_message;

							$mail_subject = $mail_message = $mail_subject_full = $mail_message_full = null;
						}

						// We have to double check here because the templates could be missing
						if (isset($notification_emails[$pms_language]))
							pun_mail($pms_email, $notification_emails[$pms_language][0], $notification_emails[$pms_language][1]);
				}
			}

			$now = time();

			// "Send" message
			$db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted) VALUES(
				'.$id.',
				\''.$db->escape($subject).'\',
				\''.$db->escape($message).'\',
				\''.$db->escape($pun_user['username']).'\',
				'.$pun_user['id'].',
				\''.get_remote_address().'\',
				\''.$show_smilies.'\',
				\'0\',
				\'0\',
				\''.$now.'\'
			)') or error('Unable to send message', __FILE__, __LINE__, $db->error());

			// Save an own copy of the message
			if (isset($_POST['savemessage']))
			{
				$db->query('INSERT INTO '.$db->prefix.'messages (owner, subject, message, sender, sender_id, sender_ip, smileys, showed, status, posted) VALUES(
					\''.$pun_user['id'].'\',
					\''.$db->escape($subject).'\',
					\''.$db->escape($message).'\',
					\''.$db->escape($user).'\',
					\''.$id.'\',
					\''.get_remote_address().'\',
					\''.$show_smilies.'\',
					\'1\',
					\'1\',
					\''.$now.'\'
				)') or error('Unable to send message', __FILE__, __LINE__, $db->error());
			}
		}
		else
		{
			message($lang_pms['No user']);
		}

		$topic_redirect = intval($_POST['topic_redirect']);
		$from_profile = isset($_POST['from_profile']) ? intval($_POST['from_profile']) : '';
		if ($from_profile != 0)
			redirect('message_list.php', $lang_pms['Sent redirect']);
		else if ($topic_redirect != 0)
			redirect('message_list.php', $lang_pms['Sent redirect']);
		else
			redirect('message_list.php', $lang_pms['Sent redirect']);
	}
}

	if (isset($_GET['id']))
		$id = intval($_GET['id']);
	else
		$id = 0;

	if ($id > 0)
	{
		$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message($lang_common['Bad request']);
		list($username) = $db->fetch_row($result);
	}

	if (isset($_GET['reply']) || isset($_GET['quote']))
	{
		$r = isset($_GET['reply']) ? intval($_GET['reply']) : 0;
		$q = isset($_GET['quote']) ? intval($_GET['quote']) : 0;

		// Get message info
		empty($r) ? $id = $q : $id = $r;
		$result = $db->query('SELECT subject, message, sender FROM '.$db->prefix.'messages WHERE id='.$id.' AND owner='.$pun_user['id']) or error('Unable to fetch message info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message($lang_common['Bad request']);
		list($q_subject, $q_message, $q_sender) = $db->fetch_row($result);

		// Quote the message
		if (isset($_GET['quote']))
		{
			// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
			if (strpos($q_message, '[code]') !== false && strpos($q_message, '[/code]') !== false)
			{
				list($inside, $outside) = split_text($q_message, '[code]', '[/code]');

				$q_message = implode("\1", $outside);
			}

			// Remove [img] tags from quoted message
			$q_message = preg_replace('%\[img(?:=(?:[^\[]*?))?\]((ht|f)tps?://)([^\s<"]*?)\[/img\]%U', '\1\3', $q_message);

			// If we split up the message before we have to concatenate it together again (code tags)
			if (isset($inside))
			{
				$outside = explode("\1", $q_message);
				$q_message = '';

				$num_tokens = count($outside);
				for ($i = 0; $i < $num_tokens; ++$i)
				{
					$q_message .= $outside[$i];
					if (isset($inside[$i]))
						$q_message .= '[code]'.$inside[$i].'[/code]';
				}

				unset($inside);
			}

			$q_message = pun_htmlspecialchars($q_message);

			if ($pun_config['p_message_bbcode'] == '1')
			{
				// If username contains a square bracket, we add "" or '' around it (so we know when it starts and ends)
				if (strpos($q_sender, '[') !== false || strpos($q_sender, ']') !== false)
				{
					if (strpos($q_sender, '\'') !== false)
						$q_sender = '"'.$q_sender.'"';
					else
						$q_sender = '\''.$q_sender.'\'';
				}
				else
				{
					// Get the characters at the start and end of $q_sender
					$ends = substr($q_sender, 0, 1).substr($q_sender, -1, 1);

					// Deal with quoting "Username" or 'Username' (becomes '"Username"' or "'Username'")
					if ($ends == '\'\'')
						$q_sender = '"'.$q_sender.'"';
					else if ($ends == '""')
						$q_sender = '\''.$q_sender.'\'';
				}

				$quote = '[quote='.$q_sender.']'.$q_message.'[/quote]'."\n";
			}
			else
				$quote = '> '.$q_sender.' '.$lang_common['wrote']."\n\n".'> '.$q_message."\n";
		}

		// Add subject
		if (strpos(strtolower($q_subject), 're:') !== false)
			$subject = $q_subject;
		else
			$subject = "RE: " . $q_subject;
	}

	$action = $lang_pms['Send a message'];
	$form = '<form id="post" method="post" action="message_send.php?action=send" onsubmit="return process_form(this)">';

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $action);
	$required_fields = array('req_username' => $lang_pms['Send to'], 'req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
	$focus_element = array('post', 'req_username');

	$cur_index = 1;
	if (!isset($username))
		$username = (isset($_POST['req_username'])) ? $_POST['req_username'] : '';
	if (!isset($quote))
		$quote = '';
	if (!isset($subject))
		$subject = '';

	define('PUN_ACTIVE_PAGE', 'pm');
	require PUN_ROOT.'header.php';

// If there are errors, we display them
if (!empty($errors))
{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox error-info">
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul class="error-list">
<?php

	foreach ($errors as $cur_error)
		echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php

}
else if (isset($_POST['preview']))
{
	require_once PUN_ROOT.'include/parser.php';
	$preview_message = parse_message($message, !$show_smilies);

?>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_pms['Message preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postbody">
				<div class="postright">
					<div class="postmsg">
						<?php echo $preview_message."\n" ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

}


$cur_index = 1;

?>
<div id="postform" class="blockform">
	<h2><span><?php echo $action ?></span></h2>
	<div class="box">
	<?php echo $form."\n" ?>
		<div class="inform">
		<fieldset>
			<legend><?php echo $lang_common['Write message legend'] ?></legend>
			<div class="infldset txtarea">
				<input type="hidden" name="form_sent" value="1" />
				<input type="hidden" name="topic_redirect" value="<?php echo isset($_GET['tid']) ? intval($_GET['tid']) : '' ?>" />
				<input type="hidden" name="from_profile" value="<?php echo isset($_POST['from_profile']) ? intval($_POST['from_profile']) : '' ?>" />
				<input type="hidden" name="form_user" value="<?php echo (!$pun_user['is_guest']) ? pun_htmlspecialchars($pun_user['username']) : 'Guest'; ?>" />
				<label class="conl required"><strong><?php echo $lang_pms['Send to'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input type="text" name="req_username" value="<?php echo pun_htmlspecialchars($username) ?>" size="25" maxlength="25" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
				<div class="clearer"></div>
				<label class="required"><strong><?php echo $lang_common['Subject'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br /><input class="longinput" type="text" name="req_subject" value="<?php echo pun_htmlspecialchars($subject) ?>" size="80" maxlength="70" tabindex="<?php echo $cur_index++ ?>" /><br /></label>
				<label class="required"><strong><?php echo $lang_common['Message'] ?> <span><?php echo $lang_common['Required'] ?></span></strong><br />
				<textarea name="req_message" rows="20" cols="95" tabindex="<?php echo $cur_index++ ?>"><?php echo isset($_POST['req_message']) ? pun_htmlspecialchars($message) : (isset($quote) ? $quote : ''); ?></textarea><br /></label>
				<ul class="bblinks">
					<li><span><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a> <?php echo ($pun_config['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></span></li>
					<li><span><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a> <?php echo ($pun_config['p_message_bbcode'] == '1' && $pun_config['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></span></li>
					<li><span><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a> <?php echo ($pun_config['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></span></li>
				</ul>
			</div>
		</fieldset>
<?php
	if (isset($_POST['form_sent']))
		$savemessage_checked = isset($_POST['savemessage']) ? 1 : 0;
	else
		$savemessage_checked = 1;

	$checkboxes = array();

	if ($pun_config['o_smilies'] == '1')
		$checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'"'.(isset($_POST['hide_smilies']) ? ' checked="checked"' : '').' />'.$lang_post['Hide smilies'];

	$checkboxes[] = '<label><input type="checkbox" name="savemessage" value="1" tabindex="'.($cur_index++).'"'.($savemessage_checked ? ' checked="checked"' : '').' />'.$lang_pms['Save message'];

	if (!empty($checkboxes))
	{
?>
		</div>
		<div class="inform">
			<fieldset>
				<legend><?php echo $lang_common['Options'] ?></legend>
				<div class="infldset">
					<div class="rbox">
						<?php echo implode('<br /></label>'."\n\t\t\t\t\t\t", $checkboxes).'<br /></label>'."\n" ?>
					</div>
				</div>
			</fieldset>
<?php
	}
?>
		</div>
		<p class="buttons"><input type="submit" name="submit" value="<?php echo $lang_pms['Send'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
	</form>
	</div>
</div>
<?php
	require PUN_ROOT.'footer.php';
