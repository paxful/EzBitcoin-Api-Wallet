<?php 
ob_start(); //so we can redirect even after headers are sent

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
$strThisPage = 		funct_ScrubVars(PAGE_WELCOME);
$intUserID = 		funct_ScrubVars(DETECT_USERID);
$strDo = 			funct_ScrubVars($_GET['do']);
//echo "do= " .$strDo. "<br>" ;
if(!$strDo){$strDo="confirmemail";}

$strType = 			funct_ScrubVars($_GET['type']);

$strError = 				(funct_ScrubVars($_GET['error']));
$strError_passwordupdate = 	(funct_ScrubVars($_GET['error_password']));
$strError_testphone = 		(funct_ScrubVars($_GET['error_testphone']));
$strError_confirmphone = 	(funct_ScrubVars($_GET['error_confirmphone']));
$strError_confirmemail = 	(funct_ScrubVars($_GET['error_confirmemail']));



switch ($strDo){

	//!CASE activatereceiveaddress
	case "activatereceiveaddress": //make wallet code

		$query="SELECT * FROM ".TBL_USERS." WHERE id= ".$intUserID ;
		//echo "SQL STMNT = " . $query . "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); 
		if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
			$FormRegEmail= 					$row["email"];
			$strWalletBTCaddress= 			$row["wallet_btc"]; //blockchain.info
			$strWallet_MainAddress_CC=		$row["wallet_address_cc"]; //coincafe.co amsterdam wallet address
		}
		
		//if wallet address does not exist then make them a new recieving wallet address
		if(!$strWalletBTCaddress){
			//make them a new  address
			$strWalletBTCaddress = funct_MakeWalletAddressUpdate($intUserID);
		}
		
		if(WALLET_NEWADDRESS_HOST=="blockchain.info"){
			/*
			//if they have an old wallet address then simply unarchive it at blockchain.info
			if($strWalletBTCaddress){
				$strResponse = funct_Billing_UnArchiveAddress($strWalletBTCaddress); 
			}else{//if they don't have a blockchain.info wallet address then make them a new one at amsterdam
				$strWalletLabel = $intUserID." ".$FormRegEmail ;
				$strWalletBTCaddress = funct_Billing_NewWalletAddress($strWalletLabel);
				//$strWalletBTCaddress = "x testing...";			
			}
			*/
		}
		
		if($strWalletBTCaddress){
			$strError = "Receive Address created." ;
		}else{
			$strError = "ERROR - Receive Address NOT created. Please try again later.. sorry" ;
		}


		//update password
		$strPassword = funct_ScrubVars($_POST['password']);
		$strPassword2 = funct_ScrubVars($_POST['password2']);
		if($strPassword AND $strPassword2){
			
			if($strPassword2!=$strPassword){ 
				$strError = "passwords do not match";  
			}else{
				//encrypt password
				$strPassword_hash = password_hash($strPassword, PASSWORD_DEFAULT); //PASSWORD_BCRYPT
/*
				//Update Database
				$query = "UPDATE ".TBL_USERS." SET password='$strPassword_hash' , date_passwordchanged= NOW() WHERE id = $intUserID " ;
				//echo "SQL STMNT = " . $query .  "<br>";
				$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
				$strError = $strError . " Password Updated.";
*/				
				$strDateTime=date("Y-m-d H:i:s");
				if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
				if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET password = ? , date_passwordchanged = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
				if(!($stmt->bind_param('ssi',								$strPassword_hash, $strDateTime,$intUserID  ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
				if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}
				
				
				//email them to remind them that they updated their password... include the password in the email?
				$strSubject = "You just changed your ".WEBSITENAME." password ";						
				$strBody = "and... activated your new bitcoin receiving address! Just give this out to anyone who wants to send you bitcoin and it will get added to your wallet balance \n ".
				"$strWalletBTCaddress \n\n ".
				" \n -Thank you \n ".WEBSITENAME." \n ".WEBSITEFULLURLHTTPS ; 
				funct_Mail_simple($FormRegEmail,$strSubject,$strBody);
			}
		}
		
		header( 'Location: '. PAGE_WALLET.'?error='.$strError ); die(); //Make sure code after is not executed

		break;


	//!CASE sendemailcode
	case "sendemailcode": //send email confirm code

		funct_SendEmailCode($intUserID);
		$strDo="confirmemail";
		$strError = "Email sent!";
		//redirect to settings page
		//header( 'Location: '. PAGE_VERIFY.'?error=Code sent to email' ); die(); //Make sure code after is not executed
		break;
		
		
		
	//!CASE confirmemailcode
	case "confirmemailcode": 
		//get code from form
		$strCode = 					funct_ScrubVars($_GET['emailcode']);
		$strEmail = 				funct_ScrubVars($_GET['email']);
		$intUserID = 				funct_ScrubVars($_GET['id']);

		if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
		if( $stmt = $DB_MYSQLI->prepare("SELECT id,emailcode,verification_level,verification_email FROM " . TBL_USERS . " WHERE emailcode = ? ") ) { 
			$stmt -> bind_param("s", $strCode); 
			$stmt -> execute(); //Execute it
			//mysqli_stmt_store_result($stmt);
			//$intTotalRowsFound = mysqli_stmt_num_rows($stmt);	
			$stmt -> bind_result($intUserID_DB,$strCode_DB,$intVerification_level,$intVerification_email); //bind results
			$stmt -> fetch(); //fetch the value
			$stmt -> close(); //Close statement
		}else{
			echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; 
		}
		
		echo "$intUserID_DB,$strCode_DB,$intVerification_level,$intVerification_email";
		echo " $strCode_DB == $strCode ";
		
		//222722444115
		if($strCode_DB AND ($strCode_DB==$strCode)){
			//email confirm code matches a record in the database
			
			//update member record with confirmed email code
			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET verification_level=1, verification_email = 1 WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
			if(!($stmt->bind_param('i',																$intUserID_DB  ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
			if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}
			
			if(!DETECT_USERID){ //not logged in
				$strError = "Email Confirmed! Thank You. Please login with the password in your email.";
				header( 'Location: '. PAGE_SIGNIN.'?error='.$strError ); 
				die(); //Make sure code after is not executed
			
			}else{ //logged in
			
				if(DETECT_USERID!=$intUserID_DB){
					header( 'Location: '. PAGE_VERIFY.'?do=confirmemail&error=Code is not for your account!.' ); 
					
				}else{
			
					header( 'Location: '. PAGE_WALLET.'?do=emailverified' ); 
					die(); //Make sure code after is not executed
				}
				
			}//end if logged in
			
		
		}else{ //email code does NOT match
		
			$strDo="confirmemail" ;  //echo $strDo ;
			//$strError = "Email Code does not match or no such member exists";
			header( 'Location: '. PAGE_VERIFY.'?do=confirmemail&error=Email code incorrect. '." $strCode_DB == $strCode " ); 
			die;
		}


		
		break;
		
		
		
	//!CASE sendphonecode
	case "sendphonecode": //send phone code via sms

		$strNumber = funct_ScrubVars($_POST["phone"]);
		$strError = funct_SendPhoneCode($intUserID,$strNumber);
		
		//redirect to settings page
		header( 'Location: '. PAGE_SETTINGS.'?error_testphone='.$strError ); die(); //Make sure code after is not executed

		break;
		
	//!CASE confirmphonecode
	case "confirmphonecode": //confirm phone code via sms

		//get code from form
		$strCode = 				funct_ScrubVars($_POST['phonecode']);
		
		//get code in db
		$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
		//echo "SQL STMNT = " . $query . "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
		$strCode_DB=					$row["phonecode"];
		$intVerification_level=			$row["verification_level"];
		$intVerification_phone=			$row["verification_phone"];


		if( $strCode_DB==$strCode){ //if it matches and verification level is lower than it will be set to then 
			//if(!$intVerification_level){ $strSQL = "verification_level=1, " ; }
			//$query = "UPDATE ".TBL_USERS." SET $strSQL verification_phone=1 WHERE id = $intUserID " ;
			//echo "SQL STMNT = " . $query . "<br>";
			//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
			
			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET verification_phone = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
			if(!($stmt->bind_param('i',														$intUserID  ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
			if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}
			
			
			
			$strError = "Phone confirmed! Thank you!";
		}else{
			$strError = "Phone code wrong...";
		}

		//redirect to settings page
		//header( 'Location: '. PAGE_SETTINGS."?error_confirmphone=".$strError ); die(); //Make sure code after is not executed
		break;


}







//Check if logged in. If not then send to login page with an error.
if($intUserID=="") { 
	header('Location: ' . PAGE_SIGNUP. '?error=Please create your wallet first and login' ) ;  die(); //Make sure code after is not executed
}//Get User Data from DataBase






	$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$row = mysqli_fetch_array($rs) ;
	$intUserID_DB=					$row["id"];
	$strFirstName_DB=				$row["first_name"];
	$strLastName_DB=				$row["last_name"];
	$strEmail_DB=					$row["email"];
	$strPhone_DB=					$row["cellphone"];
	
	//$strName=						$row["name"];
	$strAddress_DB=					$row["address"];
	$strAddress2_DB=				$row["address2"];
	$strState_DB=					$row["state"];
	$strPostal_DB=					$row["postal"];
	$strCityName_DB=				$row["cityname"];
	//$intCityID_DB=				$row["cityid"];
	$strCountryName_DB=				$row["country_name"];	
	$intCountryID_2=				$row["country_id"];
	if(!$intCountryID_2){$intCountryID_2=227;}
	$strCellPhone_code=				$row["country_phonecode"];
	//$intCityRegion_DB=			$row["regionid"];
	
	$intCurrencyID=					$row["currency_id"];
	$strCurrencyCode=				$row["currency_symbol"];

	$strBitCoinAddress=				$row["btc_address"];
	$strPayPalAddress=				$row["paypalemail"];
	$strLiteCoinAddress=			$row["lc_address"];
	$strBankAccount=				$row["bank_account"];
	$strBankRouting=				$row["bank_routing"];
	
	$strVerificationPhone=			$row["verification_phone"];
	$strVerificationEmail=			$row["verification_email"];
	$strVerificationID=				$row["verification_id"];
	$strVerificationAddress=		$row["verification_address"];
	
	$strVerificationLevel=			$row["verification_level"];
	if(!$strVerificationLevel){$strVerificationLevel=0;}
	
	if($strVerificationEmail){ $strDo="confirmid" ; }


	if($intCountryID_2){
		$query="SELECT * FROM " . TBL_COUNTRIES . " WHERE id = $intCountryID_2 ";
		//echo "SQL STMNT = " . $query .  "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		$row = mysqli_fetch_array($rs) ;
		$intPhoneCountryCode=			$row["code"];
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Welcome <?=$strFirstName_DB?> Please Verify your Identity<?=TITLE_END?></title>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<!-- Favicon -->
	<link rel="icon" type="image/png" href="/img/favicon.png" />
	<link rel="stylesheet" href="/wallet/css/web.css" />
	<link rel="stylesheet" href="/wallet/css/foundation.css" />
<link rel="stylesheet" href="/wallet/css/custom.css" />
	<link rel="stylesheet" href="/foundation-icons/foundation-icons.css" />
	<link rel="stylesheet" href="webicons-master/webicons.css" />
	<script src="/wallet/js/modernizr.js"></script>
	<script src="/wallet/js/web.js"></script>
	<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
	<? $intJquerySoundManager=1;?><script src="/wallet/js/soundmanager2-nodebug-jsmin.js"></script><script> soundManager.url = '/js/soundmanager2.swf'; soundManager.onready(function() {});</script>
	
	
	<!-- UPLOAD CSS -->
		<!-- jQuery UI styles -->
		<link rel="stylesheet" href="upload/css/jquery-ui.css" id="theme">
		<!-- CSS to style the file input field as button and adjust the jQuery UI progress bars -->
		<link rel="stylesheet" href="upload/css/jquery.fileupload-ui.css">
		<!-- Generic page styles -->
		<link rel="stylesheet" href="upload/css/style.css">
		<!-- Shim to make HTML5 elements usable in older Internet Explorer versions -->
		<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<style>.scrolly{overflow-y: auto; overflow-x: hidden; padding:7px;}</style>
	

	<script language="javaScript">




	</script>

</head>


<body onLoad="<?=$strOnBodyLoadJS?>">

<?php include __ROOT__."/inc/hud.php"; ?>

<p></p>
<!-- Main Body Area 8 columns wide -->
<div class="row">
	
	<div class="small-12 medium-8 columns">

        
        <!-- PROGRESS BAR based on verification level 
		<div class="panel radius">
            <h4>Verified <? echo $strVerificationLevel ?> of 3</h4>
            <? if($strVerificationLevel){ ?>
			<? if($strVerificationLevel==1){ ?>
                <div class="progress small-12 success round">
                    <span class="meter" style="width: 33%"></span>
                </div>
            <? } ?>
			<? if($strVerificationLevel==2){ ?>
                <div class="progress small-12 success round">
                    <span class="meter" style="width: 66%"></span>
                </div>
            <? } ?>
			<? if($strVerificationLevel==3){ ?>
                <div class="progress small-12 success round">
                    <span class="meter" style="width: 100%"></span>
                </div>
            <?  } ?>			
		<? } ?>
		</div>
        END PROGRESS BAR AREA -->


       <? if($strDo=="confirmemail"){ ?>
		<!-- BEGIN PROGRESS BAR AREA -->
		<form data-abide action="<?=PAGE_VERIFY?>?do=confirmemailcode" method="GET">
			<div class="panel radius">
				<div class="confirm_email">
					<h2 style="color:red;"><?=$strError?></h1>
					
					<? if(!$strError){ ?>
					<h2>Time to verify your email!<br> One More Step to Activate your Account.</h2>
				    <h3>Please check your email and click the confirmation link. Also be sure to check your Spam folder.</h3>
					<? } ?>
<!--				    <input name="emailcode" type="text" placeholder="enter your email code">-->
<!--				    <input name="do" type="hidden" value="confirmemailcode">-->
<!--					<span class="txtError"><?=$strError_confirmemail?></span>-->
<!--					<button type="submit">Confirm Email</button><br>-->
<!--					<?php if($strError_emailconfirm){ echo $strError_emailconfirm." <br>" ; } ?>-->
					<p></p><br><br><a href="<?=PAGE_VERIFY?>?do=sendemailcode">send code again to your email <?=$strEmail_DB?></a>
				</div>
            </div>
		</form>
		<!-- END PROGRESS BAR AREA -->
		<? } ?>



		<? if($strDo=="confirmid"){ ?>
			
		<div class="panel callout radius" id="buynow" style="display:none;">
			<a href="<?=PAGE_ORDER?>"><h1>Buy Bitcoin Now!</h1></a>
		</div>			
		
			
		<div class="radius" id="upload" style="padding:20px;">
			<h3>Upload Photo ID to Buy Bitcoin</h3>
            <h6>
              <ul>
                <li>Upload a <strong>CLEAR, BRIGHT, COLOR</strong> scan of your government-issued photo ID.</li>
                <li>Upload both the front and back.</li>
                <li>If you have a drivers license and passport, please upload both.</li>
                <li>Ensure your ID is non-blurry, high-resolution, and the <strong>entire</strong> document is shown. Don't cut it off.</li>
                <li>If you've already uploaded your ID, then please don't upload it again. We don't show your previous uploads here for security reasons.</li>
                <li>Internet Explorer on Windows will not work. Please use Chrome or Firefox.</li>
              </ul>
            </h6>
            <br>
			<h5>We will not be able to deliver your Bitcoins without this.</h5><br><br>
			
			
			<!--Upload AREA-->   
			<div id="droparea" class="container" style="width:100%;">
				

				<!-- The file upload form used as target for the file upload widget  background-image:url('/images/anim_chest.gif');-->
				<form id="fileupload" action="upload/custom_upload.php" method="POST" enctype="multipart/form-data">
					<input name="up" type="hidden" value="<?=$strUpload?>">
					<input name="ui" type="hidden" value="<?=$intUserID?>">
					<input name="userid1" type="hidden" value="<?=DETECT_USERID?>">
					<input name="chestid" id="chestid_form" type="hidden" value="">
					<input name="chestkey" type="hidden" value="<?=$strOrderCode?>">
					<input name="frompage" type="hidden" value="id">
					

					<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
					<div class="row fileupload-buttonbar">
						<div class="span7" style="width:100%;">
							<center>
							<!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn btn-success fileinput-button">
								<i class="icon-plus icon-white"></i>
								<span><small >Drag and drop anywhere on this page or <strong>click here</strong> to upload files.<br>
								JPG or PNG files only please.</small></span>
								<input type="file" name="files[]" multiple>
							</span>
							</center>
						</div>
						<!-- The global progress information -->
						<div class="span5 fileupload-progress fade actionswindow">
							<!-- The global progress bar -->
							<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
								<div class="bar" style="width:0%;"></div>
							</div>
							<!-- The extended global progress information -->
							<div class="progress-extended txtLight" style="">&nbsp;</div>
						</div>
					</div>
					<!-- The loading indicator is shown during file processing -->
					<div class="fileupload-loading"></div>
					<br>
					<!-- The table listing the files available for upload/download -->
					<table role="presentation" id="table_filelist" class="table table-striped"><tbody class="files" style="font-size:12px;" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
				</form>
				
				<div id="UploadPicDiv"></div>
				
				<br>
			
				<h4>Drag and drop your photo ID onto the page:</h4>
				<br>
				<div class="row">
					<div class="small-5 medium-5 columns">
						<img src="/img/id.jpeg" width="250" height="200" />
					</div>
					<div class="small-2 medium-2 columns" style="text-align:center;">
						<h4>and</h4>
					</div>
					<div class="small-5 medium-5 columns">
						<img src="/img/passport.jpeg" width="250" height="200" />
					</div>
				</div>
				

				<!-- The template to display files as they are uploading -->
				<script id="template-upload" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}

					<tr class="template-upload fade lockeritems_txt">
						<td class="lockeritems_txt">
						<span>{%=file.name%}</span> 
						<span>{%=o.formatFileSize(file.size)%}</span>
						{% if (file.error) { %}
							<div class="error"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%} </div>
						{% } else if (o.files.valid && !i) { %}
							<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height:10px;"><div class="bar" style="width:0%;"></div></div>
						{% } else { %}
							{% if (!i) { %}
								<button class="btn btn-warning">
									<i class="icon-ban-circle icon-white"></i>
									<span>{%=locale.fileupload.cancel%}</span>
								</button>
							{% } %}
						{% } %}
						</td>
					</tr>

				{% } %}
				</script>

				<!-- The template to display files uploaded successfully -->
				<script id="template-download" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}
						{% if (file.error) { %}
						<tr colspan="2" class="template-download fade lockerERROR_txt">
							<td class="error"><img src="upload/img/error.png" width="24" height="24" /></td>
							<td class="error">
								<span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}
								<br><span>{%=file.name%}</span> <span>{%=o.formatFileSize(file.size)%}</span>
							</td>
						</tr>
						{% } else { %}
						<tr colspan="2" class="template-download fade lockeritems_txt">
							<td><img class="lockeritems_icon" src="{%=file.icon%}" width="48" height="48" alt="{%=file.newrecordid%}" /></td>
							<td class="name" class="">
								<a href="javascript:jsfunct_OpenItem('/?id={%=file.newrecordid%}');" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">
								<span class="txtQuestsHeadline">{%=file.name%}</span>
								</a>
							</td>
						</tr>		
						{% } %}	

				{% } %}
				</script>
				<? if(!$intJqueryUI){ $intJqueryUI=1; ?><script src="<?=JQUERYUISRC?>" type="text/javascript"></script><? } ?>
				<script src="upload/js/tmpl.min.js"></script><!-- The Templates plugin is included to render the upload/download listings -->
				<!-- <script src="js/jquery.image-gallery.min.js"></script>jQuery Image Gallery -not used now but here so code will not break... -->
				<script src="upload/js/jquery.iframe-transport.js"></script><!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
				<script src="upload/js/jquery.fileupload.js"></script><!-- The basic File Upload plugin -->
				<script src="upload/js/jquery.fileupload-fp.js"></script><!-- The File Upload file processing plugin -->
				<script src="upload/js/jquery.fileupload-ui.js"></script><!-- The File Upload user interface plugin THIS FILE HAS THE ACTIVE SETTINGS, autoupload & prepend etc .... -->
				<script src="upload/js/jquery.fileupload-jui.js"></script><!-- The File Upload jQuery UI plugin -->
				<script src="upload/js/locale.js"></script><!-- The localization script -->
				<script src="upload/js/main.js"></script><!-- The main application script -->
			</div>
			</div>
			<br><br><br><br>



<!--
			<div class="panel radius">
				<h2>Level 2 - Address</h2><br>
				Please update your address, city, state and zip code on the <a href="<?=PAGE_SETTINGS?>">settings page</a>
			</div>
			

			<? if(!$strVerificationPhone){ ?>
			
			<div class="panel radius">
				<h2>Level 3 - Confirm Phone</h2>
				<form data-abide action="<?=PAGE_VERIFY?>?do=sendphonecode" method="POST">
					<input name="phone" type="text" required placeholder="cellphone #" style="" value="<?=$strPhone_DB?>">
					<button type="submit">Send Test MSG to Phone</button>
					<span class="txtError"><?=$strError_testphone?></span><br>
				</form>				
				
				<form data-abide action="<?=PAGE_VERIFY?>?do=confirmphonecode" method="POST">
				    <input name="phonecode" type="text" placeholder="enter code sent to phone">
					<span class="txtError"><?=$strError_confirmphone?></span>
					<button type="submit">Confirm Phone Code</button><br>
				</form>	
			</div>
			<? } ?>
-->




<!--
			<div class="panel radius">
				<h2>Bonus - Unlock buy with Debit!</h2>
				
				<h4>Upload your Debit Card</h4>
				<img src="/img/debit.jpeg" width="250" height="200" />
				
				<h4>Upload selfie + ID + Debit Card </h4>
				<img src="/img/selfie_id_debit.png" width="250" height="200" />
				
			</div>		
-->


		<? } //confirm id ?>	




    </div>
    <!-- END Sidebar area 4 columns wide -->
		
</div>
<!-- END ROW WITH 8+4 COLUMNS -->


	<div id="uploadedmodal" class="reveal-modal medium" data-reveal>
		<h4>Picture Uploaded!</h4>
	    <p>Thank you! You are welcome to purchase Bitcoin now. If there are problems with your scan (blurry, low resolution, shot in dim light), we'll let you know.</p>
	    <p>Thanks again and enjoy!<br>John & Ray</p>
		<a class="close-reveal-modal">&#215;</a>
	</div>


<script src="/wallet/js/foundation.min.js"></script>
<script src="/wallet/js/foundation/foundation.abide.js"></script>
<script src="/wallet/js/foundation/foundation.reveal.js"></script>
<script>


$(document).ready(function(){
	
}); //close ready function


	function jsfunct_FileAdded(){
		//this is called whenever a file is added
	}

	function jsfunct_FileUploaded(intFileID, strFileName, strFileExt) {
		//This is called whenever a file is uploaded
		//jsfunct_FileUploaded(file.newrecordid, file.name, file.extension);

		//if we don't do all processes in custom_upload then call ajax function to process uploads
		strResponse = functjs_File_Upload_Process(intFileID, strFileName, strFileExt);

		//alert(strResponse + 'ID Uploaded successfully. You may now place an order. If the ID is good the order will be filled.');

		//open modal
		if(strResponse=="ok"){
			//alert('open modal');
			$('#uploadedmodal').foundation('reveal', 'open');
		}
		
		

		//strMessage = 'Thank you for Uploading your bank receipt. We will be updating your order soon.';
		jsfunctPrepend(intFileID);
	}


	function functjs_File_Upload_Process(intFileID, strFileName, strFileExt){
		//do additional operations on file after upload
		var data;
		var dataString = '' +
		'&do=' + 					'upload' + 
		'&userid=' + 				'<?=DETECT_USERID?>' + 
		'&filename=' + 				strFileName + "." + strFileExt + 
		'&frompage=' + 				'id' + 
		'&orderid=' + 				'<?=$intOrderID?>' ;

		$.ajax({
		  type: "GET",
		  async: false, //this allows the page called via ajax to write cookies to the user
		  url: "<?=CODE_DOAJAX?>",
		  data: dataString,
		  success: function(result) { 
		   	//alert('result=' + result);
		   	data = result;
		  } // end on success
		});

		/*
		$.get("<?=CODE_DOAJAX?>?do=upload&userid=<?=DETECT_USERID?>&filename=" + strFileName + "." + strFileExt + "&frompage=id&orderid=<?=$intOrderID?>" , function(data){
			if (data != "") {
				//alert("returns" + data);
				return data;
			}
		});
		*/
		
		return data ;
		

	}

	function jsfunctPrepend(intRecordID){
	
		strPostLink = "<?=MOD_LOADCONTENT?>?do=ajax&recid=" + intRecordID + "&type=uploads" ;
		//alert('postlink=' + strPostLink );
		//Call loadcontent and get back only one record, pass that record id
		$.post(strPostLink ,
			function(data){
				if (data != "") {
					$('#UploadPicDiv').prepend( data );
					soundManager.play('uploadpic','/sounds/upload.mp3');//play sound
				}
		}); 
	}; //close function


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