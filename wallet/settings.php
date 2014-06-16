<?php 
require "inc/session.php";

//Define Page Values
$strThisPage = 		PAGE_SETTINGS;
$intUserID = 		funct_GetandCleanVariables(DETECT_USERID);
$strDo = 			funct_GetandCleanVariables($_GET['do']);
//echo "do= " .$strDo. "<br>" ;

$strError = 				(funct_GetandCleanVariables($_GET['error']));
$strError_passwordupdate = 	(funct_GetandCleanVariables($_GET['error_password']));
$strError_testphone = 		(funct_GetandCleanVariables($_GET['error_testphone']));
$strError_confirmphone = 	(funct_GetandCleanVariables($_GET['error_confirmphone']));
$strError_confirmemail = 	(funct_GetandCleanVariables($_GET['error_confirmemail']));


if($strDo=="welcome"){$strError="Email Confirmed. Please Fill in your Address below";}

//Check if logged in. If not then send to login page with an error.
if($intUserID=="") { 
	header('Location: ' . PAGE_ERROR. '?error=you are not logged in' ) ;  die(); //Make sure code after is not executed
}


	if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
	$strSQL = "SELECT id,password,email,cellphone,first_name,last_name,address,address2,cityname,state,postal,country_id,country_phonecode,currency_id,currency_symbol,crypto_miner_fee,verification_level,verification_phone,verification_email FROM ".TBL_USERS." WHERE id = ? " ;
	//echo "$strSQL <br>";
	//echo  " $intUserID <br>";
	if( $stmt = $DB_MYSQLI->prepare($strSQL) ) { 
		$stmt -> bind_param("i", $intUserID); //Bind parameters s - string, b - blob, i - int, etc
		$stmt -> execute(); //Execute it
		$stmt -> bind_result($intUserID_DB,$Password_DB,$Email_DB,$strCellPhone,$strNameFirst,$strNameLast,$strAddress_DB,$strAddress2_DB,$strCityName_DB,$strState_DB,$strPostal_DB,$intCountryID_2,$strCellPhone_code,$intCurrencyID,$strCurrencyCode,$intMinerFee,$strVerificationLevel,$strVerificationPhone,$strVerificationEmail,$kyc ); //bind results
		$stmt -> fetch(); //fetch the value
		//$stmt -> close(); //Close statement
		
		//echo "$intUserID_DB,$Password_DB,$Email_DB,$strCellPhone,$strNameFirst,$strNameLast,$strAddress_DB,$strAddress2_DB,$strCityName_DB,$strState_DB,$strPostal_DB,$intCountryID_2,$strCellPhone_code,$intCurrencyID,$strCurrencyCode,$intMinerFee,$strVerificationLevel,$strVerificationPhone,$strVerificationEmail,$kyc  <br><br>";
		
	}else{
		echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; 
	}




	if(!$intMinerFee){$intMinerFee=MININGFEE_NORMAL;}
	
	
/*
	//Get User Data from DataBase
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$row=mysqli_fetch_array($rs) ;
	$intUserID_DB=					$row["id"];
	//$UserName_DB=					$row["username"];
	$Password_DB=					$row["password"];
	$Email_DB=						$row["email"];
	$strCellPhone=					$row["cellphone"];
	
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

	$strBitCoinAddress=				$row["btc_address"];
	$intMinerFee= 					$row["crypto_miner_fee"];
	if(!$intMinerFee){$intMinerFee=MININGFEE_NORMAL;}
	
	$strPayPalAddress=				$row["paypalemail"];
	$strLiteCoinAddress=			$row["lc_address"];
	$strBankAccount=				$row["bank_account"];
	$strBankRouting=				$row["bank_routing"];

	$strWebsite=					$row["websiteurl"];
	$strVerificationLevel=			$row["verification_level"];
	$strVerificationPhone=			$row["verification_phone"];
	$strVerificationEmail=			$row["verification_email"];
	$kyc=							$row["kyc"];
	
	//$intGoalAmount=				$row["goal_amount"];
	//$intGoalType=					$row["goaltype"];
	//$Date_DB=						$row["timestamp"];
	
	//$intProfilePic_DB=			$row["profilepic"];
	//$strProfilePicPath =  PROFILEPICTUREPATH.$intUserID_DB.".jpg" ;
*/

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
<html xmlns="http://www.w3.org/1999/xhtml"><head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Account Settings<?=TITLE_END?></title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="img/favicon.png" />
	<link rel="stylesheet" href="css/web.css" />
	<link rel="stylesheet" href="css/foundation.css" />
	<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
	<script src="js/modernizr.js"></script>
	

<script language="javaScript">

	$(document).ready(function(){
		
		<?php if($strDo=="emailconfirmed"){ ?>
		//alert('Email Confirmed! Please fill in details');
        $('#emailconfirm').foundation('reveal', 'open');			
		<? } ?>
	}); //close ready function

	function validateForm_passwordupdate() {
	  var okSoFar=true
	
		if (document.passwordupdate.passwordold.value=="") {
			okSoFar=false
			alert("Please type your current password")
			document.passwordupdate.passwordold.focus()
			return false;
		}

		if (document.passwordupdate.password.value=="") {
			okSoFar=false
			alert("Please type your new password")
			document.passwordupdate.password.focus()
			return false;
		}
	
		if (document.passwordupdate.password2.value=="") {
			okSoFar=false
			alert("Please Retype your new password to Confirm it")
			document.passwordupdate.password2.focus()
			return false;
		}
	
		if (document.passwordupdate.password.value != document.passwordupdate.password2.value) {
			alert("Your new password's don't match")
			return false;
		} 
	
	  if (okSoFar==true) {
		document.passwordupdate.submit()
	  }
	}



	function validateForm() {
	  var okSoFar=true
	
		if (document.signup.password.value=="") {
			okSoFar=false
			alert("Enter a Password Please.")
			document.signup.password.focus()
			return false;
		}
		
		if ((document.signup.password.value.length < 6) || (document.signup.password.value.length > 24)) {
			okSoFar=false
			alert("Enter a Password between 6 to 12 characters please.")
			document.signup.password.focus()
			return false;
		}
		

	/*
		if (document.signup.passwordConfirm.value=="") {
			okSoFar=false
			alert("Please Retype your password to Confirm it")
			document.signup.passwordConfirm.focus()
			return false;
		}
	
		if (document.signup.password.value != document.signup.passwordConfirm.value) {
			alert("Your passwords do not match.")
			return false;
		} 
	*/	 
		//-- Reject eMail address if it doesn't contain an @ character.
		var foundAt = document.signup.email.value.indexOf("@",0)
		if (foundAt < 1 && okSoFar) {
			okSoFar = false
			alert ("Enter a working Email Please.")
			document.signup.email.focus()
			return false;
		}
	
		
	  if (okSoFar==true) {
		document.signup.submit()
	  }
	}
	
	
	function confirmDelete(delUrl) {
	  if (confirm("Are you sure ?")) {
		//document.location = delUrl;
		
		openInIFrame_All('hud_items_write_iframe_questlog','hud_items_write_questlog','<?=PAGEDO?>?do=deleteaccount');
	  }
	} 

	function functjs_UpdateCity(intRegionID, intCountryID, intCityID, strCityName){
			
		//change hidden form value
		//document.getElementById('cityid').value = intCityID ;
		
		//change region pic
		document.getElementById('regionpic').src = '/images/worldmapregions' + intRegionID + '_800.png' ;
		
		//change city nametext
		document.getElementById('cityname').innerHTML = strCityName ;
		
		//change flag 
		document.getElementById('countrypic').src = '/images/flags/' + intCountryID + '.png' ;
	
		//close window
		toggle_visibility('hud_items_write');
		
	}
	

	function functjs_pickCountry(intCountryID,strCountryName) {
		
		//set hidden form value
		document.getElementById('countryid').value = intCountryID;
		
		//update via ajax
		$.post("<?=PAGEDOAJAX?>?do=updateflag&countryid=" + intCountryID + "&userid=" + <?=$intUserID; //the logged in user detect_userid cookie ?> ,
			function(data){
				if (data == 'done') {
	
					//set flag pic for country
					document.getElementById('flagimage').src = '/images/flags/' + intCountryID + '.png';
					document.getElementById('flagname').innerHTML = strCountryName ;		
					
					//close iframe
					//document.getElementById('divCountrylist').style.display='none'; 	
					toggle_visibility('divCountrylist');	//close window		
	
				}
		});
	
	}

</script>

</head>


<body onLoad="<?=$strOnBodyLoadJS?>">

<?php require "hud.php"; ?>

<p></p>
<!-- BEGIN MAIN+SIDEBAR CONTENT AREA 8+4 COLUMNS -->
<div class="row">

    <!-- BEGIN MAIN CONTENT AREA 8 OF 12 COLUMNS -->
	<div class="small-12 medium-8 columns">
		
		<h3>Settings <?=$Email_DB?></h3><br>
		<h4><?=$strError?></h4>

        
        <!-- BEGIN SETTINGS MAIN TABLE -->
        <form data-abide action="<?=CODE_DO?>?do=update" method="POST" name="update">
            
            <!-- Begin row Country 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Country</h6>
                </div>
                <div class="small-8 columns">
                    <select name="country" id="country" >
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
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Country -->
            
            
            <!-- Begin row Currency 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
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
                <div class="small-1 columns">
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
            
            
            <!-- Begin row Email 3+8+1 Columns
            <div class="row">
                <div class="small-3 columns">
                    <h6>Email</h6>
                </div>
                <div class="small-8 columns">
                    <input name="email" type="text" required placeholder="your email"value="<?=$Email_DB?>" maxlength="50">
                    <small class="error">Email is required</small>
                </div>
                <div class="small-1 columns">
                </div>
            </div>
             End row Email -->
            
            
            <!-- Begin row Mobile Phone 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Mobile Phone</h6>
                </div>
                <div class="small-8 columns">

                      <input name="cellphone" type="text" required placeholder="your mobile phone" value="<?=$strCellPhone?>" maxlength="50">
                      <small class="error">Mobile Phone is required</small>

                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Mobile Phone -->
           

            <!-- Begin row First Name 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>First Name</h6>
                </div>
                <div class="small-8 columns">
                    <input name="namefirst" type="text" required placeholder="first name" id="namefirst" value="<?=$strNameFirst?>" maxlength="50" />
                    <small class="error">First Name is required</small>
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row First Name -->
 
            
            <!-- Begin row Last Name 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Last Name</h6>
                </div>
                <div class="small-8 columns">
                    <input name="namelast" type="text" required placeholder="last name" id="namelast" value="<?=$strNameLast?>" maxlength="50" />
                    <small class="error">Last Name is required</small>
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Last Name -->
           
            
            <!-- Begin row Address1 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Address 1</h6>
                </div>
                <div class="small-8 columns">
                    <input name="address" type="text" required placeholder="address 1" id="address1" value="<?=$strAddress_DB?>" maxlength="50" />
                    <small class="error">Address is required</small>
                </div>
                <div class="small-1 columns">
                </div>
            </div>
           <!--  End row Address1 -->
            
            
            <!-- Begin row Address2 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Address 2</h6>
                </div>
                <div class="small-8 columns">
                    <input name="address2" type="text" placeholder="address 2" id="address2" value="<?=$strAddress2_DB?>" maxlength="50" />
                </div>
                <div class="small-1 columns">
                </div>
            </div>
             <!--End row Address2 -->
            
            
            <!-- Begin row City and State/Province 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>City, State/Province</h6>
                </div>
                <div class="small-8 columns">

                    <!-- BEGIN internal row to separate City and State/Province -->
                    <div class="row">
                        <div class="small-6 columns">
                            <input name="cityname" type="text" required placeholder="city" id="cityname" value="<?=$strCityName_DB?>" maxlength="50" />
                            <small class="error">City is required</small>
                        </div>
                        <div class="small-6 columns">
                            <input name="state" type="text" required placeholder="state/province" id="state" value="<?=$strState_DB?>" maxlength="50" />
                            <small class="error">S is required</small>
                        </div>
                    </div>
                    <!-- END internal row to separate City and State/Province -->
                    
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row City and State/Province -->
            
            
            <!-- Begin row Display Country based on Country Code selected and Postal Code 3+8+1 Columns -->
            <div class="row">
                <div class="small-3 columns">
                    <h6>Country</h6>
                </div>
                <div class="small-8 columns">
                    <!-- BEGIN internal row to separate Country and Postal Code -->
                    <div class="row">
                        <div class="small-6 columns">
                            <?=$strCountryName_DB_top?>
                        </div>
                        <div class="small-6 columns">
                            <input name="postal" type="text" required placeholder="postal code" id="postalcode" value="<?=$strPostal_DB?>" maxlength="50" />
                            <small class="error">Postal Code is required</small>
                        </div>
                    </div>
                    <!-- END internal row to separate Country and Postal Code  -->
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Display Country and Postal Code -->
            
            
            <!-- Begin row Bitcoin Wallet Address 3+8+1 Columns -->
<!--            <div class="row">
                <div class="small-3 columns">
                    <h6>Bitcoin Wallet Address</h6>
                </div>
                <div class="small-8 columns">
                    <input name="btcaddress" type="text" placeholder="your outside bitcoin wallet address - optional" id="name" value="<?=$strBitCoinAddress?>" maxlength="50" />
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Bitcoin Wallet Address -->
            
            
            <!-- Begin row Bitcoin Transaction Fee 3+8+1 Columns -->
<!--            <div class="row">
                <div class="small-3 columns">
                    <h6>Bitcoin Miner Fee (goes to the network)</h6>
                </div>
                <div class="small-8 columns">
                    <select name="miningfee" >
                        <option value="<?=MININGFEE_NORMAL?>"<?php if($intMinerFee == MININGFEE_NORMAL) { echo " selected " ;} ?>>Normal <?=MININGFEE_NORMAL?></option>
                        <option value="<?=MININGFEE_SLOW?>"<?php if($intMinerFee == MININGFEE_SLOW) { echo " selected " ;} ?>>Slow <?=number_format(MININGFEE_SLOW,5)?></option>
                        <option value="<?=MININGFEE_FAST?>"<?php if($intMinerFee == MININGFEE_FAST) { echo " selected " ;} ?>>Generous <?=MININGFEE_FAST?></option>
                    </select>
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Bitcoin Wallet Address -->
            
            
            <!-- Begin row Bank Account # 3+8+1 Columns -->
<!--            <div class="row">
                <div class="small-3 columns">
                    <h6>Bank Account #</h6>
                </div>
                <div class="small-8 columns">
                    <input name="bankaccount" type="text" placeholder="your bank account number - optional" id="website" value="<?=$strBankAccount?>" maxlength="50">
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Bank Account # -->
            
            
            <!-- Begin row Bank Routing # 3+8+1 Columns -->
<!--            <div class="row">
                <div class="small-3 columns">
                    <h6>Bank Routing #</h6>
                </div>
                <div class="small-8 columns">
                    <input name="bankrouting" type="text" placeholder="your bank account routing - optional" id="website" value="<?=$strBankRouting?>" maxlength="50">
                </div>
                <div class="small-1 columns">
                </div>
            </div>
            <!-- End row Bank Routing # -->
            
            
<!--            <div class="row">
                <div class="small-3 columns">
                    <h6>PayPal Email</h6>
                </div>
                <div class="small-8 columns">
                    <input name="paypalemail" type="text" placeholder="paypal email - optional" value="<?=$strPayPalAddress?>" maxlength="50">
                </div>
                <div class="small-1 columns">
                </div>
            </div>-->
            
            
            <!-- Begin row Update Account Button 12 Columns Centered -->
            <div class="row">
                <center>
                    <div class="small-12 columns">
                        <input name="countryid" id="countryid" type="hidden" value="<?=$intCountryID_DB?>" />
                        <button type="submit" class="button" style="width:200px;">Update Account</button>
                    </div>
                </center>
            </div>


	</div>
    <!-- END MAIN CONTENT AREA 8 OF 12 COLUMNS -->

    
    
    
	<!-- BEGIN SIDEBARK CONTENT AREA 4 OF 12 COLUMNS -->
	<div class="small-12 medium-4 columns">
		
<!--		<? if($strVerificationLevel){ ?>
		<div class="panel callout radius">
			<? if($strVerificationEmail){ echo "Email verified! <br>" ; } ?>
			<? if($strVerificationPhone){ echo "Phone verified!" ; } ?>
			
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

			<form data-abide action="<?=CODE_DO?>?do=sendphonecode" method="POST">
	            <div class="panel radius">
					<div class="testphone">
					    <label>Test Phone <small></small></label>
					<?php
					//properly format phone number based on country 
					//$strCellPhoneFull = funct_PhoneNumber_format($strCellPhone, $intCountryID_2, $intUserID) ;
					/*
					//check if their current number begins with their country code
					$strCellPhone = ltrim($strCellPhone,"0"); //remove any leading zeros
					$strCellPhone = trim($strCellPhone); //remove any spaces from the number
					$strCellPhone = preg_replace("/[^0-9]/", "", $strCellPhone); //numbers only. no -() etc..
					$strCellPhoneFull=$strCellPhone; //we assume number is good
					
					//test if proper format
					$pos = strpos($strCellPhone, $intPhoneCountryCode);
					//echo "is ($intPhoneCountryCode) in [$strCellPhone] pos= ". $pos."<br>";
					//if($pos==true){ echo "proper!<br>" ;}
					if($pos==false){ //string is not found in number
						//echo "not found! pos=$pos";
						$strCellPhoneFull=$intPhoneCountryCode.$strCellPhone; //if no then add the code to the field with the number
					}else{//else it is found in number
						if($pos>0){//if not found at the very beginning
							//echo "... but not at the begging so add code";
							$strCellPhoneFull=$intPhoneCountryCode.$strCellPhone; //if no then add the code to the field with the number
						}
					}
					*/
					?>
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
-->						<!--
						full international phone number please:<br> 
						USA example 1 6461111234 <br> 
						Germany example 49 111112345<br> 
						Russia example 7 111112345<br>
					</div>
	            </div> -->
			</form>
<!--			
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
			<a name="passwordupdate"></a><br><br><br><br>
			<form action="<?=CODE_DO?>?do=updatepassword" name="passwordupdate" method="POST">
				<div class="panel radius">
					<div class="password">
					    <label>Password</label>
					    <h4><?=$strErrorPassword?></h4>
					    <div class="small-12 columns">
					    <input name="passwordold" type="password" required id="passwordold" placeholder="current password">
                        </div>
                        <div class="small-12 columns">
					    <input name="password" type="password" required id="password" placeholder="new password">
                        </div>
                        <div class="small-12 columns">
						<input name="password2" type="password" required id="password2" placeholder="confirm new password">
                        </div>
					    
						<?php if($strError_passwordupdate){ echo $strError_passwordupdate." <br>" ; } ?>
						<a href="javascript:;" class="button" onClick="validateForm_passwordupdate();">Update Password</a><br>
					</div>
	            </div>
			</form>			

		
		<?php //include __ROOT__."/inc/side_contact.php"; ?>

		
		<!--
		
		<div class="mediabutton" onMouseOver="this.style.borderColor='#000';" onMouseOut="this.style.borderColor='grey';" style="position:relative; top:50px;">
			<IFRAME id="uploadbulk_iframe" src="/scripts/blueimp-jquery/custom.php?upload=avatar&userid1=<?=DETECT_USERID?>&un=<?=DETECT_USERNAME?>&ki=" style="position:absolute; left:0px; top:0px;  z-index:1; overflow-y: auto; overflow-x: hidden;">
			</IFRAME>
			<img id="avatarimg" style="z-index:0;" src="<?=$strProfilePicURL?>?d=<?=createRandomKey(4)?>" width="100%" height="100%" />
		</div>
		
		-->	
		
		<!--
		
		<a href="javascript:confirmDelete('delete.page?id=1')" style="text-decoration:none;">
			<div class="mediabutton" style="height:70px;" onMouseOver="this.style.borderColor='#000';" onMouseOut="this.style.borderColor='grey';">
				<span class="txtRPG_Views" style="text-align:center">Delete Account</span>
				<img src="/images/bomb.png" width="48" height="48" border="0">
			</div>
		</a>
		
		-->

        


        
		
	</div>
	<!-- END SIDEBARK CONTENT AREA 4 OF 12 COLUMNS -->

</div>
<!-- END MAIN+SIDEBAR CONTENT AREA 8+4 COLUMNS -->


<div id="emailconfirm" class="reveal-modal medium" data-reveal> 
	<h4>Your email is now confirmed. Please fill in the fields below.</h4>
	<a class="close-reveal-modal">&#215;</a> 
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