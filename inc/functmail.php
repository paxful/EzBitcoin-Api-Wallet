<?php
//error_reporting(0);//turn off all reporting
include $_SERVER['DOCUMENT_ROOT']."/inc/phpmailer/class.phpmailer.php"; //for smtp mailing
include $_SERVER['DOCUMENT_ROOT']."/inc/SendGrid.php"; //for sendgrid mailing
//include $_SERVER['DOCUMENT_ROOT']."/inc/Unirest.php"; //for sendgrid mailing
//include $_SERVER['DOCUMENT_ROOT']."/inc/Smtpapi.php"; //for sendgrid mailing
//require_once 'Unirest.php'; require_once 'SendGrid.php'; require_once 'Smtpapi.php';

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
	
	//send using free method since it is coming to us
	$strReturn = funct_Mail_MX($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail);	
	//$strReturn = funct_Mail_SendGrid_curl($strEmail, $strSubject, $strBody, $strFromEmail, $strReplyEmail);
	
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

?>