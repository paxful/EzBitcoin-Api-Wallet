<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;

$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intUnArchivedCount = 0 ;


//loop through member table and for each user
 	$query="SELECT * ".
	" FROM ".TBL_USERS." ".
	" WHERE id>0 ".
	" ORDER BY id  " ;
	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query);
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	while($row = mysqli_fetch_assoc($rs)){
		$intTotalCount=			$intTotalCount + 1 ;	
	    $intUserID=				$row["id"];
		$strWalletAddress=		$row["wallet_btc"];
		$strFirstName=			$row["first_name"];
		$strLastName=			$row["last_name"];
		//echo " $intUserID $strFirstName $strLastName $strWalletAddress - ";

	
		//see if they have any send or receive transactions by querying transactions table
		$strWhere1 = " AND ( user_id = $intUserID OR user_id_sentto= $intUserID ) " ;
		$strWhereWallets = " AND walletaddress_sentto= '".$strWalletAddress."' OR walletaddress_from= '".$strWalletAddress."' " ;
		$query="SELECT * FROM ".TBL_TRANSACTIONS." WHERE status=1 $strWhere1 " ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs2 = mysqli_query($DB_LINK, $query); $NumTransactions = mysqli_num_rows($rs2);
		if(mysqli_num_rows($rs2)>0){ $row2=mysqli_fetch_array($rs2);
			$intFoundCount=$intFoundCount+1;
			$strWalletAddressTo=		$row2["walletaddress_sentto"]; 				//important
			$strWalletAddressFrom=		$row2["walletaddress_from"]; 				//important
			
			echo " $intUserID $strFirstName $strLastName $strWalletAddress - ";

			echo " $NumTransactions transactions found - ";
			
			//if trans actions found then unarchive their address
			echo " <strong>unarchived</strong> ";
			$intUnArchivedCount = $intUnArchivedCount + 1 ;
			if($strLiveFlag){ 
				$strResponse = funct_Billing_UnArchiveAddress($strWalletAddress); 
			}
			if($strResponse==$strWalletAddress){ //if archived worked then it returns back the address that was archived
				
				echo " <strong>..DONE!</strong> ";
			}
			
			//update their wallet_receive flag to 1
			$query="UPDATE " . TBL_USERS . " SET wallet_receive_on=1 WHERE id=".$intUserID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			if($strLiveFlag){ 
				mysqli_query($DB_LINK, $query); 
			}
	
			echo "<br>";
			
		}//end if transactions found
		
		
		
	}//end while
	
echo "<br><br> <strong>TOTAL=$intTotalCount - FOUND with transactions=$intFoundCount  - UnArchived=$intUnArchivedCount </strong>";

/* */
?>