<?php
/*
Houses functions that call database
Author may functSendSMSswitch
*/




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




function funct_GenerateNewID(){

	global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )

	
	//generate unique value DB mysql method
	$query="SELECT UUID() AS ordercode " ; //unique ID
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$row = mysqli_fetch_array($rs) ;
	$strOrderCode = $row["ordercode"];
	
	
	$strOrderCoder = and_char($length) ;
	$strOrderCode = rand_sha1($length) ;
	$strOrderCode = rand_md5($length) ;
	
	return $strOrderCode ; 
	
}



?>