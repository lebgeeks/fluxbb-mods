##
##
##        Mod title:  Reveal User Info to Staff
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Show information about the poster under the username in topic view to staff members only.
##                    Make sure to set "User info in posts" to "No" in the admin options.
##
##   Affected files:  viewtopic.php
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

viewtopic.php

#
#---------[ 2. FIND (line: 249) ]-----------------------------------------------
#

if ($pun_config['o_show_user_info'] == '1')

#
#---------[ 3. REPLACE WITH ] ---------------------------------------------------
#

if ($pun_config['o_show_user_info'] == '1' || $pun_user['is_admmod'])

#
#---------[ 4. SAVE/UPLOAD ]-------------------------------------------------
#
