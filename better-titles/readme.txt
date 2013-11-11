##
##
##        Mod title:  Better Titles
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Removes forum name from topic title, removes page number for the first page
##
##   Affected files:  viewtopic.php, include/functions.php
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
#---------[ 2. FIND (line: 174) ]----------------------------------------------
#

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), pun_htmlspecialchars($cur_topic['forum_name']), pun_htmlspecialchars($cur_topic['subject']));


#
#---------[ 3. REPLACE WITH ]---------------------------------------------------
#

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), pun_htmlspecialchars($cur_topic['subject']));


#
#---------[ 4. OPEN ]---------------------------------------------------------
#

include/functions.php


#
#---------[ 5. FIND (line: 596) ]-----------------------------------------------
#

if (!is_null($p))

#
#---------[ 6. REPLACE WITH] ---------------------------------------------------
#

if (!is_null($p) && $p!=1)

#
#---------[ 7. SAVE/UPLOAD ]-------------------------------------------------
#
