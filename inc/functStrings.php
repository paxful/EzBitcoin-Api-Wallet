<?php
/*
* Houses all string Functions
* Author: may
*/

function funct_GetandCleanVariables($strVariable){
	//clean any variable or cookie etc..
	$strVariable = trim($strVariable);
	$strVariable = htmlspecialchars($strVariable); //xss defense
	$strVariable = stripslashes($strVariable); //html remove
	global $DB_LINK ; 
	$strVariable = mysqli_real_escape_string($DB_LINK, $strVariable); //sql inj defense
	return $strVariable ;
}

function funct_FormVarSecurity($strVariable){
	
	$strVariable = trim($strVariable);
	$strVariable = htmlspecialchars($strVariable);
	$strVariable = stripslashes($strVariable);
	
	return $strVariable ;
}



function createRandomKey_alphanum($length){ //** we use this one often to create keys
	$keyset  = "abcdefghijklmnopqrstuvwxyz0123456789"; //we can add numbers here to improve the randomness
	$randkey = "";
	for ($i=0; $i<$length; $i++)
		$randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
	return $randkey;
}

function createRandomKey($length){ //** we use this one often to create keys
	$keyset  = "abcdefghijklmnopqrstuvwxyz"; //we can add numbers here to improve the randomness
	$randkey = "";
	for ($i=0; $i<$length; $i++)
		$randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
	return $randkey;
}

function createRandomKey_Num($length){ //** we use this one often to create keys
	$keyset  = "0123456789"; //we can add numbers here to improve the randomness
	$randkey = "";
	for ($i=0; $i<$length; $i++)
		$randkey .= substr($keyset, rand(0, strlen($keyset)-1), 1);
	return $randkey;
}


//####################### end CRITICAL CORE LOGIC Functions //####################### 



//----------------------- process text functions -----------------------//
function functShortenTEXT($strTEXT, $intMaxChar) {  //used often to short strings for previews , comments etc..

        $strTEXT = $strTEXT." "; 
        $strTEXT = substr($strTEXT,0,$intMaxChar); 
        $strTEXT = substr($strTEXT,0,strrpos($strTEXT,' '));
        return $strTEXT; 
}

function functNiceTimeDif_int($date){ //we use this often for rendering timestamp dates readable
	//takes date as a int timestamp time()
    if(empty($date)) {
        return "date ?";
    }
    
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
    
    $now             = time();
    //$unix_date         = strtotime($date);
    $unix_date       = $date;
    
       // check validity of date
    if(empty($unix_date)) {    
        return "...";
    }

    // is it future date or past date
    if($now > $unix_date) {    
        $difference     = $now - $unix_date;
        $tense         = "ago";
        
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
    
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j].= "s";
    }
    
    return "$difference $periods[$j] {$tense}";
}
function functNiceTimeDif($date)//takes date as a string datetime
{
    if(empty($date)) {
        return "date ?";
    }
    
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
    
    $now             = time();
    $unix_date         = strtotime($date);
    
       // check validity of date
    if(empty($unix_date)) {    
        return "...";
    }

    // is it future date or past date
    if($now > $unix_date) {    
        $difference     = $now - $unix_date;
        $tense         = "ago";
        
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
    
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
    
    $difference = round($difference);
    
    if($difference != 1) {
        $periods[$j].= "s";
    }
    
    return "$difference $periods[$j] {$tense}";
}


//----------------------- end process text functions -----------------------//



//----------------------- randomize functions -----------------------//
function rand_char($length) {
	$random = '';
	for ($i = 0; $i < $length; $i++) {
	$random .= chr(mt_rand(33, 126));
	}
	return $random;
}

function rand_sha1($length) {
	$max = ceil($length / 40);
	$random = '';
	for ($i = 0; $i < $max; $i ++) {
	$random .= sha1(microtime(true).mt_rand(10000,90000));
	}
	return substr($random, 0, $length);
}

function rand_md5($length) {
	$max = ceil($length / 32);
	$random = '';
	for ($i = 0; $i < $max; $i ++) {
	$random .= md5(microtime(true).mt_rand(10000,90000));
	}
	return substr($random, 0, $length);
}
//----------------------- randomize functions -----------------------//



//everything below here we have no idea if we use anywhere. most of it is legacy crap, we should put it a funct_legacy.php to test

function AlphaNumericOnly_RepaceWithSpace( $string ){
	return preg_replace('/[^a-zA-Z0-9\s]/', ' ', $string);
}

//Function to check for valid email
function is_valid_email($string) {
	return preg_match('/^[.\w-]+@([\w-]+\.)+[a-zA-Z]{2,6}$/', $string);
}
function alphanumericAndSpace( $string ){
	return preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
}

function functRemoveNonAlphaNumeric($strString){
	$new_string = preg_replace("/[^a-zA-Z0-9\s]/", "", $strString);
	return $new_string ;
}

function functRemoveNonNumeric($strString){
	$new_string = preg_replace("/[^0-9\s]/", "", $strString);
	return $new_string ;
}

?>