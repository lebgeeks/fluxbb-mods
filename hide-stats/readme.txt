##
##
##        Mod title:  Hide Forum Stats
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Hides the forum statistics to everyone except the admin
##
##   Affected files:  index.php
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

index.php


#
#---------[ 2. FIND (line: 209) ]----------------------------------------------
#

<div id="brdstats" class="block">


#
#---------[ 3. BEFORE, ADD ]---------------------------------------------------
#

<?php if ( $pun_user['g_id'] == PUN_ADMIN ) { ?>


#
#---------[ 4. FIND (line: 262) ]-----------------------------------------------
#

<?php

$footer_style = 'index';
require PUN_ROOT.'footer.php';


#
#---------[ 5. BEFORE, ADD ]---------------------------------------------------
#

<?php } ?>


#
#---------[ 6. SAVE/UPLOAD ]-------------------------------------------------
#
