<?php 

error_reporting(E_ALL & ~E_NOTICE);

include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

$strDo = 				trim($_GET['do']);
//echo "strDo= " . $strDo;
ini_set('display_errors',1); error_reporting(E_ERROR | E_PARSE);
//$intDebugFlag = true ;

switch ($strDo){
	
	case "getbalance": //!case "getbalance"
	
		$strFiatType = 					(funct_ScrubVars($_GET["fiat"])) ;
		$strCryptoType = 				(funct_ScrubVars($_GET["crypto"])) ;
		$intOwnerID = 					(funct_ScrubVars($_GET["userid"])) ;
		
		if($intOwnerID != $intUserID_fromcode){
			// 401 redirect
			if($intUserID_fromcode == ''){
				error_log('AUTH ISSUE: balance requested from user who is not logged in');
				// todo: ajax fail should redirect to login page
			} else {
				error_log('AUTH ISSUE: balance requested from unauthorized user');
				// todo: ajax fail should redirect to login page
			}
			header("HTTP/1.1 401 Unauthorized");
			exit();
		}

		//get usr info
		$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intOwnerID ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
		$intBalanceCrypto=				$row["balance_btc"];
		
		//get balances
		$intBalance = $intBalanceCrypto ;
				
		//get fiat / crypto symbol
		$intCryptoRate = funct_Billing_GetRate($strCryptoType);
		$intFiatBalance = $intBalance * $intCryptoRate ;
		
		echo $intBalance.",".$intFiatBalance ;
	
	break;
	
	
	case "upload": //!case "upload"
		
		//we need user id, filename, file extension, orderid opt, from page
		$intUserID=			funct_ScrubVars($_GET["userid"]);
		$intOrderID=		funct_ScrubVars($_GET["orderid"]);
		$uploaded_file=		__ROOT__.PATH_UPLOADS.funct_ScrubVars($_GET["filename"]); // ex filename.jpg
		$strFromPage=		funct_ScrubVars($_GET["frompage"]); //does not go into db
		
		$intTime = time();
		if(!$strFromPage){ $strFromPage="receipts"; }
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
            
            if($intOrderID){//get data from Orders table, including first and last receipt upload date/time
				$queryorder="SELECT * FROM ".TBL_ORDERS." WHERE orderid = ".$intOrderID ;
				//echo "SQL STMNT = " . $queryorder .  "<br>";
				$rsorder = mysqli_query($DB_LINK, $queryorder) or die(mysqli_error()); $roworder=mysqli_fetch_array($rsorder) ;
				$first_receipt_upload_time=		$roworder["first_receipt_upload_time"];
				$last_receipt_upload_time=		$roworder["last_receipt_upload_time"];
			}
		}
		
	
		$strExtension = get_extension($uploaded_file) ; //name is filename of uploaded file
		$strFileBaseName = basename($uploaded_file, '.'.$strExtension) ;
		
		//location of full file
		$strUploadSrc = PATH_UPLOADS.$strFileBaseName.".".$strExtension ; 
		
		//echo "$strFileBaseName . ext= $strExtension ";
		
		//security hack check
		if($strExtension=="php" OR $strExtension=="html" OR $strExtension=="htm" OR $strExtension=="shtml" OR $strExtension=="shm" OR $strExtension=="js"){
			die("hackers must die");
		}
		
		if($strExtension=="jpg" OR $strExtension=="png" OR $strExtension=="jpeg" OR $strExtension=="gif" OR $strExtension=="pdf" OR $strExtension=="tif" OR $strExtension=="tiff" OR $strExtension=="bmp"){
			//good
		}else{
			die("bad file type");
		}
		
		
		
		
		//
		if($strExtension=="jpg" OR $strExtension=="jpeg" OR $strExtension=="png" OR $strExtension=="gif"){
			//save thumbbail - [BUG] thumbnails are being rotated... ????
			$imageThumbPath = PICTURETHUMBPATH.$strFileBaseName.".jpg" ;
			$image = new SimpleImage(); //init object
			$image->load($uploaded_file);
			$width = $image->getWidth();
			$height = $image->getHeight();
			//$image->resize(100,100); //rescale
			$image->resizeToWidth(100); //we want to instead save it with proportions intact...
			$image->save(__ROOT__.$imageThumbPath);
		}
		
		
//echo "1";		
        if($strExtension=="pdf"){ $strCopyExt="pdf";}else{$strCopyExt="jpg"; }
//echo "2 $strFromPage ";
		//send admin an email on upload
		if($strFromPage=="receipts"){
		
			//get crypto rate
			$intCryptoRate = funct_Billing_GetRate();
			
			if( (int)$first_receipt_upload_time==0 )
			{
				$strSQL2 = " first_receipt_upload_time= NOW(), rate_at_time_of_first_receipt_upload=$intCryptoRate,";
			}
			
			if($intOrderID){ //RECEIPT UPLOAD has orderid's
				
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
				$reason_for_purchase=				$row["reason_for_purchase"];
				$type=								$row["type"];
				
				//update order table
				$query="UPDATE " . TBL_ORDERS . " SET ".$strSQL2.
				" no_receipts_uploaded= no_receipts_uploaded + 1 , ".
				" last_receipt_upload_time=NOW(), ".
				" rate_at_time_of_last_receipt_upload=$intCryptoRate, ".
				" status_id=3, ".
				" status_text='Waiting for deposit to clear.' ".
				" WHERE orderid=".$intOrderID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query);
				
				if($intUserID){
					$query="UPDATE " . TBL_TRANSACTIONS . " SET ".	
					" status=3, ".
					" status_msg='Waiting for deposit to clear.' ".
					" WHERE order_id=".$intOrderID." AND user_id=".$intUserID ;
					//echo "SQL STMNT = " . $query .  "<br>";
					mysqli_query($DB_LINK, $query);
				}
			}
			
			//copy reciept to reciept folder -- old
			//$strUploadSrc = PATH_RECEIPTS.$intOrderID.".".$strCopyExt ;
			//echo "uploadedfile=".$uploaded_file." receiptkyc=".$strReceiptKYC."<br>";
			//copy($uploaded_file, __ROOT__.$strUploadSrc); //upload another copy with the orderid as a jpg
			

			
			
			//email the admin about new receipt upload
			$strSubject = "Receipt: Order ".$intOrderID." $".number_format($intAmtUSD,2)." User ".$intUserID." ".$strFirstName." ".$strLastName." ".$type;
			$strBody ="File Uploaded: ".WEBSITEFULLURL.$strUploadSrc." \n"
					 ."Order Confirmation: ".WEBSITEFULLURL.PAGE_RECEIPT."?c=".$orderCode."\n"
					 ."Reason: $reason_for_purchase\n"
					 ."Order Details: ".WEBSITEFULLURL."/cp/orders_details.php?id=$intOrderID\n"
					 ."Method: $type\n"
                     ."Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=".$intUserID." \n";
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
			$query="UPDATE " . TBL_ORDERS . " SET status_id=$status_id, status_text='" . $status_name . "' , no_receipts_uploaded=no_receipts_uploaded + 1 WHERE orderid=" . $intOrderID;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());

		}//end if receipt
		
//echo "3";		
		/* */
		
		if($strFromPage=="id"){ //send email to admin
		
			//$strUploadSrc = PATH_KYC.$intUserID.".".$strCopyExt ;
			copy($uploaded_file, __ROOT__.$strUploadSrc); //copy pic to recipets folder. latest overwrites newest
			//echo "uploadedfile=".$uploaded_file." copied=".$strUploadSrc."<br>";

			//email admin
			$strSubject = "ID Upload ".$intUserID." ".$strFirstName." ".$strLastName." ".$Email ;
			$strBody ="ID File Uploaded:: ".WEBSITEFULLURL.$strUploadSrc." \n".
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