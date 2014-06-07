<?php

//Get Fresh Page QueryString Variables
$strDo = 				trim($_GET['do']);


if($strDo){
	ob_start(); //so we can redirect even after headers are sent

	error_reporting(E_ALL & ~E_NOTICE);

	include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";
	$intUserID = 			funct_ScrubVars(DETECT_USERID) ;
}

$strERRORPage="/mods/sendcrypto.php"; //for errors die with grace


//!$strDo sendcheckpassword
//we need this to simply return "ok" or "wrong" etc...
if($strDo=="sendcheckpassword"){

	$strPassword = 		funct_ScrubVars($_POST["password"]);
	$strPassword = 		funct_ScrubVars( $strPassword);
	$intUserID = 		funct_ScrubVars( DETECT_USERID);
	$strUserIDcode = 	funct_ScrubVars( DETECT_USERIDCODE);

	//get their userid, or useridcode

	//either they will have their userid specified
	if($intUserID){$strWhereSQL = " id= ".$intUserID." ";}

	//or they will have their useridcode specified
	if($strUserIDcode){$strWhereSQL = " user_code= '".$strUserIDcode."' ";}


	//check to see if the email/password combo match
	if($strWhereSQL){
		$query="SELECT * FROM " . TBL_USERS . " WHERE id_code = '".DETECT_USERIDCODE."'" ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs);
		$strPasswordDB=				$row["password"];
		$strEmail=					$row["email"];
		$intUserID_DB=				$row["id"];
	}

	if(!$intUserID_DB){ echo "no such user found" ; die; }

	//we use this function because it checks for hashed password or unhashed cleartext password and updates the password to secure has if it is not hashed
	//echo "pass db - $strPasswordDB  / pass= ".$strPassword."<br>";
	$intUserIDreturn = functConfirmUserPass_hash($strEmail, $strPassword) ; //this returns the userid if it is good
	//echo "returns $strEmail - $intUserID_DB  / id= ".$intUserIDreturn."<br>";
	//if(password_verify($password, $strPassword_DB)){$strPasswordGood = "ok";}


	if($intUserID_DB==$intUserIDreturn){
		echo "ok";
	}else{
		echo "wrong password";
	}

}


//!$strDo sendcrypto
if($strDo=="sendcrypto"){

	if(! SEND_ACTIVE){
		die('Sending temporarily disabled.');
	}

	$Form_PageFrom = 		funct_ScrubVars($_POST['page']);
	$intBTCamt = 			funct_ScrubVars($_POST['send_amount_crypto']);
	$intUSDamt = 			funct_ScrubVars($_POST['send_amount_fiat']);
	$strWalletHash = 		funct_ScrubVars($_POST['send_address']); //hackable
	$strPassword = 			funct_ScrubVars($_POST['password']); //hackable
	$strLabel = 			funct_ScrubVars($_POST['label']); //hackable
	$strCrypto = 			funct_ScrubVars($_POST['crypto']);
	$strFiat = 				funct_ScrubVars($_POST['fiat']);

	/*
	//why do we allow get here??? for testing.... easier to hack get
	$strMethod = 			funct_ScrubVars($_GET['get']);
	if($strMethod){
		// /mods/sendcrypto.php?do=sendcrypto&send_amount_crypto=.01&send_address=1FTJXv8FjqyQ1HUTx7HeLxVaZTnc8E3muW&label=testing
		$Form_PageFrom = 		funct_ScrubVars($_GET['page']);
		$intBTCamt = 			funct_ScrubVars($_GET['send_amount_crypto']);
		$intUSDamt = 			funct_ScrubVars($_GET['send_amount_fiat']);
		$strWalletHash = 		funct_ScrubVars($_GET['send_address']);
		$strPassword = 			funct_ScrubVars($_GET['password']);
		$strLabel = 			funct_ScrubVars($_GET['label']);
		$strCrypto = 			funct_ScrubVars($_GET['crypto']);
		$strFiat = 				funct_ScrubVars($_GET['fiat']);
	}
	*/

	$intTime = 				time();
	$strIPAddress = 		$_SERVER['REMOTE_ADDR'];

	if(!$strWalletHash){ $strError_send = "No wallet address specified." ; }

	//check if they are sending to an email address
	if(funct_check_email_address($strWalletHash)){
		$strSendToEmailAddress = $strWalletHash ;

	}else{ //bitcoind address so remove all non alpha numeric characters

		if(!preg_match("/[a-z0-9]/i",$strWalletHash)){ // non alpha numeric characters passed
		  $strError_send = $strError_send. " No html allowed in address. Please paste text only. Do not paste address directly from a browser link." ;
		}else{

			//check the address via bitcoind validate 1=yes noconnect means that the bitcoind was not reachable
			$strIsValidAddress =funct_Billing_ValidateAddress($strWalletHash);
			if($strIsValidAddress=="bad"){

				$strError_send = $strError_send. " Not a Valid Bitcoin Address (error= $strIsValidAddress)";
			}

		}
	}

	//remove all non alpha numeric
	$strWalletHash = functRemoveNonAlphaNumeric($strWalletHash);

	//fend of neg attack
	$intBTCamt=abs($intBTCamt);
	$intUSDamt=abs($intUSDamt);

	//calculate btc value to send based on exchange price
	$strCrypto="btc"; $strExchange=RATE_HUD_EXCHANGE;
	$intRate = funct_Billing_GetRate($strCrypto,$strExchange);
	$intUSDamt= $intBTCamt * $intRate ;


	//check their balance of SENDER
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intUserID ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$intBalanceCrypto=				$row["balance_btc"];
	$intBalanceCrypto_old=$intBalanceCrypto; //store this to compare to updated balance
	$strEmail=						$row["email"];
	$strPassword_DB=				$row["password"];
	$strFirstName=					$row["first_name"];
	$strLastName=					$row["last_name"];
	$strPhone=						$row["cellphone"];
	$intSendLocked=					$row["sendlocked"];
	$intBalancePrev_Sender=			$row["balance_prev"];
	$intBalanceCurr_Sender=			$row["balance_curr"];

	$strWalletFrom=					$row["wallet_btc"];
	$intMiningFee=					$row["crypto_miner_fee"];
	$intMiningFee = MININGFEE_NORMAL ; // blockchain.info does not let u set a custom mining fee, normal hardcoded

	//######## SECURITY - MAGICAL CHECKS AGAINST EVIL

	if($intSendLocked){ $strError_send= "Your account has been locked from sending." ;}

	//don't let them send 0
	if($intBTCamt<=0){ $strError_send= $strError_send." Cannot Send 0" ;}

	//don't let them send negative
	if($intBTCamt<0){ $strError_send= $strError_send." Cannot send Negative Amounts" ;}

	//check password hash - this takes a few seconds on the database check
	if(!password_verify($strPassword, $strPassword_DB)){ $strError_send = $strError_send." bad password."; }

	//send btc if they have enuf btc
	if($intBalanceCrypto<$intBTCamt) { $strError_send = $strError_send." Not Enough BTC in your wallet to send."; }

	//don't let them send to themselves
	if($strWalletFrom==$strWalletHash){ $strError_send= $strError_send."Can't send to yourself..." ;}


	//if their balance is negative then forbid them from doing jack shit and lock their account
	if($intBalanceCrypto<0){
		$strError_send= $strError_send." Your account balance is negative and your account has been locked." ;

		//lock their account
		$query="UPDATE " . TBL_USERS . " SET sendlocked= 1 WHERE id=".$intUserID ;
		//echo "SQL STMNT = " . $query .  "<br>";
		mysqli_query($DB_LINK, $query); // or die(mysqli_error());
	}

	//check balance on hot wallet and if it is not enough give the user an error message


	//##################################################################################
	//make sure they have at least one transaction with a order fullfilled or a receieved in
	$query="SELECT * FROM " . TBL_TRANSACTIONS . " WHERE  user_id = '" . $intUserID."' ".
	" AND credit>0 " ;
    //echo "SQL STMNT = " . $query .  "<br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
    	//records found so allow send


    }else{ //no fullfilled orders or receives found so this may be a hacker

		$strError_send = "overwithdrawl - security team notified";

		$strSubject = "Attempted send from zero credit account... ";
		$strBody = "email: $strEmail \n phone: $strPhone \n ip address: $strIPAddress" ;
		funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',$strEmail);

	}
	//##################################################################################



	//scan banned wallets table TBL_BANNEDWALLETS
	$query="SELECT * FROM " . TBL_BANNEDWALLETS . " WHERE  walletaddress = '" . $strWalletHash."' " ;
    //echo "SQL STMNT = " . $query .  "<br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	    $strError_note = 			$row["note"];
		$strError_send = $strError_send." ".BANNEDWALLETREASONTXT. " - ".$strError_note ;
		//if(!$strError_send){ $strError_send= BANNEDWALLETREASONTXT ;}

		$strSubject = "Attempted send to Banned Wallet ".$strError_note." ";
		$strBody = "email: $strEmail \n phone: $strPhone \n ip address: $strIPAddress" ;
		funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',$strEmail);
	}

	//scan banned ips.. LATER


	//if senderror banned wallet reciever or banned ip sender... then echo error and die
	if($strError_send){
		echo $intErrorCode.",".$strError_send.",".$intBalanceCrypto.",".$intBalanceUSD ;
		die; //kill whole page
	}
	//#####  END MAGICAL SECURITY CHECKS #######################################


	//if email address then search the database for the email of the user.. if found then internal send
	//why are these not prepared statements? notes above indicate $strSendToEmailAddress is hackable
	if($strSendToEmailAddress){
		$query="SELECT * FROM " . TBL_USERS . " WHERE email = '" . $strSendToEmailAddress."' " ;

	}else{ //sending via bitcoin address
		$query="SELECT * FROM " . TBL_USERS . " WHERE wallet_btc = '" . $strWalletHash."' OR wallet_address_cc= '".$strWalletHash."' " ;

	}

	//check if the RECEIVER has address is in the database
    //echo "SQL STMNT = " . $query .  "<br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	    $intUserID_get=						$row["id"];
		$strEmail_get=						$row["email"];
		$strFirstName_get=					$row["first_name"];
		$strLastName_get=					$row["last_name"];
		$strPhone_get=						$row["cellphone"];
	    $intBalanceCrypto_receive=			$row["balance_btc"];

	    $strWallet_MainAddress= 			$row["wallet_btc"];
		$strWallet_MainAddress_CC=			$row["wallet_address_cc"]; //main wallet address

		//if yes then it is OFF blockchain
		$intMiningFee = 0 ;//internal so zero mining fee
		//just do an internal database balance update
		$strNetwork="coincafe" ;
	}else{  //on block chain thus external

		if($strSendToEmailAddress){//network is email not in our system
			$strNetwork="email" ;
		}else{
			$strNetwork="blockchain" ;
		}
	}

	//redudant checks to keep inserts and updates from breaking and "" values
	if(!$intUserID_get){$intUserID_get=0;}
	if(!$intBalanceCrypto_receive){$intBalanceCrypto_receive=0;}

	//###################################################################################################
	// CALUCLATE LOGIC
	// 3 cases. internal send, external send, external send sweep

	//external send logic
	if($strNetwork=="blockchain"){ //if external send then adjust

		//prohibit mining fee negative sends.
		if($intBTCamt<=0.0001){
			$strError_send= $strError_send." You cannot send under 0.0001 as it is less than the mining fee." ;
			echo $strError_send ; die;
		}
		$intMiningFee = MININGFEE_NORMAL ; //hardcode it to normal. we have no control over it

	}else{
		$intMiningFee=0;
	}//end if external send

	//calculate balances, out flow and ledger history
	$intCryptoTotal_Outflow= $intBTCamt + $intMiningFee ;
	$balance_prev_sender = $intBalanceCrypto ;
	$intDebit = $intCryptoTotal_Outflow ;
	$balance_curr_sender = $balance_prev_sender - $intDebit ;
	if($balance_curr_sender<0){ $intBalanceCrypto=0 ;}else{ $intBalanceCrypto=$balance_curr_sender; }
	//###################################################################################################


	//INSERT TRANSACTION FOR THE SEND
	$query = "INSERT INTO ".TBL_TRANSACTIONS.
	" ( status, type,	cryptotype,	debit,					balance_prev,				balance_curr,			crypto_amt, crypto_rate_usd,crypto_miner_fee,	crypto_total_outflow, 	walletaddress_sentto, 	walletaddress_from, label, 		sender_name,					sender_email,	sender_phone, 	receiver_name,						receiver_email,	receiver_phone,	ipaddress,		user_id,		user_id_sentto,	datetime_created,	datetime ) VALUES ".
	" ( 0, 		'send',	'btc',		$intCryptoTotal_Outflow,'$balance_prev_sender',		'$balance_curr_sender',	$intBTCamt, $intRate,		$intMiningFee,		$intCryptoTotal_Outflow,'$strWalletHash',		'$strWalletFrom',	'$strLabel','$strFirstName $strLastName',	'$strEmail',	'$strPhone',	'$strFirstName_get $strLastName_get','$strEmail_get','$strPhone_get','$strIPAddress',$intUserID,	$intUserID_get,	$intTime,		NOW() ) " ;
	//echo "insert order sql = " . $query .  " \n <br>";
	//$strError_send = $strError_send. " update transactions tbl= ".$query ;
	$strERRORUserMessage="Database insert transaction Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
	mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
	$intNewOrderID = mysqli_insert_id($DB_LINK);
	if(!$intNewOrderID){ $strError_send = " Database Error- could not send.".$strError_send ;}

	//SECURTITY CHECK
	if($intBalanceCrypto==$intBalanceCrypto_old){ $strError_send= $strError_send." balance too low..." ;}

	//DEBIT SENDER BALANCE - CRITICAL!!!!!
	if(!$strError_send){
		$query="UPDATE " . TBL_USERS . " SET balance_btc= ".$intBalanceCrypto." , balance_prev=$balance_prev_sender , balance_curr=$balance_curr_sender WHERE id=".$intUserID ;
		//echo "SQL STMNT = " . $query .  "<br>";
		$strERRORUserMessage="Database deduction Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
		mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage) ;
	}
	//POTENTIAL PLACE TO INSERT THE NEW BALANCE UPDATING FUNCTION -JOHN
	$strErrorMessage2 = $strErrorMessage2. " user was debited - $intBTCamt ." ;


	//if internal then CREDIT RECEIVER
	if($strNetwork=="coincafe" AND !$strError_send AND $intUserID_get){

		//NEED TO ADD A TRANSACTION RECORD HERE FOR THE RECEIVER THAT INDICATES A CREDIT TO THEM
		$balance_prev = $intBalanceCrypto_receive;
		$balance_curr=$balance_prev + $intBTCamt;
		$status_id="24"; //transaction valid
		$status_msg="Transaction Valid (old flag was 1)";
		$query = "INSERT INTO ".TBL_TRANSACTIONS.
		" ( user_id,			user_id_receivedfrom,	currency_code,	credit,			balance_prev,	balance_curr,		type,		cryptotype,	crypto_miner_fee,	crypto_rate_usd,	label,			datetime_created,	datetime,	status,			status_msg,		ipaddress 		) VALUES ".
		" ( '$intUserID_get',	'$intUserID',			'btc',			'$intBTCamt',	'$balance_prev','$balance_curr',	'receive',	'btc',		'0',				'$intRate',			'$strLabel',	$intTime,			NOW(),		'$status_id',	'$status_msg',	'$strIPAddress' ) " ;
		$strERRORUserMessage="Database insert internal receiver transaction Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
		mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		$intNewTransactionID_internalreceiver = mysqli_insert_id($DB_LINK);

		//CREDIT RECEIVER  //update Balannce of receiver if internal
		$intBalanceCrypto_receive = $intBalanceCrypto_receive + $intBTCamt ; //CRITCAL. ADDS TO RECIEVER IF INTERNAL SEND
		$query="UPDATE " . TBL_USERS . " SET balance_btc=$balance_curr , balance_prev=$balance_prev, balance_curr=$balance_curr WHERE id=".$intUserID_get ;
		//POTENTIAL PLACE TO INSERT THE NEW BALANCE UPDATING FUNCTION -JOHN
		//echo "SQL STMNT = " . $query .  "<br>";
		$strERRORUserMessage="Database receive credit Error. Admin has been informed ".$strError_send ; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
		mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		$strStatus = 1 ;
	}


	//step 3 - attempt to send via external
	if($strNetwork=="blockchain" AND !$strError_send){ //if not in internal network... execute blockchain api to send via bitcoin network

		//we can either use web api or JSONRPC api
		$strWalletFrom = BLOCKCHAIN_SENDFROMADDRESS; //because we don't send btc to their specific addresses instead send from our loaded address... temp fix for blockchain.info api
		$strNote = $intNewOrderID." ".$intUserID." ".$strFirstName." ".$strLastName." ".$strLabel ;


		//###############################################################

		if(!SECURITY_SENDOUTS_MUSTBE_APPROVED){ //no approvals, just send

			//send coins out
			if(SEND_THROUGH_WHICH_SYSTEM=="blockchain.info"){
				$strSendReturn = funct_Billing_SendBTC($strWalletHash, $intBTCamt, $strNote, $intMiningFee, $strWalletFrom); //web api
			}
			if(SEND_THROUGH_WHICH_SYSTEM=="amsterdam"){
				$strSendReturn = funct_Billing_SendBTC_CoinCafe($strWalletHash, $intBTCamt, $strNote, $intMiningFee, $strWalletFrom); //web api
			}

		}else{ //approvals required

			/*
			//to prevent hacking we now send the user an email with a link pointing back to this transaction
			$strSubject = "Do you really want to send $intBTCamt Bitcoins ?";
			$strSendSecurityLink = "" ;
			$strBody = "Please click this security link to finalize your send " ;
			funct_Mail_simple($strEmail,$strSubject,$strBody,'',$strEmail);
			*/

			//insert a record into the transactions que table for manual queing
			$query = "INSERT INTO ".TBL_TRANSACTIONS_QUE.
			" ( user_id,		transaction_id,	transaction_type, 	transaction_amt, 	ipaddress,		status_id, 	date_created ) VALUES ".
			" ( '$intUserID', 	$intNewOrderID,	'bcsend',			$intBTCamt,			'$strIPAddress',0,			NOW() ) " ;
			$strERRORUserMessage="Database insert que transaction Error. Admin has been informed ".$strError_send;
			$strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
			mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error($DB_LINK), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage) ;

			$intNewTransactionID_que = mysqli_insert_id($DB_LINK);

			/*
			//get ip's info - limit to 1000 a day
			$json_data = file_get_contents('http://ipinfo.io/'.$strIPAddress.'/geo');
			$json_feed = json_decode($json_data);
			$strCity = $json_feed->city;
			$strRegion = $json_feed->region;
			$strCountry = $json_feed->country;
			*/

			//send to admin for authorization
			$strSendSecurityLink_admin = WEBSITEFULLURLHTTPS."/cp/que.php?queid=".$intNewTransactionID_que ;
			$strSubject = "User $strFirstName $strLastName wants to send $intBTCamt Bitcoins ?";
			$strBody = " $strSendSecurityLink_admin \n\n Location $strCity , $strRegion , $strCountry \n\n  email: $strEmail \n phone: $strPhone \n ip address: $strIPAddress" ;
			funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',$strEmail);

			//email user and let them know that a transaction has been initiated
			$strSubject = "Coin Cafe send requested for $intBTCamt BTC - pending approval";
			$authLink = WEBSITEFULLURLHTTPS."/authorizeSend.php?queid=".$intNewTransactionID_que;
			$strBody = "We have received a request to send $intBTCamt bitcoins from your account.\n\nTo authorize this transaction for $intBTCamt BTC, click on the link below.\n\n".$authLink."\n\nThis is especially important if you are traveling, using an internet proxy, or the Tor browser.\n\nOtherwise this transfer will be held in the security queue and eventually cancelled.\n\nIf you did *NOT* initiate this request, please immediately email ".SUPPORT_EMAIL." or call us at 347-454-2646.";
			funct_Mail_simple($strEmail,$strSubject,$strBody,'', SUPPORT_EMAIL);

		}//end if sen out must be approved

		//###############################################################


		//parse return array
		$strSendArry = explode("|", $strSendReturn);
		$strSendMsg=$strSendArry[0]; // message
		$strSendErr=$strSendArry[1]; // error
		$strSendHash=$strSendArry[2]; // txid - IT ONLY RETURNS A HASH IF SUCCESSFUL
		//$strSendHash="test blockchain hash";

		//BLOCKCHAIN RETURNS BACK A HASH ID SO IT IS GOOD!
		if($strSendHash){ //successful external send
			$strStatus = 24 ;
			//update transaction record below

		}else{ //hash is equal to NOTHING so... FAIL
		//################################################################################################
		//EXTERNAL FAIL THEN GIVE THE SENDER BACK HIS Money
			//$strStatus = 0 ;
			//if(!$strSendMsg AND !$strSendErr){
				$strStatus = 24 ; //we set the status to transaction valid so it shows up on their ledger
				//if no error is returned then the blochcain call failed somehow.. so don't give them back their funds..
				//blockchain seems to put transactions through and send but returns back nothing....
				//this bug has cost us over 4 coins thus far.. we must setup our own bitcoind
				$strErrorMessage2 = $strErrorMessage2. " user was NOT credited back... ";
			/*
			}else{ //if there is an error returned then credit the back their funds
				$query="UPDATE " . TBL_USERS . " SET balance_btc= balance_btc + ".$intBTCamt." , balance_prev=balance_prev - $intCryptoTotal_Outflow WHERE id=".$intUserID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				$strERRORUserMessage="Database user update Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
				mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage) ;
				$strErrorMessage2 = $strErrorMessage2. " user was credited ";
			}
			*/
			$strSubject = "FAILED External SEND - $strNetwork Send ($intBTCamt $strCrypto) $".number_format($intUSDamt,2)." USD by $intUserID ".$strFirstName." ".$strLastName ;
			$strBody = " External Block Chain send Failed - $strErrorMessage2 \n\n".
			"strSendMsg: $strSendMsg \n strSendErr: $strSendErr \n strSendHash: $strSendHash  \n ".
			"Data sent to function funct_Billing_SendBTC( $strWalletHash, $intBTCamt, $strNote, $intMiningFee, $strWalletFrom ) \n ".
			"amt: $intBTCamt $strCrypto \n total outflow: $intCryptoTotal_Outflow \ mining fee: $intMiningFee \ balance prev: $balance_prev_sender \n balance curr: $balance_curr_sender \n new balance: $intBalanceCrypto $strCrypto \n usd: $ $intUSDamt \n ".
			"email: $strEmail \n phone: $strPhone \n ip address: $strIPAddress \n Label: $strLabel \n".
			"Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=$intUserID\n";
			funct_Mail_simple(EMAIL_WALLETSEND,$strSubject,$strBody,'',$strEmail);

		//################################################################################################

		}
	} //end if networksend = blockchain aka external



	//for send via email
	if($strNetwork=="email" AND !$strError_send){

		//generate a random unique 24 character code they will use to claim the bitcoins
		$strCode=createRandomKey_Num(24);
		$strEmailCoinsLink = WEBSITEFULLURLHTTPS.CODE_DO."?do=claimcoins&email=".$strSendToEmailAddress."&code=".$strCode ;

		//add a record to the escrow table via email $intBTCamt $strLabel
		$status_id="0"; //waiting..
		$status_msg="waiting to be claimed";
		$query = "INSERT INTO ".TBL_ESCROW.
		" ( user_id,		user_email,	crypto_amount, crypto_rate, address_email, 				verify_code, 	transaction_id_send,	label,			date_created,	date_filled,	status,			status_msg,		ipaddress 		) VALUES ".
		" ( '$intUserID',	'$strEmail','$intBTCamt',	'$intRate',	'$strSendToEmailAddress',	'$strCode',		'$intNewOrderID',		'$strLabel',	NOW(),			NOW(),			'$status_id',	'$status_msg',	'$strIPAddress' ) " ;
		$strERRORUserMessage="Database add escrow record Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query ";
		mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
		$intNewEscrowID = mysqli_insert_id($DB_LINK);

		//email the receiver an link to the bitcoins
		$strSubject = "You Got Bitcoin " .$strFirstName." ".$strLastName ;
		$strBody = "amt: $intBTCamt $strCrypto \n was just sent to you by $strFirstName $strLastName - $strEmail  \n".
		"To Claim your coins and your free Wallet Please click this link \n".
		"$strEmailCoinsLink \n ".
		WEBSITENAME." thank you " ;
		funct_Mail_simple($strSendToEmailAddress, $strSubject,$strBody,'',$strEmail);

		$strSendHash = "email";
	}



	//FINISHING UP AND RECORDING RESULTS

	if (!$strStatus) {$strStatus=0;}//if internal send protect db update

	//update transaction table with status, message and hash if it exists
	//need to update  - balance_crypto_old  balance_crypto_new  crypto_total_outflow
	$query="UPDATE ".TBL_TRANSACTIONS." SET ".
	" status=$strStatus,".
	" status_msg= '".$strSendErr." ".$strSendMsg."', ".
	" hash_transaction= '".$strSendHash."' ".
	" WHERE transaction_id= ".$intNewOrderID ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$strERRORUserMessage="Database transaction update Error. Admin has been informed"; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; $strERRORPage="/mods/sendcrypto.php";
	mysqli_query($DB_LINK, $query)  or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;

	//update user  count_externalsends total_sentout


	$intBTCamt = number_format($intBTCamt,8);
	$intBalanceCrypto = number_format($intBalanceCrypto,8);


	//send email to admin informing of a new Send by a user
	$strSubject = "$strNetwork Send ($intBTCamt $strCrypto) $".number_format($intUSDamt,2)." USD by $intUserID ".$strFirstName." ".$strLastName ;
	$strBody = "amt: $intBTCamt $strCrypto \n new balance: $intBalanceCrypto $strCrypto \n usd: $ $intUSDamt \n ".
	"strSendMsg: $strSendMsg \n phone: $strPhone \n ip address: $strIPAddress \n Label: $strLabel \n ".
	"Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=$intUserID\n".
	"email: $strEmail \n strSendErr: $strSendErr \n strSendHash: $strSendHash " ;
	funct_Mail_simple(EMAIL_WALLETSEND,$strSubject,$strBody,'',$strEmail);


	if($strStatus){// no errors so it went thru

		$intErrorCode = 1 ;
		$strError_send = "SUCCESS. COIN SENT ".$strError_send;
		$intBalanceUSD = number_format($intBalanceCrypto * $intRate,2) ;

	}else{
		$intErrorCode = 0 ;
		$strError_send = $strError_send." ".$strSendErr ;
	}

	//header( 'Location: '. $Form_PageFrom.'?error_send='.$strError ); die();
	echo $intErrorCode.",".$strError_send.",".$intBalanceCrypto.",".$intBalanceUSD ;

}

?>
