##
##
##        Mod title:  Selective User Titles
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Removes default user titles but keeps guest, banned and custom titles
##
##   Affected files:  include/functions.php
##
##       Affects DB:  No
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

include/functions.php


#
#---------[ 2. FIND (line: 854) ]-----------------------------------------------
#

$user_title = pun_htmlspecialchars($user['g_user_title']);


#
#---------[ 3. REPLACE WITH ] ---------------------------------------------------
#

$user_title = '';


#
#---------[ 4. SAVE/UPLOAD ]-------------------------------------------------
#
