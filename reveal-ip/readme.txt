##
##
##        Mod title:  Reveal IP
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Show the IP address of the user instead of "IP Address Logged"
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
#---------[ 2. FIND (line: 282) ]-----------------------------------------------
#

$user_info[] = '<dd><span><a href="moderate.php?get_host='.$cur_post['id'].'" title="'.pun_htmlspecialchars($cur_post['poster_ip']).'">'.$lang_topic['IP address logged'].'</a></span></dd>';


#
#---------[ 3. REPLACE WITH ] ---------------------------------------------------
#

$user_info[] = '<dd><span><a href="moderate.php?get_host='.$cur_post['id'].'">'.pun_htmlspecialchars($cur_post['poster_ip']).'</a></span></dd>';

#
#---------[ 2. FIND (line: 295) ]-----------------------------------------------
#

$user_info[] = '<dd><span><a href="moderate.php?get_host='.$cur_post['id'].'" title="'.pun_htmlspecialchars($cur_post['poster_ip']).'">'.$lang_topic['IP address logged'].'</a></span></dd>';


#
#---------[ 3. REPLACE WITH ] ---------------------------------------------------
#

$user_info[] = '<dd><span><a href="moderate.php?get_host='.$cur_post['id'].'">'.pun_htmlspecialchars($cur_post['poster_ip']).'</a></span></dd>';


#
#---------[ 4. SAVE/UPLOAD ]-------------------------------------------------
#
