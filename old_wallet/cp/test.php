<?
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

// test calls to RPC server

//http://5.153.60.162/merchant ?do=
//login: coincafe p:coincafe
//this file is called  on each receive and then does  callback to a remote url
//this file can also be called to generate new addresses, send btc, 
//later getbalance, 
 
	//get list of all addresses bitcoind listreceivedbyaddress 0 true
	//coincafe address 1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82
	//10c724bdfe52f95b482949101cc1bb3657c9f92d7f61d469a309eacbb6782d$
	//curl http://localhost/merchant/?do=callback&txid=transactionhash=%s
	
	//has .0002 1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82  coin cafe
	// .0 19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK  ray
	
//test callback
//e6e99864a6887a9508e158a9a96b8f0b2178efcd7509c73d871a0023cce23941

//test make address

//test send
//txid
//f82ef544e215a357d88995b4e590488c5f8e543992010c44d37007add59cf659

//echo "strServer=".$strServer ;

if($strServer=="dev"){
	$strAuth = "&loginname=eAsYbItZ1920&password=6d4b1LQu7hQ7&debug=1";
	
}else{ //production
	$strAuth = "&loginname=eAsYbItZ1920&password=6d4b1LQu7hQ7&debug=";
}

$strAddress = "1m4KgDUXuuMEBJV6JBGrwJ4kfT4PjsFU8" ; //local. blockchain.info
$strTXID = "d3de9c8d5ed75ca9d265f5b4581795d002234246f19dafe4d83b17661a4e3473";
// "a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1";

?>
<h1>testing blockchain api via bitcoind rpc</h1><br>

<a href="/merchant/?do=getbalance&account=<?//=$strAddress?><?php $strAuth?>" target="_blank">
getbalance</a><br><br>

<a href="/merchant/?do=validate_transaction&txid=<?php $strTXID?><?php $strAuth?>" target="_blank">
validate_transaction</a><br><br>

<a href="/merchant/?do=validate_address&address=<?php $strAddress?><?php $strAuth?>" target="_blank">
validate_address</a><br><br>

<a href="/merchant/?do=new_address&address=&label=testing public note&label2=testing note2&label3=testingnote3<?php $strAuth?>" target="_blank">
make new address</a><br><br>

<?
$intAmount = "0.0002";
$strAddressSend = "1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL"; //ray@easybitz.com production 16dPhqxEiVhK38ctJRp6mj6oQqhJSJeNG7  // keychests BCI 1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL
$strLabel = "test send";
?>
<a href="/merchant/?do=sendtoaddress&address=<?php $strAddressSend?>&amount=<?php $intAmount?>&comment=<?php $strLabel?>&commentto=to test<?php $strAuth?>" target="_blank">
send</a><br><br>
<a href="/merchant/?do=sendfromaddress&address=19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK&amount=200&from=1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82&comment=from coincafe&commentto=toray<?php $strAuth?>" target="_blank">
send from</a><br><br>



<a href="/merchant/?do=callback&txid=<?php $strTXID?><?php $strAuth?>" target="_blank">
callback</a><br><br>

<a href="/list_transactions.php">View Transactions</a><br><br>

<?php
/* */

$strDo = funct_GetandCleanVariables($_GET['do']);

if($strDo=="call"){
	//test call internal url
	$strCallbackURL="https://10.68.9.138/~easybitz/mods/processorder.php";
	//call processorder script
	$json_url = $strCallbackURL."?secret=$strSecret&transaction_hash=$strTransactionID&address=$strAddress&input_address=$strAddress&userid=$strLabel2&value=$intAmount&confirms=$intConfirmations&server=amsterdam";
	echo "<br>url: $json_url <br>";
	$json_data = file_get_contents($json_url);
	echo "data = $json_data <br>" ;
	$json_feed = json_decode($json_data);
	$strCallbackResponse = $json_data;
	echo "strCallbackResponse = $strCallbackResponse <br>";
}





$strTransaction = funct_GetandCleanVariables($_GET['txid']);
//$strTransaction = "d3de9c8d5ed75ca9d265f5b4581795d002234246f19dafe4d83b17661a4e3473";
 //echo $strTransaction ;
 
if($strTransaction){ 
  	//get transaction info as JSON object, only for local transactions
  	$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);
  	$trxinfo = $bitcoin->gettransaction($strTransaction);
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
	."\n fee: ".$trxinfo["details"][0]["fee"]  
	;// According to https://en.bitcoin.it/wiki/Original_Bitcoin_client/API_calls_list, fee is returned, but it doesn't seem that way here
	echo nl2br($new)."<br><br><br>";	
  	
  	
  	//if we want the from address and more detail we can get the raw transaction, decode it, extract the values from Json and get more info
	//Enable txindex=1 in your bitcoin.conf (You'll need to rebuild the database as the transaction index is normally not maintained, start using -reindex to do so), and 
  	//use the getrawtransaction call to request information about any transaction 
  	$strRawHex = $bitcoin->getrawtransaction($strTransaction);
  	$objJSON = $bitcoin->decoderawtransaction($strRawHex);
	//print_r($objJSON)."<br><br>";
	$trxinfo = $objJSON;
	$json_string = json_encode($objJSON, JSON_PRETTY_PRINT);
	//$trxinfo = json_decode($objJSON);
	//print_r( ($json_string))."<br><br>";
	echo "input 1 txid: ".$trxinfo["vin"][0]["txid"]."<br>";
	echo "output 1 amt: ".$trxinfo["vout"][0]["value"]."<br>";
	echo "output 1 address: ".$trxinfo["vout"][0]["scriptPubKey"]["addresses"][0]."<br>";
	echo "output 2 amt: ".$trxinfo["vout"][1]["value"]."<br>";
	echo "output 2 address: ".$trxinfo["vout"][1]["scriptPubKey"]["addresses"][0]."<br>";
	echo "<br>";
	
	
	//get info for input transaction
	$strTXID_input = $trxinfo["vin"][0]["txid"];
  	$strRawHex = $bitcoin->getrawtransaction($strTXID_input);
  	$objJSON = $bitcoin->decoderawtransaction($strRawHex);
	//print_r($objJSON)."<br><br>";
	$trxinfo = $objJSON;
	$json_string = json_encode($objJSON, JSON_PRETTY_PRINT);
	//$trxinfo = json_decode($objJSON);
	print_r( ($json_string))."<br><br>";
	echo "input 1 txid: ".$trxinfo["vin"][0]["txid"]."<br>";
	echo "output 1 amt: ".$trxinfo["vout"][0]["value"]."<br>";
	echo "output 1 address: ".$trxinfo["vout"][0]["scriptPubKey"]["addresses"][0]."<br>";
	echo "output 2 amt: ".$trxinfo["vout"][1]["value"]."<br>";
	echo "output 2 address: ".$trxinfo["vout"][1]["scriptPubKey"]["addresses"][0]."<br>";
	echo "<br>";
}
/* */
?>