<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';


// If we are logged in, we shouldn't be here
if (!$pun_user['is_guest'])
{
	header('Location: index.php');
	exit;
}

// Load the register.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/register.php';

// Load the register.php/profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/prof_reg.php';

if ($pun_config['o_regs_allow'] == '0')
	message($lang_register['No new regs']);

define('PUN_ACTIVE_PAGE', 'signup');
$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_register['Register']);
list($x, $y, $limit) = regchallenge_init();

require PUN_ROOT.'header.php';

?>
<div id="signup" class="blockform">
	<div class="hd"><h2><span>Signup Challenge</span></h2></div>
	<div class="box">
		<form method="post" action="register.php">
			<div class="inform">
				<fieldset>
					<legend>In order to sign-up, you need to solve this challenge.</legend>
					<div class="infldset">
                                                <div class="usercontent">
                                                <p>Find the sum of the multiples of <?= $x ?> or <?= $y ?> under <?= $limit ?></p>
                                                    <p>For example, if we list all the natural numbers below 10 that are multiples of 3 or 5, we get 3, 5, 6 and 9. The sum of these multiples is 23.</p>
                                                    <p><input type="text" name="answer" placeholder="Your answer" value="" />
                                                </div>
					</div>
				</fieldset>
			</div>
			<p class="buttons"><input type="submit" name="submit" value="Submit" /></p>
		</form>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
?>
