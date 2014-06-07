<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
//echo get_current_user();
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

$strDO = 					trim($_GET["do"]); //if($strDO==""){$strDO="new";}
$intBTCamount = 			trim($_POST["amount_btc"]);
$intUSDamount = 			trim($_POST["amount_usd"]);
$intBTCrate = 				trim($_POST["rate_btc"]);
//$intBTCrate = funct_Billing_GetRate("btc","bitstamp");
		$strCrypto="btc"; $strExchange=RATE_HUD_EXCHANGE;
		$intBTCrate = funct_Billing_GetRate($strCrypto,$strExchange);


$strError = 				trim($_GET["error"]); 
$intUserID1=				DETECT_USERID; //id of user logged in

//check to see if user is logged in and an admin
include $_SERVER['DOCUMENT_ROOT']."/inc/checklogin.php";


if($strDO=="calc"){
	//1000
	//if dollar specified then get amount btc to give them
	if($intUSDamount){
		$intBTCamount = $intUSDamount / $intBTCrate ;
		$strErrorMSG = "Awesome Customer gets $intBTCamount BTC for $".$intUSDamount." now!" ;
	
	}else{ //if Btc specified then get dollars amount to ask of them
		if($intBTCamount){
			$intUSD_weget = $intBTCamount * $intBTCrate ;
			$strErrorMSG = "Awesome Customer gives us $".$intUSD_weget." USD to get $intBTCamount BTC !" ;
		}
	}
}else{
//	$intBTCrate = funct_Billing_GetBTCPrice_CoinDesk() ; //get coindesk price
//	$intBTCrate = funct_Billing_UpdateRate("btc","coindesk");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$strPageTitle?></title>
<meta charset="utf-8">
<meta name="description" content="<?=$strPageTitle?>">
<meta name="viewport" content="width=device-width">

<!-- Favicon -->
<link rel="icon" type="image/png" href="/img/favicon.png" />

<? /* */ ?> <!-- these 3 are for facebook -->
<meta property="og:title" content="<?=$strPageTitle?>" />
<meta property="og:description" content="" />
<meta property="og:image" content="<?=$strChestMainPicURL?>" />
<? /* */ ?>

<!-- Favicon -->
<link rel="icon" type="image/png" href="/img/favicon.png" />

<link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
<link rel="stylesheet" href="/webicons-master/webicons.css" />
<style>

</style>
<script src="/js/modernizr.js"></script>
<script src="<?=JQUERYSRC?>" type="text/javascript"></script>



<script>
	

	$(document).ready(function(){
		

		
	}); //close ready function
	

</script>



</head>

<body onLoad="<?=$strOnBodyLoadJS?>" class="" style="">


	
    
<!--MAIN CONTENT AREA-->          
    <p></p>

    <div class="row">
        <a href="index.php">Return to Admin Panel</a><br><br>
    </div>

    <p></p>

    <div class="row">
	
      <div class="large-8 medium-8 small-12 columns">
	
	     	<div class="panel callout radius" style="">
	           <h3>BTC Order Calculator</h3><br>
	
				<h1><?=$strErrorMSG?></h1>

				<form data-abide name="checkout" method="post" action="?do=calc">

					<div class="bitcoin_wallet">
					  <label>BTC Spot Price <small>CoinDesk</small></label>
					  <input name="rate_btc" style="width:200px;" type="integer" value="<?=$intBTCrate?>">
					  <small class="error">Error calling Api</small>
					</div>
					<br><br>

					<div class="bitcoin_wallet">
					  <label>Amount to buy in BTC <small>optional</small></label>
					  <input name="amount_btc" style="width:200px;" type="integer" value="<?=$intBTCamount?>">
					</div>
					<br><br>

					<div class="amount_usd">
					    <label>Amount to buy in USD <small>optional</small></label>
					    <input name="amount_usd" style="width:200px;" type="integer"  value="<?=$intUSDamount?>">
					  </div>
					<br><br>

					<input name="type" style="width:90%;" type="hidden" id="type" value="<?=$strType?>">
					<input name="do" style="width:90%;" type="hidden" id="do" value="add">
					<button type="submit">Calculate</button>

				</form>




	       </div>
     
		</div>
		
		

		<!--SIDEBAR AREA-->        
        <div class="large-4 medium-4 small-12 columns">
        
			<h4>Customer support #1 </h4><br><br>

			<h4>Customer is always right, especially when they're not! </h4><br><br>

		</div>



      </div>


<br><br>


<br><br><br><br><br><br>



    <script src="/js/foundation.min.js"></script>
	<script src="/js/foundation/foundation.abide.js"></script>
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