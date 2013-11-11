<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


if ($footer_style == 'message_list')
{

?>
			<dl id="searchlinks" class="conl">
				<dt><strong>PM links</strong></dt>
<?php
if ($new_messages)
	echo "\t\t\t\t\t\t".'<dd><a href="message_list.php?action=markall&amp;box='.$box.'&amp;p='.$p.'">'.$lang_pms['Mark all'].'</a></dd>'."\n";

if ($messages_exist)
	echo "\t\t\t\t\t\t".'<dd><a href="message_list.php?action=multidelete&amp;box='.$box.'&amp;p='.$p.'">'.$lang_pms['Multidelete'].'</a></dd>'."\n";
?>
			</dl>
<?php
}
