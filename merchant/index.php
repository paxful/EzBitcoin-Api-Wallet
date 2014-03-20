<?php 
/*
// This page processess all api calls send via GET requests
createaddress, sendtoaddress, sendfrom, getbalance of address
getbalance of wallet/user ?
*/

ob_start(); //so we can redirect even after headers are sent

//include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";
//need our own constants.php 
//include functmail.php /jsonsrpc/jsonRPCClient.php maybe server too
include $_SERVER['DOCUMENT_ROOT']."/constants.php";
include $_SERVER['DOCUMENT_ROOT']."/functmail.php";
include $_SERVER['DOCUMENT_ROOT']."/jsonRPCClient.php";
//just use require_once instead ?


error_reporting(0);//turn off all reporting
if(DEBUGMODE>0){
	ini_set('display_errors',1);
	error_reporting(E_ERROR | E_PARSE);
}



//common varibles and connection strings used in this file
//$DB_LINK for database
//JSONRPC_CONNECTIONSTRING_CC for bitcoind RPC server
$tbl_Users = 			"tbl_api_users";
$tbl_Addresses = 		"tbl_api_addresses";
$tbl_Transactions = 	"tbl_api_transactions";
$tbl_Logs = 			"tbl_api_logs";

$strIPaddress = $_SERVER['REMOTE_ADDR'];
$strReferrer = $_SERVER['HTTP_REFERER'];
$strQueryString = $_SERVER['QUERY_STRING'];
$strDebugHTTP = "$strIPaddress \n $strQueryString \n $strReferrer \n ";
$strERRORPage = "merchantapi";


//Get Common QueryString Variables
$strMethod = 			trim($_GET['do']);
$strLoginName = 		trim($_GET['loginname']);
$strLoginPassword = 	trim($_GET['password']);
$strLoginPassword2 = 	trim($_GET['second_password']);
$strCryptoType = 		trim($_GET['cryptotype']);
if(!$strCryptoType){$strCryptoType="btc";} //default is bitcoin

if(!$strMethod){ die;}
if(!$strLoginName){ die('nouser');}
if(!$strLoginPassword){ die('nopassword');}

//security against mysql injection and xss attacks
$strLoginName = mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($strLoginName));
$strLoginPassword = mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($strLoginPassword));
$strLoginPassword2 = mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($strLoginPassword2));


//authenticate the user by looking into the database
$query="SELECT * FROM ".$tbl_Users." WHERE user_loginid='".$strLoginName."'" ;
//echo "SQL STMNT = " . $query .  "<br>";
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

if(!$intUserID){$strError_Login="nouser" ;}
//if(!password_verify($strLoginPassword, $strPassword_DB)){ $strError_Login = "fail"; } //check hashed password match
if($strPassword_DB!=$strLoginPassword){ $strError_Login=="fail" ;} //check non hashed password match
if($strError_Login=="fail"){ $strError_Login_msg= "error: wrong username or password"; }
if($strError_Login=="nouser"){ $strError_Login_msg= "error: no such user"; }

//add login attempts to log table... for flood hacking protection how do we prevent flood attacks ?
$query = "INSERT INTO ".$tbl_Logs.
" ( user_loginid, 	user_id,		method,			ipaddress,		querystring,		referrer,	 	response, 				date_created ) VALUES ".
" ('$strUserIDcode','$intUserID',	'$strMethod',	'$strIPaddress','$strQueryString',	'$strReferrer',	'$strError_Login_msg',	NOW() ) " ;
//echo "Insert into user Table - SQL STMNT = " . $query .  "<br>";
$strERRORUserMessage="Database insert logs Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
//CATCH ERROR AND EMAIL
$intNewLogID = mysqli_insert_id($DB_LINK);


if($strError_Login_msg){
	echo $strError_Login_msg ;
	die ;
}



//echo "strDo= " . $strDo;
switch ($strMethod){


    //create new address in blockchain
    case "new_address":
    //takes an address OPT to forward funds to, label1 OPT, label2 OPT, label3 OPT
        
        $strAddress = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['address'])); 
        $strLabel1 = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['label'])); 
        $strLabel2 = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['label2'])); 
        $strLabel3 = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['label3'])); 
        
        //RPC call to the bitcoin server 
        //takes an account name to attach the address to
        $mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC) ; //our own bitcoind rpc server
		//if(!$strAddress){$strAddress='*';}
		$strAccount = $strUserName; //might be depreciated in new system
		try{
		  	$strReturnError= $mybtc->getnewaddress($strAccount);
		  	$strWalletAddress = $strReturnError ;
		  	
	        // add to address table
			$query = "INSERT INTO ".$tbl_Addresses.
			" ( user_id,		user_name,		address,			crypto_type,		date_created ) VALUES ".
			" (	'$intUserID',	'$strUserName',	'$strWalletAddress','$strCryptoType',	NOW() 	  ) " ;
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
		echo $strReturnError ; die;
		
    break;
    


	
	
	case "sendtoaddress": //sends bitcoin
	//bitcoind sendtoaddress <bitcoinaddress> <amount> [comment] [comment-to]	 
	//<amount> is a real and is rounded to 8 decimal places. 
	//Returns the transaction ID <txid> if successful.
	case "sendfromaddress":
	//bitcoind sendfrom <fromaccount> <tobitcoinaddress> <amount> [minconf=1] [comment] [comment-to]	 
	//<amount> is a real and is rounded to 8 decimal places. 
	//Will send the given amount to the given address, ensuring the account has a valid balance using [minconf] confirmations. 
	//Returns the transaction ID if successful (not in JSON object).

        $strAddress = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['address'])); 
        $intAmount = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['amount']));
        $strFromAccount=mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['from']));  
        $strComment = 	mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['comment']));  
        $strCommentTo = mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['commentto'])); 

		//Request error: -1 - value is type str, expected real
		//$intAmount = (float)0.000100 ;
		$intAmount = (float)$intAmount ;
		echo "amt= ".$intAmount."<br>";


		//RPC call to the bitcoin server 
        $mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC) ; //our own bitcoind rpc server
		//if(!$strWalletAddressForward){$strWalletAddressForward='*';}
		
		try{
			if($strFrom){ //use send from function
				$strReturnError = $mybtc->sendfrom( $strFromAccount, $strAddress , $intAmount , $strComment , $strCommentTo );
			}else{ //send from any availabe balance
		  		$strReturnError = $mybtc->sendtoaddress( $strAddress , $intAmount , $strComment , $strCommentTo );
		  	}
		  	return $strReturnError ;
		  	//should return transaction id if it works
		  	if($strReturnError){ 
		  		$strTransactionID = $strReturnError ; 
		  	}
		  
		} catch(Exception $e){
		  	//echo nl2br($e->getMessage()).'<br />'."\n";
		  	$strReturnError = $e->getMessage() ;
		  	
		  	//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Sending Transaction $strMethod Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage);
		}

		//if it returns back a transactionid then add to the transactions table
		$query = "INSERT INTO ".$tbl_Transactions.
		" ( txid, 				user_id,	method,		ipaddress,		crypto_amount,	crypto_type,		address_to,		address_from,	 response, 				date_created ) VALUES ".
		" ('$strTransactionID',$intUserID,	'$strMethod','$strIPaddress','$intAmount', 	'$strCryptoType',	'$strAddress',	'$strFrom',		'$strError_Login_msg',	NOW() 	  ) " ;
		echo "Insert into user Table - SQL STMNT = " . $query .  "<br>";
		$strERRORUserMessage="Database CREATE NEW TRANSACTION $strMethod Error. Admin has been informed "; $strERRORMessageAdmin="$strQueryString \n SQL statement failed - $query "; 
		mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		//CATCH ERROR AND EMAIL 
		$intNewTransactionID = mysqli_insert_id($DB_LINK);


		echo $strReturnError; die;

	break;
	
	
	

	case "callback": //sends callback on recieve notifcation
	//gets a transaction hash id
	//calls bitcoind d via RPC to get transaction info 
	//calls a web url specified in the user account

        $strTransaction = mysqli_real_escape_string($DB_LINK, funct_FormVarSecurity($_GET['txid'])); 
		$strTransaction="10c724bdfe52f95b482949101cc1bb3657c9f92d7f61d469a309eacbb6782d24";

		if(!$strTransaction){ 
			$strReturnError = "no transaction id provided...";
			funct_die_and_Report($strReturnError, "", $strDebugHTTP);
		}


		$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
		try{
		  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
		  	//echo $strTransaction ;
		  	$trxinfo = $bitcoin->gettransaction($strTransaction);
		  	//return $objJSON ;
		  	$strTransactionType = "get";
		  	//decoderawtransaction 
		  	//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and 
		  	//use the getrawtransaction call to request information about any transaction 
		  	
		  	//bind values to variables
			$strTransactionID = $trxinfo["txid"] ;
			$intAmount = $trxinfo["amount"] ;
			$intConfirmations = $trxinfo["confirmations"] ;
			$intTime = $trxinfo["time"] ;
			$strAccountName = $trxinfo["details"][0]["account"] ;
			$strAddress = $trxinfo["details"][0]["address"] ;
			$strAddressFrom = "" ;
			
			$new = "\n\nTransaction hash: ".$argv[1]
			."\n Getinfo balance: ".$walletinfo["balance"]
			."\n Gettransaction amount: ".$trxinfo["amount"]
			."\n Gettransaction confirmations: ".$trxinfo["confirmations"]
			."\n Gettransaction blockhash: ".$trxinfo["blockhash"]
			."\n Gettransaction blockindex: ".$trxinfo["blockindex"]
			."\n Gettransaction blocktime: ".$trxinfo["blocktime"]
			."\n Gettransaction txid: ".$trxinfo["txid"]
			."\n Gettransaction time: ".$trxinfo["time"]
			."\n Gettransaction timereceived: ".$trxinfo["timereceived"]
			."\n Gettransaction account: ".$trxinfo["details"][0]["account"]
			."\n Gettransaction address: ".$trxinfo["details"][0]["address"]
			."\n Gettransaction category: ".$trxinfo["details"][0]["category"]
			."\n Gettransaction amount: ".$trxinfo["details"][0]["amount"]
			."\n Gettransaction fee: ".$trxinfo["details"][0]["fee"]  // According to https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_calls_list, fee is returned, but it doesn't seem that way here
			;
			echo nl2br($new)."<br>";
		  
		} catch(Exception $e){

			$strReturnError = $e->getMessage(); 
			
			//CATCH ERROR AND EMAIL
			$strERRORUserMessage="Getting Transaction Info Failed. Admin has been informed ".$strReturnError; $strERRORMessageAdmin="$strQueryString \n error= $strReturnError ";
			funct_die_and_Report($strReturnError, $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage);
		}
		
		
		//only call transaction process code if we get back a transaction id
		if($strTransactionID){ 
			
			//first check if the record exists
			$query=	"SELECT * FROM " . $tbl_Transactions . " WHERE txid = '" . $strTransactionID . "' ";
			//echo "SQLSTMNT= $query <br>";
			$rs=mysqli_query($DB_LINK, $query);
			if(mysqli_num_rows($rs)<1){

				//if it does not exist then insert
				$query = "INSERT INTO ".$tbl_Transactions.
				" ( txid, 				user_id,	method,		ipaddress,		crypto_amount,	crypto_type,		address_to,		address_from,	 	confirmations,		response, 			date_created ) VALUES ".
				" ('$strTransactionID',$intUserID,	'$strMethod','$strIPaddress','$intAmount', 	'$strCryptoType',	'$strAddress',	'$strAddressFrom',	'$intConfirmations','$strReturnError',	NOW() 	  ) " ;
				//echo "Insert into user Table - SQL STMNT = " . $query .  "<br>";
				$strERRORUserMessage="Database CREATE NEW TRANSACTION $strMethod Error. Admin has been informed "; $strERRORMessageAdmin=" $strQueryString \n SQL statement failed - $query "; 
				mysqli_query($DB_LINK, $query) or funct_die_and_Report(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
				//CATCH ERROR AND EMAIL 
				$intTransactionID = mysqli_insert_id($DB_LINK);

			}else{ //Email found so ...
			
				$row = mysqli_fetch_assoc($rs);
				$intTransactionID =	$row["id"];
				
				//update with the new confirmations count
				$query="UPDATE " . $tbl_Transactions . " SET confirmations='$intConfirmations' $strSQL2 WHERE id='".$intNewTransactionID."'" ;
				//echo "SQL STMNT = " . $query .  "<br>";
				mysqli_query($DB_LINK, $query) or die(mysqli_error());
				
			}// end if records found
		
				
			//call processorder script
			$json_url = "http://getcoincafe.com/mods/processorder.php?transaction_hash=$strTransactionID&address=$strAddress&input_address=unknown&value=$intAmount";
			//$json_data = file_get_contents($json_url);
			$strCallbackResponse = "*ok*";
			//CATCH ERROR AND EMAIL

			if($strCallbackResponse=="*ok*"){
				$strSQL2 = " , callback_status=1 ";
			}
			
			//if we get back an *ok* from the script then update the transations tbl status
			$query="UPDATE " . $tbl_Transactions . " SET response_callback='".$strCallbackResponse."' $strSQL2 WHERE id='".$intTransactionID."'" ;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
	
			//if we do not get back an ok we need some method of 
			//hitting the callback url over and over until we get an *ok* how?

			
		}
		
		echo $strReturnError; die;

	break;
	
} //End Switch Statement

//update log record
$query="UPDATE " . $tbl_Logs . 
" SET response= '".$strReturnError."' ".
" WHERE id=".$intNewLogID ;
//echo "SQL STMNT = " . $query .  "<br>";
mysqli_query($DB_LINK, $query) or die(mysqli_error($DB_LINK));
//CATCH ERROR AND EMAIL



//functions used in this file
function funct_FormVarSecurity($strVariable){ //prevent XSS attacks
	$strVariable = trim($strVariable);
	$strVariable = htmlspecialchars($strVariable);
	$strVariable = stripslashes($strVariable);
	return $strVariable ;
}

function funct_die_and_Report($strMessage, $strUserMessage, $strMessageAdmin, $strPage, $intLogRecord) {
//get error string, usermsg, admin msg and page -> then send email to admin and die
	
	if(!$strUserMessage){ $strUserMessage = $strMessage ;}
	if(!$strPage){ $strPage = "merchantapi" ;}
	
	//update log record
	if($intLogRecord){
		
		$query = "UPDATE ".$tbl_Logs." SET " .
		"response='$strMessage' ".
		"WHERE log_id = $intLogRecord " ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query);
	}

    if($strMessageAdmin){
		$strSubject = "Error Detected on: ".$strPage." " ;
		$strBody = " ".$strMessageAdmin." \n ".$strMessage." \n ".$strUserMessage." \n ".$strPage ; 
		funct_Mail_simple(EMAIL_ADMIN,$strSubject,$strBody);
    }
    die($strUserMessage);
}


ob_flush(); //so we can redirect even after headers are sent

?>