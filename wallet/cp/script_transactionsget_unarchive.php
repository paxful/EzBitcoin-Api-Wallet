<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;

$file = __ROOT__.PATH_TEMP.'list_transactions.json' ;
$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intUnArchivedCount = 0 ;

if($strLiveFlag){ //production get LIVE list

	//get a list of all addresses as a JSOn object via RPC
	$strJSONobj = funct_Billing_JSONRPC_ListTransactions("");

	//write json to text file
    //$file_obj = fopen($file,'w+'); fwrite($file_obj, json_encode($strJSONobj)); fclose($file_obj);
    
}else{ //reading from text file TEST
	 
	//read json file 
	$string = file_get_contents($file); $strJSONobj=json_decode($string,TRUE); //true returns array instead of json object
}

//SELECT * FROM `tbl_transactions` WHERE walletaddress_sentto>"" OR walletaddress_from>"" GROUP BY walletaddress_sentto, walletaddress_from

//$decodedJSON = json_decode($strJSONobj, TRUE);
//var_dump($strJSONobj);
//print_r($strJSONobj);
//echo "addy". $strJSONobj['transactions']['address'];

//loop through and get address
//$obj = json_decode($strJSONobj);
//$strTransactions = $strJSONobj['transactions'] ;
//print_r($strTransactions);

/* */
foreach($strJSONobj['transactions'] as $object){
//foreach($strJSONobj->key->transactions as $object) { 
	//echo $address." - ".$balance."<br>"; 
	$strAddress = 	$object['address'];
	$intBalance = 	$object['amount'];
	$strLabel = 	$object['label'];
	$strCategory = 	$object['category'];
//"transactions":[
/* {"fee":0.0001,
"amount":0.5,
"blockindex":467463,
"time":1392344865,
"category":"receive",
"confirmations":-3943,
"address":"1D1ZRsd1Pgud4sd6ejmyiCwEM7UMbEHmR2",
"txid":"873c8214dbf4a2ea3588a03d8a2dc0db7945ccefdb6733186d36c45c022f5c77",
"blockhash":"00000000000000015bb2b65f79fcaeb429d39174ed934dc2d8d6d8a20758eef3",
"account":"MAIN ADDRESS",
"label":"MAIN ADDRESS"}
*/

	if(!$strAddress){ continue; } //no address then skip
	
	$intTotalCount = $intTotalCount + 1 ;
	
	echo $strAddress." - ".number_format($intBalance,8)." - ".$strLabel." " ;
	echo " unarchiving... ".$strResponse." " ;

	
	//unarchive that address
	if($strLiveFlag){ 
		//$strResponse = funct_Billing_UnArchiveAddress($strAddress); 
	}
	
	$intUnArchivedCount = $intUnArchivedCount +1 ;
	echo " Done ".$strResponse." <br>" ;

}//end for each loop through array

echo "<br><br> <strong>TOTAL=$intTotalCount -  UnArchived=$intUnArchivedCount </strong>";

/* */
?>