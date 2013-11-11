##
##
##        Mod title:  Hide "powered by FluxBB" in footer
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Hide "powered by FluxBB" in footer. This makes it less likely
##                    for spammers to find the forum.
##
##   Affected files:  footer.php
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

footer.php


#
#---------[ 2. FIND (line: 118) ]----------------------------------------------
#

				<p id="poweredby"><?php printf($lang_common['Powered by'], '<a href="http://fluxbb.org/">FluxBB</a>'.(($pun_config['o_show_version'] == '1') ? ' '.$pun_config['o_cur_version'] : '')) ?></p>

#
#---------[ 3. REPLACE WITH ]---------------------------------------------------
#



#
#---------[ 6. SAVE/UPLOAD ]-------------------------------------------------
#
