<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    
//include $_SERVER['DOCUMENT_ROOT']."/inc/funct_jsonrpc.php"; //calls server.php within


error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 0 ;

$file ='/cp/listaddresses.json' ;

//get a list of all addresses as a JSOn object via RPC
//$strJSONobj = funct_Billing_JSONRPC_ListAccounts(0);

//write json to text file
//$file_obj = fopen(__ROOT__.$file,'w+'); 

//$decodedJSON = json_decode($strJSONobj, TRUE);
//var_dump($strJSONobj);
//print_r($decodedJSON);
//echo " <a href='".$file."' >file active addresses</a><br><br>";

/* 
foreach($strJSONobj as $address=>$balance) { 
	$strAddress = trim($address);
	$strLine = $strLine." \n ".$strAddress;
}
//write to file
fwrite($file_obj, $strLine ); fclose($file_obj);
//echo $strLine ;
 */

//get text file and put it ito an object
$strAllAddresses = file_get_contents(__ROOT__.$file);
//echo "addresses<br>".$strAllAddresses."<br><br>" ; 

$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intArchivedCount = 0;


	//check database to see if member is in the database 
	$query="SELECT * FROM ".TBL_USERS." WHERE ".
	" wallet_btc <> '' "; /*
 AND wallet_receive_on = 0
 AND postal IS null
 AND verification_email = 0
 AND verification_id = 0
 AND id_code = ''
 AND address = '' ".
	" ORDER BY lastlogin DESC " ; */
	echo "SQL STMNT = " . $query .  "<br><br>";
	$rs = mysqli_query($DB_LINK, $query);
	//if(mysqli_num_rows($rs)>0){ 
	while( $row = mysqli_fetch_assoc($rs) ){
	
	//loop through all member records and see if address is in text file
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
		$strWallet_addy=			$row["wallet_btc"];	//

		
		echo " <strong>$strNameFirst $strNameLast</strong> " ;
		echo "$strWallet_addy -  $strEmail ";
		
		if (strpos($strAllAddresses,$strWallet_addy) == false) {	
		//if address is not in the text file then...

			echo " - purging...";
		
			//update address with ''
			$query="UPDATE " . TBL_USERS . " SET wallet_btc='', bank_account='$strWallet_addy' paypalemail='pariah address GONE!' WHERE id=".$intUserID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			if($strLiveFlag){
				mysqli_query($DB_LINK, $query) or die(mysqli_error()); 
			}else{ echo " test not live ";}
			echo "GONE!!!";
			
			//delete qrcode pictures	
			//echo "QRIMG DELETE!!"
			
			$intFoundCount = $intFoundCount + 1 ;
		
		}else{ echo "KEPT.."; $intKeptCount = $intKeptCount + 1 ;  }
		echo "<br>";

	}//while loop


	echo " ... ".$strResponse1." ".$strResponse." <br>" ;


echo "<br><br> <strong>TOTAL=$intTotalCount - GONE=$intFoundCount - KEPT=$intKeptCount - Archived=$intArchivedCount </strong>";



?>