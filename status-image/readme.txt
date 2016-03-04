##
##
##        Mod title:  Status Image
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Replaces the user status indicator with an image.
##
##   Affected files:  viewtopic.php
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

files/img/*.* to /img/


#
#---------[ 2. OPEN ]---------------------------------------------------------
#

viewtopic.php


#
#---------[ 3. FIND (line: 238) ]----------------------------------------------
#

$is_online = ($cur_post['is_online'] == $cur_post['poster_id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : '<span>'.$lang_topic['Offline'].'</span>';


#
#---------[ 4. REPLACE WITH ]--------------------------------------------------
#

$is_online = ($cur_post['is_online'] == $cur_post['poster_id']) ? '<span><img alt="'.$lang_topic['Online'].'" width="16" src="img/online.png" /></span>' : '<span><img alt="'.$lang_topic['Offline'].'" width="16" src="img/offline.png" /></span>';


#
#---------[ 5. SAVE/UPLOAD ]--------------------------------------------------
#