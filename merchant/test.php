<?

// test calls to RPC server

if($strServer=="dev"){
	$strAuth = "&loginname=&password=&debug=1";
}else{
	$strAuth = "&loginname=&password=&debug=1";
}

$strAddress = "1FTJXv8FjqyQ1HUTx7HeLxVaZTnc8E3muW" ; //local. blockchain.info

$strTXID = "aed28fbcbb8404a7010f0b5bcbfd643bbdee63a91ea4fe55660304556868a2fa";

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
$strAddressSend = "1GmEVipzfyBGQDWDije9FhvySSKHz1RjXL";
$strLabel = "test send";
?>
<a href="/merchant/?do=sendtoaddress&address=<?=$strAddressSend?>&amount=<?=$intAmount?>&comment=<?=$strLabel?>&commentto=to test<?=$strAuth?>" target="_blank">
send</a><br><br>
<a href="/merchant/?do=sendfromaddress&address=19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK&amount=200&from=1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82&comment=from&commentto=toray<?=$strAuth?>" target="_blank">
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