<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;
//$strWriteFileFlag =1;

$file = __ROOT__.PATH_TEMP.'listaddresses.json' ;
$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intArchivedCount = 0;

if($strWriteFileFlag){ //production get LIVE list

	//get a list of all addresses as a JSOn object via RPC
	$strJSONobj = funct_Billing_JSONRPC_ListAccounts(0);

	//write json to text file
    $file_obj = fopen($file,'w+'); 
    
	foreach($strJSONobj as $address=>$balance) { 
		$strAddress = trim($address);
		$strLine = $strLine." \n ".$strAddress;
	}
	fwrite($file_obj, $strLine );
	fclose($file_obj);
    
}

/* */
$handle = fopen($file, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {

		//echo $address." - ".$balance."<br>"; 
		$strAddress = trim($line);
		if(!$strAddress){ continue; } //no address then skip
		$intTotalCount = $intTotalCount + 1 ;
		
		echo $strAddress." - " ;		
		//check database to see if member is in the database 
		$query="SELECT * FROM ".TBL_USERS." WHERE wallet_btc= '".$strAddress."' " ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query);
		if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
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
			
			echo "<strong>FOUND</strong> -> $strEmail " ;
			//if member is is found
			$intFoundCount = $intFoundCount + 1 ;
			
			//update paypalcolumn
			$query="UPDATE " . TBL_USERS . " SET paypalemail='bcyes' WHERE id=".$intUserID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			if($strLiveFlag){
				mysqli_query($DB_LINK, $query) or die(mysqli_error()); 
				echo "<strong>UPDATED<strong>  ";
			}else{ echo " test not live ";}
			
		}
			
		echo " <br>" ;
		
	}

}//end for each loop through array

echo "<br><br> <strong>TOTAL=$intTotalCount - FOUND=$intFoundCount - KEPT=$intKeptCount - Archived=$intArchivedCount </strong>";

/* */
?>