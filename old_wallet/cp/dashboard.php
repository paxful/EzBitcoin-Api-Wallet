<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

$strErrorMSG = 			htmlspecialchars(trim($_GET["msg"])); //set error msg manually in query

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
	<meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   
	<?php if(!$intJquery){ $intJquery=1;?><script src="<?php JQUERYSRC?>" type="text/javascript"></script><?php } ?>

    <link rel="stylesheet" href="/wallet/css/foundation.css" />
<link rel="stylesheet" href="/wallet/css/custom.css" />
    <script src="/wallet/js/modernizr.js"></script>
	
	<SCRIPT LANGUAGE="JavaScript">
		<!--
	
	
		//-->
	</SCRIPT>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>admin</title>
    
</head>

<body onload="<?php $strOnBodyLoadJS?>">

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>

<p></p>	


	<div class="row">
		<div class="medium-9 small-12 columns">
			<h3>Welcome <?php $strFirstName_hud?></h3>
			<h4><?php $strErrorMSG?></h4>
			<p></p>

	<!-- 			Search fields -->
		<div class="row">
			<div class="small-4 columns">
	    		<form name="searchform" id="searchformuserid" method="get" action="hud.php?do=search" target="_blank">
	    			<label>User ID:</label>
		        	<input name="searchtxt" type="text" value="<?php $strSearchText?>" placeholder="User ID">
		        	<input name="searchtype" type="hidden" value="userid"> 
					<input name="do" type="hidden" value="searchhud">
	    		</form>
	        </div>
			<div class="small-4 columns">
				<form name="searchform" id="searchformname" method="get" action="hud.php?do=search" target="_blank">
	    			<label>Customer Name:</label>
	            	<input name="searchtxt" type="text" value="<?php $strSearchText?>" placeholder="Customer Name">
	            	<input name="searchtype" type="hidden" value="name"> 
					<input name="do" type="hidden" value="searchhud">
				</form>
	        </div>
			<div class="small-4 columns">
	        </div>
		</div>
		<p></p>
		<div class="row">
			<div class="small-4 columns"> 
	    		<form name="searchform" id="searchformorderid" method="get" action="orders_details.php">
	    			<label>Order ID:</label>
		        	<input name="id" type="text" value="<?php $strSearchText?>" placeholder="Order ID">
<!--
		        	<input name="searchtype" type="hidden" value="orderid"> 
					<input name="do" type="hidden" value="searchhud">
-->
	    		</form>
	        </div> 
			<div class="small-4 columns"> 
	    		<form name="searchform" id="searchformdepositamt" method="get" action="hud.php?do=search" target="_blank">
	    			<label>Deposit Amount:</label>
			    	<input name="searchtxt" type="text" value="<?php $strSearchText?>" placeholder="Deposit Amount">
			    	<input name="searchtype" type="hidden" value="depositamt"> 
					<input name="do" type="hidden" value="searchhud">
	    		</form>
	        </div>
			<div class="small-4 columns"> 
	    		<form name="searchform" id="searchformstatus" method="get" action="/cp/orders.php" target="_blank">
	    			<label>Status:</label>
			    	<input name="searchtxt" type="text" value="<?php $strSearchText?>" placeholder="Order Status">
			    	<input name="searchtype" type="hidden" value="status"> 
	    		</form>
	        </div>
		</div>




            <p></p>
			<label>In-Person Transaction Tools:</label>
            <ul class="button-group">
                <li><a class="button tiny" target="_blank" href="calculate.php">Calculate Order</a></li>
                <li><a class="button tiny" target="_blank" href="orders.php">View Orders</a></li>
                <li><a class="button tiny" target="_blank" href="ordersinperson.php">Orders In Person</a></li>
            </ul>
			<br>
			<label>Bitcoin Account Balances:</label>
            <ul class="button-group">
                <li><a class="button tiny" target="_blank" href="btcbalances.php">Bitcoin Account Balances</a></li>
                <li><a class="button tiny" target="_blank" href="members2.php">User Balances</a></li>
            </ul>
			<br>            
			<label>Beta Pages:</label>
			<ul class="button-group">
				<li><a class="button tiny" target="_blank" href="verify_kyc.php">Verify KYC</a></li>
				<li><a class="button tiny" target="_blank" href="verify_deposits_original.php">Verify Deposits orig</a></li>
				<li><a class="button tiny" target="_blank" href="verify_deposits.php?f=3">Verify Deposits</a></li>
				<li><a class="button tiny" target="_blank" href="verify_receipts.php">Verify Receipts</a></li>
				<li><a class="button tiny" target="_blank" href="orders_exfilled.php">Orders Exchange Filled</a></li>
				<li><a class="button tiny" target="_blank" href="orders_deposited.php">Orders w Deposits</a></li>
				<li><a class="button tiny" target="_blank" href="orders_cumberland.php">Cumberland</a></li>
			</ul>
		</div>	

		<!--SIDEBAR AREA-->        
		<div class="medium-3 columns">
			<h4>Wallet Server</h4>
			<p>Balance: <?php funct_Billing_GetBalance();?></p>
			<?php
				
				
				
			?>
			<form name="movefundsform" id="movefundsform" method="post" action="/cp/ajax_do.php?do=movefundsamsterdam2hot" target="_blank">
    			<label>Move funds from Amsterdam to Hot Wallet: </label>
		    	<input name="amount" type="text" value="" placeholder="Move how much BTC?"> 
		    	<button type="submit">Move Funds Now</button>
    		</form>
		</div>

	</div>
	
	


<script src="/wallet/js/jquery.js"></script>
<script src="/wallet/js/foundation.min.js"></script>
<script src="/wallet/js/foundation/foundation.abide.js"></script>
<script>
  $(document)
  .foundation()
  .foundation('abide', {
    patterns: {
		alpha: /[a-zA-Z]+/,
	    alpha_numeric : /[a-zA-Z0-9]+/,
	    integer: /-?\d+/,
	    number: /-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?/,

	    // generic password: upper-case, lower-case, number/special character, and min 8 characters
	    password : /(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/

    }
  });
</script>


</body>
</html>