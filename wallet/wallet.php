<?php 
ob_start(); //so we can redirect even after headers are sent

require "inc/session.php";

//error_reporting(E_ERROR | E_PARSE); ini_set('display_errors',2);
$strERRORPage = 		"wallpet.php";
$intTime = 				time();
$strIPAddress = 		$_SERVER['REMOTE_ADDR'];

$intUserID1=funct_GetandCleanVariables(DETECT_USERID); //id of user logged in
//echo "intUserID1=$intUserID1 <br>"; echo "DETECT_USERID=".DETECT_USERID." <br>"; echo "intUserID_fromcode=".$intUserID_fromcode." <br>";

if(!$intUserID1){ header( 'Location: '.PAGE_SIGNIN."?error=Please Sign In to Access your Wallet" ); die(); }

$strDO = 						funct_GetandCleanVariables($_GET["do"]);
$strError_send = 				funct_GetandCleanVariables($_GET["error_send"]);
$strWallet_Address_preload = 	funct_GetandCleanVariables($_GET["code"]);
$strWallet_Address_preload2 = 	funct_GetandCleanVariables($_GET["qr"]);

if($strWallet_Address_preload2){ $strWallet_Address_preload = $strWallet_Address_preload2 ;}
//$strWallet_Address_preload = "bitcoin:1Hwau6DA1dAfjhMtakhpkf6jgVmTSfTx5a?amount=0.008003&label=Pizza and Pint";

//get preloaded values for 
if(!$strWallet_Address_preload){ $strWallet_Address_preload =funct_GetandCleanVariables($_POST["wallet_hash_preload"]);  }
if(!$intWallet_Crypto_Amt_preload){ $intWallet_Crypto_Amt_preload =funct_GetandCleanVariables($_POST["wallet_crypto_amt_preload"]);  }
if(!$intWallet_Label_preload){ $intWallet_Label_preload =funct_GetandCleanVariables($_POST["wallet_label_preload"]); }
//echo "address= ".$strWallet_Address_preload = $strAddress."<br>";
//echo "amount= ".$amount."<br>" ;  // value
//echo "label= ". $label."<br><br>" ; // foo bar

$BIPSFormat = strstr($strWallet_Address_preload, "bitcoin");
if($BIPSFormat){ //if BIPS format passed as address then
	//hack together bips format
	$strString1 = str_replace("bitcoin:","",$strWallet_Address_preload);
	$arr = explode("?", $strString1, 2);
	$strAddress = $arr[0];
	$strQS2 = $arr[1];
	//echo "qs= ".$strQS2."<br>";
	parse_str($strQS2);

	$strWallet_Address_preload = $strAddress ;
	$intWallet_Crypto_Amt_preload = $amount ;
	$intWallet_Label_preload = $label ;
}


$intLastMSGID = 0 ;



if($intUserID1){ 

	//Get User Data from DataBase
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intUserID1 ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$intUserID_db=						$row["id"];
	$Password_db=						$row["password"];
	$Email_db=							$row["email"];
	$strName=							$row["first_name"]." ".$row["first_name"];
	$strDate_PasswordChanged=			$row["date_passwordchanged"];
	$intSendLocked=						$row["sendlocked"];
	$intEmailConfirmed=					$row["verification_email"];
	//echo "intEmailConfirmed= ".$intEmailConfirmed."<br>";

	$intWalletReceiveOn = 				$row["wallet_receive_on"];
	$strWalletLocation = 				$row["wallet_location"];
	//echo "strWallet_Receive_Show= ".$strWallet_Receive_Show."<br>";

    //Check if user's email is confirmed, then give them access to this page.
    if(!$intEmailConfirmed){ 
		//header( 'Location: '.PAGE_VERIFY."?do=confirmemail&error=Please verify your email to use your wallet." ); die();
	}
	
	//$intBalance_USD=					$row["balance"]; //balance of dollars
	$intBalance_BTC=					$row["balance_btc"]; //balance of BTC
	$intBalance_BTC= number_format($intBalance_BTC,8) ;
	
	//get usd value of BTC - coindesk, gox, bitstamp
	$intRate_BTC_USD = funct_Billing_GetRate("btc");
	$intBalance_BTC_usd = $intBalance_BTC * $intRate_BTC_USD;

	$strWalletBTC=						$row["wallet_btc"]; //their wallet address legacy blockchain.info	
	$strWallet_MainAddress_CC=			$row["wallet_address_cc"]; //amsterdam wallet address
	$strHashBTC=						$row["btc_address"]; //address to forward all funds too..
	
	$strQRcodeIMG = PATH_QRCODES.$strWalletBTC.".png";
	
	
	
	//#####################################################################################
	//WALLET AUTO REGENERATION ROUTINE
	/*
	we consider the following information in this routine
	email confirmed flag, wallet actiaved flag, password changed flag
	walletaddress (blockchain.info) walletaddress coincafe.co
	*/
	
	if(!$intEmailConfirmed){ $strShowEmailConfirmFlag=true ;}
	if($intEmailConfirmed AND (!$intWalletReceiveOn ) ){ $strWalletRequestFlag=true ;}
	
	//AUTO HEALING 
	//if they should have a receive address yet don't then make them one
	if($intEmailConfirmed AND $intWalletReceiveOn AND !$strWalletBTC){ 
		
		$strWalletBTC = funct_MakeWalletAddressUpdate($intUserID_db);
		//function should update their main address wallet_btc in members table 
		//thus this condition will not be met again
	}
	
	if($intEmailConfirmed AND $intWalletReceiveOn AND ( $strWalletBTC OR $strWallet_MainAddress_CC ) ){ $strWalletShowReceiveAdddressFlag=true ;}

	
	//set wallet id to the new wallet code
	if($strWallet_MainAddress_CC AND $strWalletLocation=="amsterdam"){ $strWalletBTC = $strWallet_MainAddress_CC ;}

	//if their qrcode image doesn't exist then create it again (legacy check from userid.png naming convention. now walletaddress.png)
	if($strWalletShowReceiveAdddressFlag){
		
		//if no qr code image is detected then create one
		if(!file_exists(__ROOT__.$strQRcodeIMG)){
			$strError = funct_Billing_GetQRCodeImage($strWalletBTC, $strQRcodeIMG ); //save img to disk
			//echo "no qr image.. writing file... $strError <br>";
		}
	}
	
	/* 
	at the end we need to know if we should ask them to confirm their email
	or if we should ask them to change their password & generate receiver address
	or if we should should show them their receive address and show them qrcode image
	*/
	//#####################################################################################



	//##########################################################
	//process coin claims claimcode

	//read cookie for claim code
	$strCode = funct_GetandCleanVariables( $_COOKIE["claimcode"]) ; //clean cookie for db

	if($strCode){ //cookie found 
		
		//if claim code exists and status is zero and email is the same
		$query=	"SELECT * FROM " . TBL_ESCROW . " WHERE verify_code = '" . $strCode . "' ";
		//echo "SQLSTMNT= $query <br>";
		$rs=mysqli_query($DB_LINK, $query);
		if(mysqli_num_rows($rs)){
			while($row = mysqli_fetch_assoc($rs)){
				$intUserIDsentcoins=		$row["user_id"];
				$intUserEmail=				$row["user_email"]; //email of user who sent the coins
				$strEmailReceiver=			$row["address_email"];
				$intStatusID=				$row["status_id"];
				$strStatusMsg=				$row["status_msg"];
				$intCryptoAmount=			$row["crypto_amount"];
				$strLabel=					$row["label"];
				$intTransactionID_send=		$row["transaction_id_send"];
				$intTransactionID_get=		$row["transaction_id_get"];
				
				//if found then see if the email matches and 
				if($strEmailReceiver!=$Email_db){
					$strErrorClaim = "Email does not match. Admin Alerted";
				}
					
				//if status is zero then. Important!
				if($intStatusID>0){
					$strErrorClaim = "Coins already Claimed. $strStatusMsg ";
				}
				
				//if email not confirmed then error
				if(!$intEmailConfirmed){
					$strErrorClaim = "Please Confirm your email To Claim Coins. ";
				}
				
				//if wallet address not turned on
				if(!$intWalletReceiveOn OR !$strWalletBTC){
					$strErrorClaim = "Please turn on your receive address to Claim Coins.  ";
				}
				
				
			}//end while
		}else{
			$strErrorClaim = "Code not found";
		}
		
		//there is an error delete cookie
		if($strErrorClaim){ 
		
			//leave email
			setcookie("claimcode" ,"", time()-10000, COOKIE_PATH, COOKIE_DOMAIN);
			
		}else{ //no error so fulfill their coin claim
			
			
			//set escrow status = 0
			$query="UPDATE " . TBL_ESCROW . " SET status_id=1, user_id_received= '$intUserID1' ".
			" WHERE verify_code='".$strCode."'" ;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
			
			
			//add transaction for receive
			$balance_prev = $intBalance_BTC;
			$balance_curr=$balance_prev + $intBTCamt;
			$status_id="24"; //transaction valid
			$status_msg="Coins Claimed! (old flag was 1)";
			$query = "INSERT INTO ".TBL_TRANSACTIONS.
			" ( user_id,			user_id_receivedfrom,	currency_code,	credit,				balance_prev,	balance_curr,		type,		cryptotype,	crypto_miner_fee,	crypto_rate_usd,	label,			datetime_created,	datetime,	status,			status_msg,		ipaddress 		) VALUES ".
			" ( '$intUserID1',		'$intUserIDsentcoins',	'btc',			'$intCryptoAmount',	'$balance_prev','$balance_curr',	'receive',	'btc',		'0',				'$intRate_BTC_USD',	'$strLabel',	$intTime,			NOW(),		'$status_id',	'$status_msg',	'$strIPAddress' ) " ;
			$strERRORUserMessage="Database insert coinclaim receiver transaction Error. Admin has been informed ".$strError_send; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
			mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
			$intNewTransactionID = mysqli_insert_id($DB_LINK);
			
			
			//update escrow record with transaction id
			$query="UPDATE " . TBL_ESCROW . " SET transaction_id_get='$intNewTransactionID'".
			" WHERE verify_code='".$strCode."'" ;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
			
			
			//update their balance in users table
			//CREDIT RECEIVER  //update Balannce of receiver if internal
			$intBalanceCrypto_receive = $intBalanceCrypto_receive + $intCryptoAmount ; //CRITCAL. ADDS TO RECIEVER IF INTERNAL SEND
			$query="UPDATE " . TBL_USERS . " SET balance_btc= balance_btc + ".$intCryptoAmount." WHERE id='".$intUserID1."'" ;
			//POTENTIAL PLACE TO INSERT THE NEW BALANCE UPDATING FUNCTION -JOHN
			//echo "SQL STMNT = " . $query .  "<br>";
			$strERRORUserMessage="Database receive credit claim coin Error. Admin has been informed ".$strError_send ; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
			mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
			$strStatus = 1 ;
			
			
			
			//do we also email sender and inform them that their target got their coins?
			
			
			//inform the receiver somehow that they got their coins.. perhaps with a modal popup/
			
			
			//inform admin that the email system worked?
			
			
			
		}

	}//end if cookie found
	//##########################################################


}//end if user id found


//get current BTC rate
$intRate = funct_Billing_GetRate($strCrypto,$strExchange); 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Your Wallet - <?=WEBSITENAME?></title>
<meta charset="utf-8">
<meta name="description" content="<?=$strPageTitle?>">
<meta name="viewport" content="width=device-width">

<!-- Favicon -->
<link rel="icon" type="image/png" href="img/favicon.png" />

<link rel="stylesheet" href="css/foundation.css" />
<link rel="stylesheet" href="css/custom.css" />

<style type="text/css">
    .loader_anim {
        position:relative; width:100px; height:100px; text-align:center; opacity:1.0; z-index:4;
        padding:10px; text-align:center;
        background-color:#fff;background-size: 100% 100%;background-image:url('img/rodincoil.gif');
        border-style:normal;border-color:#666;border-width:2px;border-radius:16px;-webkit-border-radius:16px;-moz-border-radius:16px;
    }
</style>

<script src="js/modernizr.js"></script>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<? $intJquerySoundManager=1;?><script src="js/soundmanager2-nodebug-jsmin.js"></script><script> soundManager.url = 'js/soundmanager2.swf'; soundManager.onready(function() {});</script>

<script>

	$(document).ready(function(){
		
		<? if($strDO=="justjoined"){ ?>
			
//			jsfunct_Alert('Welcome to your free web wallet. ',5000);
        	$('#welcomemodal').foundation('reveal', 'open');
//        	$('#myModal').foundation('reveal', 'close');
			
		<? } ?>
		

		<? if($strDO=="emailverified"){ ?>

        	//$('#emailverifiedmodal').foundation('reveal', 'open');
			
		<? } ?>

		<? if($strDO=="sendSuccess"){ ?>

        	$('#sendsuccessmodal').foundation('reveal', 'open');

		<? } ?>
		
		
		<? if(WALLET_NOTICE){ 
			//show a notice for wallet users ex. blackout etc... db down
			
			//only show if they have a balance greater than zero
			if($intBalance_BTC>0){
		?>
			//load important info html from 
			$( "#walletnotice_html" ).load( "walletnotice.php" );
			$('#walletnotice').foundation('reveal', 'open');
		<? 
			}
		} ?>
		
		<? 
			if($strErrorClaim){
		?>
		$( "#walletnotice_html" ).html('<?=$strErrorClaim?>'); //load( "walletnotice.php" );
		$('#walletnotice').foundation('reveal', 'open');
		
		<? } ?>
		
		var bSuppressScroll = false ;
		intLastRecord = 0 ;
		intNewestID = 0 ;
		intNewestID_old = 0 ;
		intTotalRecords = 0 ;
		intTotalRecordsShowing = 0 ;
		
		strLoadContentAjaxURL = "&do=ajax&maxrecords=<?=$intMaxRecords?>&c=<?=$intChestID?>&type=<?=$intType?>&genre=<?=$intGenreID?>&sort=<?=$sortby?>&dl=<?=$intDownloadAllowed?>&viewer=<?=DETECT_USERID?><?=$strModFlag?>";

		//Call more records on scroll to bottom //  this is jumpy ...
		$(window).scroll(function(){
			if ( ( $(window).scrollTop() +  $(window).height() == $(document).height()  ) && bSuppressScroll == false ){
				//alert('at end of page');
				jsfunct_LoadMoreRecords();
				window.bSuppressScroll = true;
				
			}
		}); //close $(window).scroll(function(){
		
	}); //close ready function

	
	function jsfunct_LoadMoreRecords(){
		$("#loader_bottom").fadeIn(1000);
		strPostString = "<?=ADMIN_MOD_LOADCONTENT?>?last_msg_id=" + intLastRecord + strLoadContentAjaxURL ;
		//"<?=MOD_LOADCONTENT?>?last_msg_id=" + intLastRecord + strLoadContentAjaxURL
		//alert('at bottom');
		
		$.post(strPostString,
			function(data){
				if (data != "") {
					//code to get color box working with ajax content
					
					var $html = $(data);
					$('#tabledata').append( $html ) ;
					window.bSuppressScroll = false; //allow more records to be loaded
					//strNoMoreRecords = "<div class='cell1  box_chestfade' style=''><span class='txtRPG_Actions'>no more records</span></div>";
					
					//$("#totalrecords").html(intTotalRecords);
					$("#totalrecordsshowing").html(intTotalRecordsShowing);
					if(intLastRecord>=intTotalRecords){
						//jsfunct_Alert('files loaded'); // last=' + intLastRecord + ' ttl=' + intTotalRecords );
						//$container.append( strNoMoreRecords ).masonry( 'appended', strNoMoreRecords ); //doesn't go at the end.. only at the beginning..odd
					}
				}
				//$('div#last_msg_loader').empty();
		}); $("#loader_bottom").fadeOut(2000);
	}; //close last_msg_funtion		
	
	
	function jsfunctGetLatest(){ 
	//pass lastest record id or latest int timestamp and get back records most recent and add them to the page
		
		intNewestID_old=intNewestID ; //store first record id, freshest
		
		$.post("<?=MOD_LOADCONTENT?>?do=ajax&newest_msg_id=" + intNewestID + "&type=transactions&sort=new&viewer=<?=DETECT_USERID?>" , function(data){
			if (data != "") {
				//var $html = $(data);
				//prepend container
				$('#tabledata').prepend( data );
			}
		});
		
		//if js id is greater than it was before the get then play a sound and show alert
		if(intNewestID > intNewestID_old){ //new transaction incoming so... give feedback
			document.getElementById('window_get_alert_txt').innerHTML = 'You Got Coin!';
			$('#window_get_alert').fadeIn(500).delay(intDelay).fadeOut(500); //animate it
			soundManager.play('gotcoin','/sounds/life.mp3');//play sound
		}
		
		//update balance with dynamic var
		functjs_Refresh_Balance();
	}
	
	function functjs_Refresh_Balance(){
		//
		$.get("<?=CODE_DOAJAX?>?do=getbalance&userid=<?=$intUserID1?>" , function(data){
			if (data != "") {

				var arrayResponse = data.split(",");
				var intCryptoBalance = arrayResponse[0];
				var intFiatBalance = arrayResponse[1];
				
				document.getElementById('txtBTCbalance').innerHTML = intCryptoBalance + ' BTC' ; //crypto balance
				//document.getElementById('txtFIATbalance').innerHTML = '$' + intFiatBalance  ;//update fiat value too txtFIATbalance
				
			}
		});
	}
	
	function functjs_RefreshList() { //unused
		$("#tabledata").load(''); //reloads div	
	}
	
	function debug_getnew(){ //debug
		alert('newstrec=' + intNewestID);
		jsfunctGetLatest();
	}
	
		
	<? if(REFRESH_WALLET_SEC){ ?> 
	var auto_refresh = setInterval( function () { jsfunctGetLatest(); }, <?=REFRESH_WALLET_SEC * 1000 ?>); // refresh every * milliseconds 10000= 10 seconds
	<? } ?>

</script>
</head>

<body>

<?php require "hud.php"; ?>

<!--MAIN CONTENT AREA-->

<div class="row" style="">

    <div class="small-12 columns">
		<!--balance-->
            <div style="text-align:right;">
                <h3><strong id="txtBTCbalance"><?=$intBalance_BTC?> BTC</strong></h3>
<!-- 				<br><small id="txtFIATbalance"><small>(approx. $<?=number_format($intBalance_BTC_usd,2)?> USD)</small></small></h3> -->
            </div>
	</div>

</div>




<div class="row">
	
	<!--SIDEBAR AREA LEFT-->
        <div class="small-12 medium-4 columns">
			<div class="panel radius">
<!--                RECEIVE MODULE-->
                
                <h4>My Wallet Address</h4>
				<?php
				//if email is not verified then show the please verify your email message
				if($strShowEmailConfirmFlag){
				?>
						<!-- BEGIN email verify AREA -->
						<form data-abide action="<?=CODE_DO?>?do=confirmemailcode" method="GET">
							<div class="confirm_email">
							    <h5>To activate your receive wallet address. Please check your email and click the confirmation link. <br>Tip: check your Spam folder.</h5>
								<h3><?=$strError?></h3>
								    <input name="emailcode" type="text" placeholder="enter your email code">
								    <input name="do" type="hidden" value="confirmemailcode">
									<span class="txtError"><?=$strError_confirmemail?></span>
									<button type="submit">Confirm Email</button><br>
									<?php if($strError_emailconfirm){ echo $strError_emailconfirm." <br>" ; } ?>
								<a href="<?=CODE_DO?>?do=sendemailcode">send code again to your email <?=$strEmail_DB?></a>
							</div>
						</form>
						<!-- END email verify AREA -->
				<?
				}//end if email is not confirmed
				
				
				//else if walleton flag is off then show the make wallet button
				if($strWalletRequestFlag){ 
				?>
						<!-- BEGIN email verify AREA -->
						<form action="<?=CODE_DO?>?do=activatereceiveaddress" name="passwordupdate" method="POST">
							<div class="confirm_email">

		                        <script>
								function validateForm_passwordupdate() {
									//just autosubmit it
									document.passwordupdate.submit();
									}
								</script>
								<input name="do" type="hidden" value="activatereceiveaddress">
								<!--<button type="submit"></button>--><br>
								<a href="javascript:;" class="button" onClick="validateForm_passwordupdate();">Turn On my Receive Address Now</a><br>


							</div>
						</form>
				
				<?
				}
				
				
				
				if($strWalletShowReceiveAdddressFlag){
				?>
				    <input name="wallethash" type="text" id="wallethash" value="<?=$strWalletBTC?>">
					<script> $("#wallethash").focus(function() { var $this = $(this);$this.select(); $this.mouseup(function() { $this.unbind("mouseup"); return false; });	}); </script>
					<img src="<?=$strQRcodeIMG?>" />
				<?php
				}
				?>
				
            </div>
            
            
            
<!--        SEND MODULE-->
			<? if($intSendLocked){ ?>
				Your sending privileges have been locked.... sorry please contact <?=SUPPORT_EMAIL?><br><br>
			<? }else{ //show send code ?>
		
					<div class="panel radius">
						<a name="get" id="get" />
					    <h4>Send</h4>
					
						<div class="hide-for-large-up">
							<?php
							
							//Detect special conditions devices
							$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
							$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
							$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
							$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
							$webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
						
							//do something with this information 
							if( $iPod || $iPhone || $iPad ){ //browser reported as an iPhone/iPod touch -- do something here
								$strScanURL = QRSCANAPP_IOS_URINAME. "?callback=".'<?=WEBSITEFULLURLHTTPS?>/wallet.php?code=EAN';
								//$strScanAhref = "javascript:jsfunct_DetectApp();";
								$strScanAhref = $strScanURL ;
								$strAppURL = QRSCANAPP_IOS_URL;
						    ?>
							<a href="<?=$strAppURL?>">First Download This app to scan.</a><br>
							<? 
							}else if($Android){ //browser reported as an Android device -- do something here
								$strScanURL = QRSCANAPP_DROID_URINAME."?callback=".'<?=WEBSITEFULLURLHTTPS?>/wallet.php?code=EAN' ; //%7BCODE%7D
								$strScanAhref = $strScanURL ;
								$strAppURL = QRSCANAPP_DROID_URL;
							?>
							<a href="javascript:;" onClick="jsfunct_DetectApp();">First Download This app to scan.</a><br>  
							<? } ?>
							<center><a href="<?=$strScanAhref?>" class="button small expand">Scan QR</a></center>
						</div>
						  	
							<form data-abide name="sendbtc" id="sendbtc" method="post" action="#">
								<div class="row">
									<div class="small-12 columns">
									   <input name="send_address" type="alpha_numeric" required id="send_address" placeholder="send to bitcoin address" style="width:100%;" value="<?=$strWallet_Address_preload?>">
									   <small class="error">Please enter a Bitcoin address.</small>
								   </div>
								</div><br>
								<div class="row">
								   <div class="small-6 columns">
								        <input name="send_amount_crypto" id="send_amount_crypto" type="number" placeholder="amount BTC" style="width:100%;" value="<?=$intWallet_Crypto_Amt_preload?>">
										<small class="error">BTC amount must be a number</small>
								   </div>
								   <div class="small-6 columns">
								        <input name="send_amount_fiat" id="send_amount_fiat" type="number" placeholder="or amount $" style="width:100%;" value="<?=$intAmountFiat?>">
										<small class="error">USD $ amount must be a number</small>
								   </div>
								</div>
								<div class="row">
								   <div class="small-12 columns">
								        <input name="label" type="" id="label" placeholder="optional label" style="width:100%;" value="<?=$intWallet_Label_preload?>">
								   </div>
								</div>
								<br>
								<div id="window_send_alert_error" class="alertwindow_error" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_send_alert_error_txt" class="txtRPG_Actions"></span></div>
								<div id="window_send_alert" class="alertwindow" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_send_alert_txt" class="txtRPG_Actions"></span></div>
								
								<div class="row">
									<div class="small-12 columns">
<!-- 										<button type="button small" style="width:100%;" id="button_send" onClick="jsfunct_SentUpdate();">Send Now</button> -->
										<center><a href="#" class="button small expand" id="button_send" onClick="jsfunct_SentUpdate();">Send Now</a></center>
									</div>
								</div>
								<strong style="color:#C00;"><?=$strError_send?></strong>
							</form>
					</div>
					
					<script>
					
						$(document).ready(function(){
						
							//get rate from ajax call every 10 seconds
							var intRate=<?=$intRate?>;
						
							//set newest id so pages js does not throw an error if there are now rows
							intNewestID = 0;	
							
							//auto calculate fiat /crypto based on rate
							var intCryptoVal ;
							var intFiatVal ;
							$("#send_amount_crypto").keyup(function(e) {
								if(e.keyCode != 9){
									intFiatVal = ( intCryptoVal * intRate ) ;
									document.getElementById("send_amount_fiat").value =  ( Math.abs(document.getElementById("send_amount_crypto").value) * intRate ).toFixed(2) ;
								}
							});
							$("#send_amount_fiat").keyup(function(e) {
								if(e.keyCode != 9){
									intCryptoVal = ( intFiatVal / intRate ) ;
									document.getElementById("send_amount_crypto").value = ( Math.abs(document.getElementById("send_amount_fiat").value) / intRate ).toFixed(8) ;
								}
							});
							<? if($strWallet_Address_preload){ //if returning from scanner then scroll to send part #get ?>
								$('html, body').animate({
								        scrollTop: $("#get").offset().top
								    }, 2000);
								soundManager.play('select','/sounds/messagesent.mp3');//play sound

							<? } ?>
							

						
						}); //close ready function
						
						
						function jsfunct_DetectApp(){
							
							var now = new Date().valueOf();
							setTimeout(function () {
							    if (new Date().valueOf() - now > 100) return;
							    window.location = "<?=$strAppURL?>";
							}, 25);
							window.location = "<?=$strScanURL?>";
							//setTimeout(function () { window.location = "<?=$strAppURL?>"; }, 25);
							//window.location = "<?=$strAppURL?>";
							
						}
						
						
						function jsfunct_SentUpdate(){ //sends bitcoin via ajax
							
						    var okSoFar=true
					
								if (document.getElementById('send_address').value==""){
									okSoFar=false
									alert("Please fill in Send Address.")
									return false;
								}
								if (document.getElementById('send_amount_crypto').value==""){
									okSoFar=false
									alert("Please fill in how much coin to send.")
									return false;
								}
								
								if (okSoFar==true) {
								
									//check if password is in session or ask for password each and every time.
									
									$("#password_header_txt").html("Please enter your password");
									
									//prompt for password
									$('#askpassword').foundation('reveal', 'open');
									
									//set password to nothing to prevent browser prefill
									document.getElementById("input_password").value = '' ;
									
									//set to focus on password field
									$('#input_password').focus();
									document.getElementById("input_password").focus();
							  
							  } //end if form passes verification
						} //end function
						
						
						function jsfunct_SubmitPassword(){
						
							document.getElementById("password_header_txt").innerHTML = 'Now sending. Do NOT click twice! Please wait...' ;

						
							var okSoFar=true
				
							if (document.getElementById('input_password').value==""){
								okSoFar=false
								alert("Please fill your password")
								return false;
							}
	
							if (okSoFar==true) {
							
								//$("#password_header_txt").html("Now sending. Don't click twice! Please wait... ");
								
								document.getElementById("password_error").innerHTML = 'checking password......' ;
								
								strPassword = $("#input_password").val() ;
								
								var dataString = '' +
								'&do=' + 					'sendcheckpassword' + 
								'&password=' + 				$("#input_password").val() ;
								
								$.ajax({
								  type: "POST",
								  async: false, //this allows the page called via ajax to write cookies to the user
								  url: "<?=MOD_SENDCRYPTO?>?do=sendcheckpassword",
								  data: dataString,
								  success: function(result) { //result is the new id of user from db
								   	
								   	if(result=='ok'){
								   	
								   		document.getElementById("password_error").innerHTML = 'GOOD!.. Checking One Last Time. Please wait.... .... ..' ;
								   		//document.getElementById("password_error").innerHTML = '' ;
								   		//close modal window
								   		$('#askpassword').foundation('reveal', 'close');
								   		
								   		//disable button to send
								   		
								   		
								   		//call function
								   		jsfunctSendCrypto(strPassword);
								   		
								   		document.getElementById("password_error").innerHTML = '';
								   		
								   	}else{ 
									   	
									   	//report error
									   	document.getElementById("password_error").innerHTML = result ;
								   	}
								   	
								   	
								  } // end on success
								 }); //end ajax submit
							}
							
						}
						
						function pausecomp(ms) {
							ms += new Date().getTime();
							while (new Date() < ms){}
						} 
						
						
						function jsfunctSendCrypto(strPassword){
							
							
							
							//disable send button while sending
							$('#button_send').click(false);
							$('#button_submitpassword').click(false);
						
							//alert('username=' + document.getElementById('password').value);
							//document.getElementById('signin').submit();
							//submit form via ajax
							//alert('ajax submit');
							strError='';
							strErrorMSG='';
							intBalanceCrypto='';
							intBalanceFiat='';
							
							var dataString = '' +
							'&do=' + 					'sendcrypto' + 
							'&password=' + 				strPassword +
							'&label=' + 				$("#label").val() +
							'&send_address=' + 			$("#send_address").val() +
							'&send_amount_crypto=' + 	$("#send_amount_crypto").val() +
							'&send_amount_fiat=' + 		$("#send_amount_fiat").val() ;
							//alert ('postdatasent=' + dataString);
							
							document.getElementById('button_send').innerHTML = "Sending..." ;
							
							$.ajax({
							  type: "POST",
							  async: false, //this allows the page called via ajax to write cookies to the user
							  url: "<?=MOD_SENDCRYPTO?>?do=sendcrypto",
							  data: dataString,
							  success: function(result) { //result is the new id of user from db
							    //alert('result= ' + result );
								arrayResponse = result.split(",");
								strError = arrayResponse[0];
								strErrorMSG = arrayResponse[1];
								intBalanceCrypto = arrayResponse[2];
								intBalanceFiat = arrayResponse[3];
								
							  } // end on success
							 }); //end ajax submit
							 
							 //alert('strError= ' + strError + ' strErrorMSG= ' + strErrorMSG );
							 
							 if(strError==1){ //if good then update the balance amount and set the dollar amount and give feedback
									
									//update balance at top of page
									document.getElementById('txtBTCbalance').innerHTML = intBalanceCrypto + ' BTC' ;//update crypto balance
									//document.getElementById('txtFIATbalance').innerHTML = '$' + intBalanceFiat + 'USD' ;//update fiat balance
									
									//updates text in the send module button
									document.getElementById('window_send_alert_txt').innerHTML = strErrorMSG ;
									
									//use modal instead bitcoinsend_errormsg
									//document.getElementById('bitcoinsend_errormsg').innerHTML = strErrorMSG ;
									

									//document.getElementById('password_header_txt').innerHTML = "BITCOINS SENT SUCCESSFULLY!" ;
							        $("#password_header_txt").html("Bitcoin Transfer Requested! Check your Email.");
							        $('#askpassword').delay(2000).foundation('reveal', 'close');
									//$("#password_header_txt").html("Please enter your password");

									
									
									//$('#bitcoinsend').foundation('reveal', 'open');
									//$('#window_send_alert').fadeIn(500).delay(3000).fadeOut(500); //animate it
									
								    soundManager.play('select','/sounds/send.mp3');//play sound
									
								}else{ //if not good then display error
								    
									//jsfunct_Alert('Error:' + strErrorMSG ,7000); 
									$("#password_header_txt").html("Error! " + strErrorMSG);
									//$("#password_header_txt").delay(5000).html("Error! " + strErrorMSG);
									//document.getElementById('window_send_alert_error_txt').innerHTML = strErrorMSG ;
									//$('#window_send_alert_error').fadeIn(500).delay(7000).fadeOut(500); //animate it
									soundManager.play('select','/sounds/error.mp3');//play sound
								}
							 
							 	document.getElementById('button_send').innerHTML = "Send Now" ;
								document.getElementById('send_amount_crypto').value = "" ;
								document.getElementById('send_amount_fiat').value = "" ;
								
								//call function to update ledger
								//jsfunctGetLatest();
							 
							 //enable send button while sending
							$('#button_send').click(true);
							$('#button_submitpassword').click(true);
							
							
						}//end function
						
					</script>
								
			
			<? } ?>
<!--            SEND MODULE END-->

        </div>

	<!--MAIN-->
        <div class="small-12 medium-8 columns">
			<div id="window_get_alert" class="alertwindow" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_get_alert_txt" class="txtRPG_Actions"></span></div>
	
			<!--ledger-->
				 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
					<thead>
			        <tr>
<!-- 						<td align="left" width="20%"><h5>Date</h5></td> -->
						<td align="left" width="30%"><h5>Date</h5></td>
						<td align="left" width="40%"><h5>Description</h5></td>
						<!--<td align="left" width="40%"><h5></h5></td>-->
			          	<td align="left" width="15%"><h5>Amount</h5></td>
			          	<td align="left" width="15%"><h5>Balance</h5></td>
			        </tr>
			  		</thead>
					<tbody id="tabledata">
					<?php 
					if($intUserID_db){ //chestid specified and not a new chest
						$strDo= 			"include";
						$sortby=			"top";
						$intType = 			"transactions"; //files - get from top
						//$intLastMSGID = 	0; 
						$intMaxRecords = 100 ; //MAXCHAR_RECORDS_TRANSACTIONS ;//get from top
						$intRecID = false;
						$intUserID_viewer = $intUserID_db ; 
						if($intShowEditMod){$intMod="1";}
						include __ROOT__.MOD_LOADCONTENT ;
					}
					?>
					</tbody>
			    </table>
			    
			</div>
		</div>
		
		
	<p></p><p></p><p></p>
	
	

	
	<div id="myModal" class="reveal-modal" data-reveal> 
		<h2>Transaction Details</h2> 
		<p class="lead">loading...</p> 
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	

	<div id="walletnotice" class="reveal-modal" data-reveal> 
		<h2>Important Notice</h2> 
		<p id="walletnotice_html">loading important notice ...</p> 
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	
	
	<div id="welcomemodal" class="reveal-modal medium" data-reveal> 
		<h4>Welcome to your Bitcoin Wallet!</h4>
	    <p>We're so stoked to have you as a Coin Cafe member. You can start using your wallet immediately.</p>
	    <p>Thanks for being a part of the family.
	        We promise to make buying, selling and using Bitcoins as easy and empowering as possible.</p>
	    <p>A temporary password has been emailed to <strong><?=$Email_db?></strong>. Please <a href="/settings.php">update your information</a> as soon as you can.<br>
	    <p>Thanks again and enjoy!<br>Coin Cafe</p>
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	
	<div id="emailverifiedmodal" class="reveal-modal medium" data-reveal> 
		<h4>Your email is verified!</h4>
	    <p>Now just update your password ( pick a tough one ) and you can turn on your wallet and start getting bitcoins!</p>
	    <p>Thanks again and enjoy!<br>Coin Cafe</p>
		<a class="close-reveal-modal">&#215;</a> 
	</div>	
	
	<div id="sendsuccessmodal" class="reveal-modal medium" data-reveal> 
		<h4>Bitcoin sent!</h4>
	    <p>You have successfully authorized your send.</p>
		<a class="close-reveal-modal">&#215;</a> 
	</div>

	<div id="askpassword" class="reveal-modal medium" data-reveal> 
		<h4 id="password_header_txt">Please enter your password</h4>

		<div class="row">
		   <div class="large-4 columns">
		        <input name="input_password" type="password" required id="input_password" placeholder="password" value="">
				<small id="password_error"></small><br>
				<button type="button" id="button_submitpassword" onClick="jsfunct_SubmitPassword();">Submit</button>
		   </div>
		</div>
		<script>
			//fire password function on enter of password form
			document.getElementById('input_password').onkeypress = function(e) { 
				if (e.keyCode == 13){   
			        jsfunct_SubmitPassword(); 
			    } 
			}
		</script>
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	
	
	<div id="bitcoinsend" class="reveal-modal" data-reveal> 
		<h2>Transaction Status</h2> 
		<p class="lead" id="bitcoinsend_errormsg">sending bitcoin...</p> 
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	
	
	<div style="height:50px;"></div>	
	
	<div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;"><center>
		<div id="loader_bottom" class="loader_anim" style="display:none;">
		    <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
		    loading...</span>
		</div></center>
	</div>
	

	<script src="js/foundation.min.js"></script>
	<script src="js/foundation/foundation.abide.js"></script>
	<script src="js/foundation/foundation.reveal.js"></script>
	
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
		    //password : /(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/
	    }
	  });
	</script>


</body>
</html>