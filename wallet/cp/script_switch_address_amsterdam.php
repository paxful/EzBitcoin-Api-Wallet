<?php
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

//SWITCH OVER ADDRESSES TO AMSTERDAM!
$strLiveFlag = 1;
$intTotalCount = 0;
$intFoundCount = 0;
$intNewAddressCount = 0;
$intArchivedCount = 0;

//loop through member table for each user
 	$query = "SELECT * ".
	" FROM ".TBL_USERS." ".
	" WHERE wallet_location='blockchain.info' AND wallet_address_cc='' ".   //wallet_address_cc='' ".  balance_btc=0 AND wallet_btc<>'' AND
	" ORDER BY id LIMIT 0,500 " ;
	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query);
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	while($row = mysqli_fetch_assoc($rs)){
		$intTotalCount=			$intTotalCount + 1 ;	
	    $intUserID=				$row["id"];
		$strWalletAddressOLD=	$row["wallet_btc"]; //old blockchin.info address
		$strWalletAddress_CC=	$row["wallet_address_cc"]; //amsterdam address
		$strEmail=				funct_ScrubVars($row["email"]); //
		$strFirstName=			funct_ScrubVars($row["first_name"]); //
		$strLastName=			funct_ScrubVars($row["last_name"]); //
		//echo " $intUserID $strFirstName $strLastName $strWalletAddress - ";
		//$strEmail="techzombie432@gmail.com";
		
		
		/*
		//see if they have any send or receive transactions by querying transactions table
		$strWhere1 = " AND ( user_id = $intUserID OR user_id_sentto= $intUserID ) " ;
		//$strWhereWallets = " AND walletaddress_sentto= '".$strWalletAddress."' OR walletaddress_from= '".$strWalletAddress."' " ;
		$query="SELECT * FROM ".TBL_TRANSACTIONS." WHERE status=1 $strWhere1 " ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs2 = mysqli_query($DB_LINK, $query); $NumTransactions = mysqli_num_rows($rs2);
		if(mysqli_num_rows($rs2)<1){ $row2=mysqli_fetch_array($rs2);
			$intFoundCount=$intFoundCount+1;
			//$strWalletAddressTo=			$row2["walletaddress_sentto"]; 				//important
			//$strWalletAddressFrom=		$row2["walletaddress_from"]; 				//important
			
			echo " $intUserID $strFirstName $strLastName $strEmail $strWalletAddress - ";
			echo " $NumTransactions transactions  - ";
		*/
			
			//if no new address on amsterdam then make them a new one
			//if(!$strWalletAddress_CC){
				echo " <strong>making a new address ... </strong> ";
				if($strLiveFlag){
					$strResponse = funct_MakeWalletAddressUpdate($intUserID,"amsterdam");
				}else{
					$strResponse = "test";
				}
			//}
			
			if($strResponse){ //if worked then it returns back the address that was made
				$intNewAddressCount = $intNewAddressCount + 1 ;
				$strWalletAddress= $strResponse ;
				echo " <strong> ..Created! ( $strWalletAddress )</strong> ";
				
				/*
				//update their wallet_receive flag to 1  // , wallet_btc='$strWalletAddress'
				$query="UPDATE " . TBL_USERS . " SET wallet_receive_on=1, wallet_address_cc='$strWalletAddress' , wallet_location = 'amsterdam' WHERE id=".$intUserID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				if($strLiveFlag){ 
					mysqli_query($DB_LINK, $query); 
				}
				*/
				
				if($strWalletAddressOLD){
					//archive old address
					$intArchivedCount = $intArchivedCount + 1 ;
					if($strLiveFlag){ $strResponse = funct_Billing_ArchiveAddress($strWalletAddressOLD); }
					if($strResponse==$strWalletAddress){ //if archived worked then it returns back the address that was archived
						echo " <strong>..Archived!</strong> ";
					}
				}
				
				if($strLiveFlag){ //send email
					$strSubject = "Your CoinCafe Bitcoin Address has been Updated! ".$FormRegFirstName." ".$FormRegLastName;
					$strBody = "Thank you for your continued support of Coin Cafe! We've had a tremendous amount of user growth this quarter and are extremely proud to say that we have the only iPhone compatible bitcoin wallet in the world!\n\n".
					"We have been busy coding new features for the site, and to improve the speed and security of transactions, we have upgraded our bitcoin server, which necessitated a change to your Bitcoin Receiving Address. \n\n".
					"Your new Bitcoin Address is $strWalletAddress \n\n Please give it to anyone wanting to send you bitcoin. Your old address will soon be phased out.\n\n".
					"You may verify this new address by logging into your Coin Cafe account. Thanks kindly for your support and for using Coin Cafe for all your bitcoin needs!\n\n";
					funct_Mail_simple($strEmail,$strSubject,$strBody,'',SUPPORT_EMAIL);
				}
				echo ".. email sentto $strEmail ";
			}
			
			echo "<br>";
			
		//}//end if transactions found
	}//end while
	
echo "<br><br> <strong>TOTAL=$intTotalCount - Address Made $intNewAddressCount - Archived=$intArchivedCount </strong>";

/* */
?>