##
##
##        Mod title:  Bullet quicklinks
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Replaces the "|" characters by a bullet in the quicklinks.
##
##   Affected files:  header.php
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
#---------[ 1. OPEN ]---------------------------------------------------------
#

header.php


#
#---------[ 2. FIND (line: 286) ]----------------------------------------------
#

	$tpl_temp .= "\n\t\t\t\t".'<li><span>'.$lang_common['Topic searches'].' '.implode(' | ', $page_topicsearches).'</span></li>';


#
#---------[ 3. REPLACE WITH ]---------------------------------------------------
#


	$tpl_temp .= "\n\t\t\t\t".'<li><span>'.$lang_common['Topic searches'].' '.implode(' &bull; ', $page_topicsearches).'</span></li>';
#
#---------[ 6. SAVE/UPLOAD ]-------------------------------------------------
#
