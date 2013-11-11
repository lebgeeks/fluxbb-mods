##
##
##        Mod title:  MailChimp integration for FluxBB
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Allows users to subscribe to your newsletter on sign-up
##                    Users are unsubscribed automatically if they are deleted from 
##                    your forum.
##
##   Affected files:  register.php, include/functions.php, profile.php 
##
##       Affects DB: No
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

files/include/mailchimp/*.* to include/mailchimp/
files/lang/English/mailchimp.php to lang/English/


#
#---------[ 2. OPEN ]---------------------------------------------------------
#

register.php


#
#---------[ 3. FIND (line: 158) ]---------------------------------------------
#
		// Add the user

#
#---------[ 4. AFTER, ADD ]--------------------------------------------------
#

if($_POST['newsletter'] == '1'){
	add_user_to_newsletter($email1, $username);
}


#
#---------[ 5. FIND (line: 330) ]---------------------------------------------
#


<input type="text" name="req_email2" value="<?php if (isset($_POST['req_email2'])) echo pun_htmlspecialchars($_POST['req_email2']); ?>" size="50" maxlength="80" /><br /></label>
<?php endif; ?>

#
#---------[ 6. AFTER, ADD ]--------------------------------------------------
#


<div class="rbox">
	<label>
		<input type="checkbox" name="newsletter" value="1" checked="checked" />
		<?= $lang_mailchimp['newsletter_description']; ?><br />
	</label>
</div>



#
#---------[ 7. FIND (line: 23) ]---------------------------------------------
#

// Load the register.php/profile.php language file

#
#---------[ 8. BEFORE, ADD ]--------------------------------------------------
#

// Load the mailchimp.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/mailchimp.php'; 


#
#---------[ 9. OPEN ]--------------------------------------------------------
#

profile.php



#
#--------[ 10. FIND (line: 629) ]---------------------------------------------
#

$db->query('UPDATE '.$db->prefix.'posts SET poster_id=1 WHERE poster_id='.$id) or error('Unable to update posts', __FILE__, __LINE__, $db->error());


#
#---------[ 11. AFTER, ADD ]--------------------------------------------------
#

// unsubscribe user from the newsletter
remove_user_from_newsletter($id);


#
#---------[ 12. OPEN ]---------------------------------------------------------
#

include/functions.php


#
#---------[ 13. FIND (line: 2085) ]----------------------------------------------
#

// DEBUG FUNCTIONS BELOW


#
#---------[ 14. BEFORE, ADD ]----------------------------------------------
#

function add_user_to_newsletter($email, $username)
{
	require_once 'include/mailchimp/MailChimp.class.php';
	require_once 'include/mailchimp/config.inc.php';
	$mc_api = new MailChimp($apikey);
	$merge_vars = !is_null($usernamecolumn) ? array($usernamecolumn=>$username) : array();

        $result = $mc_api->call('lists/subscribe', array(
                    'id'    => $listId,
                    'email' => array('email' => $email),
                    'merge_vars' => $merge_vars
                  ));

        if($result['status'] == "error"){
          echo "Code: $result[code]\n";
          echo "Name: $result[name]\n";
          echo "Error: $result[error]\n";
          die();
        }
}

function remove_user_from_newsletter($id)
{
    global $db;
    require_once 'include/mailchimp/MailChimp.class.php';
    require_once 'include/mailchimp/config.inc.php';

    $result = $db->query('SELECT email FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    $email = $db->fetch_assoc($result);

    $mc_api = new MailChimp($apikey);

    $memberInfo = $mc_api->call('lists/member-info', array(
                    'id'    => $listId,
                    'emails' => array($email)
                  ));

    if($memberInfo['success_count']>0){
        $result = $mc_api->call('lists/unsubscribe', array(
                      'id'    => $listId,
                      'email' => $email,
                      'delete_member' => true,
                      'send_goodbye' => false
                  ));

        if($result['status'] == "error"){
          echo "Code: $result[code]\n";
          echo "Name: $result[name]\n";
          echo "Error: $result[error]\n";
          die();
        }
    }
}


#
#---------[ 15. SAVE/UPLOAD ]--------------------------------------------------
#
