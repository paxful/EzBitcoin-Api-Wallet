<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
//$strThisPage = 		PAGE_SETTINGS;
$intUserID = 			DETECT_USERID;

//check to see if user is logged in and an admin
include $_SERVER['DOCUMENT_ROOT']."/inc/checklogin.php";

//!Get QueryString Values
$strDO = 						trim($_GET["do"]);
$strDO = mysqli_real_escape_string($DB_LINK, $strDO);


//!$strDo buy_anx
if($strDO=="buy_anx"){
	$buy_anx_btc_amt =			trim($_POST["buy_anx_btc_amt"]);
	$buy_anx_btc_amt =			mysqli_real_escape_string($DB_LINK, $buy_anx_btc_amt);

	$buy_anx_btc_amt = $buy_anx_btc_amt * 100000000;


	
	//place order on anx
	$mtGoxClient = new MtGoxClient( ANX_API_KEY, ANX_API_SECRET );
	#$result = $mtGoxClient->getInfo();
	///api/2/money/bitcoin/send_simple
	#$result = $mtGoxClient->getWalletHistory();
	#$result = $mtGoxClient->setPair('BTCEUR')->getCurrency();
	#$result = $mtGoxClient->orderAdd('bid',$buy_anx_btc_amt,'',time() );
	$result = $mtGoxClient->orderAdd('bid',$buy_anx_btc_amt,'',time() );
	echo "$result = $mtGoxClient->orderAdd('bid',$buy_anx_btc_amt,'',time() )";
	#$result = $mtGoxClient->orderCancel('4d484979-6237-4e66-b76d-4e8085a8b8be');
	#$result = $mtGoxClient->getDepositAddress();
	#$result = $mtGoxClient->generateDepositAddress();
	#$result = $mtGoxClient->getOrders();
	print_r($result);
	$strBTCBalance = $result['data']['Wallets']['BTC']['Balance']['display'];
	$strUSDBalance = $result['data']['Wallets']['USD']['Balance']['display'];


	//lookup settled trade on anx
	
	//insert record into trades table





}



//!$strDo update_balance
if($strDO=="update_balance"){
	$bal_cold_entered =			trim($_POST["Cold wallet"]);
	$bal_cold_entered =			mysqli_real_escape_string($DB_LINK, $bal_cold_entered);
	$bal_anx_entered =			trim($_POST["ANX"]);
	$bal_anx_entered = 			mysqli_real_escape_string($DB_LINK, $bal_anx_entered);
	$bal_bitstamp_entered =		trim($_POST["Bitstamp"]);
	$bal_bitstamp_entered =		mysqli_real_escape_string($DB_LINK, $bal_bitstamp_entered);


	//Update the balance in the accounts table
	if($bal_hot_entered) {
		$query="UPDATE ".TBL_ACCOUNTS." SET balance=$bal_cold_entered WHERE id=2" ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}
	if($bal_hot_entered) {
		$query="UPDATE ".TBL_ACCOUNTS." SET balance=$bal_anx_entered WHERE id=3" ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}
	if($bal_hot_entered) {
		$query="UPDATE ".TBL_ACCOUNTS." SET balance=$bal_bitstamp_entered WHERE id=4" ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}


}//END $strDo update_balance




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

	<title>Members</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
    <link rel="stylesheet" href="/webicons-master/webicons.css" />
    <script src="/js/modernizr.js"></script>

	<? if(!$intJquery){ $intJquery=1;?><script src="<?=JQUERYSRC?>" type="text/javascript"></script><? } ?>

<script type="text/javascript">

	$(document).ready(function(){
	

	
	}); //close ready function


</script>

</head>


<body onLoad="<?=$strOnBodyLoadJS?>">

<?php include __ROOT__.PATH_ADMIN."hud.php"; 

$strOrderBySTMT = " date_joined	 DESC ";
//$intLastMSGID = 0 ; $intMaxRecords = 1000 ;

$query="SELECT SUM(balance_btc) AS sum_balance_btc ".
" FROM ".TBL_USERS." ".
" WHERE id>0 AND balance_btc>0 AND admin<1";
//echo "<label>SQL STMNT = " . $query .  "</label><br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
$row = mysqli_fetch_assoc($rs);
    $user_balances=					$row["sum_balance_btc"];




$guid = BLOCKCHAIN_GUID ;
$password=BLOCKCHAIN_PASSWORD1;

//send command and get response
$json_url = "https://blockchain.info/merchant/$guid/balance?password=$password" ;
$json_data = file_get_contents($json_url);//send
$data = json_decode($json_data, TRUE);
$strValue = $data['balance'];
$bal_hot = $strValue/100000000;
////echo $bal_hot ;



//Update accounts table with balance of Hot Wallet
if($bal_hot){
	$query="UPDATE ".TBL_ACCOUNTS." SET balance=".$bal_hot." WHERE account_id=1" ;
	//echo "<label>SQL STMNT = " . $query .  "</label><br>";
	mysqli_query($DB_LINK, $query) or die(mysqli_error());
}




//Get some information from the orders table
/* $query="SELECT * FROM " . TBL_ORDERS . " WHERE orderid=" . $orderid." " ; */
$query="SELECT sum(tbl_orders.crypto_sold)
FROM tbl_orders
WHERE crypto_sold > 0
 AND (status_id <> 8 AND status_id <> 31)
 AND time > 1394629617
" ;
//echo "<label>SQL STMNT = " . $query .  "</label><br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_row($rs); }

$sum_crypto_pending=					$row["0"];


$total_liabilities = $user_balances + $sum_crypto_pending;


?>

<p></p>

<div class="row">


<!-- 	BEGIN ASSETS COLUMN ON THE LEFT -->
	<div class="small-12 medium-6 columns">
		<center><h4><strong>Assets</strong></h4></center>
	    <table width="100%">
			<!-- !Get data from database to display wallet balances -->
			<?php
			$strOrderBySTMT = " date DESC ";
			$query="SELECT * FROM ".TBL_ACCOUNTS
			//." WHERE account_id=$account_id and (type='BTC Wallet' OR type='Exchange')"
			." ORDER BY account_id" ;
			//echo "<label>SQL STMNT = " . $query .  "</label><br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
			$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
			//begin loop
			while($row = mysqli_fetch_assoc($rs)){
				$name=							$row["name"];
				$balance=						$row["balance"];
			?>
			    <tr>
					<td>
						<?php
						
							switch ($name){
								case "Hot wallet":
									echo "<a href=".'"'."https://blockchain.info/wallet/6d78fc5f-559e-4fab-b0fb-6ca9803208f1".'"'." target=".'"'."_blank".'"'.">$name</a>: ";
									break;

								case "Cold wallet":
									echo "<a href=".'"'."https://blockchain.info/wallet/b43427dd-a5c4-453b-a44a-ee9d351b4c6c".'"'." target=".'"'."_blank".'"'.">$name</a>: ";
									break;

								case "ANX":
									echo "<a href=".'"'."https://anxbtc.com/".'"'." target=".'"'."_blank".'"'.">$name</a>: ";
									break;

								case "Bitstamp":
									echo "<a href=".'"'."https://www.bitstamp.net/account/login/".'"'." target=".'"'."_blank".'"'.">$name</a>: ";
									break;

								case $name:
									echo $name.":";
									break;
								
							} //END switch

						?>
					</td>
					<td>
						<form data-abide action="?do=update_balance" method="POST">
						<input name="<?=$name?>" type="number" id="right-label" value="<?=$balance?>" placeholder="Amt BTC">
						</form>
					</td>
			    </tr>
			<?php
			}//end while
			?>
	
	        <tr>
	            <td><h5><strong>Total Assets:</strong></h5></td>
	            <td><h5><strong>
		            <?php
		            //Get sum of total balances of exchange accounts and wallet accounts
					$query="SELECT sum(balance) FROM ".TBL_ACCOUNTS ;
					//echo "<label>SQL STMNT = " . $query .  "</label><br>";
					$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
					if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_row($rs); }
					$total_assets=					$row["0"];
					echo "$total_assets";
					$btc_net_worth = $total_assets - $total_liabilities;
					?>
	            </strong></h5></td>
	        </tr>
	    </table>
	</div>
<!-- 	END ASSETS COLUMN ON THE LEFT -->












<!-- 	BEGIN LIABILITIES COLUMN ON THE RIGHT -->
	<div class="small-12 medium-6 columns">
		<center><h4><strong>Liabilities</strong></h4></center>
		<table width="100%">
	        <tr>
	            <td>Total of user balances:</td>
	            <td><?=$user_balances?></td>
	        </tr>
	        <tr>
	            <td>Orders in process:</td>
	            <td><?=$sum_crypto_pending?></td>
	        </tr>
	        <tr>
	            <td><h5><strong>Total Liabilities:</strong></h5></td>
	            <td><h5><strong><?=$total_liabilities?></strong></h5></td>
	        </tr>
	    </table>



		<br><br><br>
		<?php
			if($btc_net_worth<0) {
				$display_btc_net_worth = "<font color=".'"'."#ff0000".'"'.">$btc_net_worth</font>";
			} else {
				$display_btc_net_worth = $btc_net_worth;
			}
			
		?>
		
		
		<center><h4><strong>BTC Net Worth: <?=$display_btc_net_worth?></strong></h4></center>


		<?	
			$bs = new Bitstamp(BITSTAMP_API_KEY,BITSTAMP_API_SECRET,BITSTAMP_API_CLIENTID);
			$strJSONbalance = $bs->balance() ;
			$balance_usd = $strJSONbalance['usd_balance'];
			$balance_btc = $strJSONbalance['btc_balance'];
			$balance_usd_available = $strJSONbalance['usd_available'];
		?>
		<center><h4><strong>Bitstamp: <br>balance BTC <?=$balance_btc?> <br>balance USD <?=$balance_usd?> <br> available balance <?=$balance_usd_available?> </strong></h4></center>



		<?	
			$mtGoxClient = new MtGoxClient( ANX_API_KEY, ANX_API_SECRET );
			$result = $mtGoxClient->getInfo();
			///api/2/money/bitcoin/send_simple
			#$result = $mtGoxClient->getWalletHistory();
			#$result = $mtGoxClient->setPair('BTCEUR')->getCurrency();
			#$result = $mtGoxClient->orderAdd('bid',1000000,1340293 );
			#$result = $mtGoxClient->orderCancel('4d484979-6237-4e66-b76d-4e8085a8b8be');
			#$result = $mtGoxClient->getDepositAddress();
			#$result = $mtGoxClient->generateDepositAddress();
			#$result = $mtGoxClient->getOrders();
			//print_r($result);
			$strBTCBalance = $result['data']['Wallets']['BTC']['Balance']['display'];
			$strUSDBalance = $result['data']['Wallets']['USD']['Balance']['display'];


		?>
		<center><h4><strong>ANX: <br>balance BTC <?=$strBTCBalance?><br>balance USD <?=$strUSDBalance?> </strong></h4></center>




		<center><h4><strong>Bitstamp: Transactions list</strong></h4></center>
		<?	
			//$bs = new Bitstamp(BITSTAMP_API_KEY,BITSTAMP_API_SECRET,BITSTAMP_API_CLIENTID);
			//$strJSONtransactions = $bs->transactions() ;
			//print_r($strJSONbalance); // show bid,ask & other price stats
		/*
			foreach ($strJSONtransactions as $i => $row)
			{
			    echo "usd=".$row['usd']." " ;
			    echo "btc=".$row['btc']." " ;
				echo "btc_usd=".$row['btc_usd']." " ;
				echo "order_id=".$row['order_id']." ";
				echo "fee=".$row['fee']." " ;
				echo "type=".$row['type']." " ;
				echo "id=".$row['id']." " ;
				echo "dtaetime=".$row['datetime']." " ;
				echo "<br>";
			}
		*/
		?>


<!-- 		!DO buy_anx -->
<!-- 		Area to type in BTC amount to buy from ANX -->
		<form data-abide action="?do=buy_anx" method="POST">
		<input name="buy_anx_btc_amt" type="number" id="right-label" value="" placeholder="BTC from ANX">
		</form>









	</div>
<!-- 	END LIABILITIES COLUMN ON THE RIGHT -->



	</div>
	<!-- 	END ROW -->






    <script src="/js/jquery.js"></script>
    <script src="/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>



</body>
</html>