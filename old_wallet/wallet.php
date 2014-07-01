<?php 
ob_start(); //so we can redirect even after headers are sent

require "inc/session.php";

error_reporting(E_ERROR | E_PARSE); ini_set('display_errors',2);
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

$strCryptoCode  = 	funct_GetandCleanVariables($_GET["crypto"]);
if(!$strCryptoCode){$strCryptoCode="btc";}

$strFiatCode  = 	funct_GetandCleanVariables($_GET["fiat"]);
if(!$strFiatCode){$strFiatCode="usd";}






//#### Get the qr code , label and amount from qrcode scanning app

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


//logged in users only
if(! $intUserID1){ echo "login please"; die; }


//Get User Data from DataBase
$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intUserID1 ;
//echo "SQL STMNT = " . $query .  "<br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
$intUserID_db=						$row["id"];
$Password_db=						$row["password"];
$Email_db=							$row["email"];
$strName=							$row["first_name"]." ".$row["last_name"];
$strDate_PasswordChanged=			$row["date_passwordchanged"];
$intSendLocked=						$row["sendlocked"];

$intEmailConfirmed=					$row["verification_email"];
//echo "intEmailConfirmed= ".$intEmailConfirmed."<br>";

//if email is not confirmed then show the confirm email form
if(!$intEmailConfirmed){ $strShowEmailConfirmFlag=true ;}

$intWalletReceiveOn = 				$row["wallet_receive_on"];
$strWallet_MainAddress=		    	$row["wallet_address"]; //bitcoin wallet address
$strQRcodeIMG = PATH_QRCODES.$strWallet_MainAddress.".png";
//echo "wallet: $strWallet_MainAddress <br>" ;

//#####################################################################################
//WALLET AUTO REGENERATION ROUTINE
//if their qrcode image doesn't exist then create it again (/media/qrcode/walletaddress.png)
if(!$strWallet_MainAddress){

    //make new wallet address
    $strWallet_MainAddress = funct_MakeWalletAddressUpdate($intUserID1, $strCrypto_Code);
}

//if no qr code image is detected then create one
if(!file_exists(__ROOT__.$strQRcodeIMG)){
    $strError = funct_Billing_GetQRCodeImage($strWallet_MainAddress, $strQRcodeIMG ); //save img to disk
    echo "no qr image.. writing file... $strError - $strQRcodeIMG <br>";
}
//#####################################################################################


//get balance for crypto
$query="SELECT * FROM " . TBL_WALLET_BALANCES . " WHERE userid = ". $intUserID1." AND currency_code='".$strCryptoCode."' AND currency_type='crypto' " ;
//echo "SQL STMNT = " . $query .  "<br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
$intBalance=						$row["balance"];
$intBalance= number_format($intBalance,8) ; //balance of BTC


//get bitcoin rate
if($strCryptoCode=="btc"){
    $intRate_BTC_USD = funct_Billing_GetRate("btc", 'coindesk'); //get usd value of BTC - coindesk, gox, bitstamp
    $intCrypto2Fiat_rate = $intRate_BTC_USD ;
    $intBalance_Fiat = money_format($intBalance * $intRate_BTC_USD,2);
}




//convert to other fiat if not dollars
if($strFiatCode!="usd"){

    //get fiat rate
    $intBalance_Fiat2USD = funct_Billing_UpdateRate_Fiat($strFiatCode);

    //echo "fiat $strFiatCode - $intBalance_Fiat2USD <br>";

    //get other crypto rate
    //$intCrypto2Fiat_rate = $intRate_BTC_USD ;
    $intCrypto2Fiat_rate = $intBalance_Fiat2USD * $intRate_BTC_USD ;

    //no rate found so stick to dollars
    if(!$intBalance_Fiat2USD){

        //set crypto back to usd
        $strFiatCode = "usd";

    }
}

//get fiat info
$query="SELECT * FROM " . TBL_CURRENCY . " WHERE currency_code='".$strFiatCode."'" ;
//echo "SQL STMNT = " . $query .  "<br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
$intFiat_id=						$row["currency_id"];
$strFiat_name=						$row["currency_name"];
$strFiat_code=                      $row["currency_code"];
$intFiat_rate_usd=					$row["currency_rate_USD"];
$strFiat_rate_btc=					$row["currency_rate_BTC"];
$intFiat_countryic=					$row["countryid"];



/*
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

	}//end process claim code
	//##########################################################
*/


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

    <link rel="icon" type="image/png" href="img/favicon.png" />

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/bootstrapValidator.min.css" rel="stylesheet" />


<style type="text/css">
    .loader_anim {
        position:relative; width:100px; height:100px; text-align:center; opacity:1.0; z-index:4;
        padding:10px; text-align:center;
        background-color:#fff;background-size: 100% 100%;background-image:url('img/rodincoil.gif');
        border-style:normal;border-color:#666;border-width:2px;border-radius:16px;-webkit-border-radius:16px;-moz-border-radius:16px;
    }
</style>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<?php $intJquerySoundManager=1;?><script src="js/soundmanager2-nodebug-jsmin.js"></script><script> soundManager.url = 'js/soundmanager2.swf'; soundManager.onready(function() {});</script>

<script>

	$(document).ready(function(){
		
		<?php if($strDO=="justjoined"){ ?>
			
//			jsfunct_Alert('Welcome to your free web wallet. ',5000);
        	$('#welcomemodal').foundation('reveal', 'open');
            $('#welcomemodal').modal('show');

		<?php } ?>
		

		<?php if($strDO=="emailverified"){ ?>

        	//$('#emailverifiedmodal').foundation('reveal', 'open');
			
		<?php } ?>

		<?php if($strDO=="sendSuccess"){ ?>

        	$('#sendsuccessmodal').foundation('reveal', 'open');
            $('#sendsuccessmodal').modal('show');

		<?php } ?>
		
		
		<?php if(WALLET_NOTICE){ 
			//show a notice for wallet users ex. blackout etc... db down
			
			//only show if they have a balance greater than zero
			if($intBalance_BTC>0){
		?>
			//load important info html from 
			$( "#walletnotice_html" ).load( "walletnotice.php" );
			//$('#walletnotice').foundation('reveal', 'open');
            $('#walletnotice').modal('show');
		<?php 
			}
		} ?>
		
		<?php 
			if($strErrorClaim){
		?>
		$( "#walletnotice_html" ).html('<?=$strErrorClaim?>'); //load( "walletnotice.php" );
		//$('#walletnotice').foundation('reveal', 'open');
        $('#walletnotice').modal('show');
		
		<?php } ?>
		
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


        $('#fiat_type').change(function(e){
            var locAppend = $(this).find('option:selected').val(),
                locSnip   = window.location.href.split('?')[0];

            window.location.href = locSnip + locAppend;
        });


	}); //close ready function

	
	function jsfunct_LoadMoreRecords(){
		$("#loader_bottom").fadeIn(1000);
		strPostString = "<?=MOD_LOADCONTENT?>?last_msg_id=" + intLastRecord + strLoadContentAjaxURL ;
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
        strURLgetbalance = "<?=CODE_DO?>?do=getbalance&userid=<?php echo $intUserID1?>&crypto=<?php echo $strCryptoCode?>&fiat=<?php echo $strFiatCode?>" ;
        //alert(strURLgetbalance);
		$.get(strURLgetbalance , function(data){
			if (data != "") {

				var arrayResponse = data.split(",");
				var intCryptoBalance = arrayResponse[0];
				var intFiatBalance = arrayResponse[1];
                var intCryptoFiatRate = arrayResponse[2];

                //alert('crypto ' + intCryptoBalance + ' fiat ' + intFiatBalance + ' rate ' + intCryptoFiatRate);

				document.getElementById('txtCryptoBalance').innerHTML = intCryptoBalance  ; //crypto balance
				document.getElementById('txtFiatBalance').innerHTML =  intFiatBalance  ;//update fiat value too txtFiatBalance
                document.getElementById('txtCryptoFiatRate').innerHTML =  intCryptoFiatRate  ;
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
		
	<?php if(REFRESH_WALLET_SEC){ ?> 
	var auto_refresh = setInterval( function () { jsfunctGetLatest(); }, <?=REFRESH_WALLET_SEC * 1000 ?>); // refresh every * milliseconds 10000= 10 seconds
	<?php } ?>

</script>
</head>

<body>

<?php require "hud.php"; ?>

<div class="container-fluid">

    <!--MAIN CONTENT AREA-->
    <div class="row">

        <div class="col-sm-7">
            <!--balance-->
            <div style="text-align:left;">
                <h2><div id="txtCryptoBalance"><?=$intBalance?> BTC</div>  <small id="txtFiatBalance"><?php echo number_format($intBalance_Fiat,2)." ".$strFiatCode ?></small> (<em id="txtCryptoFiatRate"><?php echo $intCrypto2Fiat_rate ?></em>)</h2>
                <!--<br><small id="txtFIATbalance"><small>(approx. $<?=number_format($intBalance_Fiat,2)?> USD)</small></small></h3> -->
            </div>
        </div>

        <div class="col-sm-5" style="padding-top: 20px; ">
            <select id="fiat_type" class="form-control">
                <?php
                $query=	"SELECT * FROM " .TBL_CURRENCY. " ORDER BY sortid ASC, currency_name ASC";
                $rsCountry= mysqli_query($DB_LINK, $query) or die(mysqli_error());
                while ($row=mysqli_fetch_array($rsCountry))
                {
                $intFiatCurrency_id=			$row["currency_id"];
                $strFiatCurrency_Code=	        $row["currency_code"];
                $strFiatCurrency_Name=			$row["currency_name"];

                ?>
                <option value="?fiat=<?php echo $strFiatCurrency_Code ?>" <?php if($strFiatCode==$strFiatCurrency_Code){ echo ' selected ' ; } ?>  ><?php echo $strFiatCurrency_Code." - ".$strFiatCurrency_Name ?></option>
                <?php } ?>
            </select>
        </div>

    </div>




<div class="row">
	
	<!--SIDEBAR AREA LEFT-->
        <div class="col-xs-12 col-md-4">


        <!-- ############################## RECEIVE MODULE -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">My Account</h4>

                <select id="crypto_type" class="form-control">
                    <?php
                    $query=	"SELECT * FROM " .TBL_CURRENCY_CRYPTO. " ORDER BY crypto_name ASC";
                    $rsCountry= mysqli_query($DB_LINK, $query) or die(mysqli_error());
                    while ($row=mysqli_fetch_array($rsCountry))
                    {
                        $strCryptoCurrency_Code=	        $row["crypto_code"];
                        $strCryptoCurrency_Name=			$row["crypto_name"];

                        ?>
                        <option value="<?php echo $strFiatCurrency_Code ?>"><?php echo $strCryptoCurrency_Name ?></option>
                    <?php } ?>
                </select>

            </div>
            <div class="panel-body">
				<?php
				//if email is not verified then show the  verify your email form
				if(!$strWallet_MainAddress){
				?>
                    <!-- BEGIN email verify AREA -->
                    <form role="form" action="<?=CODE_DO?>?do=confirmemailcode" method="GET">
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

				//show receive address and qr code image
				if($strWallet_MainAddress){
				?>
				    <input name="wallethash" type="text" class="form-control" id="wallethash" value="<?=$strWallet_MainAddress?>"><script> $("#wallethash").focus(function() { var $this = $(this);$this.select(); $this.mouseup(function() { $this.unbind("mouseup"); return false; });	}); </script>
					<img src="<?=$strQRcodeIMG?>" class="img-responsive" />
				<?php
				}else{ //no btc wallet address in members table so

                    //scan balances table for address


                        //if address found then update members table with it


                        //if no address then make them a new one

                        //


                }
				?>
            </div>
        </div>
        <!-- ############################## END RECEIVE MODULE -->





<!-- ############################## SEND MODULE -->
			<?php if($intSendLocked){ ?>
				Your sending privileges have been locked.... sorry please contact <?=SUPPORT_EMAIL?><br><br>
			<?php }else{ //show send code ?>

                <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Send</h4>
                </div>
                <div class="panel-body">

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
							<?php } ?>
							<center><a href="<?=$strScanAhref?>" class="btn btn-info btn-sm btn-block" >Scan QR</a></center>
						</div>

							<form role="form" name="sendbtc" id="sendbtc" method="post" action="#">
								<div class="row">
									<div class="col-xs-12">
									   <input name="send_address" type="alpha_numeric" required id="send_address" placeholder="send to bitcoin address" class="form-control" style="width:100%;" value="<?=$strWallet_Address_preload?>">
								   </div>
								</div><br>
								<div class="row">
								   <div class="col-xs-6">
								        <input name="send_amount_crypto" id="send_amount_crypto" type="number" placeholder="amount BTC" class="form-control" style="width:100%;" value="<?=$intWallet_Crypto_Amt_preload?>">
								   </div>
								   <div class="col-xs-6">
								        <input name="send_amount_fiat" id="send_amount_fiat" type="number" placeholder="or amount $" class="form-control" style="width:100%;" value="<?=$intAmountFiat?>">
								   </div>
								</div>
								<div class="row">
								   <div class="col-xs-12">
								        <input name="label" type="" id="label" placeholder="optional label" style="width:100%;" class="form-control" value="<?=$intWallet_Label_preload?>">
								   </div>
								</div>
								<br>
								<div id="window_send_alert_error" class="alertwindow_error" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_send_alert_error_txt" class="txtRPG_Actions"></span></div>
								<div id="window_send_alert" class="alertwindow" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_send_alert_txt" class="txtRPG_Actions"></span></div>

								<div class="row">
									<div class="col-xs-12">
<!-- 										<button type="button small" style="width:100%;" id="button_send" onClick="jsfunct_SentUpdate();">Send Now</button> -->
										<center><button class="btn btn-primary btn-block" id="button_send" onClick="jsfunct_SentUpdate();">Send Now</button></center>
									</div>
								</div>
								<strong style="color:#C00;"><?=$strError_send?></strong>
							</form>
                        </div>
					</div>




			<?php } ?>
<!-- ############################## SEND MODULE END-->

        </div>







	<!--MAIN-->
        <div class="col-xs-12 col-md-8">
			<div id="window_get_alert" class="alertwindow" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_get_alert_txt" class="txtRPG_Actions"></span></div>

			<!--ledger-->
				 <table class="table table-striped">
					<thead>
			        <tr>
<!-- 						<td align="left" width="20%"><h5>Date</h5></td> -->
						<th align="left" width="30%"><h5>Date</h5></td>
						<th align="left" width="40%"><h5>Description</h5></td>
						<!--<td align="left" width="40%"><h5></h5></td>-->
			          	<th align="left" width="15%"><h5>Amount</h5></td>
			          	<th align="left" width="15%"><h5>Balance</h5></td>
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


    <!-- Modal - loading transactions-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Transaction Details</h4>
                </div>
                <div class="modal-body">
                    <p class="lead">loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal - email verified-->
    <div class="modal fade" id="emailverifiedmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>Your email is verified!</h4>
                    <p>Now just update your password ( pick a tough one ) and you can turn on your wallet and start getting bitcoins!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal - send success-->
    <div class="modal fade" id="sendsuccessmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>Bitcoin sent!</h4>
                    <p>You have successfully authorized your send.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal - ask password-->
    <div class="modal fade" id="askpassword" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h4 id="password_header_txt">Please enter your password</h4>

                    <div class="row">
                        <div class="col-xs-4">
                            <input name="input_password" type="password" required id="input_password" placeholder="password" class="form-control" value="">
                            <button type="button" class="btn btn-primary" id="button_submitpassword" onClick="jsfunct_SubmitPassword();">Submit</button>
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

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal - send success-->
    <div class="modal fade" id="sendsuccessmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h2>Transaction Status</h2>
                    <p class="lead" id="bitcoinsend_errormsg">sending bitcoin...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
	
	
	<div style="height:50px;"></div>	
	
	<div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;"><center>
		<div id="loader_bottom" class="loader_anim" style="display:none;">
		    <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
		    loading...</span>
		</div></center>
	</div>

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
    <?php if($strWallet_Address_preload){ //if returning from scanner then scroll to send part #get ?>
    $('html, body').animate({
        scrollTop: $("#get").offset().top
    }, 2000);
    soundManager.play('select','/sounds/messagesent.mp3');//play sound

    <?php } ?>



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
        //$('#askpassword').foundation('reveal', 'open');
        $('#askpassword').modal('show');

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
                    //$('#askpassword').foundation('reveal', 'close');
                    $('#askpassword').modal('hide')


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


<script src="js/bootstrap.min.js"></script>
<script src="js/angular.min.js"></script>
<script type="text/javascript" src="js/bootstrapValidator.min.js"></script>



</body>
</html>