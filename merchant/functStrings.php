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


function funct_die_and_Report($strMessage, $strUserMessage, $strMessageAdmin, $strPage, $intLogRecord) {
//get error string, usermsg, admin msg and page -> then send email to admin and die

    if(!$strUserMessage){ $strUserMessage = $strMessage ;}
    if(!$strPage){ $strPage = "merchantapi" ;}

    //update log record
    if($intLogRecord){

        $query = "UPDATE ".$tbl_Logs." SET " .
            "response='$strMessage' ".
            "WHERE log_id = $intLogRecord " ;
        //echo "SQL STMNT = " . $query .  "<br>";
        $rs = mysqli_query($DB_LINK, $query);
    }

    if($strMessageAdmin){
        $strSubject = "Error Detected on: ".$strPage." " ;
        $strBody = " ".$strMessageAdmin." \n ".$strMessage." \n ".$strUserMessage." \n ".$strPage ;
        funct_Mail_simple(EMAIL_ADMIN,$strSubject,$strBody);
    }
    die($strUserMessage);
}


?>