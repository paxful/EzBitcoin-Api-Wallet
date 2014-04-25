<?php


function funct_Billing_JSONRPC_GetInfo($strConnectionString){ //works
//get info about wallet as | delimited string

	if(!$strConnectionString){ $strConnectionString = JSONRPC_CONNECTIONSTRING ; }
	//echo "conn = $strConnectionString <br>";

	$mybtc = new jsonRPCClient($strConnectionString);
	$strReturnInfo = $mybtc->getinfo();
	//echo "array=$strReturnInfo<br>";
	
	foreach($strReturnInfo as $key => $value){
    	//echo $key."=".$value."<br>";
    	$strReturn = $strReturn.$key."=".$value."|";
   	}
	return $strReturn ;
}
function funct_Billing_JSONRPC_SetLabel($strWalletAddress, $strLabel){ //works
//change the label for an address. give address and label, get back bolean
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	$strReturnInfo = $mybtc->setaccount($strWalletAddress, $strLabel);
	return $strReturnInfo ;
}
function funct_Billing_JSONRPC_GetLabel($strWalletAddress){ //works
//give address and get back label
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	$strReturnInfo = $mybtc->getaccount($strWalletAddress);
	return $strReturnInfo ;
}
function funct_Billing_JSONRPC_GetAccountByLabel($strLabel){ //works
//give a label string, get back the first matching address
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	$strWalletAddress = $mybtc->getaccountaddress($strLabel);
	return $strWalletAddress ;
}
function funct_Billing_JSONRPC_ValidateAddress($strWalletAddress){ //works
//validate address, returns json obj
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING); //- ** requires second password
	$objJSON= $mybtc->validateaddress($strWalletAddress) ;
	foreach($objJSON as $key => $value){ 
    	$strReturn = $strReturn.$key."=".$value."|";
   	}
	return $strReturn ;
}

function funct_Billing_JSONRPC_GetBalance($strWalletAddress, $intConfirmationsCountMin){
//give address and minimum confirmations it must have and get the balance of that address
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	if(!$strWalletAddress){$strWalletAddress='*';}
	if(!$intConfirmationsCountMin){$intConfirmationsCountMin=0;}
	$intBalance= $mybtc->getbalance($strWalletAddress, $intConfirmationsCountMin) ;
	return $intBalance ;
}

function funct_Billing_JSONRPC_ListAccounts($intConfirmationsCountMin){
//give address and minimum confirmations it must have and get the balance of that address
	if(!$intConfirmationsCountMin){$intConfirmationsCountMin=0;}
	
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	try{
	  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
	  	$objJSON= $mybtc->listaccounts($intConfirmationsCountMin) ;
	  	return $objJSON ;
	  
	} catch(Exception $e){
	  echo nl2br($e->getMessage()).'<br />'."\n"; 
	  
	}
}

function funct_Billing_JSONRPC_ListTransactions($strWalletAddress){
//give address and get all transactions for that address
	
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING);
	try{
	  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
	  	$objJSON= $mybtc->listtransactions($strWalletAddress) ;
	  	return $objJSON ;
	  
	} catch(Exception $e){
	  	echo nl2br($e->getMessage()).'<br />'."\n"; 
	}
}




function funct_Billing_JSONRPC_Move($strWalletAddressFrom,$strWalletAddress,$intAmount){
//send to an address from an address
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING); //- ** requires second password
	try{
	  	//return print_r($mybtc->listaccounts($intConfirmationsCountMin) );
	  	$strResponse= $mybtc->move($strWalletAddressFrom,$strWalletAddress,$intAmount) ;
	  	return $objJSON ;
	  
	} catch(Exception $e){
	  	$strResponse= $e->getMessage();
	}
	
	return $strResponse ;
}
function funct_Billing_JSONRPC_GetNewAddress($strWalletAddressForward){ //Important
//give label and get back new receiving address in wallet
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING) ; //- ** requires second password
	if(!$strWalletAddressForward){$strWalletAddressForward='*';}
	$strWalletAddress= $mybtc->getnewaddress($strWalletAddressForward) ;
	return $strWalletAddress ;
}
function funct_Billing_JSONRPC_SendTo($strWalletAddress,$intAmount){
//Send amount from the server's available balance.
//echo "1 ";
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING); //- ** requires second password
//echo "$strWalletAddress,$intAmount";
	$strTransactionID= $mybtc->sendtoaddress($strWalletAddress,$intAmount) ;
//echo "json=".json_decode($strTransactionID);

	return $strTransactionID ;
}
function funct_Billing_JSONRPC_SendFrom($strWalletAddress,$intAmount,$strFromAddress){
//send to an address and specify sent from as well
	$mybtc = new jsonRPCClient(JSONRPC_CONNECTIONSTRING); //- ** requires second password
	$strWalletAddress= $mybtc->sendfrom($strFromAddress,$strWalletAddress,$intAmount) ;
	return $strTransactionID ;
}

?>