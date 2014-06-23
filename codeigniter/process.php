<?php
//dump all get requests to a text file

$file = 'callback.txt';

//$fh = fopen($file, 'w');
// Open the file to get existing content
$current = file_get_contents($file);
// Append a new person to the file
$current .= $_SERVER['REQUEST_URI']." \t";

$transaction_hash = $_GET['transaction_hash'];
$value = $_GET['value'];
$address = $_GET['address'];
$confirmations = $_GET['confirmations'];

$response = "Hash: ".$transaction_hash.
	", satoshis: ".$value.", address: ".$address.
	", confirmations: ".$confirmations. "\n";

$current .= $response ;

// Write the contents back to the file
file_put_contents($file, $current);

echo "*ok*";
?>
