<?php

error_reporting(E_ALL & ~E_NOTICE);

include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";

$strDo = trim($_GET['do']);
//echo "strDo= " . $strDo;

//$intDebugFlag = true ;

switch ($strDo){

	case "movefundsamsterdam2hot":
	
		$intToAmount = abs(funct_ScrubVars($_POST["amount"])) ;
		
		if(!$intToAmount){ echo "no amount"; die; }
		
		//call function to move funds
		$strToAddress = BLOCKCHAIN_SENDFROMADDRESS; //blockchain.info hot wallet.
		$strNote = "amst2hot";
		$strResponse = funct_Billing_SendBTC_CoinCafe($strToAddress, $intToAmount, $strNote, $intMiningFee, $strFrom );
		
		$strSendArry = explode("|", $strResponse);
		$strSendMsg=$strSendArry[0]; // message
		$strSendErr=$strSendArry[1]; // error
		$strSendHash=$strSendArry[2];
		
		echo $strResponse ;
	
	break;

	
	//!CASE $strDo "verifykyc"
	case "verifykyc":
	
		$user_id = funct_ScrubVars($_GET["id"]) ; //this "id" here is passed in the URL (querystring)
		
		$query3="UPDATE " .TBL_USERS. " SET kyc=1 WHERE id=".$user_id ;
		//echo "SQL STMNT = " .$query3.  " <br>";
		$rs = mysqli_query($DB_LINK, $query3) or die(mysqli_error());
		
		echo "ok" ;
	
	break;
	

	//!CASE $strDo "upload"
	case "upload":
		//we need user id, filename, file extension, orderid opt, from page
		$intUserID=			funct_ScrubVars($_GET["userid"]);
		$intOrderID=		funct_ScrubVars($_GET["orderid"]);
		$uploaded_file=		__ROOT__.PATH_UPLOADS.funct_ScrubVars($_GET["filename"]);
		$strFromPage=		funct_ScrubVars($_GET["frompage"]);	
		
		$intTime = 			time();
		if(!$strFromPage){$strFromPage="receipts";}
		
		if(!$intUserID){ $intUserID = DETECT_USERID; }
		if($intUserID){ //Get User Data from DataBase
			$query="SELECT * FROM ".TBL_USERS." WHERE id = ".$intUserID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
			$Email=							$row["email"];
			$strFirstName=					$row["first_name"];
			$strLastName=					$row["last_name"];
			$strPhone=						$row["cellphone"];
            $intverification_id =    		$row["verification_id"];
		}
		
		//make thumbnail if it is an image...?
		$strExtension = get_extension($strFileName) ; //name is filename of uploaded file
		if($strExtension=="jpg" OR $strExtension=="jpeg" OR $strExtension=="png" OR $strExtension="gif"){
			//save thumbbail - [BUG] thumbnails are being rotated... ????
			$imageThumbPath = PICTURETHUMBPATH.$strKeyLink.".jpg" ;
			$image = new SimpleImage(); //init object
			$image->load($uploaded_file);
			$width = $image->getWidth();
			$height = $image->getHeight();
			//$image->resize(100,100); //rescale
			$image->resizeToWidth(200); //we want to instead save it with proportions intact...
			$image->save(__ROOT__.$imageThumbPath);
		}
		
        if($strExtension=="pdf"){$strCopyExt="pdf";}else{$strCopyExt="jpg";}
		
		//send admin an email on upload
		if($strFromPage=="receipts"){
		
			//get crypto rate
			$intCryptoRate = funct_Billing_GetRate();
			
			if($dateFirstreceiptUpload==0){
				$strSQL2 = " first_receipt_upload_time= NOW(), rate_at_time_of_first_receipt_upload=$intCryptoRate,";
			}
			
			if($intOrderID){ 
				
				//get order info
				$query="SELECT * FROM ".TBL_ORDERS." WHERE orderid = ".$intOrderID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				$rs = mysqli_query($DB_LINK, $query); $row=mysqli_fetch_array($rs) ;
				$intAmtUSD=							$row["amt_usd"];
				$intFiat2Convert=					$row["fiat_to_convert"];
				$intUserID=							$row["from_id"];
				
				//Added by John:
				$orderCode=							$row["ordercode"];
				$orderType=							$row["type"];
				
				//update order table
				$query="UPDATE " . TBL_ORDERS . " SET ".$strSQL2.
				" no_receipts_uploaded= no_receipts_uploaded + 1 , ".
				" last_receipt_upload_time=NOW(), ".
				" rate_at_time_of_last_receipt_upload=$intCryptoRate ".
				" status_id=3, ".
				" status_text='Waiting for desposit to clear.' ".
				" WHERE orderid=".$intOrderID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query);
				
				if($intUserID){
					$query="UPDATE " . TBL_TRANSACTIONS . " SET ".	
					" status=3, ".
					" status_msg='Waiting for desposit to clear.' ".
					" WHERE order_id=".$intOrderID." AND user_id=".$intUserID ;
					//echo "SQL STMNT = " . $query .  "<br>";
					mysqli_query($DB_LINK, $query);
				}
			}
			
			$strUploadSrc = PATH_RECEIPTS.$intOrderID.".".$strCopyExt ;
			//echo "uploadedfile=".$uploaded_file." receiptkyc=".$strReceiptKYC."<br>";
			copy($uploaded_file, __ROOT__.$strUploadSrc); //upload another copy with the orderid as a jpg
			
			//email the admin about new reciept upload
			$strSubject = "Receipt for Order ".$intOrderID." $".number_format($intAmtUSD,2)." User ".$intUserID." ".$strFirstName." ".$strLastName ;
			$strBody ="File Uploaded: ".WEBSITEFULLURL.$strUploadSrc." \n"
					 ."Order link ".WEBSITEFULLURL.PAGE_RECEIPT."?c=".$orderCode."\n"
                     ."Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=".$intUserID." \n\n"
					 ."".WEBSITEFULLURL." ";
			funct_Mail_simple(EMAIL_ORDERS,$strSubject,$strBody);
			
			
			
			
			//John's code to update the database with the next status of waiting for deposit to clear once the receipt is uploaded			
			if ($orderType=="check") {
				$status_id = "4";
				$status_name = "Waiting for bank check to arrive";				
			} else {
				$status_id = "3";
				$status_name = "Waiting for deposit to clear";				
			}
			
			$query = "INSERT INTO ".TBL_ORDERS_DETAILS.
			" ( orderid,		status_id,		statustxt,	date ) VALUES ".
			" ( $intOrderID,	$status_id,	'$status_name', NOW() ) " ;
			//echo "SQL STMNT = " . $query .  "<br><br><br>";
			mysqli_query($DB_LINK, $query);	
		
			//update transactions table so that user can see status in their wallet.php	
			$query="UPDATE " . TBL_TRANSACTIONS . " SET status=$status_id, status_msg='" . $status_name . "' WHERE order_id=" . $intOrderID . " AND type='buy'";
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
		
			//update orders table with latest status of order
			$query="UPDATE " . TBL_ORDERS . " SET status_id=$status_id, status_text='" . $status_name . "' WHERE orderid=" . $intOrderID;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());

			
			
			
	
		}//end if chest id specified
		/* */
		
		if($strFromPage=="id"){ //send email to admin
		
			$strUploadSrc = PATH_KYC.$intUserID.".".$strCopyExt ;
			copy($uploaded_file, __ROOT__.$strUploadSrc); //copy pic to recipets folder. latest overwrites newest
			//echo "uploadedfile=".$uploaded_file." copied=".$strUploadSrc."<br>";

			
			//email admin
			$strSubject = "ID Upload ".$intUserID." ".$strFirstName." ".$strLastName." ".$Email ;
			$strBody ="ID File Uploaded: ".WEBSITEFULLURL.$strUploadSrc." \n".
			"Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=".$intUserID." \n".
			"$Email \n $strPhone \n".
			"".WEBSITEFULLURL." ";
			funct_Mail_simple(EMAIL_ORDERS,$strSubject,$strBody);
			
			if($intUserID){
				$query="UPDATE " . TBL_USERS . " SET verification_id= verification_id + 1 WHERE id=".$intUserID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query) ;
			}
		}

		echo "ok" ;
			

	
	break;














	//!CASE $strDo "setstatus1"
	case "setstatus1":
		//we need user id, filename, file extension, orderid opt, from page
		$intUserID=			funct_ScrubVars($_GET["userid"]);
		$intOrderID=		funct_ScrubVars($_GET["orderid"]);
		$uploaded_file=		__ROOT__.PATH_UPLOADS.funct_ScrubVars($_GET["filename"]);
		$strFromPage=		funct_ScrubVars($_GET["frompage"]);		
		
		$intTime = 			time();
		if(!$strFromPage){$strFromPage="receipts";}
		
		if(!$intUserID){ $intUserID = DETECT_USERID; }
		if($intUserID){ //Get User Data from DataBase
			$query="SELECT * FROM ".TBL_USERS." WHERE id = ".$intUserID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
			$Email=							$row["email"];
			$strFirstName=					$row["first_name"];
			$strLastName=					$row["last_name"];
			$strPhone=						$row["cellphone"];
            $intverification_id =    		$row["verification_id"];
		}
		
		//make thumbnail if it is an image...?
		$strExtension = get_extension($strFileName) ; //name is filename of uploaded file
		if($strExtension=="jpg" OR $strExtension=="jpeg" OR $strExtension=="png" OR $strExtension="gif"){
			//save thumbbail - [BUG] thumbnails are being rotated... ????
			$imageThumbPath = PICTURETHUMBPATH.$strKeyLink.".jpg" ;
			$image = new SimpleImage(); //init object
			$image->load($uploaded_file);
			$width = $image->getWidth();
			$height = $image->getHeight();
			//$image->resize(100,100); //rescale
			$image->resizeToWidth(200); //we want to instead save it with proportions intact...
			$image->save(__ROOT__.$imageThumbPath);
		}
		
        if($strExtension=="pdf"){$strCopyExt="pdf";}else{$strCopyExt="jpg";}
		
		//send admin an email on upload
		if($strFromPage=="receipts"){
		
			//get crypto rate
			$intCryptoRate = funct_Billing_GetRate();
			
			if($dateFirstreceiptUpload==0){
				$strSQL2 = " first_receipt_upload_time= NOW(), rate_at_time_of_first_receipt_upload=$intCryptoRate,";
			}
			
			if($intOrderID){ 
				
				//get order info
				$query="SELECT * FROM ".TBL_ORDERS." WHERE orderid = ".$intOrderID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				$rs = mysqli_query($DB_LINK, $query); $row=mysqli_fetch_array($rs) ;
				$intAmtUSD=							$row["amt_usd"];
				$intFiat2Convert=					$row["fiat_to_convert"];
				$intUserID=							$row["from_id"];
				
				//Added by John:
				$orderCode=							$row["ordercode"];
				$orderType=							$row["type"];
				
				//update order table
				$query="UPDATE " . TBL_ORDERS . " SET ".$strSQL2.
				" no_receipts_uploaded= no_receipts_uploaded + 1 , ".
				" last_receipt_upload_time=NOW(), ".
				" rate_at_time_of_last_receipt_upload=$intCryptoRate ".
				" status_id=3, ".
				" status_text='Waiting for desposit to clear.' ".
				" WHERE orderid=".$intOrderID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query);
				
				if($intUserID){
					$query="UPDATE " . TBL_TRANSACTIONS . " SET ".	
					" status=3, ".
					" status_msg='Waiting for desposit to clear.' ".
					" WHERE order_id=".$intOrderID." AND user_id=".$intUserID ;
					//echo "SQL STMNT = " . $query .  "<br>";
					mysqli_query($DB_LINK, $query);
				}
			}
			
			$strUploadSrc = PATH_RECEIPTS.$intOrderID.".".$strCopyExt ;
			//echo "uploadedfile=".$uploaded_file." receiptkyc=".$strReceiptKYC."<br>";
			copy($uploaded_file, __ROOT__.$strUploadSrc); //upload another copy with the orderid as a jpg
			
			//email the admin about new reciept upload
			$strSubject = "Receipt for Order ".$intOrderID." $".number_format($intAmtUSD,2)." User ".$intUserID." ".$strFirstName." ".$strLastName ;
			$strBody ="File Uploaded: ".WEBSITEFULLURL.$strUploadSrc." \n"
					 ."Order link ".WEBSITEFULLURL.PAGE_RECEIPT."?c=".$orderCode."\n"
                     ."Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=".$intUserID." \n\n"
					 ."".WEBSITEFULLURL." ";
			funct_Mail_simple(EMAIL_ORDERS,$strSubject,$strBody);
			
			
			
			
			//John's code to update the database with the next status of waiting for deposit to clear once the receipt is uploaded			
			if ($orderType=="check") {
				$status_id = "4";
				$status_name = "Waiting for bank check to arrive";				
			} else {
				$status_id = "3";
				$status_name = "Waiting for deposit to clear";				
			}
			
			$query = "INSERT INTO ".TBL_ORDERS_DETAILS.
			" ( orderid,		status_id,		statustxt,	date ) VALUES ".
			" ( $intOrderID,	$status_id,	'$status_name', NOW() ) " ;
			//echo "SQL STMNT = " . $query .  "<br><br><br>";
			mysqli_query($DB_LINK, $query);	
		
			//update transactions table so that user can see status in their wallet.php	
			$query="UPDATE " . TBL_TRANSACTIONS . " SET status=$status_id, status_msg='" . $status_name . "' WHERE order_id=" . $intOrderID . " AND type='buy'";
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
		
			//update orders table with latest status of order
			$query="UPDATE " . TBL_ORDERS . " SET status_id=$status_id, status_text='" . $status_name . "' WHERE orderid=" . $intOrderID;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());

			
			
			
	
		}//end if chest id specified
		/* */
		
		if($strFromPage=="id"){ //send email to admin
		
			$strUploadSrc = PATH_KYC.$intUserID.".".$strCopyExt ;
			copy($uploaded_file, __ROOT__.$strUploadSrc); //copy pic to recipets folder. latest overwrites newest
			//echo "uploadedfile=".$uploaded_file." copied=".$strUploadSrc."<br>";

			
			//email admin
			$strSubject = "ID Upload ".$intUserID." ".$strFirstName." ".$strLastName." ".$Email ;
			$strBody ="ID File Uploaded: ".WEBSITEFULLURL.$strUploadSrc." \n".
			"Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=".$intUserID." \n".
			"$Email \n $strPhone \n".
			"".WEBSITEFULLURL." ";
			funct_Mail_simple(EMAIL_ORDERS,$strSubject,$strBody);
			
			if($intUserID){
				$query="UPDATE " . TBL_USERS . " SET verification_id= verification_id + 1 WHERE id=".$intUserID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query) ;
			}
		}

		echo "ok" ;
			

	
	break;
		














		
} //End Switch Statement

?>