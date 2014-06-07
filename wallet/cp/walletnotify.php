<?php 
/*
// This page gets the walletnotify alert from /home/api/walletnotify.sh bash script
It then looks up the transaction info, gets the address to, checke if it matches our database
then it gets the info for the matching user and calls /merchant/?do=callback

*/

ob_start(); //so we can redirect even after headers are sent

include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

error_reporting(E_ALL & ~E_NOTICE);

//$strOutPutType= "json"; //text json

if(RETURN_OUTPUTTYPE=="json"){
	header('Content-Type: application/json'); //spit out JSON
}

//common varibles and connection strings used in this file
//$DB_LINK for database
//JSONRPC_CONNECTIONSTRING_CC for bitcoind RPC server
$tbl_Users = 			TBL_USERS;
$tbl_Addresses = 		TBL_WALLET_ADDRESSES;
$tbl_Transactions = 	TBL_TRANSACTIONS;
$tbl_Logs = 			TBL_LOGS;

$strIPaddress = 		$_SERVER['REMOTE_ADDR']; //will always be local
//$strReferrer = 			$_SERVER['HTTP_REFERER'];
$strQueryString = 		$_SERVER['QUERY_STRING'];
$strDebugHTTP = 		"$strIPaddress \n $strQueryString \n $strReferrer \n ";
$strERRORPage = 		"walletnotify";
//echo "1";

//Get Common QueryString Variables security against mysql injection and xss attacks
$strDebug = 			funct_ScrubVars($_GET['debug']);
$strLocal = 			funct_ScrubVars($_GET['local']); //n00nez indicates local call from walletnotify
$strCryptoType = 		funct_ScrubVars($_GET['cryptotype']);
if(!$strCryptoType){$strCryptoType="btc";} //default is bitcoin

//get transaction info by id
$strTransaction = funct_ScrubVars($_GET['txid']); 
	//test //$strTransaction="a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1";


	if(!$strTransaction){ 
		$strReturnError = "no transaction id provided...";
		funct_die_and_Report($strReturnError, "", $strDebugHTTP);
	}

	$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
	try{
	  	$trxinfo = $bitcoin->gettransaction($strTransaction);
	  	$strTransactionType = "get";

	  	//bind values to variables
		$strTransactionID = 	$trxinfo["txid"] ;
		$intAmount = 			$trxinfo["amount"] ; //this is a decimal.. NOT satoshi
		$intConfirmations = 	$trxinfo["confirmations"] ;
		$intTime = 				$trxinfo["time"] ;
		$strAccountName = 		$trxinfo["details"][0]["account"] ;
		$strAddress = 			$trxinfo["details"][0]["address"] ;
		$strReturnError = $strTransactionID ;
	  
	} catch(Exception $e){

		$strReturnError = $e->getMessage(); 
		//CATCH ERROR AND EMAIL
		$strERRORUserMessage="Getting Transaction Info Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
		funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
	}

	if(!$strAddress){ echo "addressnotfound"; die; } //this demands that the address be in our database table for a call back to work

	//look for user associated with address in database
		$query="SELECT * FROM ".$tbl_Addresses." WHERE address='".$strAddress."'" ;
		if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
		$strERRORUserMessage="Looking up address record failed. Admin has been informed "; $strERRORMessageAdmin=" \n SQL statement failed - $query "; 
		$rs = mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		//CATCH ERROR AND EMAIL
		if(mysqli_num_rows($rs)>0){
			$row=mysqli_fetch_array($rs) ;
			$intUserID = 		$row["user_id"];
	
			//if found then call the merchant api script with method callback with username and password etc..
			$query="SELECT * FROM ".$tbl_Users." WHERE user_id='".$intUserID."'" ;
			if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
			$strERRORUserMessage="Looking up user record failed. Admin has been informed "; $strERRORMessageAdmin=" \n SQL statement failed - $query "; 
			$rs = mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
			//CATCH ERROR AND EMAIL
			$row=mysqli_fetch_array($rs) ;
			$intUserID=			$row["user_id"];
			$strUserIDcode=		$row["user_loginid"];
			$strUserName=		$row["user_name"];
			$strPassword_DB=	$row["user_password"];
			$strEmail=			$row["user_email"];
			$strCallbackURL=	$row["callbackurl"];
			$intLastLogin=		$row["date_updated"];
			$strSecret=			$row["user_secret"];			
			
			$strAuth = "&loginname=$strUserIDcode&password=$strPassword_DB";
			if(!$strCallbackURL){$strCallbackURL="http://getcoincafe.com/mods/processorder.php";}
			
			
			//call processorder script
			$json_url = $strCallbackURL."?do=callback&txid=".$strTransactionID.$strAuth ;
			//echo "<br>url: $json_url";
			$json_data = file_get_contents($json_url);
			//echo $json_data ;
			$json_feed = json_decode($json_data);
			//echo $json_data ;
			
			$strReturnError = $strCallbackResponse ;
		}




ob_flush(); //so we can redirect even after headers are sent

?>