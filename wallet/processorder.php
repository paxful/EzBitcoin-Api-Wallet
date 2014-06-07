<?php 
//error_reporting(E_ERROR | E_PARSE);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//get values from query string 
$real_secret = 				funct_ScrubVars($_GET['secret']); //this I entered into the blockchain wallet form
$transaction_hash = 		funct_ScrubVars($_GET['transaction_hash']); //The transaction hash.
$input_address = 			funct_ScrubVars($_GET['input_address']); //The bitcoin address that received the transaction
$input_address2 = 			funct_ScrubVars($_GET['address']); //The bitcoin address that received the transaction.
//if(!$input_address){$input_address=$input_address2;}//not sure which it sends..
$value_in_satoshi = 		funct_ScrubVars($_GET['value']);

//coincafe.info gives confirmations & userid (label2 in amsterdam)
$confirmations = 			funct_ScrubVars($_GET['confirms']); //The bitcoin address that received the transaction
$intUserID = 				funct_ScrubVars($_GET['userid']);

//test this page with string
//http://getcoincafe.com/mods/processorder.php?secret=n00n3z&transaction_hash=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&input_address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&value=200000&confirms=3
//http://coincafe.us/mods/processorder.php?secret=n00n3z&transaction_hash=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&input_address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&value=00000001&confirms=3&from=amsterdam&userid=
//http://local.coincafe/mods/processorder.php?secret=n00n3z&transaction_hash=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&input_address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&value=00000001&confirms=3&from=amsterdam&userid=



if($value_in_satoshi<1){$value_in_satoshi=0;}

$strURL = $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'];
$strURL_referrer = $_SERVER['HTTP_REFERER'];
$intTime = time();
$strIPAddress = $_SERVER['REMOTE_ADDR'];
$strIPAddress2 = $_SERVER['HTTP_X_FORWARDED_FOR'];

//get info from database for member with hash get their id
$query="SELECT * FROM " . TBL_USERS . " WHERE wallet_btc= '".$input_address."' " ;
//echo "SQL STMNT = " . $query .  "<br>";
if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."select user sql = " . $query .  " \n <br>";}
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	$intUserID=					$row["id"]; 				//important
	$strEmail=					$row["email"]; 				//important
	$strPhone=					$row["cellphone"]; 			//important
	$strName=					$row["name"]; 				//important
	$strNameFirst=				$row["first_name"]; 				//important
	$strNameLast=				$row["last_name"]; 				//important
	$strName= $strNameFirst." ".$strNameLast ;
	
}else{ //not found...... so perhaps the address was updated to a new one
	
	//if the userid was passed from the callback check if the user exists
	if($intUserID){
		$query="SELECT * FROM " . TBL_USERS . " WHERE id= '".$intUserID."' " ;
		//echo "SQL STMNT = " . $query .  "<br>";
		if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."select userid sql = " . $query .  " \n <br>";}
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		if(mysqli_num_rows($rs)>0){  $row=mysqli_fetch_array($rs);
			$strEmail=					$row["email"]; 				
			$strPhone=					$row["cellphone"]; 			
			$strName=					$row["name"]; 				
			$strNameFirst=				$row["first_name"]; 		
			$strNameLast=				$row["last_name"];
			$strAddress=				$row["wallet_btc"]; //old blockchain.info
			$strAddressAmsterdam=		$row["wallet_address_cc"]; //amsterdam			
		
			//if the user exists but the address doesn't match then... inform admin of orphaned address
			$strSubject = "Orphaned Address Alert";
			$strBody = "($intUserID) $strNameFirst $strNameLast $strEmail  \n ".
			"Address: ".$strAddress." \n ".
			"Address Amsterdam: ".$strAddressAmsterdam." \n ".
			"address:$input_address - amount:$value_in_satoshi - confirms:$confirmations - txid:$transaction_hash " .WEBSITEFULLURLHTTPS.PAGE_SETTINGS." \n " ;
			funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',SUPPORT_EMAIL);
			
			//should we do anything else here... like set their address back to the old one?
			//processorders2 checks for wallet_btc and wallet_address_cc
			//if we don't change either one of these to the input address then process_order2.php will NOT credit them
				//unless we alter processorder2.php to also check for the userid
				
				//update wallet_btc to the orphaned address NO instead alter processorder2.php to check userid

				
				
		
		}
	}else{
		$intUserID=0;
	}
}


if(!$intUserID){$intUserID=0;}

//insert into callbacks table for debugging
$query = "INSERT INTO ".TBL_ORDERS_CALLBACKS.
" ( url,		url_referrer,		value_satoshi, 		address,			input_address,		hash_transaction, 	userid, 	email, 		phone,		name, 		ipaddress,		ipaddress2,			date_added,	datetime ) VALUES ".
" ( '$strURL', 	'$strURL_referrer',	$value_in_satoshi, 	'$input_address2',	'$input_address',	'$transaction_hash',$intUserID, '$strEmail','$strPhone','$strName', '$strIPAddress','$strIPAddress2',	$intTime,	NOW() ) " ;
//echo "SQLSTMNT = " . $query .  "<br>";
mysqli_query($DB_LINK, $query);// or die(mysqli_error());
$intNewCallBackID = mysqli_insert_id($DB_LINK); 

//#######################################################################
//call process script
$URL = WEBSITEFULLURL.CODE_PROCESSORDER."?".$_SERVER['QUERY_STRING']."callbackid=".$intNewCallBackID ;
$strError = file_get_contents($URL);
//#######################################################################


if($intNewCallBackID AND $strError){ 
	$query="UPDATE " .TBL_ORDERS_CALLBACKS. " SET ".
	" errorcode='$strError' ".
	" WHERE callback_id=".$intNewCallBackID ;
	//if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update order sql = " . $query .  " \n <br>";}
	mysqli_query($DB_LINK, $query);
}

//blockchain needs this but amsterdam doesn't care as it doesn't keep calling until it gets this response.
echo "*ok*"; //blockchain callback script needs this

?>