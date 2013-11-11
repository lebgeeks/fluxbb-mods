##
##
##        Mod title:  Relative Timestamps
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  Displays timestamps as relative, for example "5 days ago" instead of a date.
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
#---------[ 4. OPEN ]---------------------------------------------------------
#

include/functions.php


#
#---------[ 5. FIND (line: 994) ]-----------------------------------------------
#

return $date.' '.gmdate($time_format, $timestamp);

#
#---------[ 6. REPLACE WITH] ---------------------------------------------------
#

return time2str($timestamp);

#
#---------[ 5. FIND (line: 957) ]-----------------------------------------------
#

//
// Format a time string according to $time_format and time zones
//

#
#---------[ 6. BEFORE, ADD ] ---------------------------------------------------
#

//
// Adapted from osm's time2str function on StackOverflow
//
function time2str($ts)
{
  global $pun_user;
    if(!ctype_digit($ts))
        $ts = strtotime($ts);
    if(is_null($date_format))
    	$date_format = $forum_date_formats[$pun_user['date_format']];

    $offset = ($pun_user['timezone'] + $pun_user['dst']) * 3600;
    $ts -= $offset;
    $diff = time() - $ts;

    if($diff == 0)
        return 'now';
    elseif($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if($day_diff == 0)
        {
            if($diff < 60) return 'just now';
            if($diff < 120) return '1 minute ago';
            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
            if($diff < 7200) return '1 hour ago';
            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
        }
        if($day_diff == 1) return 'Yesterday';
        if($day_diff < 7) return $day_diff . ' days ago';
        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
        if($day_diff < 60) return 'last month';
        if(date('Y') == date('Y', $ts)){
          return date('F j', $ts); //add time!
        } else {
          return date('F j Y', $ts);
        }
      } else {
        return $pun_user['timezone'];
      }
}


#
#---------[ 7. SAVE/UPLOAD ]-------------------------------------------------
#
