<?php 
ob_start(); //so we can redirect even after headers are sent

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
//echo get_current_user();
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

include __ROOT__.PATH_ADMIN."checklogin.php";


$intUserID1=DETECT_USERID; //id of user logged in
//echo "intUserID1=$intUserID1 <br>"; echo "DETECT_USERID=".DETECT_USERID." <br>"; echo "intUserID_fromcode=".$intUserID_fromcode." <br>";
if(!$intUserID1){ header( 'Location: '.PAGE_SIGNIN."?error=Please Sign In to Access your Wallet" ); die(); }

$strDO = 						trim($_GET["do"]); //if($strDO==""){$strDO="new";}
$strError_send = 				trim($_GET["error_send"]); 
$strWallet_Address_preload = 	trim($_GET["code"]);
$strWallet_Address_preload2 = 	trim($_GET["qr"]);

if($strWallet_Address_preload2){ $strWallet_Address_preload = $strWallet_Address_preload2 ;}
//$strWallet_Address_preload = "bitcoin:1Hwau6DA1dAfjhMtakhpkf6jgVmTSfTx5a?amount=0.008003&label=Pizza and Pint";

//get preloaded values for 
if(!$strWallet_Address_preload){ $strWallet_Address_preload =htmlspecialchars(trim($_POST["wallet_hash_preload"]));  }
if(!$intWallet_Crypto_Amt_preload){ $intWallet_Crypto_Amt_preload =htmlspecialchars(trim($_POST["wallet_crypto_amt_preload"]));  }
if(!$intWallet_Label_preload){ $intWallet_Label_preload =htmlspecialchars(trim($_POST["wallet_label_preload"])); }
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

$intMaxRecords = "100" ;
$intLastMSGID = 0 ;



if($intUserID1){ 

	//Get User Data from DataBase
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intUserID1 ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$intUserID_db=						$row["id"];
	$Password_db=						$row["password"];
	$Email_db=							$row["email"];
	
	$intSendLocked=						$row["sendlocked"];
	$intEmailConfirmed=					$row["verification_email"];
//echo "intEmailConfirmed= ".$intEmailConfirmed."<br>";

	$strWallet_Receive_Show=			$row["wallet_receive_on"];
//echo "strWallet_Receive_Show= ".$strWallet_Receive_Show."<br>";

    //Check if user's email is confirmed, then give them access to this page.
    if(!$intEmailConfirmed){ 
//		header( 'Location: '.PAGE_VERIFY."?do=confirmemail&error=Please verify your email to use your wallet." ); die();
	}
	
	
	//$intBalance_USD=					$row["balance"]; //balance of dollars
	$intBalance_BTC=					$row["balance_btc"]; //balance of BTC
	$intBalance_BTC= number_format($intBalance_BTC,8) ;
	
	//get usd value of BTC - coindesk, gox, bitstamp
	$intRate_BTC_USD = funct_Billing_GetRate("btc");
	$intBalance_BTC_usd = $intBalance_BTC * $intRate_BTC_USD;
	
	$strHashBTC=						$row["btc_address"];
	$strWalletBTC=						$row["wallet_btc"];
	$strQRcodeIMG = PATH_QRCODES.DETECT_USERID.".png";
	
	
	
	
}

$intRate = funct_Billing_GetRate($strCrypto,$strExchange); 


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

<link rel="stylesheet" href="/css/web.css" />

<link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
<link rel="stylesheet" href="/webicons-master/webicons.css" />
<script src="/js/modernizr.js"></script>
<script src="/js/web.js"></script>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<? $intJquerySoundManager=1;?><script src="/js/soundmanager2-nodebug-jsmin.js"></script><script> soundManager.url = '/js/soundmanager2.swf'; soundManager.onready(function() {});</script>

<script>

	$(document).ready(function(){
		
		<? if($strDO=="justjoined"){ ?>
			
//			jsfunct_Alert('Welcome to your free web wallet. ',5000);
        	$('#welcomemodal').foundation('reveal', 'open');
//        	$('#myModal').foundation('reveal', 'close');
			
		<? } ?>
		
		
		strLoadContentAjaxURL = "&do=ajax&maxrecords=<?=$intMaxRecords?>&c=<?=$intChestID?>&type=<?=$intType?>&genre=<?=$intGenreID?>&sort=<?=$sortby?>&dl=<?=$intDownloadAllowed?>&viewer=<?=DETECT_USERID?><?=$strModFlag?>";
	
		//Call more records on scroll to bottom //  this is jumpy ...
		$(window).scroll(function(){

			if ( ( $(window).scrollTop() +  $(window).height() == $(document).height()  ) && window.bSuppressScroll == false ){
				last_msg_funtion();
				window.bSuppressScroll = true;
				//alert('at end of page');
			}
		}); //close $(window).scroll(function(){
		
		
	}); //close ready function
	
	
	

	
	
	function last_msg_funtion(){
		$("#loader_bottom").fadeIn(1000);
		$.post("<?=ADMIN_MOD_LOADCONTENT?>?last_msg_id=" + intLastRecord + strLoadContentAjaxURL,
			function(data){
				if (data != "") {q
					//code to get color box working with ajax content
					var $html = $(data);
					$('#tabledata').append( $html ) ;
					//$container.append( $html ).masonry( 'reload' );
					//$container.append( $html ).isotope( 'appended', $html );
					window.bSuppressScroll = false; //allow more records to be loaded
					//strNoMoreRecords = "<div class='cell1  box_chestfade' style=''><span class='txtRPG_Actions'>no more records</span></div>";
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
		
		$.post("<?=ADMIN_MOD_LOADCONTENT?>?do=ajax&newest_msg_id=" + intNewestID + "&type=transactions&sort=new&viewer=<?=DETECT_USERID?>" , function(data){
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
				
				document.getElementById('txtBTCbalance').innerHTML = intCryptoBalance ; //crypto balance
				document.getElementById('txtFIATbalance').innerHTML = intFiatBalance  ;//update fiat value too txtFIATbalance
				
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

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>

<!--MAIN CONTENT AREA-->          

<p></p>




<div class="row">

<h2>Transactions</h2>
	
	<!--MAIN-->
        <div class="small-12 medium-12 columns">
			<div id="window_get_alert" class="alertwindow" style="display:none; position: relative; width:300px; min-height:60px; z-index:10;"><span id="window_get_alert_txt" class="txtRPG_Actions"></span></div>
	
			<!--ledger-->
				 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
					<thead>
			        <tr>
						<td align="left"">TX ID</td>
						<td align="left">Type</td>
						<td align="left">Order ID</td>
						<td align="left" width="30%">Date</td>
			          	<td align="left">Status</td>
			          	<td align="left">Status Msg</td>
			          	<td align="left">Amount</td>
			        </tr>
			  		</thead>
					<tbody id="tabledata">
					<?php 
					if($intUserID_db){ //chestid specified and not a new chest
						$strDo= "include";
						$sortby="top";
						$intType = "transactions"; //files - get from top
						//$intLastMSGID = 0; 
						$intMaxRecords = "1000";//get from top
						//$intRecID = false;
						//$intUserID_viewer = $intUserID_db ; 
						if($intShowEditMod){$intMod="1";}
						include __ROOT__.ADMIN_MOD_LOADCONTENT ;
					}
					?>
					</tbody>
			    </table>
			    
			</div>
		</div>
		
		
	<p></p><p></p><p></p>
	
	
	<div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;"><center>
		<div id="loader_bottom" class="loader_anim" style="display:none;">
		    <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
		    loading...</span>
		</div></center>
	</div>
	
	<div id="myModal" class="reveal-modal" data-reveal> 
		<h2>Transaction Details</h2> 
		<p class="lead">loading...</p> 
		<a class="close-reveal-modal">&#215;</a> 
	</div>
	
	<div id="welcomemodal" class="reveal-modal medium" data-reveal> 
		<h4>Welcome to your Bitcoin Wallet!</h4>
	    <p>We're so stoked to have you as a Coin Cafe member. You can start using your wallet immediately.</p>
	    <p>Thanks for being a part of the family.
	        We promise to make buying, selling and using Bitcoins as easy and empowering as possible.</p>
	    <p>A temporary password has been emailed to <strong><?=$Email_db?></strong>. Please <a href="settings.php">update your information</a> as soon as you can.<br>
	    <p>Thanks again and enjoy!<br>John & Ray</p>
		<a class="close-reveal-modal">&#215;</a> 
	</div>

	<div id="askpassword" class="reveal-modal medium" data-reveal> 
		<h4>Please enter your password</h4>

		<div class="row">
		   <div class="large-4 columns">
		        <input name="input_password" type="password" required id="input_password" placeholder="password" value="">
				<small id="password_error"></small><br>
				<button type="button" onClick="jsfunct_SubmitPassword();">Submit</button>
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
	
	<script src="/js/jquery.js"></script>
	<script src="/js/foundation.min.js"></script>
	<script src="/js/foundation/foundation.abide.js"></script>
	<script src="/js/foundation/foundation.reveal.js"></script>
	
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