<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";


$strDO = 						trim($_GET["do"]); 
$intMemberID = 					trim($_GET["id"]); 
$intMemberID = mysqli_real_escape_string($DB_LINK, $intMemberID);
$strErrorMSG = 					trim($_GET["msg"]); //set error msg manually in query


if($strDO=="update"){
	
	$intMemberID = 				funct_ScrubVars($_POST['userid']);
	//Get all post values, ckeck & clean them
	$strPassword = 				funct_ScrubVars($_POST['password']);
	$strEmail = 				funct_ScrubVars($_POST['email']);
	$intCountryID = 			funct_ScrubVars($_POST['country']);
	$intCurrencyID = 			funct_ScrubVars($_POST['currency']);
	$strNameFirst = 			funct_ScrubVars($_POST['namefirst']);
	$strNameLast = 				funct_ScrubVars($_POST['namelast']);
	$strAddress = 				funct_ScrubVars($_POST['address']);
	$strAddress2 = 				funct_ScrubVars($_POST['address2']);
	$strCity = 					funct_ScrubVars($_POST['cityname']);
	$strState = 				funct_ScrubVars($_POST['state']);
	$strPostal = 				funct_ScrubVars($_POST['postal']);
	$intFiatConvertPercent = 	funct_ScrubVars($_POST['fiatconvertpercent']);
	$strCellPhone = 			funct_ScrubVars($_POST['cellphone']);
	$strCellPhone_code = 		funct_ScrubVars($_POST['cellphone_countrycode']);
	//$intCityID = 				funct_ScrubVars($_POST['repcity']);
	
	$strCryptoBalance = 		funct_ScrubVars($_POST['cryptobalance']);
	$intSendLocked = 			funct_ScrubVars($_POST['sendlocked']);
	
	$strPayPalEmail = 			funct_ScrubVars($_POST['paypalemail']);
	$strBTCaddress = 			funct_ScrubVars($_POST['btcaddress']);
	$strLTCaddress = 			funct_ScrubVars($_POST['ltcaddress']);
	$strBankaccount = 			funct_ScrubVars($_POST['bankaccount']);
	$strBankrouting = 			funct_ScrubVars($_POST['bankrouting']);
	$intMemberID= 				mysqli_real_escape_string($DB_LINK,$intMemberID);
	$strPassword= 				mysqli_real_escape_string($DB_LINK,$strPassword);
	$strEmail= 					mysqli_real_escape_string($DB_LINK,$strEmail);
	$intCountryID= 				mysqli_real_escape_string($DB_LINK,$intCountryID);
	$intCurrencyID= 			mysqli_real_escape_string($DB_LINK,$intCurrencyID);
	$strNameFirst= 				mysqli_real_escape_string($DB_LINK,$strNameFirst);
	$strNameLast= 				mysqli_real_escape_string($DB_LINK,$strNameLast);
	$strAddress= 				mysqli_real_escape_string($DB_LINK,$strAddress);
	$strAddress2= 				mysqli_real_escape_string($DB_LINK,$strAddress2);
	$strCity= 					mysqli_real_escape_string($DB_LINK,$strCity);
	$strState= 					mysqli_real_escape_string($DB_LINK,$strState);
	$strPostal= 				mysqli_real_escape_string($DB_LINK,$strPostal);
	$intFiatConvertPercent=		mysqli_real_escape_string($DB_LINK,$intFiatConvertPercent);
	$strCellPhone= 				mysqli_real_escape_string($DB_LINK,$strCellPhone);
	$strCellPhone_code= 		mysqli_real_escape_string($DB_LINK,$strCellPhone_code);
	
	$strCryptoBalance= 			mysqli_real_escape_string($DB_LINK,$strCryptoBalance);
	$intSendLocked= 			mysqli_real_escape_string($DB_LINK,$intSendLocked);

	
	$strPayPalEmail= 			mysqli_real_escape_string($DB_LINK,$strPayPalEmail);
	$strBTCaddress= 			mysqli_real_escape_string($DB_LINK,$strBTCaddress);
	$strLTCaddress= 			mysqli_real_escape_string($DB_LINK,$strLTCaddress);
	$strBankaccount= 			mysqli_real_escape_string($DB_LINK,$strBankaccount);
	$strBankrouting= 			mysqli_real_escape_string($DB_LINK,$strBankrouting);

	if($intFiatConvertPercent){ $intFiatConvertPercent=0; }
	//if($intGoalType<1){ $intGoalType=0 ;}
	//if($intGoalAmount<1){ $intGoalAmount=0 ;}
	if($intCountryID<1){ $intCountryID=0 ;}
	if($intCurrencyID<1){ $intCurrencyID=0 ;}
	/*
	if($intCityID>0){ //Get name of city
		$query= "SELECT * FROM ".TBL_CITIES." WHERE cityid = " .$intCityID ;
		//echo "SQL.getcityinfo = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) ;
		$row = mysqli_fetch_array($rs) ;
		$strCityName =			$row["cityname"];
		$intCityCountryID =		$row["countryid"];
		$intCityRegionID =		$row["regionid"];
		}else{
		$intCityCountryID = 0 ;
	}else{ $intCityID=0 ; }
	*/

	if(!$intSendLocked){$intSendLocked=0;}
	if(!$strCryptoBalance){$strCryptoBalance=0;}


	//Update Database 
	$query = "UPDATE ".TBL_USERS." SET " .
	//"password='$strPassword', ".
	"email='$strEmail', ".
	"cellphone='$strCellPhone', ".
	"country_phonecode='$strCellPhone_code', ".
	"first_name='$strNameFirst', ".
	"last_name='$strNameLast', ".
	"address='$strAddress', ".
	"address2='$strAddress2', ".
	"cityname='$strCity', ".
	"state='$strState', ".
	"postal='$strPostal', ". 
	"country_id=$intCountryID, ".
	"currency_id=$intCurrencyID, ".
	
	"sendlocked=$intSendLocked, ".
	"balance_btc=$strCryptoBalance, ".
	
	"paypalemail='$strPayPalEmail', ".
	"btc_address='$strBTCaddress', ".
	"bank_account='$strBankaccount', ".
	"bank_routing='$strBankrouting', ".
	"lastlogin=NOW() ".
	"WHERE id = $intMemberID " ;
	//echo "SQL STMNT = " . $query .  "<br>"; //"cityid=$intCityID, ". //"city='$strCityName', ". //"regionid=$intCityRegionID, ". 
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	
	
}




//Get User Data from DataBase
$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intMemberID ";
//echo "SQL STMNT = " . $query .  "<br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
$row=mysqli_fetch_array($rs) ;
$intUserID_DB=					$row["id"];
//$UserName_DB=					$row["username"];
$Password_DB=					$row["password"];
$Email_DB=						$row["email"];
$strCellPhone=					$row["cellphone"];
$id_code=						$row["id_code"];

$intFiatConvertRate=			$row["fiat_conversion_percent"];

$strNameFirst=					$row["first_name"];
$strNameLast=					$row["last_name"];

$strAddress_DB=					$row["address"];
$strAddress2_DB=				$row["address2"];
$strCityName_DB=				$row["cityname"];
$strState_DB=					$row["state"];
$strPostal_DB=					$row["postal"];

//$intCityID_DB=				$row["cityid"];
//$intCityRegion_DB=			$row["regionid"];
$intCountryID_2=				$row["country_id"];
$strCellPhone_code=				$row["country_phonecode"];
	
$intCurrencyID=					$row["currency_id"];
$strCurrencyCode=				$row["currency_symbol"];

$strCryptoBalance =				$row["balance_btc"];
$intSendLocked = 				$row["sendlocked"];

$strBitCoinAddress=				$row["btc_address"];
$CC_wallet_address=				$row["wallet_address_cc"];
$strPayPalAddress=				$row["paypalemail"];
$strLiteCoinAddress=			$row["lc_address"];
$strBankAccount=				$row["bank_account"];
$strBankRouting=				$row["bank_routing"];

$strWebsite=					$row["websiteurl"];
$strVerificationLevel=			$row["verification_level"];
$strVerificationPhone=			$row["verification_phone"];
$strVerificationEmail=			$row["verification_email"];
$strVerificationID=				$row["verification_id"];


//$intGoalAmount=				$row["goal_amount"];
//$intGoalType=					$row["goaltype"];
//$Date_DB=						$row["timestamp"];

//$intProfilePic_DB=			$row["profilepic"];
//$strProfilePicPath =  PROFILEPICTUREPATH.$intUserID_DB.".jpg" ;

if($intCountryID_2){
	$query="SELECT * FROM " . TBL_COUNTRIES . " WHERE id = $intCountryID_2 ";
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$row = mysqli_fetch_array($rs) ;
	$intPhoneCountryCode=			$row["code"];
	$strCountryName_DB_top=				$row["name"];
}	


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$intUserID_DB?> <?=$strNameFirst?> <?=$strNameLast?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
<meta charset="utf-8">
<meta name="description" content="<?=$strPageTitle?>">
<meta name="viewport" content="width=device-width">

<!-- Favicon -->
<link rel="icon" type="image/png" href="/img/favicon.png" />

<link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
<script src="/js/modernizr.js"></script>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<script src="/js/web.js" type="text/javascript"></script>

<script>
	

	$(document).ready(function(){
		

		
	}); //close ready function
	
	

</script>



</head>

<body onLoad="<?=$strOnBodyLoadJS?>" class="" style="">

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>

<p></p>


	<!-- BEGIN MAIN+SIDEBAR CONTENT AREA 8+4 COLUMNS -->
	<div class="row">

	    <!-- BEGIN MAIN CONTENT AREA 8 OF 12 COLUMNS -->
		<div class="small-12 medium-8 columns">

			<h4>User: <?=$intUserID_DB?> <?=$strNameFirst?> <?=$strNameLast?> <?=$Email_DB?></h4><br>
			<?=$id_code?><br>
			coin cafe wallet: <?=$CC_wallet_address?><br>
			<h4><?//=$strError?></h4>

            
            <div class="panel radius">
            
			<!-- List all uploads here from uploads table -->
			<?php
	        $query="SELECT * ".
			" FROM ".TBL_UPLOADS." ".
			" WHERE usernameid=$intUserID_DB ".
			" ORDER BY date_added DESC " ;
			//echo "SQLstmt=$query<br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
			$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
			
			?>
			<?=$nr?> total uploads<br>
			<div id="UploadPicDiv">
			<?
			while($row = mysqli_fetch_assoc($rs)){

			    $intUploadID=					$row["uploadid"];
				$strExt=						$row["ext"];
				$strKeyLink=					$row["keylink"];
				$intOrderID=					$row["orderid"];
				
				$strUploadPath = PATH_UPLOADS.$strKeyLink.".".$strExt ;
				$strReceiptPath = PATH_RECEIPTS.$intOrderID.".png" ;
			
	        ?>
			<a href="<?=$strUploadPath?>" target="_new"><img src="<?=$strUploadPath?>" width="100" height="100" /></a>
			<?php

			}//end while

			?></div>
        </div>

            
            
        <!-- ORDERS  -->
        <h3>Orders</h3><br>

		 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
	        <tr>
				<td align="left" width="60"><h5>Type</h5></td>
				<td align="left" width="200"><h5>USD - BTC</h5></td>
	          	<td align="left" width="100"><h5>Rate/Fee</h5></td>
	          	<td align="left" width="100"><h5>Status</h5></td>
	          	<td align="left" width="100"><h5>Date</h5></td>
	        </tr>	

	        <?php

	        $strOrderBySTMT = " date DESC ";
	        $intLastMSGID = 0 ; $intMaxRecords = 1000 ;
/*
			if($intUserID_db){ $strWhereSQL2= " AND ( from_id>0 AND from_id=$intUserID_db ) " ;}
			$strWhereSTMT = " AND from_email= '$Email_db' $strWhereSQL2 " ;
*/
	        $query="SELECT * ".
			" FROM ".TBL_ORDERS." ".
			" WHERE orderid>0 AND from_id=$intUserID_DB ".
			" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords " ;
			//echo "SQLstmt=$query<br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
			$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
			/*
			$query0 = "Select FOUND_ROWS()";
			$rs0 = 	mysqli_query($DB_LINK, $query0) or die(mysqli_error());
			$row0 =	mysqli_fetch_array($rs0);
			$nr0 = 	$row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
			if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
			*/
			//begin loop
			while($row = mysqli_fetch_assoc($rs)){

			    $intOrderID=					$row["orderid"];
				//$strOrderID = BLOAT_ORDERID + $intOrderID ;
			    $strOrderCode=					$row["ordercode"];
				$strType=						$row["type"];

				$intStatus=						$row["status_id"];
				$strStatusText=					$row["status_text"];

				if(!$intStatus){$strStatusText="no status in tbl_orders";}

				$intFromUserID= 				$row["from_id"]; 
			    $strFromName=					$row["from_name"];
			    $strFromNameLast= 				$row["from_namelast"]; 
			    $strEmail= 						$row["from_email"]; 
			    $strPhone= 						$row["from_phone"]; 
			    $strMessage= 					$row["from_message"]; 
			    $intAmtUSD= 					$row["amt_usd"];
				$intAmtBTC=						$row["amt_btc"];

				$intRateBTC=					$row["btc_rate"];

			    $intTipUSD= 					$row["tip_usd"]; 
			    $intOurFeeUSD= 					$row["our_fee_usd"]; 
			    $intOurFeeBTC= 					$row["our_fee_btc"]; 
			    $intOurFeePER= 					$row["our_fee_percent"]; 
			    $intBankFee=					$row["bank_fee"];
				$intTotalUSD= 					$row["total_usd"]; 

				$intBankID= 					$row["bankid"]; 
			    $strBankName= 					$row["bank_name"]; 
			    $strWalletTo= 					$row["hash_to"];

				$strDate= 						$row["date"];
/* 				$strDate_formatted = date("d-m-y", strtotime($strDate)); */
				$strDate_formatted = date("Y-m-d H:i", strtotime($strDate));
				$strDate_formatted_nice = functNiceTimeDif($strDate);
	        ?>
	        <tr>
				<td align="left"><br><a href="orders_details.php?id=<?=$intOrderID?>"><?=$intOrderID?></a><br><?=$strType?></td>
	          	
				<td align="left"><strong>$<?=number_format($intAmtUSD,2)?></strong> <?=trim( number_format($intAmtBTC,8),0)?> BTC <br>
					$<?=number_format($intTotalUSD,2)?> + $<?=money_format('%i', $intTipUSD)?> tip
				</td>
					
	          	<td align="left">$<?=money_format('%i', $intRateBTC)?> / $<?=money_format('%i', $intBankFee)?>
				</td>
	          	
				<td align="left"><?=$strStatusText?></td>
				<td align="left"><?=$strDate_formatted?><br><?=$strDate_formatted_nice?></td>
	        </tr>
			<?php

			}//end while

			?>
	    	</table>

        
        
        
        <!-- ORDERS end -->
            
            
<!-- RIPPED OUT TRANSACTIONS FROM HERE -->








		</div>
	    <!-- END MAIN CONTENT AREA 8 OF 12 COLUMNS -->




		<!-- BEGIN SIDEBARK CONTENT AREA 4 OF 12 COLUMNS -->
		<div class="small-12 medium-4 columns">

			<? if($strVerificationLevel){ ?>
			<div class="panel callout radius">
				<? if($strVerificationEmail){ echo "Email verified! <br>" ; } ?>
				<? if($strVerificationPhone){ echo "Phone verified!" ; } ?>
				<? if($strVerificationID){ echo "ID verified!" ; } ?>
			</div>
			<? } ?>




	           <? if(!$strVerificationEmail){ ?>
				<form data-abide action="<?=CODE_DO?>?do=confirmemailcode" method="POST">
					<div class="panel radius">
						<div class="confirm_email">
						    <label>Confirm Email </label>
						    <input name="emailcode" type="text" placeholder="email code">
							<span class="txtError"><?=$strError_confirmemail?></span>
							<button type="submit">Confirm Email</button><br>
							<?php if($strError_emailconfirm){ echo $strError_emailconfirm." <br>" ; } ?>
							<a href="<?=CODE_DO?>?do=sendemailcode">send code to email</a>
						</div>
		            </div>
				</form>
				<? } ?>



	        <!-- BEGIN SETTINGS MAIN TABLE -->
	        <div class="panel radius">
	        <form action="?do=update" method="POST" name="update">
	        
	        
	        <div class="row">
                <div class="small-4 columns">
                    <h6>Balance</h6>
                </div>
                <div class="small-8 columns">
                    <input name="cryptobalance" type="text" required placeholder="crypto balance"value="<?=$strCryptoBalance?>" maxlength="50">
                </div>
            </div>
	        
	        
	        <div class="row">
                <div class="small-4 columns">
                    <h6>LockSends</h6>
                </div>
                <div class="small-8 columns">

	                    <select name="sendlocked" id="sendlocked" >
	                      <option value="1"<?php if($intSendLocked) { echo " selected " ;} ?>>Sending Locked</option>
	                      <option value="0"<?php if(!$intSendLocked) { echo " selected " ;} ?>>Can send</option>
	                    </select>

                </div>
            </div>	        
	        
	        
	        <div class="row">
	            <!-- BEGIN MAIN TABLE OF SETTINGS DATA FOUNDATION 2+10 COLUMNS -->


	            <!-- Begin row Country 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Country</h6>
	                </div>
	                
	                
	                <div class="small-8 columns">
	                    <select name="country"id="country" >
	                      <option value="0"<?php if($intCountryID < 1) { echo " selected " ;} ?>>Country...</option>
	                      <?php                       
	                        $query=	"SELECT * FROM " .TBL_COUNTRIES. " ORDER BY sortid DESC, name ASC";
	                        //echo "SQL STMNT = " . $q .  "<br>";
	                        $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());

	                        //Begin Record While Loop 
	                        while ($row=mysqli_fetch_array($rs))
	                        {
	                            $intCountryID_DB=		$row["id"];
	                            $strCountryName_DB=		$row["name"];
	                            $strCountryCode_DB=		$row["code"];
	                        ?>
	                      <option value="<?php echo $intCountryID_DB ?>"<?php if($intCountryID_2 == $intCountryID_DB) { echo " selected " ;} ?>><?php echo $strCountryName_DB . " - ".$strCountryCode_DB ?></option>
	                      <?php } //End Record While Loop ?>
	                    </select>
	                </div>
	            </div>
	            <!-- End row Country -->


	            <!-- Begin row Currency 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Currency</h6>
	                </div>
	                <div class="small-8 columns">
	                    <select name="currency">
	                    <?php                       
	                        $query=	"SELECT * FROM " .TBL_CURRENCY. " ORDER BY sortid DESC, currency_name ASC";
	                        //echo "SQL STMNT = " . $q .  "<br>";
	                        $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());

	                        //Begin Record While Loop
	                        while ($row=mysqli_fetch_array($rs))
	                        {
	                            $intCurrencyID_DB=		$row["currency_id"];
	                            $strCurrencyName=		$row["currency_name"];
	                            $strCurrencyCode=		$row["currency_code"];

	                    ?>
	                      <option value="<?php echo $intCurrencyID_DB ?>"<?php if($intCurrencyID == $intCurrencyID_DB) { echo " selected " ;} ?>><?php echo $strCurrencyName ?> - <?=$strCurrencyCode?></option>
	                    <?php } //End Record While Loop ?>
	                    </select>
	                </div>
	            </div>
	            <!-- End row Currency -->


	            <!-- Begin row Password 3+8+1 Columns 
	            <div class="row">
	                <div class="small-3 columns">
	                    <h6>Password</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="password" type="password" required placeholder="password" value="<? echo $Password_DB; ?>" maxlength="30">
	                </div>
	                <div class="small-1 columns">
	                </div>
	            </div>
	             End row Password -->


	            <!-- Begin row Email 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Email</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="email" type="text" placeholder="your email"value="<?=$Email_DB?>" maxlength="50">
	                </div>

	            </div>
	            <!-- End row Email -->


	            <!-- Begin row Mobile Phone 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Mobile Phone</h6>
	                </div>
	                <div class="small-8 columns">

	                    <!-- BEGIN internal row to separate Country Code from Mobile Phone number -->
	                    <div class="row">
	                        <div class="small-3 columns">
	                            <input name="cellphone_countrycode" type="text" placeholder="country code" value="<?=$strCellPhone_code?>" maxlength="6">
	                        </div>
	                        <div class="small-9 columns">
	                            <input name="cellphone" type="text" placeholder="your mobile phone" value="<?=$strCellPhone?>" maxlength="50">
	                        </div>
	                    </div>
	                    <!-- END internal row to separate Country Code from Mobile Phone number -->

	                </div>
	            </div>
	            <!-- End row Mobile Phone -->


	            <!-- Begin row Business Name 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>First Name</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="namefirst" type="text" placeholder="first name" id="namefirst" value="<?=$strNameFirst?>" maxlength="50" />
	                </div>

	            </div>
	            <!-- End row Business Name -->


	            <!-- Begin row Business Name 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Last Name</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="namelast" type="text" placeholder="last name" id="namelast" value="<?=$strNameLast?>" maxlength="50" />
	                </div>

	            </div>
	            <!-- End row Business Name -->


	            <!-- Begin row Address1 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Address 1</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="address" type="text" placeholder="address 1" id="address1" value="<?=$strAddress_DB?>" maxlength="50" />
	                </div>

	            </div>
	           <!--  End row Address1 -->


	            <!-- Begin row Address2 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Address 2</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="address2" type="text" placeholder="address 2" id="address2" value="<?=$strAddress2_DB?>" maxlength="50" />
	                </div>

	            </div>
	             <!--End row Address2 -->


	            <!-- Begin row City and State/Province 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>City, State/Province</h6>
	                </div>
	                <div class="small-8 columns">

	                    <!-- BEGIN internal row to separate City and State/Province -->
	                    <div class="row">
	                        <div class="small-6 columns">
	                            <input name="cityname" type="text" placeholder="city" id="cityname" value="<?=$strCityName_DB?>" maxlength="50" />
	                        </div>
	                        <div class="small-6 columns">
	                            <input name="state" type="text" placeholder="state/province" id="state" value="<?=$strState_DB?>" maxlength="50" />
	                        </div>
	                    </div>
	                    <!-- END internal row to separate City and State/Province -->

	                </div>

	            </div>
	            <!-- End row City and State/Province -->


	            <!-- Begin row Display Country based on Country Code selected and Postal Code 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Country</h6>
	                </div>
	                <div class="small-8 columns">
	                    <!-- BEGIN internal row to separate Country and Postal Code -->
	                    <div class="row">
	                        <div class="small-6 columns">
	                            <?=$strCountryName_DB_top?>
	                        </div>
	                        <div class="small-6 columns">
	                            <input name="postal" type="text" placeholder="postal code" id="postalcode" value="<?=$strPostal_DB?>" maxlength="50" />
	                        </div>
	                    </div>
	                    <!-- END internal row to separate Country and Postal Code  -->
	                </div>
	            </div>
	            <!-- End row Display Country and Postal Code -->


	            <!-- Begin row Bitcoin Wallet Address 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Bitcoin Wallet Address</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="btcaddress" type="text" placeholder="your outside bitcoin wallet address" id="name" value="<?=$strBitCoinAddress?>" maxlength="50" />
	                </div>

	            </div>
	            <!-- End row Bitcoin Wallet Address -->


	            <!-- Begin row Bank Account # 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Bank Account #</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="bankaccount" type="text" placeholder="your bank account number" id="website" value="<?=$strBankAccount?>" maxlength="50">
	                </div>
	            </div>
	            <!-- End row Bank Account # -->


	            <!-- Begin row Bank Routing # 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>Bank Routing #</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="bankrouting" type="text" placeholder="your bank account routing" id="website" value="<?=$strBankRouting?>" maxlength="50">
	                </div>

	            </div>
	            <!-- End row Bank Routing # -->


	            <!-- Begin row PayPal Email 3+8+1 Columns -->
	            <div class="row">
	                <div class="small-4 columns">
	                    <h6>PayPal Email</h6>
	                </div>
	                <div class="small-8 columns">
	                    <input name="paypalemail" type="text" placeholder="paypal email" value="<?=$strPayPalAddress?>" maxlength="50">
	                </div>

	            </div>
	            <!-- End row PayPal Email -->
                
                



	            <!-- Begin row Update Account Button 12 Columns Centered -->
	            <div class="row">
	                <center>
	                    <div class="small-12 columns">
							<input name="userid" id="userid" type="hidden" value="<?=$intMemberID?>" />
	                        <button type="submit" class="button" style="width:200px;">Update Account</button>
	                    </div>
	                </center>
	            </div>
	            <!-- End row Update Account Button 12 Columns Centered -->


	            <!-- Begin row Item 3+8+1 Columns
	            <div class="row">
	                <div class="small-3 columns">
	                    <h6></h6>
	                </div>
	                <div class="small-8 columns">
	                </div>
	                <div class="small-1 columns">
	                </div>
	            </div>
	             End row Item -->
	        </div>
	        </form>
	        </div>
	        <!-- END MAIN TABLE OF SETTINGS DATA FOUNDATION 2+10 COLUMNS -->

<!--


				<a name="passwordupdate"></a>
				<form data-abide action="<?=CODE_DO?>?do=updatepassword" name="passwordupdate" method="POST">
					<div class="panel radius">
						<div class="password">
						    <label>Password</label>
						    <input name="password" type="password" required id="password" placeholder="password" style="width:200px;">
							<input name="password2" type="password" required id="password2" placeholder="confirm password" style="width:200px;">
							<?php if($strError_passwordupdate){ echo $strError_passwordupdate." <br>" ; } ?>
							<a href="javascript:;" class="button" onClick="validateForm_passwordupdate();">Update Password</a><br>
						</div>
		            </div>
				</form>		
				
				

				<form data-abide action="<?=CODE_DO?>?do=sendphonecode" method="POST">
		            <div class="panel radius">
						<div class="testphone">
						    <label>Test Phone <small></small></label>

							<div class="row">
								<div class="small-1 columns">
								    <input name="phone_countrycode" type="text" required style="width:50px;" value="<?=$strCellPhone_code?>">
								</div>
								<div class="small-9 columns">
									<input name="phone" type="text" required placeholder="cellphone #" style="width:150px;" value="<?=$strCellPhone?>">
								</div>
							</div>

							<button type="submit">Send Test MSG to Phone</button>
							<span class="txtError"><?=$strError_testphone?></span><br>

						</div>
		            </div>
				</form>

				<? if(!$strVerificationPhone){ ?>
				<form data-abide action="<?=CODE_DO?>?do=confirmphonecode" method="POST">
		            <div class="panel radius">
						<div class="confirmphone">
						    <label>Confirm Phone <small></small></label>
						    <input name="phonecode" type="text" placeholder="enter code sent to phone">
							<span class="txtError"><?=$strError_confirmphone?></span>
							<button type="submit">Confirm Phone Code</button><br>
						</div>
		            </div>
				</form>	
				<? } ?>
-->

	




		</div>
		<!-- END SIDEBAR CONTENT AREA 4 OF 12 COLUMNS -->

	</div>
	</div>
	<!-- END MAIN+SIDEBAR CONTENT AREA 8+4 COLUMNS -->



<div class="row">
<div class="small-12 columns">

            <br><br>
            <h3>Transactions</h3><br>
            
                 <!--transactions-->
				 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
					<thead>
			        <tr>
			          	<td align="left" width="5%"><h5>Txn</h5></td>
			          	<td align="left" width="5%"><h5>Type</h5></td>
			          	<td align="left" width="5%"><h5>Order</h5></td>
						<td align="left" width="65%"><h5>Date</h5></td>
<!--						<td align="left" width="40%"><h5></h5></td>-->
			          	<td align="left" width="5%"><h5></h5></td>
			          	<td align="left" width="10%"><h5>Status</h5></td>
			          	<td align="left" width="5%"><h5>Amount</h5></td>
			        </tr>
			  		</thead>
					<tbody id="tabledata">
					<?php 
					if($intMemberID){ //chestid specified and not a new chest
						$strDo= "include";
						$sortby="top";
						$intType = "transactions"; //files - get from top
						//$intLastMSGID = 0; 
						$intMaxRecords = 100 ; //get from top
						$intRecID = false;
						$intUserID_viewer = $intMemberID ; 
						if($intShowEditMod){$intMod="1";}
						include __ROOT__.ADMIN_MOD_LOADCONTENT ;
					}
					?>
					</tbody>
			    </table>


</div>
</div>





<script src="/js/jquery.js"></script>
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