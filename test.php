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

$strAuth = "&loginname=d4sd6ejmyiCwEM7UMb&password=u7hQ7IzP9o6sOCrJr&debug=1";

$strAddress = "1FTJXv8FjqyQ1HUTx7HeLxVaZTnc8E3muW" ; //local. blockchain.info

$strTXID = "aed28fbcbb8404a7010f0b5bcbfd643bbdee63a91ea4fe55660304556868a2fa";
// "a6eb6a8c2a66dbdfeb87faf820492222a80c2db3422706bdc1eb3bff0dbe8ab1";

?>
<h1>testing blockchain api via bitcoind rpc</h1><br>

<a href="/merchant/?do=getbalance&account=<?//=$strAddress?><?=$strAuth?>" target="_blank">
getbalance</a><br><br>

<a href="/merchant/?do=validate_transaction&txid=<?=$strTXID?><?=$strAuth?>" target="_blank">
validate_transaction</a><br><br>

<a href="/merchant/?do=validate_address&address=<?=$strAddress?><?=$strAuth?>" target="_blank">
validate_address</a><br><br>

<a href="/merchant/?do=new_address&address=&label=testing public note&label2=testing note2&label3=testingnote3<?=$strAuth?>" target="_blank">
make new address</a><br><br>

<?
$intAmount = "0.0002";
$strAddressSend = "1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL"; //ray@easybitz.com production 16dPhqxEiVhK38ctJRp6mj6oQqhJSJeNG7  // keychests BCI 1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL
$strLabel = "test send";
?>
<a href="/merchant/?do=sendtoaddress&address=<?=$strAddressSend?>&amount=<?=$intAmount?>&comment=<?=$strLabel?>&commentto=to test<?=$strAuth?>" target="_blank">
send</a><br><br>
<a href="/merchant/?do=sendfromaddress&address=19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK&amount=200&from=1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82&comment=from coincafe&commentto=toray<?=$strAuth?>" target="_blank">
send from</a><br><br>



<a href="/merchant/?do=callback&txid=<?=$strTXID?><?=$strAuth?>" target="_blank">
callback</a><br><br>

<a href="/list_transactions.php">View Transactions</a><br><br>

<?php
/*
$bitcoin = new jsonRPCClient(JSONRPC_CONNECTIONSTRING_CC);

	$strTransaction = "10c724bdfe52f95b482949101cc1bb3657c9f92d7f61d469a309eacbb6782d24";
  	//echo $strTransaction ;
  	//get transaction info as JSON object, only for local transactions
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

*/
?>