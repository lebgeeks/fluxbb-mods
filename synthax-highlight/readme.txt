##
##
##        Mod title:  Syntax Highlight
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Highlight [code] blocks using prettify.js by Google. 
##
##   Affected files:  header.php, include/parser.php
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

files/include/prettify/*.* to /include/prettify/


#
#---------[ 2. OPEN ]---------------------------------------------------------
#

header.php

#
#---------[ 3. FIND (line: 95) ]----------------------------------------------
#

<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />

#
#---------[ 4. AFTER, ADD ]--------------------------------------------------
#

<link href="include/prettify/prettify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="include/prettify/prettify.js"></script>
<script type="text/javascript">
  window.onload = prettyPrint;
</script>

#
#---------[ 5. OPEN ]---------------------------------------------------------
#

include/parser.php

#
#---------[ 6. FIND (line: 917) ]----------------------------------------------
#

$text .= '</p><div class="codebox"><pre'.(($num_lines > 28) ? ' class="vscroll"' : '').'><code>'.pun_trim($inside[$i], "\n\r").'</code></pre></div><p>';


#
#---------[ 7. REPLACE WITH ]--------------------------------------------------
#

$text .= '</p><div class="codebox"><pre'.(($num_lines > 28) ? ' class="vscroll"' : '').'><code class="prettyprint">'.pun_trim($inside[$i], "\n\r").'</code></pre></div><p>';


#
#---------[ 8. SAVE/UPLOAD ]--------------------------------------------------
#
