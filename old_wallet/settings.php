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
	//echo "$strSQL $intUserID <br>";
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
	<title>Account Settings</title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel="icon" type="image/png" href="img/favicon.png" />

    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <link href="css/bootstrapValidator.min.css" rel="stylesheet" />

	<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
	

<script language="javaScript">

	$(document).ready(function(){
		
		<?php if($strDo=="emailconfirmed"){ ?>
		//alert('Email Confirmed! Please fill in details');
        $('#emailconfirm').modal('show');
		<? } ?>
	}); //close ready function



    $(document).ready(function() {
        $('#passwordupdate').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {

                password2: {
                    message: 'The password is not valid',
                    validators: {
                        notEmpty: {
                            message: 'The password is required and cannot be empty'
                        },
                        stringLength: {
                            min: 6,
                            max: 30,
                            message: 'The password must be more than 6 and less than 30 characters long'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9_!@#$%^^&*]+$/,
                            message: 'The password can only consist of alphabetical, number and symbols like !@#$%^&*'
                        }
                    }
                }


            }
        });



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
<div class="container-fluid">

<!-- BEGIN MAIN+SIDEBAR CONTENT AREA 8+4 -->
<div class="row">

    <!-- BEGIN MAIN CONTENT AREA 8 OF 12 -->
	<div class="col-xs-12 col-md-8">

    <div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">Settings <?=$Email_DB?></h4>
    </div>
    <div class="panel-body">

		<h4><?=$strError?></h4>

        
        <!-- BEGIN SETTINGS MAIN TABLE -->
        <form role="form" action="<?=CODE_DO?>?do=update" method="POST" name="update">
            
            <!-- Begin row Country 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Country</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                    <select class="form-control" name="country" id="country" >
                      <option class="form-control" value="0"<?php if($intCountryID < 1) { echo " selected " ;} ?>>Country...</option>
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
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row Country -->
            
            
            <!-- Begin row Currency 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Currency</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                    <select class="form-control" name="currency">
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
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row Currency -->
            

            <!-- Begin row Mobile Phone 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Mobile Phone</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                      <input class="form-control" name="cellphone" type="text" required placeholder="your mobile phone" value="<?=$strCellPhone?>" maxlength="50">
                    </div>
                </div>
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row Mobile Phone -->
           

            <!-- Begin row First Name 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>First Name</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                        <input class="form-control" name="namefirst" type="text" required placeholder="first name" id="namefirst" value="<?=$strNameFirst?>" maxlength="50" />
                    </div>
                </div>
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row First Name -->
 
            
            <!-- Begin row Last Name 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Last Name</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                        <input class="form-control" name="namelast" type="text" required placeholder="last name" id="namelast" value="<?=$strNameLast?>" maxlength="50" />
                    </div>
                </div>
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row Last Name -->
           
            
            <!-- Begin row Address1 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Address 1</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                        <input class="form-control" name="address" type="text" required placeholder="address 1" id="address1" value="<?=$strAddress_DB?>" maxlength="50" />
                    </div>
                </div>
                <div class="col-xs-1">
                </div>
            </div>
           <!--  End row Address1 -->
            
            
            <!-- Begin row Address2 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Address 2</h6>
                </div>
                <div class="col-xs-8">
                    <div class="form-group">
                        <input class="form-control" name="address2" type="text" placeholder="address 2" id="address2" value="<?=$strAddress2_DB?>" maxlength="50" />
                    </div>
                </div>
                <div class="col-xs-1">
                </div>
            </div>
             <!--End row Address2 -->
            
            
            <!-- Begin row City and State/Province 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>City, State/Province</h6>
                </div>
                <div class="col-xs-8">

                    <!-- BEGIN internal row to separate City and State/Province -->
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <input class="form-control" name="cityname" type="text" required placeholder="city" id="cityname" value="<?=$strCityName_DB?>" maxlength="50" />
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <input class="form-control" name="state" type="text" required placeholder="state/province" id="state" value="<?=$strState_DB?>" maxlength="50" />
                            </div>
                        </div>
                    </div>
                    <!-- END internal row to separate City and State/Province -->
                    
                </div>
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row City and State/Province -->
            
            
            <!-- Begin row Display Country based on Country Code selected and Postal Code 3+8+1 -->
            <div class="row">
                <div class="col-xs-3">
                    <h6>Country</h6>
                </div>
                <div class="col-xs-8">
                    <!-- BEGIN internal row to separate Country and Postal Code -->
                    <div class="row">
                        <div class="col-xs-6">
                            <?=$strCountryName_DB_top?>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <input class="form-control" name="postal" type="text" required placeholder="postal code" id="postalcode" value="<?=$strPostal_DB?>" maxlength="50" />
                            </div>
                        </div>
                    </div>
                    <!-- END internal row to separate Country and Postal Code  -->
                </div>
                <div class="col-xs-1">
                </div>
            </div>
            <!-- End row Display Country and Postal Code -->

            
            <!-- Begin row Update Account Button 12 Centered -->
            <p></p>
            <input class="form-control" name="countryid" id="countryid" type="hidden" value="<?=$intCountryID_DB?>" />
            <button type="submit" class="btn btn-primary btn-block" >Update Account</button>



	</div>
    <!-- END MAIN CONTENT AREA 8 OF 12 -->


    </div>
    </div>
    
    
    
	<!-- BEGIN SIDEBARK CONTENT AREA 4 OF 12 -->
	<div class="col-xs-12 col-md-4">
		
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
					    <input class="form-control" name="emailcode" type="text" placeholder="email code">
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
							<div class="col-xs-1">
							    <input class="form-control" name="phone_countrycode" type="text" required style="width:50px;" value="<?=$strCellPhone_code?>">
							</div>
							<div class="col-xs-9">
								<input class="form-control" name="phone" type="text" required placeholder="cellphone #" style="width:150px;" value="<?=$strCellPhone?>">
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
					    <input class="form-control" name="phonecode" type="text" placeholder="enter code sent to phone">
						<span class="txtError"><?=$strError_confirmphone?></span>
						<button type="submit">Confirm Phone Code</button><br>
					</div>
	            </div>
			</form>	
			<? } ?>
-->

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Change Password</h4>
            </div>
            <div class="panel-body">

            <a name="passwordupdate"></a>
                <form role="form" action="<?=CODE_DO?>?do=updatepassword" name="passwordupdate" method="POST">
                    <div class="panel">
                        <div class="password">

                            <h4><?=$strErrorPassword?></h4>

                            <div class="form-group">
                                <input class="form-control" name="passwordold" type="password" required id="passwordold" placeholder="current password">
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="password" type="password" required id="password" placeholder="new password">
                            </div>

                            <div class="form-group">
                                <input class="form-control" name="password2" type="password" required id="password2" placeholder="confirm new password">
                            </div>

                            <?php if($strError_passwordupdate){ echo $strError_passwordupdate." <br>" ; } ?>
                            <a href="javascript:;" class="btn btn-primary btn-block" onClick="validateForm_passwordupdate();">Update Password</a>


                        </div>
                    </div>
                </form>

            </div>
        </div>


        
		
	</div>
	<!-- END SIDEBARK CONTENT AREA 4 OF 12 -->

</div>
<!-- END MAIN+SIDEBAR CONTENT AREA 8+4 -->


<!-- Modal - send success-->
<div class="modal fade" id="emailconfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4>Your email is now confirmed. Please fill in the fields below.</h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</div>

<script src="js/bootstrap.min.js"></script>
<script src="js/angular.min.js"></script>
<script src="js/bootstrapValidator.min.js"></script>

</body>
</html>