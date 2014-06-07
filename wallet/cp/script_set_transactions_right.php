<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;
$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intArchivedCount = 0;

	//check database to see if member is in the database 
	$query="SELECT * FROM ".TBL_USERS." WHERE ".
	"    balance_btc > 0 " ;
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
		$strName = $strNameFirst." ".$strNameLast;
		$intBalanceBTC=				$row["balance_btc"]; 		//
	
			//if member is is found
		//make a new transaction TBL_TRANSACTIONS
		
		
		$intFoundCount = $intFoundCount + 1 ;
		echo " <strong>$strNameFirst $strNameLast</strong> " ;
		echo "$strWallet_addy -  $strEmail ";
		echo " - making new transaction...";
		if($strLiveFlag){ 

			$balance_prev = $intBalanceBTC;
			$balance_curr = $intBalanceBTC;
			$intTime = time();
			$strWalletLocation = ""; //either blockchain.info OR coincafe.co
			$query = "INSERT INTO ".TBL_TRANSACTIONS.
			" ( status,	status_msg,			balance_prev,	balance_curr,	user_id, 	sender_name,	sender_email,	datetime_created,	datetime ) VALUES ".
			" ( 24, 	'accounting reset',	'$balance_prev','$balance_curr','$intUserID','$strName',		'$strEmail',	$intTime,			NOW() ) " ;
			//echo "SQL STMNT = " . $query .  "<br>";
			if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."insert order sql = " . $query ." \n <br>";}
			mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), "Database insert internal receiver transaction Error. Admin has been informed $strError_send " , "$strError_send \n SQL statement failed - $query ", $strERRORPage)  ;
			//!ADD CATCH ERRORS
			$intNewID = mysqli_insert_id($DB_LINK);
			
			if($intTotalCount==1){ $intVirginTransactionID = $intNewID ; }
						
			echo "<strong>DONE transactionid: $intNewID</strong> $strResponse "; 

			
		}else{echo "not live $strResponse "; }
		echo "<br>";

	}//while loop


	echo " ... ".$strResponse1." ".$strResponse." <br>" ;


echo "<br><br> <strong>TOTAL=$intTotalCount - FOUND=$intFoundCount  - New Transactions=$intArchivedCount </strong><br><br>";
echo "virgin seed transaction id is: $intVirginTransactionID ";

/* */
?>