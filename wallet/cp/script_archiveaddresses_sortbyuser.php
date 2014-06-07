<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;
$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intArchivedCount = 0;

//get all active addresses into a variable from text file
$file = __ROOT__.PATH_TEMP.'listaddresses.json' ;



	//check database 
	$query=" SELECT
     tbl_member.id,
     tbl_transactions.user_id,
     tbl_transactions.transaction_id,
     tbl_member.id_code,
     tbl_member.wallet_btc
FROM
     tbl_transactions RIGHT OUTER JOIN tbl_member ON tbl_transactions.user_id = tbl_member.id
WHERE
     balance_btc = 0
 AND wallet_btc <> ''
 AND first_name = ''
     and NOT EXISTS ( SELECT null FROM tbl_transactions WHERE tbl_member.id = tbl_transactions.user_id )
ORDER BY
     lastlogin ASC LIMIT 3000,4000 ";
	
	echo "SQL STMNT = " . $query .  "<br><br>";
	$rs = mysqli_query($DB_LINK, $query);
	//if(mysqli_num_rows($rs)>0){ 
	while( $row = mysqli_fetch_assoc($rs) ){
		$intTotalCount = $intTotalCount + 1 ;
		$intUserID=					$row["id"]; 				//important
		$strEmail=					$row["email"]; 				//important
		$strPhone=					$row["cellphone"]; 			//important
		$strNameFirst=				$row["first_name"]; 		//important
		$strNameLast=				$row["last_name"]; 			//important
		$intBalance=				$row["balance"]; 			//important
		$intBalanceBTC=				$row["balance_btc"]; 		//
		$intSendLocked=				$row["sendlocked"];			//
		$intEmailConfirmed=			$row["verification_email"];	//
		$strWallet_Receive_Show=	$row["wallet_receive_on"];	//
		$strWallet_addy=			$row["wallet_btc"];			//
		
		//if member is is found
		$intFoundCount = $intFoundCount + 1 ;
		echo " <strong>$strNameFirst $strNameLast</strong> " ;
		echo "$strWallet_addy -  $strEmail ";
		echo " - archiving...";
		if($strLiveFlag){ 
			$strResponse = funct_Billing_ArchiveAddress($strWallet_addy); 
						
			if($strResponse==$strWallet_addy){
				echo "<strong>DONE</strong> $strResponse "; 
				$intArchivedCount = $intArchivedCount + 1 ;
			}else{
				echo "error $strResponse ";
			}
			
		}else{ echo "not live $strResponse "; }
		echo "<br>";

	}//while loop


	echo " ... ".$strResponse1." ".$strResponse." <br>" ;


echo "<br><br> <strong>TOTAL=$intTotalCount - FOUND=$intFoundCount - KEPT=$intKeptCount - Archived=$intArchivedCount </strong>";

/* */
?>