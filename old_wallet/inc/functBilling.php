<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);



function funct_Billing_GetBalance($strAccount){ 

	$guid=urlencode(JSONRPC_API_LOGIN);
	$firstpassword=urlencode(JSONRPC_API_PASSWORD);
	$strAccount = urlencode($strAccount);
	$json_url = JSONRPC_API_MERCHANT_URL."?do=getbalance&loginname=$guid&password=$firstpassword&account=$strAccount";
	$strReturnError = file_get_contents($json_url);
	return $strReturnError ;
}

function funct_Billing_ValidateTransactionHash($strTransactionHash){ 

	$guid=urlencode(JSONRPC_API_LOGIN);
	$firstpassword=urlencode(JSONRPC_API_PASSWORD);
	$strAddress = urlencode($strAddress);
	$json_url = JSONRPC_API_MERCHANT_URL."?do=validate_transaction&loginname=$guid&password=$firstpassword&txid=$strTransactionHash";
	$strReturnError = file_get_contents($json_url);
	
	//echo "url: $json_url" ;
	//site just spits our normal code now not json...
	//$json_data = file_get_contents($json_url);
	//$json_feed = json_decode($json_data);
	//print_r($json_feed);
	//$intIsValid = $json_feed->isvalid;

	return $strReturnError ;
}


function funct_Billing_ValidateAddress($strAddress){ 
//get our account balance - restricted to getcoincafe.com server ip address 162.144.93.87
//called by /mods/sendcrypto.php /mods/processorder2.php
	
	$guid=urlencode(JSONRPC_API_LOGIN);
	$firstpassword=urlencode(JSONRPC_API_PASSWORD);
	$strAddress = urlencode($strAddress);
	$json_url = JSONRPC_API_MERCHANT_URL."?do=validate_address&loginname=$guid&password=$firstpassword&address=$strAddress";
	
	//echo "url: $json_url" ;
	//site just spits our normal code now not json...
	$json_data = file_get_contents($json_url);
	$json_feed = json_decode($json_data);
	//print_r($json_feed);
	
	$intIsValid = $json_feed->isvalid;
	$strAddress = $json_feed->address;
	$intIsMine = $json_feed->ismine;
	//$intIsValid = $json_feed["isvalid"] ;
	
	//if the call to the server failed then mark as bad
	if(!$json_feed){ $strReturnError="noconnect"; }
	
	//if the address is good then return back 
   	if($intIsValid=="1"){ 
   		$strReturnError="good"; 
   	}else{
   		//else if address is not 1 and NOT a failed connection then it is a bad address
	   	if($strReturnError!="noconnect"){$strReturnError="bad";}
   	}
   	
   	//if the address is owned by the server then set the return  equal to the address passed
   	if($intIsMine==$strAddress){ $strReturnError="mine"; }
   	
   	//should tell us if address is good, address is on server, address is bad or if the call failed
	//our api returns back either 1 for valid or the address if our account owns it or noconnect if reeaching the server failed.
	return $strReturnError ;
	//noconnect , bad, good, mine
}	



//################### WALLET FUNCTIONS BEGIN #######################################

function funct_Billing_NewWalletAddress( $strLabel, $strCrypto_Code ){ //create a new wallet address, returns address as lone string
	//creates a new address via webapi 

//api call should take the crypto coin type

	//http://5.153.60.162/merchant/?do=new_address&address=&label=testing%20public%20note&label2=testing%20note2&label3=testingnote3&loginname=coincafe&password=coincafe
	$guid=urlencode(JSONRPC_API_LOGIN);
	$firstpassword=urlencode(JSONRPC_API_PASSWORD);
	$strLabel = urlencode($strLabel);
	$json_url = JSONRPC_API_MERCHANT_URL."".$guid."/new_address?password=$firstpassword&label=$strLabel&cryptotype=$strCrypto_Code";
	echo "$json_url <br>";
	//site just spits our normal code now not json...
	$json_data = file_get_contents($json_url);
	$json_feed = json_decode($json_data);
	$address = $json_feed->address;
	$label = $json_feed->label;
	$error = $json_feed->error;
	return $address ;
}



function funct_MakeWalletAddressUpdate($intUserID, $strCrypto_Code){
//create a new wallet address

    global $DB_LINK ; //Allows Function to Access variable defined in constants.php ( database link )

    //get info from database for member with hash get their id
    $query="SELECT * FROM " . TBL_USERS . " WHERE id= '".$intUserID."' " ;
    //echo "SQL STMNT = " . $query .  "<br>";
    $rs = mysqli_query($DB_LINK, $query);// or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
        $strNameFirst=				$row["first_name"]; 		//important
        $strNameLast=				$row["last_name"]; 			//important
        $strEmail=					$row["email"]; 				//important
        $strPhone=					$row["cellphone"]; 			//important
        $intCountryID= 				$row["country_id"];
        $intCountryPhoneCode= 		$row["country_phonecode"];
        //$strWalletAddress_BTC=		$row["wallet_btc"]; 		//their own personal wallet to forward btc to
        $intBalance=				$row["balance"]; 			//important
        $intBalanceBTC_old=			$row["balance_btc"];
        $intEarnedTotal=			$row["total_earned"];
        $strName = 	$strNameFirst." ".$strNameLast;
    }

    /* make wallet receiving address or full wallet */
    //get their custom wallet address - Blockchain.info  or local bitcoin qt json rpc
    $strWalletLabel = AlphaNumericOnly_RepaceWithSpace($strEmail);


    //create the new wallet address via json rpc
    //https://github.com/goethewins/EzBit-BitCoin-API--Wallet
    $strWallet_Address = funct_Billing_NewWalletAddress($strEmail);

    if($strWallet_Address){

        //update database with new wallet hash code
        $query="UPDATE " . TBL_USERS . " SET ".
            $strSQLUpdate.
            " wallet_receive_on = 1 ,  ".
            " wallet_address= '$strWallet_Address' ".
            " WHERE id=".$intUserID ;
        //echo "SQL STMNT = " . $query .  "<br>";
        mysqli_query($DB_LINK, $query);// or die(mysqli_error());


        //add record to wallet addresses table TBL_WALLET_ADDRESSES
        $query = "INSERT INTO ".TBL_WALLET_ADDRESSES.
            " ( user_id, 	wallet_address,	 		date_created ) VALUES ".
            " ( $intUserID,	'$strWallet_Address',	NOW() 	  ) " ;
        //echo "SQL STMNT = " . $query .  "<br>";
        mysqli_query($DB_LINK, $query); //$intWalletID = mysqli_insert_id($DB_LINK);


        //add record to balances table - must only be on record per crypto type
        //this record is needed only if their is multiple crypto alt coin support
        //first see if there is already a record for this crypto type in the database
        $query="SELECT * FROM " .TBL_WALLET_BALANCES. " WHERE currency_code= '".$strCrypto."' AND userid=".$intUserID."" ;
        //echo "Rate Select SQL = " . $query .  "<br>";
        $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
        if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
            //record found


        }


        if(!$strCrypto_Code){$strCrypto_Code="btc" ;}
        $query = "INSERT INTO ".TBL_WALLET_BALANCES.
            " ( user_id, 	currency_type, 	currency_code,	    balance	) VALUES ".
            " ( $intUserID,	'crypto',	    '$strCrypto_Code',  0 ) " ;
        //echo "SQL STMNT = " . $query .  "<br>";
        mysqli_query($DB_LINK, $query); //$intWalletID = mysqli_insert_id($DB_LINK);


        //make QR Code and save to their directory - google, phpapi
        if($strWallet_Address){
            $strQRcodeIMG = PATH_QRCODES.$strWallet_Address.".png";
            $strError = funct_Billing_GetQRCodeImage( $strWallet_Address, $strQRcodeIMG ); //save img to disk
        }

        //update database with successful code creation
        $query="UPDATE " . TBL_USERS . " SET flag_qrcodeimg=1 WHERE id=".$intUserID ;
        //echo "SQL STMNT = " . $query .  "<br>";
        mysqli_query($DB_LINK, $query);// or die(mysqli_error());

    }


    return $strWallet_Address ;
}



//!Send BTC CoinCafe
function funct_Billing_SendBTC($strToAddress, $intToAmount, $strNote, $intMiningFee, $strFrom, $strLabel,$strLabel2,$strLabel3 ){

	//logic
	if(!$intMiningFee){$intMiningFee = MININGFEE_NORMAL;}
	//convert to satoshi NO CC takes REAL decimals
	//$intToAmount = $intToAmount * 100000000 ;
	//$intMiningFee = $intMiningFee * 100000000 ;
	//if we specify from address then they must be on blockchain!
	//if(SECURITY_ON_BLOCKCHAIN_ALLOWED<1 AND !$strFrom){ $strFrom=BLOCKCHAIN_SENDFROMADDRESS ;} //if onblockchain not allowed then set from to nothing
	//BUG - if any from address is set at all then we get a "no available imputs" errors
	//$strFromSQL = "&from=".$strFrom ;	
	
	//send code
	$guid=urlencode(JSONRPC_API_LOGIN); 
	$main_password=urlencode(JSONRPC_API_PASSWORD); 
	$secret=urlencode(JSONRPC_API_SECRET); 
	$strToAddress = urlencode($strToAddress); 
	$intToAmount = urlencode($intToAmount);
	$intMiningFee = urlencode($intMiningFee); 
	$strNote = urlencode($strNote); 
	$strFrom = urlencode($strFrom); 
	$strLabel = urlencode($strLabel); 
	$strLabel2 = urlencode($strLabel2); 
	$strLabel3 = urlencode($strLabel3); 
	//http://local.ccapi/merchant/?do=sendtoaddress&address=1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL&amount=0.0002&comment=test%20send&commentto=to%20test&loginname=d4sd6ejmyiCwEM7UMb&password=u7hQ7IzP9o6sOCrJr&debug=1
	$json_url = JSONRPC_API_MERCHANT_URL."?do=sendtoaddress".
	"&address=$strToAddress&amount=$intToAmount&fee=$intMiningFee&comment=$strNote&commentto=$strNote&label=$strLabel&label2=$strLabel2&label3=$strLabel3".
	"&loginname=$guid&password=$main_password&secret=$secret";
	//echo "url=".$json_url."<br>";
	$json_data = file_get_contents($json_url);
	$json_feed = json_decode($json_data);
	$message = $json_feed->message;
	$txid = $json_feed->tx_hash;
	$error = $json_feed->error;
	//{"message":"*ok*","tx_hash":"cb646bc87b076e5185029f23fd5d93e852ea98964b48178cf6441aaa0232222f","error":"cb646bc87b076e5185029f23fd5d93e852ea98964b48178cf6441aaa0232222f"}
	return $message."|".$error."|".$txid ; //
}




//################### WALLET FUNCTIONS END #######################################








//################## GET RATES ###################################################
function funct_Billing_GetRate($strCrypto,$strExchange){
	
	global $DB_LINK ; //needed for all db calls
	
	if(!$strCrypto){ $strCrypto="btc"; }
	if(!$strExchange){ $strExchange=RATE_HUD_EXCHANGE; }
	$intTime_FreshSeconds = RATE_REFRESH_SECONDS ;
	$query="SELECT * FROM " .TBL_RATES. " WHERE crypto= '".$strCrypto."' AND exchange='".$strExchange."'" ;
	//echo "Rate Select SQL = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
		$intRate =					$row["rate"];
		$intTimeLast =				$row["date"];
	}
	$intTimeDiffRate = time() - $intTimeLast ;
	
	if($intTimeDiffRate>$intTime_FreshSeconds){ 
		$intRate = funct_Billing_UpdateRate($strCrypto,$strExchange);
	}
	
	//custom modifers
	if(RATE_MINIMUM_SELL AND $intRate<RATE_MINIMUM_SELL){ $intRate = RATE_MINIMUM_SELL ; }
	$intRate = $intRate + rand(0,RATE_RANDOMIZER_MAX) ;
	
	return $intRate ;

}
function funct_Billing_UpdateRate($strCrypto,$strExchange){

	global $DB_LINK ; //needed for all db calls

	//call right function for  exchange
	if(!$strCrypto){ $strCrypto= "btc" ;}
	
	if(!$strExchange){ $strExchange= RATE_HUD_EXCHANGE ;}
	if($strExchange=="gox"){ $intRate= funct_Billing_GetBTCPrice_Gox() ; }
	if($strExchange=="coindesk"){ $intRate= funct_Billing_GetBTCPrice_CoinDesk() ;}
	if($strExchange=="bitstamp"){ $intRate= funct_Billing_GetBTCPrice_BitStamp() ;}

     //RATE HIKE
     $intRate = $intRate * ( 1 + RATE_HIKE_PERCENT ) ;
     $intRate = $intRate + RATE_HIKE_LUMP ;
	
	if($intRate){ //update database record
		$intNow=time(); //unix int timestamp
		$query="UPDATE " .TBL_RATES. " SET ".
		" rate= '$intRate' , ".
		" date= $intNow ".
		" WHERE crypto='".$strCrypto."' AND exchange='".$strExchange."'" ;
		//echo "Update Rates SQL = " . $query .  "<br>";
		mysqli_query($DB_LINK, $query);
	}
	
	return $intRate ;
}
function funct_Billing_GetBTCPrice_Gox() { //convert BTC to currency
	//$strCurrency = 'USD';//set currency
	$return = file_get_contents('http://data.anxbtc.com/api/1/BTCUSD/ticker_fast');//get json response
	$info = json_decode($return, true);//decode it (into an array rather than object [using 'true' parameter])
	$intValueOne = $info['return']['last_local']['value'];//access the dollar value
	
	$strCurrency="USD"; $intBTCvalue=1;
	
	if($strCurrency=="USD"){$intTotal = $intValueOne * $intBTCvalue ; } //BTC 2 USD total is the value of 1 BTC so multiply that times the value we were passed
	//if($strCurrency=="BTC"){$intTotal = $intBTCvalue / $intValueOne ; } //USD 2 BTC value total is the value of 1 BTC so divide that times the value we were passed
		
	return round($intTotal, 7) ; //round out to 3 decimal places
	//echo "[ <strong>$intTotal</strong>] <br>";
}
function funct_Billing_GetBTCPrice_BitStamp(){
	
	$strUrl = 'https://www.bitstamp.net/api/ticker/';
	$json_string = file_get_contents($strUrl);//get json response
	//{"high": "839.74", "last": "820.12", "timestamp": "1390290726", "bid": "820.03", "volume": "7117.94976314", "low": "815.00", "ask": "820.12"}
	
	$data = json_decode($json_string, TRUE);
	$strValue = $data['last'];
	
	return $strValue ;
}
function funct_Billing_GetBTCPrice_CoinDesk(){
	
	$strUrl = 'http://api.coindesk.com/v1/bpi/currentprice.json';
	$json_string = file_get_contents($strUrl);//get json response
	//$json_string = '{"time":{"updated":"Jan 15, 2014 08:36:00 UTC","updatedISO":"2014-01-15T08:36:00+00:00","updateduk":"Jan 15, 2014 at 08:36 GMT"},"disclaimer":"This data was produced from the CoinDesk Bitcoin Price Index. Non-USD currency data converted using hourly conversion rate from openexchangerates.org","bpi":{"USD":{"code":"USD","symbol":"$","rate":"876.6650","description":"United States Dollar","rate_float":876.665},"GBP":{"code":"GBP","symbol":"£","rate":"534.3685","description":"British Pound Sterling","rate_float":534.3685},"EUR":{"code":"EUR","symbol":"€","rate":"642.7375","description":"Euro","rate_float":642.7375}},"exchanges":{"mtgox":"$954.95","BTC-e":"$840.00","Bitstamp":"$835.05"}}';
	
	$data = json_decode($json_string, TRUE);
	$worker_stats = $data['bpi']['USD'];
	$strValue = $worker_stats['rate'];
	
	return $strValue ;
}


function funct_Billing_UpdateRate_Fiat($strFiat){

	global $DB_LINK ; //needed for all db calls

	//get fiat rate from database
	$query="SELECT * FROM " .TBL_CURRENCY. " WHERE currency_code= '".$strFiat."'" ;
	//echo "Rate Select SQL = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
		$intRateBTC =					$row["currency_rate_BTC"];
		$intRateUSD =					$row["currency_rate_USD"];
		$intTimeLast =					$row["date"];
	}
	
	$intTimeDiffRate = time() - $intTimeLast ; 
	//echo "rate data is $intTimeDiffRate seconds old <br>";
	//if not there or too old then call it fresh OR If rate in database is 60 seconds old then get new rate
	if(!$intRateUSD OR $intTimeDiffRate>RATE_REFRESH_FIAT_SECONDS){
		//call right function for exchange
		$intRateUSD= funct_Billing_GetFiat_Rate_Google("USD",$strFiat) ;
	}
	
	if(!$intRateBTC){	//get BTC rate as well
		$query="SELECT * FROM " .TBL_RATES. " WHERE crypto= 'btc' AND exchange='".RATE_HUD_EXCHANGE."'" ;
		//echo "Rate Select SQL = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
			$intRateBTC =					$row["rate"];
		}else{ $intRateBTC = 0; }
	}
	
	if($intRateUSD){ //update database record
		$intNow=time(); //unix int timestamp
		$query="UPDATE " .TBL_CURRENCY. " SET ".
		" currency_rate_USD= $intRateUSD , ".
		" currency_rate_BTC= $intRateBTC, ".
		" date= $intNow ".
		" WHERE currency_code='".$strFiat."'" ;
		//echo "Update Rates SQL = " . $query .  "<br>";
		mysqli_query($DB_LINK, $query);
	}
	
	return $intRateUSD ;
}

function funct_Billing_GetFiat_Rate_Google($strCurrency1,$strCurrency2){
	
	//Request: http://rate-exchange.appspot.com/currency?from=USD&to=EUR
	//Response: {"to": "EUR", "rate": 0.76911244400000001, "from": "USD"}
	
	if(!$strCurrency1){$strCurrency1="USD";}
	$strUrl = 'http://rate-exchange.appspot.com/currency?from='.$strCurrency1.'&to='.$strCurrency2;
	$json_string = file_get_contents($strUrl);//get json response
	$data = json_decode($json_string, TRUE);
	$strValue = $data['rate'];
	return $strValue ;
}







//################## MEDIA functions ###################################################
function funct_Billing_GetQRCodeImage($strHash, $strSaveToPath){ //create a new qrcode image from a string
	
	$strAbsolutePath = __ROOT__.$strSaveToPath ; 
	
	//we can either use google
	//echo "calling funct_CreateQRcode($strHash, $strSaveToPath) <br>";
	$strError = funct_CreateQRcode($strHash, $strSaveToPath) ; //this function calls the php qrcode lib 
	
	if (!file_exists($strAbsolutePath)) { //if file is not found then fall back onto a second method-
		//echo "funct_CreateQRcode_Google($strHash, $strSaveToPath) <br>" ;
		$strError = funct_CreateQRcode_Google($strHash, $strSaveToPath);//google
	}
	
	if (file_exists($strAbsolutePath)) {
		$strError = $strSaveToPath ; 
	}else{ 
		$strError="error" ;
	}
	
	return $strError ;
}







function funct_CreateQRcode($strHashPaymentAddress, $pngFilePath){

    //echo "calling QRcode::png  <br>";
    $pngAbsoluteFilePath = __ROOT__.$pngFilePath ;

    // generating from php qr code lib
    QRcode::png($strHashPaymentAddress, $pngAbsoluteFilePath, "H",25,8); //25x8 = 980px
    echo "called QRcode::png - $pngAbsoluteFilePath  <br>";

    if (file_exists($pngAbsoluteFilePath)) { //if file is not found then fall back onto a second method- google
        return $pngFilePath ;
    }

}

function funct_CreateQRcode_Google($strHashPaymentAddress, $pngFilePath){

    $pngAbsoluteFilePath = __ROOT__.$pngFilePath ;

    $url = 'https://chart.googleapis.com/chart?chs=540&cht=qr&chl='.$strHashPaymentAddress.'&choe=UTF-8';
    file_put_contents($pngAbsoluteFilePath, file_get_contents($url));

    if (file_exists($pngAbsoluteFilePath)) { //if file is not found then fall back onto a second method- google
        return $pngFilePath ;
    }

}





//################### UNUSED FUNCTIONS ####################################################



?>