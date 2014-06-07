<?php 
/*
// This page processess all api calls send via GET requests
createaddress, sendtoaddress, sendfrom, getbalance of address
getbalance of wallet/user ?
*/

ob_start(); //so we can redirect even after headers are sent

require "server.php"; //for server specific data $strServer value dev, production etc.. doesn't change
require "constants.php"; //calls server.php within .. updated often
require "functStrings.php"; //holds all custom string formatting functions
include $_SERVER['DOCUMENT_ROOT']."/inc/jsonRPCClient.php"; //connect to RPC server as a client
include $_SERVER['DOCUMENT_ROOT']."/inc/functmail.php"; //holds all email functions * use as needed



error_reporting(0);//turn off all reporting
//if(DEBUGMODE>0){
	//ini_set('display_errors',1);
	//error_reporting(E_ERROR | E_PARSE);
//}

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

$strIPaddress = 		$_SERVER['REMOTE_ADDR'];
$strReferrer = 			$_SERVER['HTTP_REFERER'];
$strQueryString = 		$_SERVER['QUERY_STRING'];
$strDebugHTTP = 		"$strIPaddress \n $strQueryString \n $strReferrer \n ";
$strERRORPage = 		"merchantapi";
//echo "1";

//Get Common QueryString Variables security against mysql injection and xss attacks
$strDebug = 			funct_GetandCleanVariables($_GET['debug']);
$strLocal = 			funct_GetandCleanVariables($_GET['local']); //n00nez indicates local call from walletnotify.sh
if($strIPaddress=="10.68.9.140" || $strIPaddress=="127.0.0.1" || $strIPaddress=="208.105.10.78" ){$strLocal=1;}else{$strLocal=0;}


$strMethod = 			funct_GetandCleanVariables($_GET['do']);
$strLoginName = 		funct_GetandCleanVariables($_GET['loginname']);
$strLoginPassword = 	funct_GetandCleanVariables($_GET['password']);
$strLoginPassword2 = 	funct_GetandCleanVariables($_GET['second_password']);
$strCryptoType = 		funct_GetandCleanVariables($_GET['cryptotype']);
if(!$strCryptoType){$strCryptoType="btc";} //default is bitcoin
//echo "2";
if(!$strMethod){ $strError_Login='nomethod';}
if(!$strLoginName){ $strError_Login='nouser';}
if(!$strLoginPassword){ $strError_Login='nopassword';}
//echo "3";

//!Log All calls
//add login attempts to log table... for flood hacking protection how do we prevent flood attacks ?
$query = "INSERT INTO ".$tbl_Logs.
" ( user_loginid, 	method,			ipaddress,		querystring,		referrer,	 	response, 			date_created ) VALUES ".
" ('$strUserIDcode','$strMethod',	'$strIPaddress','$strQueryString',	'$strReferrer',	'$strError_Login',	NOW() ) " ;
if($strDebug){ echo "Insert into user Table - SQL STMNT = " . $query .  "<br><br>"; }
$strERRORUserMessage="Database insert logs Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
//CATCH ERROR AND EMAIL
$intNewLogID = mysqli_insert_id($DB_LINK);


if(!$strLocal){ //coming from outside then authenticate
	//############################################################################################
	//!Being Authentication of users
	//kill script if no login
	if($strError_Login){ die($strError_Login);}
	
	//authenticate the user by looking into the database
	$query="SELECT * FROM ".$tbl_Users." WHERE user_loginid='".$strLoginName."'" ;
	if($strDebug){ echo "SQL STMNT = " . $query .  "<br><br>"; }
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
	
	if(!$intUserID){$strError_Login="nouser" ;}
	if(PASSWORD_ENCRYPT){
		if(!password_verify($strLoginPassword, $strPassword_DB)){ $strError_Login = "fail"; } //check hashed password match
	}else{
		if($strPassword_DB!=$strLoginPassword){ $strError_Login="fail" ;} //check non hashed password match
	}
	if($strError_Login=="fail"){ $strError_Login_msg= "error: wrong username or password"; }
	if($strError_Login=="nouser"){ $strError_Login_msg= "error: no such user"; }
	
	//echo "p_db: $strPassword_DB - p: $strLoginPassword - error: $strError_Login ";
	
	//update log record
	$query="UPDATE " . $tbl_Logs . " SET ".
	" user_id= '".$intUserID."' , ".
	" response= '".$strError_Login."' ".
	" WHERE log_id=".$intNewLogID ;
	if($strDebug){ echo "SQL STMNT = " . $query .  "<br><br>"; }
	mysqli_query($DB_LINK, $query) or die(mysqli_error($DB_LINK));
	
	if($strError_Login){
		echo $strError_Login_msg ;
		die ;
	}
	//############### End Authentication #################################################
} //end if not local


//! Begin Methods Switch

//echo "strDo= " . $strDo;
switch ($strMethod){



	//!Validate Transaction
	case "getbalance":
	
		$strAccount = funct_GetandCleanVariables($_GET['account']);
		$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
		try{
		  	$strReturnError = $bitcoin->getbalance();
			if($strDebug){ echo nl2br($strReturnError)."<br>";}
		  
		} catch(Exception $e){

			$strReturnError = $e->getMessage(); 
			//CATCH ERROR AND EMAIL
			//$strERRORUserMessage="Getting Transaction Info Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			//funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
		}

		echo $strReturnError;
		
	break;
	



	//!Validate Transaction
	case "validate_transaction":
	
		$strTXID = funct_GetandCleanVariables($_GET['txid']) ; 
		//test //$strTransaction="10c724bdfe52f95b482949101cc1bb3657c9f92d7f61d469a309eacbb6782d24";

		if(!$strTXID){ 
			$strReturnError = "notxid";
			funct_die_and_Report($strReturnError, "", $strDebugHTTP);
		}

		$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
		try{
		  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
		  	//echo $strTransaction ;
		  	//get transaction info as JSON object, only for local transactions
		  	$trxinfo = $bitcoin->gettransaction($strTXID);
		  	
		  	//if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
			//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and 
		  	//use the getrawtransaction call to request information about any transaction 
		  	//$strRawHex = $bitcoin->getrawtransaction($strTransaction);
		  	//$objJSON = $bitcoin->decoderawtransaction($strRawHex);
		  	
		  	$strTransactionType = "get";
		  	//decoderawtransaction 

		  	//bind values to variables
			$strTransactionID = 	$trxinfo["txid"] ;
			$intAmount = 			$trxinfo["amount"] ; //this is a decimal.. NOT satoshi
			$intConfirmations = 	$trxinfo["confirmations"] ;
			$intTime = 				$trxinfo["time"] ;
			$strAccountName = 		$trxinfo["details"][0]["account"] ;
			$strAddress = 			$trxinfo["details"][0]["address"] ;
			$strAddressFrom = 		"" ; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
			$intTime = 				$trxinfo["time"];
			$intTimeReceived = 		$trxinfo["timereceived"];
			$strCategory = 			$trxinfo["details"][0]["category"];
			$strBlockHash = 		$trxinfo["blockhash"];
			$strBlockIndex = 		$trxinfo["blockindex"];
			$strBlockTime = 		$trxinfo["blocktime"];
			
			$new = "Transaction hash: ".$argv[1]
			."\n balance: ".$trxinfo["balance"]
			."\n amount: ".$trxinfo["amount"]
			."\n confirmations: ".$trxinfo["confirmations"]
			."\n blockhash: ".$trxinfo["blockhash"]
			."\n blockindex: ".$trxinfo["blockindex"]
			."\n blocktime: ".$trxinfo["blocktime"]
			."\n txid: ".$trxinfo["txid"]
			."\n time: ".$trxinfo["time"]
			."\n timereceived: ".$trxinfo["timereceived"]
			."\n account: ".$trxinfo["details"][0]["account"]
			."\n address: ".$trxinfo["details"][0]["address"]
			."\n category: ".$trxinfo["details"][0]["category"]
			."\n amount: ".$trxinfo["details"][0]["amount"]
			."\n fee: ".$trxinfo["details"][0]["fee"]  // According to https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_calls_list, fee is returned, but it doesn't seem that way here
			;
			if($strDebug){ echo nl2br($new)."<br>";}

			$strReturnError = $strTransactionID ;
		  
		} catch(Exception $e){

			$strReturnError = $e->getMessage(); 
			//echo $strReturnError ;
			//CATCH ERROR AND EMAIL
			//$strERRORUserMessage="Getting Transaction Info Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			//funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
		}

		if($strReturnError==$strTXID){ 
			echo "good";
		}else{
			
			echo "bad ";// .$strReturnError;
		}
	
	break;
	
	

//!Validate Address
	case "validate_address":
	
		$strAddress = funct_GetandCleanVariables($_GET['address']); 

		//RPC call to the bitcoin server 
        //takes an account name to attach the address to
        $mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC) ; //our own bitcoind rpc server
		try{
		  	$strReturnError= $mybtc->validateaddress($strAddress) ;
		  	$strWalletAddress = $strReturnError ;
		  	//if( strlen($strWalletAddress)>=27 ){ $strReturnError = "*ok*" ;}
		  	
		} catch(Exception $e){
		  	//echo nl2br($e->getMessage()).'<br />'."\n";
		  	$strReturnError = $e->getMessage() ;
		  	
		  	//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Validate address Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
		  	
		}
        
        //print_r($strReturnError);
        /*
        Array
		(
		    [isvalid] => 1
		    [address] => 1FTJXv8FjqyQ1HUTx7HeLxVaZTnc8E3muW
		    [ismine] => 
		)
		*/
		
		$arrReturn = $strReturnError;
		$intIsValid = $arrReturn["isvalid"] ;
		$address = $arrReturn["address"] ;
		$intIsMine = $arrReturn["ismine"] ;
        //echo "$intIsValid|$address|$intIsMine" ;
       
        
        //if the address is good then return back 1
       	if($intIsValid){ $strReturnError="1";} 
        
        
        //return the address is the bitcoind and the current merchant owns the address
        if($intIsMine AND $address){ 
        
        	$query="SELECT * FROM ".TBL_WALLET_ADDRESSES." WHERE address='".$address."'" ;
			//echo "SQL STMNT = " . $query .  "<br>";
			$strERRORUserMessage="Looking up is mine merchant address failed. Admin has been informed "; $strERRORMessageAdmin=" \n SQL statement failed - $query "; 
			$rs = mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
			//CATCH ERROR AND EMAIL	
			if(mysqli_num_rows($rs)>0){
	        	//if current merchant owns the address then return back the address
	        	$intIsMine=$address ;
        	}

        }
        
		//return error will be wallet address if it works
		if(RETURN_OUTPUTTYPE=="json"){
			//echo $strReturnError ; //json
			echo json_encode(array( 'isvalid'=>"$intIsValid", 'address'=>"$address", 'ismine'=>"$intIsMine" ));
		}else{
			echo "$error|$address|$intIsMine" ; //die;
		}
	
	
	break;



	//!New Address
    //create new address in blockchain
    case "new_address":
    //takes an address OPT to forward funds to, label1 OPT, label2 OPT, label3 OPT
        
        $strAddress = 	funct_GetandCleanVariables($_GET['address']); 
        $strLabel1 = 	funct_GetandCleanVariables($_GET['label']); 
        $strLabel2 = 	funct_GetandCleanVariables($_GET['label2']); 
        $strLabel3 = 	funct_GetandCleanVariables($_GET['label3']); 
        
        //RPC call to the bitcoin server 
        //takes an account name to attach the address to
        $mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC) ; //our own bitcoind rpc server
		//if(!$strAddress){$strAddress='*';}
		$strAccount = $strUserName; //might be depreciated in new system
		try{
		  	$strReturnError= $mybtc->getnewaddress($strAccount) ;
		  	$strWalletAddress = $strReturnError ;
		  	if( strlen($strWalletAddress)>=27 ){ $strReturnError = "*ok*" ;}
		  	
		  	
	        // add to address table
			$query = "INSERT INTO ".$tbl_Addresses.
			" ( user_id,		user_name,		address,			label,			label2,			label3,			crypto_type,		date_created ) VALUES ".
			" (	'$intUserID',	'$strUserName',	'$strWalletAddress','$strLabel1',	'$strLabel2',	'$strLabel3',	'$strCryptoType',	NOW() 	  ) " ;
			//echo "Insert into user Table - SQL STMNT = " . $query .  "<br>";
			$strERRORUserMessage="Database CREATE NEW ADDRESS Error. Admin has been informed \n $strReturnError "; $strERRORMessageAdmin="$strQueryString \n SQL statement failed - $query "; 
			mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
			//CATCH ERROR AND EMAIL  		  	
		  
		} catch(Exception $e){
		  	//echo nl2br($e->getMessage()).'<br />'."\n";
		  	$strReturnError = $e->getMessage() ;
		  	
		  	//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Making new address Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
		  	
		}
        
		//return error will be wallet address if it works
		if(RETURN_OUTPUTTYPE=="json"){
			echo json_encode(array( 'address'=>"$strWalletAddress", 'label'=>"$strLabel1", 'error'=>"$strReturnError" ));
		}else{
			echo $strReturnError ; //die;
		}
		
    break;
    


	
	//!Send Address
	case "sendtoaddress": //sends bitcoin
	//bitcoind sendtoaddress <bitcoinaddress> <amount> [comment] [comment-to]	 
	//<amount> is a real and is rounded to 8 decimal places. 
	//Returns the transaction ID <txid> if successful.
	case "sendfromaddress":
	//bitcoind sendfrom <fromaccount> <tobitcoinaddress> <amount> [minconf=1] [comment] [comment-to]	 
	//<amount> is a real and is rounded to 8 decimal places. 
	//Will send the given amount to the given address, ensuring the account has a valid balance using [minconf] confirmations. 
	//Returns the transaction ID if successful (not in JSON object).

        $strAddress = 	funct_GetandCleanVariables($_GET['address']); 
        $intAmount = 	funct_GetandCleanVariables($_GET['amount']);
        $strFromAccount=funct_GetandCleanVariables($_GET['from']);  
        
        //pass address... is this needed? are address and account the same?
        $strFromAddress=funct_GetandCleanVariables($_GET['addressfrom']);  
        $strComment = 	funct_GetandCleanVariables($_GET['comment']);  
        $strCommentTo = funct_GetandCleanVariables($_GET['commentto']); 
        
        $strLabel1 = 	funct_GetandCleanVariables($_GET['label']); 
        $strLabel2 = 	funct_GetandCleanVariables($_GET['label2']); 
        $strLabel3 = 	funct_GetandCleanVariables($_GET['label3']); 
		
		//set comment to equal to comment
		if(!$strCommentTo AND $strComment) { $strCommentTo = $strComment ;}

		//do we take the amount in satoshi ? 
		//$intAmount = $intAmount / 100000000;

		//Request error: -1 - value is type str, expected real
		//$intAmount = (float)0.000100 ;
		$intAmount = (float)$intAmount ;
		//echo "amt= ".$intAmount."<br>";
		
		//RPC call to the bitcoin server 
        $mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC) ; //our own bitcoind rpc server
		//if(!$strWalletAddressForward){$strWalletAddressForward='*';}
		
		try{
			if($strFrom){ //use send from function
				$strReturnError = $mybtc->sendfrom( $strFromAccount, $strAddress , $intAmount , $strComment , $strCommentTo );
			}else{ //send from any availabe balance
		  		$strReturnError = $mybtc->sendtoaddress( $strAddress , $intAmount , $strComment , $strCommentTo );
		  	}
		  	//return $strReturnError ;
		  	//should return transaction id if it works
		  	if($strReturnError){ 
		  		$strTransactionID = $strReturnError ; 
		  		
				//if it returns back a transactionid then add to the transactions table
				$query = "INSERT INTO ".$tbl_Transactions.
				" ( txid, 				transaction_type,	user_id,	method,		ipaddress,		crypto_amount, debit,		crypto_type,		address_to,		address_from,		account_to, account_from, 		messagetext, 	label,			label2,			label3,			response, 				date_created ) VALUES ".
				" ('$strTransactionID',	'send',				$intUserID,	'$strMethod','$strIPaddress','$intAmount', '$intAmount','$strCryptoType',	'$strAddress',	'$strFromAccount',	'',			'$strFromAccount',	'$strComment', 	'$strLabel1',	'$strLabel2',	'$strLabel3',	'$strError_Login_msg',	NOW() 	  ) " ;
				//echo "Insert into transactions Table - SQL STMNT = " . $query .  "<br>";
				$strERRORUserMessage="Database CREATE NEW TRANSACTION $strMethod Error. Admin has been informed "; $strERRORMessageAdmin="$strQueryString \n SQL statement failed - $query "; 
				mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID)  ;
				//CATCH ERROR AND EMAIL 
				$intNewTransactionID = mysqli_insert_id($DB_LINK);		  		
		  		
		  		$strERRORUserMessage = "*ok*";
		  		$strReturnError = "";
		  	}
		  
		} catch(Exception $e){
		  	//echo nl2br($e->getMessage()).'<br />'."\n";
		  	$strReturnError = $e->getMessage() ;
		  	$strERRORUserMessage = $strReturnError ;
		  	//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Sending Transaction $strMethod Failed. Admin has been informed "; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			//funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage);
		}


		//return error will be wallet address if it works
		if(RETURN_OUTPUTTYPE=="json"){
			echo json_encode(array( 'message'=>"$strERRORUserMessage", 'tx_hash'=>"$strTransactionID", 'error'=>"$strReturnError" ));
		}else{
			echo $strReturnError ; //die;
		}
		
		//$message = $json_feed->message;
		//$txid = $json_feed->tx_hash;
		//$error = $json_feed->error;
		//echo $strReturnError; die;

	break;
	
	
	
	
	
	//!Callback
	case "callback": //sends callback on recieve notifcation
	//gets a transaction hash id
	//calls bitcoind d via RPC to get transaction info 
	//calls a web url specified in the user account
	//called from /home/api/walletnotify.sh
	//sudo curl http://127.0.0.1/merchant/?do=callback&txid=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&local=n00nez&loginname=amsterdam&password=PVLRQWKO4KDLQTX&debug=1

        $strTransaction = funct_GetandCleanVariables($_GET['txid']); 
		//test //$strTransaction="a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1";

		if(!$strTransaction){ 
			$strReturnError = "no transaction id provided...";
			funct_die_and_Report($strReturnError, "", $strDebugHTTP);
		}

		$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
		try{
		  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
		  	//echo $strTransaction ;
		  	//get transaction info as JSON object, only for local transactions
		  	$trxinfo = $bitcoin->gettransaction($strTransaction);
		  	
		  	//if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
			//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and 
		  	//use the getrawtransaction call to request information about any transaction 
		  	//$strRawHex = $bitcoin->getrawtransaction($strTransaction);
		  	//$objJSON = $bitcoin->decoderawtransaction($strRawHex);
		  	
		  	$strTransactionType = "get";
		  	//decoderawtransaction 

		  	//bind values to variables
			$strTransactionID = 	$trxinfo["txid"] ;
			$intAmount = 			$trxinfo["amount"] ; //this is a decimal.. NOT satoshi
			$intConfirmations = 	$trxinfo["confirmations"] ;
			$intTime = 				$trxinfo["time"] ;
			$strAccountName = 		$trxinfo["details"][0]["account"] ;
			$strAddress = 			$trxinfo["details"][0]["address"] ;
			$strAddressFrom = 		"" ; //always blank as there is no way to know where bitcoin comes from UNLESS we do get rawtransaction
			$intTime = 				$trxinfo["time"];
			$intTimeReceived = 		$trxinfo["timereceived"];
			$strCategory = 			$trxinfo["details"][0]["category"];
			$strBlockHash = 		$trxinfo["blockhash"];
			$strBlockIndex = 		$trxinfo["blockindex"];
			$strBlockTime = 		$trxinfo["blocktime"];
			
			$new = "Transaction hash: ".$argv[1]
			."\n balance: ".$trxinfo["balance"]
			."\n amount: ".$trxinfo["amount"]
			."\n confirmations: ".$trxinfo["confirmations"]
			."\n blockhash: ".$trxinfo["blockhash"]
			."\n blockindex: ".$trxinfo["blockindex"]
			."\n blocktime: ".$trxinfo["blocktime"]
			."\n txid: ".$trxinfo["txid"]
			."\n time: ".$trxinfo["time"]
			."\n timereceived: ".$trxinfo["timereceived"]
			."\n account: ".$trxinfo["details"][0]["account"]
			."\n address: ".$trxinfo["details"][0]["address"]
			."\n category: ".$trxinfo["details"][0]["category"]
			."\n amount: ".$trxinfo["details"][0]["amount"]
			."\n fee: ".$trxinfo["details"][0]["fee"]  // According to https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_calls_list, fee is returned, but it doesn't seem that way here
			;
			if($strDebug){ echo nl2br($new)."<br>"; }

			$strReturnError = $strTransactionID ;
		  
		} catch(Exception $e){

			$strReturnError = $e->getMessage(); 
			
			//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Getting Transaction Info Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID);
		}
		
		
		//get info from the address table to imput into the transactions table for external user associations
		//$strAddress
		$query="SELECT * FROM ".$tbl_Addresses." WHERE address='".$strAddress."'" ;
		if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
		$strERRORUserMessage="Looking up address record failed. Admin has been informed "; $strERRORMessageAdmin=" \n SQL statement failed - $query "; 
		$rs = mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		//CATCH ERROR AND EMAIL
		$row=mysqli_fetch_array($rs) ;
		$intUserID=			$row["user_id"];
		$strLabel=			$row["label"];
		$strLabel2=			$row["label2"];
		$strLabel3=			$row["label3"];
		$intAddressBalance= $row["crypto_balance"];
		$intAddressTotalReceived= $row["crypto_totalreceived"];
		$intAddressPreviousBalance= $row["crypto_previousbalance"];
		
		$intNewBalance = $intAddressBalance + $intAmount ; //db=decimal txid amt= decimal
		
		//get balance from bitcoind
		//$intCryptoBitcoinDBalance = $bitcoin->getbalance($strAddress);
		//$intCryptoBitcoinDBalance = "";
		
		//get their userid
		$intUserID = $row["user_id"];
		
		
		
		//if no user is found then then recieve is to an orpahned address
		// script should die here ? 
		//before that create a transaction  and log the recieve address and amount? 
		
		//if userid = 0 then right here the script dies
		if(!$intUserID){$intUserID=1;} //instead we always set it to coincafe
		
		//get their user info
		$query="SELECT * FROM ".$tbl_Users." WHERE user_id='".$intUserID."'" ;
		if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
		$strERRORUserMessage="Looking up user record failed. Admin has been informed "; $strERRORMessageAdmin=" \n SQL statement failed - $query "; 
		$rs = mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		//CATCH ERROR AND EMAIL
		if(mysqli_num_rows($rs)>0){
			$row=mysqli_fetch_array($rs);
			$strUserIDcode=		$row["user_loginid"];
			$strUserName=		$row["user_name"];
			$strPassword_DB=	$row["user_password"];
			$strEmail=			$row["user_email"];
			$strCallbackURL=	$row["callbackurl"];
			$intLastLogin=		$row["date_updated"];
			$strSecret=			$row["user_secret"];
			
			//update the logs table with new userinfo of the user who owns the address
			$query="UPDATE " . $tbl_Logs . " SET ".
			" user_id= '".$intUserID."' ".
			" WHERE log_id=".$intNewLogID ;
			if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
			mysqli_query($DB_LINK, $query) ;//or die(mysqli_error($DB_LINK));
		
		}else{ //user not found so... no callback to send...
			echo "nouser"; die;
		}
			

		//only call transaction process code if we get back a transaction id
		if($strTransactionID){ 
			
			//first check if the record exists
			$query=	"SELECT * FROM " . $tbl_Transactions . " WHERE txid = '" . $strTransactionID . "' ";
			if($strDebug){ echo "SQLSTMNT= $query <br>"; }
			$rs=mysqli_query($DB_LINK, $query)  or funct_die_and_Report(mysqli_error($DB_LINK), "Error searching transactions table. Admin informed.", "Error searching transactions table. \n $strQueryString \n $query ", $strERRORPage, $intNewLogID)  ;

			if(mysqli_num_rows($rs)<1){

				//if it does not exist then insert
				$query = "INSERT INTO ".$tbl_Transactions.
				" ( txid, 				user_id,	method,			transaction_type, 	ipaddress,		crypto_amount,	credit,			crypto_type,		address_to,		address_from,	 	confirmations,		response, 			block_hash,		block_index,		block_time,			tx_time,	tx_timereceived,	tx_category,	address_account,	crypto_balancecurrent, 	crypto_previousbalance, 		crypto_bitcoindbalance, 	label,		label2,			label3,			date_created ) VALUES ".
				" ('$strTransactionID',$intUserID,	'$strMethod',	'get',				'$strIPaddress','$intAmount', 	'$intAmount',	'$strCryptoType',	'$strAddress',	'$strAddressFrom',	'$intConfirmations','$strReturnError',	'$strBlockHash','$strBlockIndex',	'$strBlockTime',	'$intTime','$intTimeReceived',	'$strCategory',	'$strAccountName',	'$intNewBalance',		'$intAddressPreviousBalance',	'$intCryptoBitcoinDBalance','$strLabel','$strLabel2',	'$strLabel3',	NOW() ) " ;
				if($strDebug){ echo "Insert into user Table - SQL STMNT = " . $query .  "<br>"; }
				$strERRORUserMessage="Database CREATE NEW TRANSACTION $strMethod Error. Admin has been informed "; $strERRORMessageAdmin=" $strQueryString \n SQL statement failed - $query "; 
				mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage, $intNewLogID)  ;
				//CATCH ERROR AND EMAIL 
				$intTransactionID = mysqli_insert_id($DB_LINK);
				
				
				//if it is a new transaction then update address table with received amount
				$intAddressTotalReceived = $intAddressTotalReceived + $intAmount ;
				$query="UPDATE " . $tbl_Addresses . " SET crypto_totalreceived='$intAddressTotalReceived'  WHERE address='".$strAddress."' " ;
				if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
				mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), "Error updating address total received.  Admin has been informed", "$strQueryString \n $query \n error= $strReturnError ", $strERRORPage, $intNewLogID) ;
				

			}else{ //transaction found so ...
			
				$row = mysqli_fetch_assoc($rs);
				$intTransactionID =	$row["id"];
				
				//update with the new confirmations count
				$query="UPDATE " . $tbl_Transactions . " SET confirmations='$intConfirmations' WHERE txid='".$strTransactionID."'" ;
				if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
				mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), "Error updating confirmations.  Admin has been informed", "$strQueryString \n $query \n error= $strReturnError ", $strERRORPage, $intNewLogID) ;
				
			}// end if records found
			
			
			//!Callback-send_callback_to_remote_server
		
			
			//convert $intAmount to satoshi as our script is setup to handle that... blockchain.info does it.. legacy
			$intAmount = $intAmount * 100000000; //bitcoind never passes more than 8 decimal places thus value will always be a whole number.. satoshi
			
			// secret transaction_hash input_address value confirmations
			if(!$strCallbackURL){$strCallbackURL="http://easybitz.com/mods/processorder.php";}
			//call processorder script
			$json_url = $strCallbackURL."?secret=$strSecret&transaction_hash=$strTransactionID&address=$strAddress&input_address=$strAddress&userid=$strLabel2&value=$intAmount&confirms=$intConfirmations&server=amsterdam";
			//echo "<br>url: $json_url";
			$json_data = file_get_contents($json_url);
			//echo $json_data ;
			$json_feed = json_decode($json_data);
			$strCallbackResponse = $json_data;
			//$strCallbackResponse = "*ok*";
			//echo $json_data ;
			
			$strReturnError = $strCallbackResponse ;
			//CATCH ERROR AND EMAIL

			if($strCallbackResponse=="*ok*"){
				$strSQL2 = " , callback_status=1 ";
			}
			
			//if we get back an *ok* from the script then update the transations tbl status
			$query="UPDATE " . $tbl_Transactions . " SET response_callback='".$strCallbackResponse."' , callback_url='$json_url' $strSQL2 WHERE txid='".$strTransactionID."'" ;
			if($strDebug){ echo "SQL STMNT = " . $query .  "<br>"; }
			mysqli_query($DB_LINK, $query)  or funct_die_and_Report(mysqli_error($DB_LINK), "Error updating transaction response.  Admin has been informed", "$strQueryString \n $query \n error= $strReturnError ", $strERRORPage, $intNewLogID) ;
			
			//if we do not get back an ok we need some method of 
			//hitting the callback url over and over until we get an *ok* how?
			
			
		}
		
		
		if(RETURN_OUTPUTTYPE=="json"){
			echo json_encode(array( 'confirmations'=>"$intConfirmations", 'address'=>"$strAddress", 'amount'=>"$intAmount", 'txid'=>"$strTransactionID", 'callback_url'=>"$json_url", 'error'=>"$strReturnError" ));
		}else{
			echo $strReturnError ; //die;
		}
		
		
	break;
	
} //End Switch Statement


//update log record
$query="UPDATE " . $tbl_Logs . 
" SET response= '".$strReturnError."' ".
" WHERE log_id=".$intNewLogID ;
if($strDebug){ echo "<br><br> SQL STMNT = " . $query .  "<br>"; }
mysqli_query($DB_LINK, $query) or die(mysqli_error($DB_LINK));
//CATCH ERROR AND EMAIL



ob_flush(); //so we can redirect even after headers are sent

?>