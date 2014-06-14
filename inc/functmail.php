<?php
//error_reporting(0);//turn off all reporting
include $_SERVER['DOCUMENT_ROOT']."/inc/class.phpmailer.php"; //for smtp mailing
include $_SERVER['DOCUMENT_ROOT']."/inc/SendGrid.php"; //for sendgrid mailing


/**
 * Mailer.php
 *
 * The Mailer class is meant to simplify the task of sending
 * emails to users. 
 */
 
 
function funct_Mail_simple($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail){
//used as a switch for mail functions

	if(!$strFromEmail){ $strFromEmail= SUPPORT_EMAIL; }
	if(!$strReplyEmail){ $strReplyEmail= SUPPORT_EMAIL; }

    //decide which method to use to send emails EMAIL_METHOD
    switch ($strDo){

        case "": //send using free method
            $strReturn = funct_Mail_MX($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail);
        break;

        case "sendgrid": //send using sendgrid
            $strReturn = funct_Mail_SendGrid_curl($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail);
        break;

        //other methods go here, mailgun etc..

    }
	
	return $strReturn ;
}

function funct_Mail_SendGrid_curl($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail){
	$url = 'http://sendgrid.com/';
	$user = EMAIL_SENDGRID_USERNAME;
	$pass = EMAIL_SENDGRID_PASSWORD;
	
	if(!$strFromEmail){$strFromEmail=EMAIL_WALLETSEND;}
	if(!$strReplyEmail){$strReplyEmail=$strEmail;}
	
	$params = array(
	    'api_user'  => $user,
	    'api_key'   => $pass,
	    'to'        => $strEmail,
	    'subject'   => $strSubject,
	    'text'      => $strBody,
	    'from'      => $strFromEmail
	  );
	
	$request =  $url.'api/mail.send.json';
	
	// Generate curl request
	$session = curl_init($request);
	// Tell curl to use HTTP POST
	curl_setopt ($session, CURLOPT_POST, true);
	// Tell curl that this is the body of the POST
	curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
	// Tell curl not to return headers, but do return the response
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	// obtain response
	$response = curl_exec($session);
	curl_close($session);
	
	// print everything out
	return $response;
		
}

function funct_Mail_MX($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail){
//this function works when you change your mx server settings to google mail
	if(!$strFromEmail){ $strFromEmail= SUPPORT_EMAIL;}
	if(!$strReplyEmail){ $strReplyEmail= SUPPORT_EMAIL;}

	//echo "sending email 2... <br>";
	$headers = 	'From: '.$strFromEmail." \r\n".
	    		'Reply-To: '.$strReplyEmail." \r\n".
	    		'X-Mailer: PHP/' . phpversion();
	mail($strEmail,$strSubject,$strBody,$headers );

}

function funct_Mail_SendGrid($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail){
	
	if(!$strFromEmail){$strFromEmail=EMAIL_WALLETSEND;}
	if(!$strReplyEmail){$strReplyEmail=$strEmail;}

	//echo "calling sendgrid function... <br>";
	$sendgrid = new SendGrid(EMAIL_SENDGRID_USERNAME, EMAIL_SENDGRID_PASSWORD);
	//echo "object created <br>";

	$email = new SendGrid\Email();
	$email->addTo($strEmail)->
       setFrom($strFromEmail)->
       setReplyTo($strReplyEmail)->
       setSubject($strSubject)->
       setText($strBody);
       //setHtml('<strong>Hello World!</strong>');

	$sendgrid->send($email);
		
}



function functSendEmail($emailaddr, $strSubject, $strBody, $name, $strFromName, $strFromEmail, $strAttachmentFileFullPath, $strEmailType="text/plain"){
//base function to send all emails from webservers smtp service

	if($strFromName=="") { $strFromName=EMAIL_FROM_NAME ;}
	if($strFromEmail=="") { $strFromEmail=EMAIL_FROM_ADDR ;}
	
	// instantiate the class
	$mailer = new FreakMailer();
	// Get From Variable from Constants File
	$mailer->ContentType = $strEmailType; 
	if($strEmailType=="text/html"){ $strHTMLMime=true;}else{ $strHTMLMime=false ;}
    $mailer->IsHTML($strHTMLMime);
	$mailer->From = 			$strFromEmail;
	$mailer->FromName = 		$strFromName;
	$mailer->Subject = 			$strSubject;
	$mailer->Body = 			$strBody;
	
	if( is_array($emailaddr) ){ 

		foreach($emailaddr as $email) { 
			$mailer->AddAddress($email, $name); 
		}
	}else{	
		//send one
		$mailer->AddAddress($emailaddr, $name);
	}
	
	//now Attach file submitted
	if($strAttachmentFileFullPath && file_exists($strAttachmentFileFullPath)){
		$mailer->AddAttachment($strAttachmentFileFullPath);
	}
	 
	if(!$mailer->Send()){
		$strErrorString = 'There was a problem sending this mail!';
	}else{
		$strErrorString =  "sent";
	}
	$mailer->ClearAddresses();
	$mailer->ClearAttachments();
	
	return $strErrorString;
}



function funct_SendEmailCode($intUserID){

    global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )

    //generate unique code
    $intCode=createRandomKey_Num(12);

    //update member record
    $query = "UPDATE ".TBL_USERS." SET emailcode='$intCode' WHERE id = $intUserID " ;
    //echo "SQL STMNT = " . $query . "<br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());

    //get email
    $query = "SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
    //echo "SQL STMNT = " . $query . "<br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
    $strEmail=					$row["email"];

    $strEmailLink = WEBSITEFULLURLHTTPS.CODE_DO."?do=confirmemailcode&emailcode=".$intCode ;

    //send email
    $strSubject= WEBSITENAME." Verification Code ".$intCode ;
    $strBody="Your ".WEBSITENAME." Verification Code is \n ".$intCode." \n ".$strEmailLink ;
    funct_Mail_simple($strEmail,$strSubject,$strBody);
    //$name=EMAIL_FROM_NAME ; $strFromName=WEBSITENAME ; $strFromEmail=EMAIL_FROM_ADDR ;
    //$strError = functSendEmail($strEmail, $strSubject, $strBody, $name, $strFromName, $strFromEmail);

    return $strError;


}

?>