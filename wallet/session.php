<?php

//$intDebugMode=1;
if($intDebugMode){
	ini_set('display_errors',1);
	if(DEBUGMODE==2){ error_reporting(E_ERROR | E_WARNING | E_PARSE); }
	if(DEBUGMODE==1){ error_reporting(E_ERROR | E_PARSE); }
}else{
	//error_reporting(0);// Turn off all error reporting
	//error_reporting(E_ERROR | E_WARNING | E_PARSE);// Report simple running errors
}

//protect against acunetix security scanner
if ($_SERVER['HTTP_ACUNETIX_PRODUCT'] || 
    $_SERVER['HTTP_ACUNETIX_SCANNING_AGREEMENT'] || 
    $_SERVER['HTTP_ACUNETIX_USER_AGREEMENT']){ 
    exit; 
} 

session_start(); //Start Session before Constants.php to allow defining of session varibles names
require "constants.php"; //calls server.php within
require "functStrings.php"; //holds all custom string formatting functions
require "functBilling.php"; //holds billing functions * should really only use as needed
include $_SERVER['DOCUMENT_ROOT']."/inc/functmail.php"; //holds all email functions * use as needed
include $_SERVER['DOCUMENT_ROOT']."/inc/password.php"; //holds all email functions * use as needed

//include $_SERVER['DOCUMENT_ROOT']."/inc/jsonRPCClient.php"; //holds all custom string formatting functions
//include $_SERVER['DOCUMENT_ROOT']."/inc/functdatabase.php"; //holds database functions, largely useless for now ?
//include $_SERVER['DOCUMENT_ROOT']."/inc/functApps.php"; //holds all application shell call functions * BUG cmp3.php which is included in this writes out to the headers and disables writing of cookies.


$intUserID_fromcode = funct_GetUserIDfromUserCode(); //legacy fix to replace int with 48 char hash from cookie

// track user in log
error_log("{".SERVERTAG."} ==== [$intUserID_fromcode @ ".$_SERVER['REMOTE_ADDR']."] (".$_SERVER['REQUEST_URI'].")");


//echo "useridcode in sessions.php = $intUserID_fromcode <br>" ;
if($intUserID_fromcode){ //if value is returned then define detect userid with internal int
    define("DETECT_USERID", $intUserID_fromcode); //echo "redefining DETECT_USERID ";
}else{
    define("DETECT_USERID", "");
}

//they are browsing the site , not coming to a content link from the outside
$_SESSION['visitedIndex'] = TRUE;


//########################################################################################################################################
//# temp fix to fix security hole
//#
function funct_GetUserIDfromUserCode(){ //legacy function to switch over to user code hash instead of easily spoofed id
	//only works if they are logged in, must be either userid or userid_code
	global $DB_LINK ;
	$strUserIDcode=DETECT_USERIDCODE;
	$strCookieUserIDlegacy = $_COOKIE[SESSION_USERID] ;
	
	if($strCookieUserIDlegacy){ $query="SELECT * FROM " . TBL_USERS . " WHERE id= ".$strCookieUserIDlegacy." " ; }
	if($strUserIDcode){ $query="SELECT * FROM " . TBL_USERS . " WHERE id_code= '".DETECT_USERIDCODE."'" ; }
	
	if($query){
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) ;// or die(mysqli_error());
		if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
			$intUserID=						$row["id"];
			$strUserIDcodeDB=					$row["id_code"];

		}else{//no user found
			$intUserID=0;
		}
	}
	//echo "code=".$strUserIDcode."<br>";echo "id=".$intUserID."<br>";
	
    //if userid code is in cookie yet not in database then update it.. when would this be the case?
	if(!$strUserIDcodeDB){
		$strUserIDcode=rand_sha1(48) ;
		$query = "UPDATE ".TBL_USERS." SET id_code='$strUserIDcode' WHERE id = $strCookieUserIDlegacy " ;
		//echo "SQL STMNT = " . $query . "<br>";
		$rs = mysqli_query($DB_LINK, $query) ;
		//Write Session & cookies
		//$_SESSION[SESSION_USERIDCODE] = $strUserIDcode;
		setcookie(SESSION_USERIDCODE , 	$strUserIDcode , COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
	}
	
	return $intUserID ;
}
//########################################################################################################################################




//########################################################################################################################################
// Write out Session Data & Cookie for login
// called from: /join.php , 
function functLoginUser($intUserID, $strRememberFlag, $strPasswordHash){
	
	/* Make connection to database */
	global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )
	
	$query="SELECT * FROM ".TBL_USERS." WHERE id=".$intUserID ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$row=mysqli_fetch_array($rs) ;
	$intUserID=			$row["id"];
	$strUserIDcode=		$row["id_code"];
	$strPassword=		$row["password"];
	$strEmail=			$row["email"];
	$intLastLogin=		$row["lastactivity"];
	
	if(!$strUserIDcodeDB){
		$strUserIDcode=rand_sha1(48) ;
		$query = "UPDATE ".TBL_USERS." SET id_code='$strUserIDcode' WHERE id = $intUserID " ;
		//echo "SQL STMNT = " . $query . "<br>";
		$rs = mysqli_query($DB_LINK, $query) ;
		//Write Session & cookies
		//$_SESSION[SESSION_USERIDCODE] = $strUserIDcode;
		setcookie(SESSION_USERIDCODE , 	$strUserIDcode , COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
	}
	
	//if no password passed then get it from the db
	if(!$strPasswordHash){ $strPasswordHash = $strPassword ;}

	//echo "writing sessions and cookies.... <br><br>" ; 
	setcookie('SESSION_EMAIL' , 	"" , $intCookiePast, COOKIE_PATH, COOKIE_DOMAIN); //delete cookie..legacy  wipe
	
	setcookie(SESSION_USERIDCODE , 	$strUserIDcode , COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(SESSION_EMAIL , 		$strEmail , 	COOKIE_EXPIRE,COOKIE_PATH,COOKIE_DOMAIN);	
	//setcookie(SESSION_PASSWORD , 	$strPasswordHash , 	COOKIE_EXPIRE,COOKIE_PATH,COOKIE_DOMAIN);

	//functUpdateUserActivityDate($intUserID) ;//Update User table with new Login Date
	$query="UPDATE " . TBL_USERS . " SET lastlogin= NOW() WHERE id=".$intUserID." ";
	//echo "SQL STMNT = " . $query .  "<br>";
	mysqli_query($DB_LINK, $query) or die(mysqli_error());
	return $intLastLogin ;

}
//########################################################################################################################################


//########################################################################################################################################
// confirmUserPass_hash - this version takes the encrypted password and checks it
function functConfirmUserPass_hash($email, $password){ //this ver

	global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )
	
	$email= mysqli_real_escape_string($DB_LINK,$email); //prevent sql injection attacks
	$email= strtolower($email); //case insensitive search
	$password= mysqli_real_escape_string($DB_LINK,$password); //prevent sql injection attacks
	
	/* Verify that user is in database */
	$query = "SELECT id,password FROM ".TBL_USERS." WHERE email = '$email' "; 
	//echo "SQL STMNT = " . $query .  "<br>"; 
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row = mysqli_fetch_array($rs) ; 
	$strPassword_DB = $row["password"]; 
	$intUserID_DB = $row["id"]; 
	$strUserIDcode=	$row["id_code"]; 
	//echo "strPassword_DB=$strPassword_DB - password=$password <br>";
	if(!$rs || (mysqli_num_rows($rs) < 1)){	return "nousername"; }//Indicates username failure
	
	/* Validate that hashed password is correct */
    if(password_verify($password, $strPassword_DB)){
    	$strPasswordGood = "ok";
    	
    	if(SECURITY_WRITE_PASSWORD){
    		setcookie(SESSION_PASSWORD, $strPassword_DB, COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN); //write out hashed password to cookie
		}

    }else{ //hashed password is not correct so check if password is clear text
    	//echo "hashpassword wrong - checking clear text password ... $password==$strPassword_DB <br>";
	    if(SECURITY_PASSWORD_LOOSE_LOGIN){ //allow them to login with loose password
	    
	        if($password==$strPassword_DB){ //check if password as clear text is good
	        	$strPasswordGood = "ok"; //if clear text password is good then hash it and store it
	        	//echo "checking clear text password $strPasswordGood <br>";
		        if(PASSWORD_ENCRYPT){ //encrypt password and update the database with it
		        	            
		            $password = password_hash($password, PASSWORD_DEFAULT); //PASSWORD_BCRYPT
		            
		            $query="UPDATE " . TBL_USERS . " SET password='".$password."' WHERE id=".$intUserID_DB ;
		            //echo "SQL STMNT = " . $query .  "<br>";
		            mysqli_query($DB_LINK, $query) or die(mysqli_error());
		            
		            if(SECURITY_WRITE_PASSWORD){
		            	//write password to cookie
		            	setcookie(SESSION_PASSWORD, $strPassword_DB, COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN); //write out hashed password to cookie
		            }
		        }//end hashing password        	
	        }//end check clear text password
	    }//end loose password check
    }//end check password_verify hash check
    
	if ($strPasswordGood) { //password is hashed and good!

		return $intUserID_DB ; //Success! Username and password confirmed
		
	} else { // password is not good
	   
        return "badpassword"; //Indicates password failure
			
	}//end if password is hashed and good
}//end function
//########################################################################################################################################








//########################################################################################################################################
// confirmUserPass - Checks whether or not the given username is in the database, if so it checks if the given password is the same password in the database
// for that user. If the user doesn't exist or if the passwords don't match up, it returns an error code (1 or 2). On success it returns 0.
function functConfirmUserPass($email, $password){

	global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )
	
	$email= mysqli_real_escape_string($DB_LINK,$email); //prevent sql injection attacks
	$email= strtolower($email); //case insensitive search
	$password= mysqli_real_escape_string($DB_LINK,$password); //prevent sql injection attacks
	$password= strtolower($password); //case insensitive search

	/* Verify that user is in database */
	$query = "SELECT id,password FROM ".TBL_USERS." WHERE email = '$email'";
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row = mysqli_fetch_array($rs) ;
	$strPassword_DB = $row["password"];
	$intUserID_DB = $row["id"];
	
	if(!$rs || (mysqli_num_rows($rs) < 1)){
		return "nousername"; //Indicates username failure
	}
	
	/* Retrieve password from result, strip slashes */
	$password = stripslashes($password);
	
	/* Validate that password is correct */
	if(stripslashes($password) == stripslashes($strPassword_DB)){
	 return $intUserID_DB ; //Success! Username and password confirmed
	}
	else{
		return "badpassword"; //Indicates password failure
	}
}
//########################################################################################################################################









//########################################################################################################################################
// Write out Cookie for login
function functLogOutUser(){
	
	//Destory all Cookie Data
	$intCookiePast = time() - 10000; //10000 seconds ago

	//leave email
	setcookie(SESSION_USERID , 	"" , $intCookiePast, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(SESSION_USERIDCODE , 	"" , $intCookiePast, COOKIE_PATH, COOKIE_DOMAIN);
	setcookie(SESSION_PASSWORD , 	"" , $intCookiePast, COOKIE_PATH, COOKIE_DOMAIN);
	
	//Destory Session
	session_destroy();
	
}
//########################################################################################################################################


?>