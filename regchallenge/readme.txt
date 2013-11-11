##
##
##        Mod title:  Registration Challenge
##
##      Mod version:  1.0
##  Works on FluxBB:  1.4.8, 1.4.9, 1.4.10, 1.4.11, 1.5.0, 1.5.1, 1.5.2, 1.5.3, 1.5.4
##     Release date:  2013-10-18
##           Author:  Samer (fluxbb@lebgeeks.com)
##
##      Description:  A math challenge that visitors need to solve in order to sign up
##                    it was written for a tech community, so the assumption is that the 
##                    user has basic programming knowledge.
##
##   Affected files:  register.php, include/functions.php 
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

files/*.* to /


#
#---------[ 2. OPEN ]---------------------------------------------------------
#

register.php


#
#---------[ 3. FIND (line: 30) ]---------------------------------------------
#

// User pressed the cancel button

#
#---------[ 4. BEFORE, ADD ]--------------------------------------------------
#

if (!regchallenge_checkanswer(intval($_POST['answer'])) && !regchallenge_checkanswer(intval($_COOKIE['answer'])))
        message("The answer is not correct. Hint: it's easier to write a small program that does it for you.");


#
#---------[ 5. OPEN ]--------------------------------------------------------
#

include/functions.php


#
#--------[ 6. FIND (line: 2044) ]---------------------------------------------


// DEBUG FUNCTIONS BELOW


#
#---------[ 7. BEFORE, ADD ]--------------------------------------------------
#


function regchallenge_setcookie($challenge, $expire)
{
	forum_setcookie("reg", regchallenge_getcookie($challenge, $expire), $expire);
}


function regchallenge_getcookie($challenge, $expire)
{
  return $challenge.'|'.$expire.'|'.regchallenge_gethmac($challenge, $expire);
}

function regchallenge_gethmac($challenge, $expire){
        global $cookie_seed;

        return forum_hmac($challenge.'|'.$expire, $cookie_seed.'_regchallenge_hash');
}


function regchallenge_checkanswer($answer)
{
        list($challenge, $expire, $hmac) = explode("|", $_COOKIE['reg']);
        if (regchallenge_checkcookie($challenge, $expire, $hmac) && (regchallenge_getanswer($challenge) == $answer)) {
          forum_setcookie("answer", $answer, time()+15*60); 
          return true;
        } else {
          return false;
        }
}

function regchallenge_checkcookie($challenge, $expire, $hmac)
{
        $new_hmac = regchallenge_gethmac($challenge, $expire);
        return ($new_hmac != $hmac || time() > $expire) ? false : true;
}

function regchallenge_getanswer($challenge)
{
        list($x, $y, $limit) = array_map("intval", explode(",", $challenge));
        $sum = 0;

        for($i = 0; $i < $limit; $i+=1 ){
          if($i % $x == 0 || $i % $y == 0){
                  $sum += $i;
          }
        }

        return $sum;
}


function regchallenge_init()
{
        $primes = array(2,3,5,7,11,13,17,19);
        shuffle($primes);
        $limit = rand(500, 1000);
        $challenge = array($primes[0], $primes[1], $limit);
        regchallenge_setcookie(implode(",", $challenge), time()+60*15);

        return $challenge;
}


#
#---------[ 8. OPEN ]--------------------------------------------------------
#

header.php

#
#---------[ 9. FIND (line: 200) ]---------------------------------------------
#

	$links[] = '<li id="navregister"'.((PUN_ACTIVE_PAGE == 'register') ? ' class="isactive"' : '').'><a href="register.php">'.$lang_common['Register'].'</a></li>';


#
#---------[ 10. REPLACE WITH ]-------------------------------------------------
#

	$links[] = '<li id="navregister"'.((PUN_ACTIVE_PAGE == 'register') ? ' class="isactive"' : '').'><a href="signup.php">'.$lang_common['Register'].'</a></li>';


#
#---------[ 8. SAVE/UPLOAD ]--------------------------------------------------
#
