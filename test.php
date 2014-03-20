<?
include $_SERVER['DOCUMENT_ROOT']."/constants.php";

// test calls to RPC server
 
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


$strAuth = "&loginname=coincafe&password=coincafe";
?>
<h1>testing blockchain api via bitcoind rpc</h1><br>
<a href="/merchant/?do=new_address&address=&label=testing public note&label2=testing note2&label3=testingnote3<?=$strAuth?>" target="_blank">
make new address</a><br><br>

<a href="/merchant/?do=sendtoaddress&address=19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK&amount=0.0002&comment=testing send to dewd&commentto=sup bro<?=$strAuth?>" target="_blank">
send</a><br><br>

<a href="/merchant/?do=sendfromaddress&address=19LgQ83sFkudy9qmA5Ub9zbG1SVDYVmqsK&amount=200&from=1GQ3cstjtPrhf9yJwp9332QjDaQoSpyb82&comment=from coincafe&commentto=to ray<?=$strAuth?>" target="_blank">
send from</a><br><br>

<a href="/merchant/?do=callback&txid=<?=$strAuth?>" target="_blank">
callback</a><br><br>

<a href=""></a><br><br>


