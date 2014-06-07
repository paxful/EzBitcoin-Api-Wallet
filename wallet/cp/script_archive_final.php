<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

//archive addresses
$file = '/cp/addresses.txt' ;
$strGetFreshList = 1 ;
$strLiveFlag = 0 ;
$intTotalCount = 0;
$intFoundCount = 0;
$intArchivedCount = 0;

if($strGetFreshList){
	//get a list of all addresses as a JSOn object via RPC
	//$strJSONobj = funct_Billing_JSONRPC_ListAccounts(0);
	$decodedJSON = json_decode($strJSONobj, TRUE);
	var_dump($decodedJSON);
	//print_r($decodedJSON);
	foreach($strJSONobj as $address=>$balance) { 
		$strAddress = trim($address);
		$strLine = $strLine." \n ".$strAddress;
	}
	$file_obj = fopen(__ROOT__.$file,'w+');
    fwrite($file_obj, $strLine ); fclose($file_obj); //write to file
    echo " <a href='".$file."'>fresh active addresses file</a><br><br>";
}


//get all active addresses into a variable from text file
//$strAllAddresses = file_get_contents(__ROOT__.$file);
$json = file_get_contents(__ROOT__.$file,0,null,null);
$json_output = json_decode($json,true);
//print_r($json);
foreach($json as $object){
    echo " -".$object[0]." \n";
    //print "{$object->title}\n";
}


echo "all addresses: <br>".$strAllAddresses."<br><br>" ; 

echo "<h1>Archiving Addresses from all active</h1>";


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
     lastlogin ASC LIMIT 0,10000 ";
	
	//echo "SQL STMNT = " . $query .  "<br><br>";
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
		
		$strFoundInTextDB = strpos($strAllAddresses,$strWallet_addy) ;
		
		if ($strFoundInTextDB == true) {	
		//if address IS  in the text file then...
			echo "found ($strFoundInTextDB) ";
			$intFoundCount = $intFoundCount + 1 ;
			echo "<strong>$strNameFirst $strNameLast</strong> " ;
			echo "$strWallet_addy - archiving...";
		
			//unarchive
			if($strLiveFlag){ 
				$strResponse = funct_Billing_ArchiveAddress($strWallet_addy); 
							
				if($strResponse==$strWallet_addy){
					echo "<strong>DONE</strong> $strResponse "; 
					$intArchivedCount = $intArchivedCount + 1 ;
				}else{
					echo "error $strResponse ";
				}
				
			}else{ 
				echo "not live $strResponse "; 
			}			
			
			echo "<br>";
			
		}//end if member is is found text file,db
		
	}//while loop

	echo " ... ".$strResponse1." ".$strResponse." <br>" ;


echo "<br><br> <strong>TOTAL=$intTotalCount - FOUND=$intFoundCount - Archived=$intArchivedCount </strong>";

/* */
?>