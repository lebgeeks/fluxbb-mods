<?php
/***********************************************************************/

// Some info about your mod.
$mod_title      = 'Private Messaging System';
$mod_version    = '1.3.3';
$release_date   = '2013-08-20';
$author         = 'Koos (original 1.2.x version by Connorhd)';
$author_email   = 'pampoen10@yahoo.com';

// Versions of FluxBB this mod was created for. A warning will be displayed, if versions do not match
$fluxbb_versions= array('1.4.8', '1.4.9', '1.4.10', '1.4.11', '1.5.0', '1.5.1', '1.5.2', '1.5.3', '1.5.4');

// Set this to false if you haven't implemented the restore function (see below)
$mod_restore	= true;


// This following function will be called when the user presses the "Install" button
function install()
{
	global $db, $db_type, $pun_config;

	//New Install
	if (!$db->table_exists('messages'))
	{
		$schema = array(
			'FIELDS'		=> array(
				'id'			=> array(
					'datatype'		=> 'SERIAL',
					'allow_null'	=> false
				),
				'owner'			=> array(
					'datatype'		=> 'INT(10) UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '0'
				),
				'subject'		=> array(
					'datatype'		=> 'VARCHAR(255)',
					'allow_null'	=> false,
					'default'		=> '\'\''
				),
				'message'		=> array(
					'datatype'		=> 'MEDIUMTEXT',
					'allow_null'	=> true
				),
				'sender'		=> array(
					'datatype'		=> 'VARCHAR(200)',
					'allow_null'	=> false,
					'default'		=> '\'\''
				),
				'sender_id'		=> array(
					'datatype'		=> 'INT(10) UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '0'
				),
				'posted'		=> array(
					'datatype'		=> 'INT(10) UNSIGNED',
					'allow_null'	=> false,
					'default'		=> '0'
				),
				'sender_ip'		=> array(
					'datatype'		=> 'VARCHAR(39)',
					'allow_null'	=> true
				),
				'smileys'		=> array(
					'datatype'		=> 'TINYINT(1)',
					'allow_null'	=> false,
					'default'		=> '1'
				),
				'status'		=> array(
					'datatype'		=> 'TINYINT(1)',
					'allow_null'	=> false,
					'default'		=> '0'
				),
				'showed'		=> array(
					'datatype'		=> 'TINYINT(1)',
					'allow_null'	=> false,
					'default'		=> '0'
				)
			),
			'PRIMARY KEY'	=> array('id'),
		);

		$db->create_table('messages', $schema) or error('Unable to create table '.$db->prefix.'messages.', __FILE__, __LINE__, $db->error());
	}


	// Insert new config option o_pms_enabled
	if (!array_key_exists('o_pms_enabled', $pun_config))
		$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES (\'o_pms_enabled\', \'1\')') or error('Unable to insert config value \'o_pms_enabled\'', __FILE__, __LINE__, $db->error());

	// Insert new config option o_pms_mess_per_page
	if (!array_key_exists('o_pms_mess_per_page', $pun_config))
		$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES (\'o_pms_mess_per_page\', \'10\')') or error('Unable to insert config value \'o_pms_mess_per_page\'', __FILE__, __LINE__, $db->error());

	// Insert new config option o_pms_email
	if (!array_key_exists('o_pms_email', $pun_config))
		$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES (\'o_pms_email\', \'1\')') or error('Unable to insert config value \'o_pms_email\'', __FILE__, __LINE__, $db->error());


	// Add the g_pm field to the groups table
	$db->add_field('groups', 'g_pm', 'INT(10) UNSIGNED', false, 1, null) or error('Unable to add g_pm field', __FILE__, __LINE__, $db->error());

	// Add the g_pm_limit field to the groups table
	$db->add_field('groups', 'g_pm_limit', 'INT(10) UNSIGNED', false, 20, null) or error('Unable to add g_pm_limit field', __FILE__, __LINE__, $db->error());

	// Add the email_pm field to the users table
	$db->add_field('users', 'email_pm', 'TINYINT(1)', false, 1, null) or error('Unable to add email_pm field', __FILE__, __LINE__, $db->error());


	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
}

// This following function will be called when the user presses the "Restore" button (only if $mod_restore is true (see above))
function restore()
{
	global $db, $db_type, $pun_config;

	$db->drop_table('messages') or error('Unable to remove table', __FILE__, __LINE__, $db->error());

	$like_command = ($db_type == 'pgsql') ? 'ILIKE' : 'LIKE';
	$db->query('DELETE FROM '.$db->prefix.'config WHERE conf_name '.$like_command.' \'o_pms%\'') or error('Unable to remove config entries', __FILE__, __LINE__, $db->error());

	// Drop g_pm column from groups table
	$db->drop_field('groups', 'g_pm') or error('Unable to drop g_pm field', __FILE__, __LINE__, $db->error());

	// Drop g_pm_limit column from groups table
	$db->drop_field('groups', 'g_pm_limit') or error('Unable to drop g_pm_limit field', __FILE__, __LINE__, $db->error());

	// Drop email_pm column from users table
	$db->drop_field('users', 'email_pm') or error('Unable to drop email_pm field', __FILE__, __LINE__, $db->error());

	// Regenerate the config cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
}

/***********************************************************************/

// DO NOT EDIT ANYTHING BELOW THIS LINE!


// Circumvent maintenance mode
define('PUN_TURN_OFF_MAINT', 1);
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

// We want the complete error message if the script fails
if (!defined('PUN_DEBUG'))
	define('PUN_DEBUG', 1);

// Make sure we are running a FluxBB version that this mod works with
$version_warning = !in_array($pun_config['o_cur_version'], $fluxbb_versions);

$style = (isset($pun_user)) ? $pun_user['style'] : $pun_config['o_default_style'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo pun_htmlspecialchars($mod_title) ?> installation</title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $style.'.css' ?>" />
</head>
<body>

<div id="punwrap">
<div id="puninstall" class="pun" style="margin: 10% 20% auto 20%">

<?php

if (isset($_POST['form_sent']))
{
	if (isset($_POST['install']))
	{
		// Run the install function (defined above)
		install();

?>
<div class="block">
	<h2><span>Installation successful</span></h2>
	<div class="box">
		<div class="inbox">
			<p>Your database has been successfully prepared for <?php echo pun_htmlspecialchars($mod_title) ?>. See readme.txt for further instructions.</p>
		</div>
	</div>
</div>
<?php

	}
	else
	{
		// Run the restore function (defined above)
		restore();

?>
<div class="block">
	<h2><span>Restore successful</span></h2>
	<div class="box">
		<div class="inbox">
			<p>Your database has been successfully restored.</p>
		</div>
	</div>
</div>
<?php

	}
}
else
{

?>
<div class="blockform">
	<h2><span>Mod installation</span></h2>
	<div class="box">
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?foo=bar">
			<div><input type="hidden" name="form_sent" value="1" /></div>
			<div class="inform">
				<p>This script will update your database to work with the following modification:</p>
				<p><strong>Mod title:</strong> <?php echo pun_htmlspecialchars($mod_title.' '.$mod_version) ?></p>
				<p><strong>Author:</strong> <?php echo pun_htmlspecialchars($author) ?> (<a href="mailto:<?php echo pun_htmlspecialchars($author_email) ?>"><?php echo pun_htmlspecialchars($author_email) ?></a>)</p>
				<p><strong>Disclaimer:</strong> Mods are not officially supported by FluxBB. Mods generally can't be uninstalled without running SQL queries manually against the database. Make backups of all data you deem necessary before installing.</p>
<?php if ($mod_restore): ?>
				<p>If you've previously installed this mod and would like to uninstall it, you can click the Restore button below to restore the database.</p>
<?php endif; ?>
<?php if ($version_warning): ?>
				<p style="color: #a00"><strong>Warning:</strong> The mod you are about to install was not made specifically to support your current version of FluxBB (<?php echo $pun_config['o_cur_version']; ?>). This mod supports FluxBB versions: <?php echo pun_htmlspecialchars(implode(', ', $fluxbb_versions)); ?>. If you are uncertain about installing the mod due to this potential version conflict, contact the mod author.</p>
<?php endif; ?>
			</div>
			<p class="buttons"><input type="submit" name="install" value="Install" /><?php if ($mod_restore): ?><input type="submit" name="restore" value="Restore" /><?php endif; ?></p>
		</form>
	</div>
</div>
<?php

}

?>

</div>
</div>

</body>
</html>
<?php

// End the transaction
$db->end_transaction();

// Close the db connection (and free up any result data)
$db->close();
