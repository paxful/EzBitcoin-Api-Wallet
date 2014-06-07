<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);


$strLiveFlag = 1 ;
$file = __ROOT__.'/cp/listaddresses.json' ;


	//get a list of all addresses as a JSOn object via RPC
	$strJSONobj = funct_Billing_JSONRPC_ListAccounts(0);

	//write json to text file
    $file_obj = fopen(__ROOT__.$file,'w+'); 
    
	//$decodedJSON = json_decode($strJSONobj, TRUE);
	//var_dump($strJSONobj);
	//print_r($decodedJSON);
	
	echo " <a href='".$file."' >file active addresses</a><br><br>";
	
	foreach($strJSONobj as $address=>$balance) { 
		$strAddress = trim($address);
		$strLine = $strLine." \n ".$strAddress;
	}
	
	//write to file
    fwrite($file_obj, $strLine ); fclose($file_obj);

	echo $strLine ;

?>