<?php
require "inc/session.php";

//process incoming orders. each time bitcoin is sent to a merchant this url will be accessed.
//this page will be called everytime the account Ghaleon gets btc so only use it for easybitz
$intDebugFlag = funct_GetandCleanVariables($_GET['debug']); //this I entered into the blockchain wallet form
//$intDebugFlag = 1;
$strERRORPage = "processorder2.php";

/* 
http://getcoincafe.com/mods/processorder2.php?secret=n00n3z&transaction_hash=a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1&address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&input_address=1DP1NWLQ11VDTBkjunwxJJzLDJYKLw44Jt&value=200000&confirms=3
*/

//get values from query string 
$real_secret = 				funct_GetandCleanVariables($_GET['secret']); //this I entered into the blockchain wallet form
$transaction_hash = 		funct_GetandCleanVariables($_GET['transaction_hash']); //The transaction hash.
$input_address = 			funct_GetandCleanVariables($_GET['input_address']); //The bitcoin address that received the transaction
$value_in_satoshi = 		funct_GetandCleanVariables($_GET['value']);
$intNewCallBackID = 		funct_GetandCleanVariables($_GET['callbackid']); //callback id of script that called it.. so we can amtch the callback log to the transaction when it updates


//coincafe.co amsterdam sends confirms as well so we need to be able to handle updates
$confirmations = 			funct_GetandCleanVariables($_GET['confirms']); //The bitcoin address that received the transaction
$intUserID = 				funct_GetandCleanVariables($_GET['userid']);
$strServer = 				funct_GetandCleanVariables($_GET['server']);



//if zero satoshi value then kill script
if(!$value_in_satoshi OR $value_in_satoshi<=0){ echo "satoshi:".$value_in_satoshi ; die; }


//############ % security checks //###################################
//if coming from the loaded send from address then skip this transaction
//this is our hack to keep uses from getting free btc via blockchain.info random change address bug!
if($input_address==BLOCKCHAIN_SENDFROMADDRESS){ echo "ignore" ; die; }

//checks secret key
//echo "real_secret=$real_secret - bc secret=".BLOCKCHAIN_SECRET."<br>";
//COINCAFE_API_SECRET
//if($real_secret!=BLOCKCHAIN_SECRET){ echo "secretmismatch $real_secret"; die; }

//verify that the transaction is real ... call our bitcoind for LIVE only
$strValidTransaction = funct_Billing_ValidateTransactionHash($transaction_hash);
if($strValidTransaction!="good"){ echo "transactionnotinbitcoind"; die; } //transaction is not valid

//this is only for our own bitcoind server on amsterdam
if($confirmations OR $strServer){	
	//verify that the address is real and ( exists on our bitcoind server Live Only )
	$strValidAddress = funct_Billing_ValidateAddress($input_address);
	if($strValidAddress=="bad" ){ echo "badaddress"; die; } //address is not valid
	if($strValidAddress!="mine"){ echo "addressnotinbitcoind"; die; } //address is not owned by our bitcoind
}
/* */
//####################################################################




//search order table for transaction hash to avoid duplicates!
	//** now duplicates are ok.. we can just update the confirmations count...
	//
$query="SELECT * FROM " . TBL_TRANSACTIONS . " WHERE hash_transaction= '".$transaction_hash."' " ;
if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }
//if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."select order sql = " . $query .  " \n <br>";}
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	$intTransactionID=					$row["transaction_id"]; 				//important
	$intUserID=							$row["user_id"];
	
	
	//instead of killing the page.... we see if it is passing a # of confirmations..
	if($confirmations){ //only coincafe.co passes back confirm count walletnotify fires at least twice... on 0 and 6 confirms
		//if it is passing the # of confirmations then we update the transaction confirm count.
		//update record as a success
		if($intOrderID){ 
			$query="UPDATE " .TBL_TRANSACTIONS. " SET ".
			" confirmations=$confirmations ".
			" WHERE transaction_id=".$intTransactionID ;
			//echo "update transactions $query";
			//if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update order sql = " . $query .  " \n <br>";}
			mysqli_query($DB_LINK, $query);
		}
		
		//and... ?
		$strError = 'confirmsupdated '.$confirmations; 
		echo $strError ; die;
		
	}else{
		$strError = 'duplicateorder'; 
		echo $strError ;
		/*
		//update callback table
		if($intNewCallBackID){ 
			$query="UPDATE " .TBL_ORDERS_CALLBACKS. " SET ".
			" errorcode='$strError' ".
			" WHERE callback_id=".$intNewCallBackID ;
			if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update order sql = " . $query .  " \n <br>";}
			mysqli_query($DB_LINK, $query);
		} //the calling script /mods/processorder.php updates the db itself from the output
		*/
		die; //end page
	}
}//end if a transaction match is found/.. no trans actions found in the database
//###############




//get info from database for member with hash get their id
	//** check new amsterdam column as well 
	
	//if a userid is specified then also check that
	if($intUserID){ $strSQLuser = " OR id='$intUserID' "; }

//RECEIVER INFO 
$query="SELECT * FROM " . TBL_USERS . " WHERE wallet_btc= '".$input_address."' OR wallet_address_cc= '".$input_address."' $strSQLuser " ;
if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }
if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."select user sql = " . $query .  " \n <br>";}
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	$intUserID=					$row["id"]; 				//important
	$strEmail=					$row["email"]; 				//important
	$strPhone=					$row["cellphone"]; 			//important
	$strNameFirst=				$row["first_name"]; 		//important
	$strNameLast=				$row["last_name"]; 			//important
	
	$intCountryID= 				$row["country_id"];
	$intCountryPhoneCode= 		$row["country_phonecode"];
	//$strWalletAddress_BTC=		$row["btc_address"]; //UNUSED! their own personal wallet to forward btc to
	$intBalance=				$row["balance"]; 						//important
	
	$intBalanceBTC_old=			$row["balance_btc"]; 
	$intEarnedTotal=			$row["total_earned"]; 					//important
	$intBalance = $intBalance + $intTotalUSD ;
	$intEarnedTotal = $intEarnedTotal + $intTotalUSD ;
	$intFiatConversionPercent=	$row["fiat_conversion_percent"]; 		//how much of the coin do we convert to fiat
	$intCurrencyID=				$row["currency_id"]; 					//important
	$strCurrencyCode=			$row["currency_symbol"]; 				//important
	if(!$intCurrencyID){ $intCurrencyID= 144; $strCurrencyCode="USD"; } //USD by default
	$intCountryPhoneCode = funct_GetPhoneCountryCode($intUserID);
	
	$intFlag_sms_onget=			$row["flag_sms_on_get"]; 				//important
	$intFlag_sms_onsend=		$row["flag_sms_on_send"]; 				//important
	$intFlag_email_onget=		1;//$row["flag_email_on_get"]; 			//important
	$intFlag_email_onsend=		$row["flag_email_on_send"]; 			//important
	
	$strWallet_Receive_Show=	$row["wallet_receive_on"];
	
}else{ 
	$strError = $strError."nosuchuser";
	
		if(WALLET_LEGACY_CREDITLOSTADDRESS){
			//we may need a way to credit users who have gotten coins at an old address...
			//we now check both columns... so this is done
		}
	
	
	echo $strError ; die ;
	$intUserID = 0 ;
}


//############ LOGIC HERE #############################
//$value_in_satoshi = urldecode($value_in_satoshi);
$value_in_satoshi = abs($value_in_satoshi); // SECURITY prevent negative attacks
//$value_in_satoshi = (float) $value_in_satoshi; //why do we float this value here? no decimal..
$crypto_amt = $value_in_satoshi / 100000000;
//echo "satoshi= ".$value_in_satoshi." <br> ";

//we need to make sure this function works. a backup may be needed! Gox, blockchain, coinbase, bitpay ?
$intRate = funct_Billing_UpdateRate("btc",RATE_HUD_EXCHANGE); //bitstamp.. lower the rate the bigger the spread

//get value of BTC to convert to fiat
$intBTC_Convert2Fiat = $crypto_amt * ( 1/$intFiatConversionPercent ) ;

//calculate final BTC to add to BTC balance
$intBTC_Add2Balance = $crypto_amt - $intBTC_Convert2Fiat ;

//calculate final FIAT to add to balance
$intTotalUSD = $intRate * $intBTC_Convert2Fiat ;
//echo "userid: $intUserID";
//IF THEY have specified a different currency then we need to convert BTC to that currency 
if($intCurrencyID!=144){ //if fiat currency is NOT USD then we need to get the exchange rate and adjust the amount of fiat they earned
	
	//get fiat rate, fiat code and 
	$intFiatRate = funct_Billing_UpdateRate_Fiat($strCurrencyCode);
	
	//convert to value of btc.. we store all this into the orders table  //ex. 0.7 for usd-eur
	$intTotalFiat = $intFiatRate * $intTotalUSD ;
}else{ 
	$intFiatRate = 1; 
	$strCurrencyCode = "USD"; 
	$intTotalFiat = $intTotalUSD; //defualt fiat is usd
}

//echo "BTC= ".$crypto_amt." <br> - USD= ".$intTotalUSD." \n <br>";
$strDebugSqlTxt=$strDebugSqlTxt."BTC= ".$crypto_amt." - USD= ".$intTotalUSD." \n <br>";
//##########################################
//echo $strDebugSqlTxt ;


//add a new transaction record 
//btc recieved (amt_btc), btc2 add to btc balance, btc 2 convert to fiat, btc2usd (rate), btc recieved worth in usd (amt_usd), worth in fiat (fiat_earned), btc2 (fiat_rate) 
if($intUserID){  //
	$intTime = time();
	if(!$intFiatRate){$intFiatRate=0;} //set to 0 if it does not exist, to keep insert statement from crashing
	$intforward2wallet = 0 ;
	$strCryptoCode = "btc" ;
	$balance_prev = $intBalanceBTC_old;
	$balance_curr = $balance_prev + $crypto_amt;
	$strNameReceiver = $strNameFirst." ".$strNameLast;
	$strWalletLocation = ""; //either blockchain.info OR coincafe.co
	$query = "INSERT INTO ".TBL_TRANSACTIONS.
	" ( status,	fiat_amt, 		credit,		crypto_amt, balance_prev,	balance_curr,	type,	fiat_type, 			fiat_rate, 	user_id, 	user_id_sentto, cryptotype,			walletaddress_sentto,	walletaddress_from,	 wallet_location,		hash_transaction,	receiver_name, 		receiver_email, receiver_phone,		datetime_created,	datetime ) VALUES ".
	" ( 1, 		$intTotalFiat, 	$crypto_amt,$crypto_amt,'$balance_prev','$balance_curr','bcget','$strCurrencyCode', $intRate, 	$intUserID,	$intUserID,		'$strCryptoCode', 	'$input_address',		'',					'$strWalletLocation',	'$transaction_hash','$strNameReceiver',	'$strEmail',	'$strPhone',		$intTime,			NOW() ) " ;
	if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }
	if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."insert order sql = " . $query ." \n <br>";}
	mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), "Database insert internal receiver transaction Error. Admin has been informed $strError_send " , "$strError_send \n SQL statement failed - $query ", $strERRORPage)  ;
	//!ADD CATCH ERRORS
	$intNewOrderID = mysqli_insert_id($DB_LINK);
}

/* //update table balances
$query="UPDATE " .TBL_BALANCES. " SET ".
" currency_amt=currency_amt + $intTotalFiat ,".
" total_earned=total_earned + $intTotalFiat ".
" WHERE userid=".$intUserID." AND currency_id=".$intCurrencyID ;
//echo "SQL STMNT = " . $query .  "<br>";
if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update balacances sql = " . $query .  " \n <br>";}
mysqli_query($DB_LINK, $query);
*/

//properlyformat numbers to avoid scientitic notation
$crypto_amt = number_format($crypto_amt,8); //eliminate scientific notation

//prepare new balance for emails
$intNewBalance = $intBalanceBTC_old + $crypto_amt ;
$intNewBalance = number_format($intNewBalance,8);

//# IMPORTANT 
//update the member table with new balanace and total earned
$query="UPDATE " .TBL_USERS. " SET ".
" balance=balance + $intTotalFiat  ,".
" balance_btc=balance_btc + $crypto_amt  ,".
" total_earned=total_earned + $intTotalFiat ".
" WHERE id=".$intUserID ;
if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }
if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update users sql = " . $query .  " \n <br>";}
mysqli_query($DB_LINK, $query)  or funct_die_with_grace(mysqli_error(), "Database !! update member balance !! Error. Admin has been informed $strError_send " , "$strError_send \n SQL statement failed - $query ", $strERRORPage)  ;
//!ADD CATCH ERRORS


//update record as a success
if($intNewOrderID){
	if($intNewCallBackID){ $strSQLTransUpdate= " , callback_id= $intNewCallBackID " ;} 
	$query="UPDATE " .TBL_TRANSACTIONS. " SET ".
	" status=1 ".$strSQLTransUpdate.
	" WHERE transaction_id=".$intNewOrderID ;
	if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }
	if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update order sql = " . $query .  " \n <br>";}
	mysqli_query($DB_LINK, $query)  or funct_die_with_grace(mysqli_error(), "Database !! update transaction !! Error. Admin has been informed $query \n $strError_send " , "$strError_send \n SQL statement failed - $query ", $strERRORPage)  ;
	//!ADD CATCH ERRORS
}



/*
//###------------------------------------------
//### Move funds from blockchain address to our main blockchain address to avoid change back bug
$strWalletAddressFrom = $input_address ;
$strWalletAddressTO = BLOCKCHAIN_SENDFROMADDRESS ;
$intAmount = $crypto_amt ;
$strRPCerror= funct_Billing_JSONRPC_Move($strWalletAddressFrom,$strWalletAddressTO,$intAmount);
//### -----------------------------------------
*/


//main flag overrides user flag
if(EMAIL_USER_ON_RECEIVE){ $intFlag_email_onget=$intFlag_email_onget; }else{$intFlag_email_onget=EMAIL_USER_ON_RECEIVE ;}
//send email to merchant
if($strEmail AND $intFlag_email_onget){
	$strSubject="You got Coin! $crypto_amt BTC (est. $".$intTotalUSD.") ".WEBSITENAME ; 
	$strBody="You just received $crypto_amt BTC into your ".WEBSITENAME." account. \n\n".
	" -Thank you " ;
	$strError = $strError." ".funct_Mail_simple($strEmail, $strSubject, $strBody);
}


if(EMAIL_ADMIN_ON_RECEIVE){ //send email to admin
	$strSubject="$intUserID {$strNameFirst} {$strNameLast} received $crypto_amt btc $".$intTotalUSD ; 
	$strBody= "User: $intUserID {$strNameFirst} {$strNameLast} received {$crypto_amt} BTC\n
	USD Equivalent: (${$intTotalUSD})\n\n
	Old balance: {$intBalanceBTC_old} BTC\n
	New balance: {$intNewBalance} BTC\n
	intEarnedTotal: $intEarnedTotal\n
	Email: $strEmail\n
	Phone: $strPhone\n
	Member Details: ".WEBSITEFULLURL."/cp/member_details.php?id=$intUserID\n
	Street Address: $strAddress" ;
	$strError = $strError." ".funct_Mail_simple(EMAIL_WALLETSEND, $strSubject, $strBody, EMAIL_ORDERS);
}

if($intFlag_sms_onget){//send sms message to merchant IMPORTANT
	$strTxtMessage = '+ $'.$intTotalUSD.' You got '.$crypto_amt.' Bitcoin ' ;
	$strError = $strError." ".functSendSMSswitch($strPhone, $strTxtMessage, $intCountryPhoneCode);
	if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."sms error=".$strError."<br>"; }
	//echo "fone: ".$strPhone."<br>";
}

if($intNewCallBackID AND ($strError OR $strDebugSqlTxt)){ 
	$query="UPDATE " .TBL_ORDERS_CALLBACKS. " SET ".
	" errorcode='{$strError} {$strDebugSqlTxt}' ".
	" WHERE callback_id=".$intNewCallBackID ;
	if($intDebugFlag){ echo "SQL STMNT = " . $query .  "<br>"; }	
	if($intDebugFlag){ $strDebugSqlTxt=$strDebugSqlTxt."update order sql = " . $query .  " \n <br>";}
	mysqli_query($DB_LINK, $query);
}

//unarchive that address
if(!$strWallet_Receive_Show){ //if they do not have an active receive address flag as on then aut archive their address??
//not sure why we do this, people who send funds in should not have their address shut down..
//	$strResponse = funct_Billing_UnArchiveAddress($input_address); 
}

if(!$strError){ $strError="done";}

echo $strError ;

if($intDebugFlag){ echo $strDebugSqlTxt ;}

?>