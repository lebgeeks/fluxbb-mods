##
##
##        Mod title:  Private Messaging System (PMS)
##
##      Mod version:  1.3.3
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-08-20
##           Author:  Koos (pampoen10@yahoo.com)
##  Original Author:  Connorhd (connorhd@mypunbb.com) & Smartys
##
##      Description:  Private Messaging System for FluxBB
##
##   Affected files:  viewtopic.php
##                    footer.php
##                    header.php
##                    profile.php
##
##       Affects DB:  New table:
##                       'messages'
##                    New options in 'config' table:
##                       'o_pms_enabled'
##                       'o_pms_mess_per_page'
##                       'o_pms_email'
##                    New column in 'groups' table:
##                       'g_pm'
##                       'g_pm_limit'
##                    New column in 'users' table:
##                       'email_pm'
##
##
##       DISCLAIMER:  Please note that "mods" are not officially supported by
##                    FluxBB. Installation of this modification is done at your
##                    own risk. Backup your forum database and any and all
##                    applicable files before proceeding.
##
##


#
#---------[ 1. UPLOAD ]-------------------------------------------------------
#

install_mod.php to /

files/*.* to /

files/plugins/AP_Private_messaging.php to /plugins/AP_Private_messaging.php

files/include/pms/*.* to /include/pms/

files/lang/English/pms.php to /lang/English/pms.php

files/lang/English/mail_templates/new_message.tpl to /lang/English/mail_templates/new_message.tpl


#
#---------[ 2. RUN ]----------------------------------------------------------
#

install_mod.php


#
#---------[ 3. DELETE ]-------------------------------------------------------
#

install_mod.php


#
#---------[ 4. OPEN ]---------------------------------------------------------
#

footer.php


#
#---------[ 5. FIND (line: 47) ]----------------------------------------------
#

$footer_style = isset($footer_style) ? $footer_style : NULL;


#
#---------[ 6. AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/pms/footer_links.php';


#
#---------[ 7. OPEN ]---------------------------------------------------------
#

header.php


#
#---------[ 8. FIND (line: 186) ]---------------------------------------------
#

	$page_statusinfo[] = '<li><span>'.sprintf($lang_common['Last visit'], format_time($pun_user['last_visit'])).'</span></li>';


#
#---------[ 9. AFTER, ADD ]---------------------------------------------------
#

require PUN_ROOT.'include/pms/header_new_messages.php';


#
#---------[ 10. FIND (line: 220) ]--------------------------------------------
#

$tpl_temp = '<div id="brdmenu" class="inbox">'."\n\t\t\t".'<ul>'."\n\t\t\t\t".implode("\n\t\t\t\t", $links)."\n\t\t\t".'</ul>'."\n\t\t".'</div>';


#
#---------[ 11. BEFORE, ADD ]-------------------------------------------------
#

require PUN_ROOT.'include/pms/functions_navlinks2.php';


#
#---------[ 12. OPEN ]--------------------------------------------------------
#

profile.php


#
#---------[ 13. FIND (line: 604) ]--------------------------------------------
#

		$db->query('DELETE FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to delete user', __FILE__, __LINE__, $db->error());


#
#---------[ 14. AFTER, ADD ]--------------------------------------------------
#

		require PUN_ROOT.'include/pms/profile_delete.php';


#
#---------[ 15. FIND (line: 995) ]--------------------------------------------
#

	if ($email_field != '')
	{
		$user_personal[] = '<dt>'.$lang_common['Email'].'</dt>';
		$user_personal[] = '<dd><span class="email">'.$email_field.'</span></dd>';
	}


#
#---------[ 16. AFTER, ADD ]--------------------------------------------------
#

	require PUN_ROOT.'include/pms/profile_send.php';


#
#---------[ 17. FIND (line: 1072) ]-------------------------------------------
#

		}
		else
		{
			$username_field = '<p>'.$lang_common['Username'].': '.pun_htmlspecialchars($user['username']).'</p>'."\n";


#
#---------[ 18. BEFORE, ADD ]--------------------------------------------------
#

			require PUN_ROOT.'lang/'.$pun_user['language'].'/pms.php';
			$email_field .= '<p><a href="message_send.php?id='.$id.'">'.$lang_pms['Quick message'].'</a></p>'."\n";


#
#---------[ 19. OPEN ]--------------------------------------------------------
#

viewtopic.php


#
#---------[ 20. FIND (line: 241) ]--------------------------------------------
#

			else if ($cur_post['email_setting'] == '1' && !$pun_user['is_guest'] && $pun_user['g_send_email'] == '1')
				$user_contacts[] = '<span class="email"><a href="misc.php?email='.$cur_post['poster_id'].'">'.$lang_common['Email'].'</a></span>';


#
#---------[ 21. AFTER, ADD ]--------------------------------------------------
#										

			require PUN_ROOT.'include/pms/viewtopic_PM-link.php';


#
#---------[ 22. SAVE/UPLOAD ]-------------------------------------------------
#