<?php

ob_start(); //so we can redirect even after headers are sent

include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

error_reporting(E_ALL & ~E_NOTICE);

//Get Fresh Page QueryString Variables
$strDo = 			trim($_GET['do']);

$intUserID = 		funct_ScrubVars(DETECT_USERID);
//$strChestKey = DETECT_CHESTKEY ;

//echo "strDo= " . $strDo;
switch ($strDo){

	//!CASE claimcoins
	case "claimcoins":
	//let them claim their coins
		$strEmail = 	funct_ScrubVars($_POST['email']);
		$strCode = 		funct_ScrubVars($_POST['code']);

		if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
		if( $stmt = $DB_MYSQLI->prepare("SELECT user_id,user_email,address_email,status_id,status_msg,label,transaction_id_send,transaction_id_get FROM ".TBL_ESCROW." WHERE verify_code = ? ") ) {

			$stmt -> bind_param("i", $intUserID); //Bind parameters s - string, b - blob, i - int, etc
			$stmt -> execute(); //Execute it
			mysqli_stmt_store_result($stmt);
			$intTotalRowsFound = mysqli_stmt_num_rows($stmt);

			$stmt -> bind_result($intUserID,$intUserEmail,$strEmailReceiver,$intStatusID,$strStatusMsg,$strLabel,$intTransactionID_send,$intTransactionID_get); //bind results
			$stmt -> fetch(); //fetch the value

			//echo "totalrows: $intTotalRowsFound <br>";
			if($intTotalRowsFound<1){
        		$strError = "Code not found";
			}else{ //Email found so ...

				while ($stmt->fetch()){

					//if found then see if the email matches and
					if($strEmailReceiver!=$strEmail){
						$strError = "Email does not match. Admin Alerted";
					}

					//if status is zero then. Important!
					if($intStatusID>0){
						$strError = "Coins already Claimed. $strStatusMsg ";
					}

				}
			}

			$stmt -> close(); //Close statement
		}else{
			echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
		}


		//if it all checks out
		if(!$strError){

			//write cookie with the code for cashing in on the wallet page
			setcookie("claimcode", $strCode, COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);

			//redirect them to signup.
			header( 'Location: '. CODE_DO.'?do=join&email='.$strEmailReceiver ); die(); //Make sure code after is not executed

		}


	break;




		//!CASE newpublickey
		case "newpublickey": //send email confirm code

			$intUserID = funct_ScrubVars($_POST["userid"]);

			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if( $stmt = $DB_MYSQLI->prepare("SELECT id,email,cellphone,name,wallet_btc FROM ".TBL_USERS." WHERE id = ? ") ) {

				$stmt -> bind_param("i", $intUserID); //Bind parameters s - string, b - blob, i - int, etc
				$stmt -> execute(); //Execute it
				$stmt -> bind_result($intUserID_DB,$Email_DB,$Phone_DB,$strName,$strWalletAddress); //bind results
				$stmt -> fetch(); //fetch the value
				//mysqli_stmt_store_result($stmt);
				//$intTotalRowsFound = mysqli_stmt_num_rows($stmt);
				//echo "totalrows: $intTotalRowsFound <br>";
				$stmt -> close(); //Close statement
			}else{
				echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
			}
/*
			//Get User Data from DataBase
			$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
			//echo "SQL STMNT = " . $query .  "<br>";
			$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
			$intUserID_DB=					$row["id"];
			$Email_DB=						$row["email"];
			$Phone_DB=						$row["cellphone"];
			$strName=						$row["name"];
			$strWalletAddress=				$row["wallet_btc"];
*/
			$strWalletAddress= trim($strWalletAddress) ;
			$strWalletLabel = $intUserID_DB."|".$Email_DB."|".$Phone_DB."|".$strName ;
			//echo "making wallet address for ... ".$strWalletLabel."<br>";
			$strWalletAddress = funct_Billing_NewWalletAddress($strWalletLabel);
			if($strWalletAddress){
				//update database with new wallet hash code
				//$query="UPDATE " . TBL_USERS . " SET wallet_btc='".$strWalletAddress."' WHERE id=".$intUserID_DB ;
				//echo "SQL STMNT = " . $query .  "<br>";
				//mysqli_query($DB_LINK, $query) or die(mysqli_error());

				if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
				if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET wallet_btc = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
				if(!($stmt->bind_param('si',								$strWalletAddress, $intUserID_DB ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
				if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}

				$strQRcodeIMG = PATH_QRCODES.$intUserID_DB.".png" ;
				$strError = funct_Billing_GetQRCodeImage($strWalletAddress, $strQRcodeIMG ); //save img to disk

				$strPostImgPath = PATH_POSTERS.$intUserID_DB.".png" ;
				funct_CreateQRPoster($strQRcodeIMG, $strPostImgPath, $strWalletAddress); //add easy bits logo to image - simple image, php lib
			}

			//redirect to settings page
			header( 'Location: '. PAGE_SETTINGS.'?error=Code sent to email' ); die(); //Make sure code after is not executed

			break;


		//!CASE update
		case "update":

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
			$strPayPalEmail = 			funct_ScrubVars($_POST['paypalemail']);
			$strBTCaddress = 			funct_ScrubVars($_POST['btcaddress']);
			$intMiningFee = 			funct_ScrubVars($_POST['miningfee']);

			$strLTCaddress = 			funct_ScrubVars($_POST['ltcaddress']);
			$strBankaccount = 			funct_ScrubVars($_POST['bankaccount']);
			$strBankrouting = 			funct_ScrubVars($_POST['bankrouting']);


			if($intFiatConvertPercent){ $intFiatConvertPercent=0; }
			//if($intGoalType<1){ $intGoalType=0 ;}
			//if($intGoalAmount<1){ $intGoalAmount=0 ;}
			if($intCountryID<1){ $intCountryID=0 ;}
			if($intCurrencyID<1){ $intCurrencyID=0 ;}
			if(!$intMiningFee){ $intMiningFee=MININGFEE_NORMAL; }

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

/*
			//Update Database mysql hackable update,... fucked up.
			$query = "UPDATE ".TBL_USERS." SET " .
			//"password='$strPassword', ".
			//"email='$strEmail', ".
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
			"paypalemail='$strPayPalEmail', ".
			"btc_address='$strBTCaddress', ".
			"crypto_miner_fee=$intMiningFee, ".
			"bank_account='$strBankaccount', ".
			"bank_routing='$strBankrouting', ".
			"lastlogin=NOW() ".
			"WHERE id = $intUserID " ;
			//echo "SQL STMNT = " . $query . "<br>"; //"cityid=$intCityID, ". //"city='$strCityName', ". //"regionid=$intCityRegionID, ".
			//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
*/

			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET cellphone = ?, country_phonecode = ?, first_name = ?, last_name = ?, address = ?, address2 = ?, cityname = ?, state = ?, postal = ?, country_id = ?, currency_id = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
			if(!($stmt->bind_param('sssssssssddd',					$strCellPhone, $strCellPhone_code, $strNameFirst, $strNameLast, $strAddress, 		$strAddress2, $strCity, $strState, $strPostal, $intCountryID, $intCurrencyID, $intUserID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
			if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}

			//EMAIL_WALLETSEND
			header( 'Location: '.PAGE_SETTINGS."?error=Account Updated!" ); die();

			break;



			//!CASE updatepassword
			case "updatepassword":

				//Get all post values
				$strPassword_old = 			funct_ScrubVars($_POST['passwordold']);
				$strPassword = 				funct_ScrubVars($_POST['password']);
				$strPassword2 = 			funct_ScrubVars($_POST['password2']);

				if(!$intUserID){ header( 'Location: '.PAGE_ERROR."?error=you are not logged in" ); }

				if($strPassword2!=$strPassword){
					header( 'Location: '.PAGE_SETTINGS."?error_password=passwords do not match#passwordupdate" );
				}

				if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
				if( $stmt = $DB_MYSQLI->prepare("SELECT password FROM ".TBL_USERS." WHERE id = ? ") ) {

					$stmt -> bind_param("i", $intUserID); //Bind parameters s - string, b - blob, i - int, etc
					$stmt -> execute(); //Execute it
					$stmt -> bind_result($strPassword_DB); //bind results
					$stmt -> fetch(); //fetch the value
					// mysqli_stmt_store_result($stmt);
					//$intTotalRowsFound = mysqli_stmt_num_rows($stmt);
					//echo "totalrows: $intTotalRowsFound <br>";
					$strIsPasswordGood = password_verify($strPassword_old, $strPassword_DB);
					//echo "password verify $strPassword_old ? db=$strPassword_DB  Good? ( $strIsPasswordGood )<br>";
					if(!$strIsPasswordGood){ // bad password, current password is wrong
						header( 'Location: '.PAGE_SETTINGS."?error_password=current password is wrong..." );

					}else{ //password is good.

						//encrypt password
						$strPassword_hash = password_hash($strPassword, PASSWORD_DEFAULT); //PASSWORD_BCRYPT
						//echo "hashing password $strPassword .... $strPassword_hash <br>" ;
						//$strPassword_hash = $strPassword ;

						//Update Database
						//$query = "UPDATE ".TBL_USERS." SET password='$strPassword_hash' WHERE id = $intUserID " ;
						//echo "SQL STMNT = " . $query .  "<br>";
						//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
						//echo "SQL.updatesettings = " . $query .  "<br>";

						if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
						// mysqli_report(MYSQLI_REPORT_ALL);
						$stmt = mysqli_stmt_init($DB_MYSQLI);
						if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET password = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
						if(!($stmt->bind_param('si',								$strPassword_hash, $intUserID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
						if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}

						header( 'Location: '.PAGE_SETTINGS."?error_password=password updated" );

					}


					$stmt -> close(); //Close statement

				}else{
					echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
				}



				break;




				//!CASE debitpassword
				case "debitpassword":

					$FormRegpassword = (funct_ScrubVars($_POST["password"]));

					if($FormRegpassword=="zoe5" OR $FormRegpassword=="ronybtc" OR $FormRegpassword=="t3he8uxj") {

						setcookie("debit" , $FormRegpassword , 	COOKIE_EXPIRE,COOKIE_PATH,COOKIE_DOMAIN);
						header( 'Location: /buybitcoinswithdebit.php' ); die();
					}else{

						header( 'Location: '. PAGE_ERROR.'?error=debitpassword&msg=wrong password' ); die(); //Make sure code after is not executed
					}

					break;






	//!CASE join
	case "join": //Register User BEGIN ----------------------------------------------------------------------------------------------------

		//Get Form Post Data - auto protect against xss and sql injection
		$FormRegEmail = 		funct_ScrubVars($_POST["email"]);
		$FormRegPhone = 		funct_ScrubVars($_POST["phonenumber"]);
		$FormRegFirstName = 	funct_ScrubVars($_POST["firstname"]);
		$FormRegLastName = 		funct_ScrubVars($_POST["lastname"]);
		$FormRegAddress = 		funct_ScrubVars($_POST["address"]);
		$FormRegpassword = 		funct_ScrubVars($_POST["password"]);
		//echo "form vars: $FormRegEmail , $FormRegPhone , $FormRegFirstName , $FormRegLastName , $FormRegAddress <br/><br/>";
		//echo "request: ".$_REQUEST["email"]." , get: ".$_GET["email"]." , post: ".$_POST["email"]."<br>";

		//#######################################################
		//Error Checking & Formatting
		//if(!$FormRegFirstName){$FormRegFirstName="guest";}
		if($FormRegEmail=="") { $strError = "No email given! " ;}
		if($FormRegEmail){

			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if( $stmt = $DB_MYSQLI->prepare("SELECT id FROM ".TBL_USERS." WHERE email = ? ") ) {

				$stmt -> bind_param("s", $FormRegEmail); //Bind parameters s - string, b - blob, i - int, etc
				$stmt -> execute(); //Execute it
				$stmt -> bind_result($intUserID); //bind results
				//$stmt -> fetch(); //fetch the value
				mysqli_stmt_store_result($stmt);
				$intTotalRowsFound = mysqli_stmt_num_rows($stmt);
				//echo "totalrows: $intTotalRowsFound <br>";
				if($intTotalRowsFound>0){
	        		$strError = "This email is already in use. Please use a different email. " ;
				}
				$stmt -> close(); //Close statement

			}else{
				echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
			}

			//check members table for email duplicates.
			//$query="SELECT * FROM ".TBL_USERS." WHERE email= '".$FormRegEmail."' " ;
			//echo "SQL STMNT = " . $query .  "<br>";
			//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
			//if(mysqli_num_rows($rs)>0){ $strError = "This email is already in use. Please use a different email. " ;}

		}
		//if($FormRegpassword=="") { $strError = $strError. "No password given! " ;}

		//password routine
		if(!$FormRegpassword){ $FormRegpassword = rand_sha1(5); $strTemptxt="temporary"; }//Temporary random password creation here
        $PasswordToEmail = $FormRegpassword; //Store the temp password in another variable
		if(PASSWORD_ENCRYPT){ $FormRegpassword = password_hash($FormRegpassword, PASSWORD_DEFAULT);} //encrypt password
		//#######################################################

		//echo "userE= $strUserError passE=$strPassError emailE= $strEmailError " ;
		if( !$strError ) {

			//$query = "INSERT INTO ".TBL_USERS.
			//" ( password, 			email,		 	cellphone, 		first_name, 		last_name, 			address,			date_joined ) VALUES ".
			//" ('$FormRegpassword',	'$FormRegEmail','$FormRegPhone','$FormRegFirstName','$FormRegLastName',	'$FormRegAddress',	NOW() 	  ) " ;
			//echo "Insert into user Table - SQL STMNT = " . $query .  "<br>";
			//mysqli_query($DB_LINK, $query);
			//$intNewRecordID = mysqli_insert_id($DB_LINK);

			$strDateTime=date("Y-m-d H:i:s");
			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if(!($stmt = $DB_MYSQLI->prepare("INSERT INTO ".TBL_USERS."(password, 		email,		 	cellphone, 		first_name, 	last_name, 		address,		date_joined) VALUES (?,?,?,?,?,?,?)") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
			if(!($stmt->bind_param('sssssss', 							$FormRegpassword,$FormRegEmail,$FormRegPhone,$FormRegFirstName,$FormRegLastName,$FormRegAddress,$strDateTime ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
			if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error; }
			$intNewRecordID = mysqli_insert_id($DB_MYSQLI);

			if($intNewRecordID > 0 ){ // create new member successful, do other operations

				$intCode=createRandomKey_Num(12); //generate unique code for email confirmation
				$strEmailLink = WEBSITEFULLURLHTTPS.PAGE_VERIFY."?do=confirmemailcode&emailcode=$intCode&id=$intNewRecordID&email=$FormRegEmail" ;

				if(LOGIN_ONJOIN){
					//Write Session & Cookies to Login User
					$strRememberFlag = "1" ; //Remember username and password by default
					functLoginUser($intNewRecordID, $strRememberFlag, $FormRegpassword);
					$_SESSION["justjoined"] = time();//set flag for new user
				}

				if(CREATEADDRESS_ONJOIN){ //create a new wallet address for them
					$strWalletAddress = funct_MakeWalletAddressUpdate($intUserID);
				}

				if(LOGIN_SENDEMAILCODE){ //update member record with new confirm code
					//$query = "UPDATE ".TBL_USERS." SET emailcode='$intCode' WHERE id = $intNewRecordID " ;
					//echo "SQL STMNT = " . $query . "<br>";
					//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
					if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
					if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET emailcode = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
					if(!($stmt->bind_param('sd',								$intCode, $intNewRecordID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
					if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}
					$strEmailCodeText = "Your ".WEBSITENAME." Verification Code is \n ".$intCode." \n".$strEmailLink ;
				}

				//send them an email
				$strSubject = "Welcome to ".WEBSITENAME." ".$FormRegFirstName." ".$FormRegLastName;
				$strBody = "We're so stoked to have you as a Coin Cafe member. \n\n ".
				"We're doing our part to further the reach of Bitcoin and other cryptocurrencies. Thanks for being a part of the family. We promise to do everything in our power to make buying, selling and using Bitcoins as easy and empowering as possible.".
/*
				"Your ".WEBSITENAME.
                    "\n\nYour $strTemptxt password is: ".$PasswordToEmail.
*/
                    "\n\nPlease verify your email here: ".$strEmailLink.
				    "\n\n-Thank you \n Coin Cafe  \n ".WEBSITEFULLURLHTTPS ;
				funct_Mail_simple($FormRegEmail,$strSubject,$strBody);

				//send the admin an email
				$ipaddress = $_SERVER['REMOTE_ADDR'];
				$strSubject = "New Member ".$intNewRecordID." ".$FormRegFirstName." ".$FormRegLastName;
				$strBody = "User ID: $intNewRecordID\nName: $FormRegFirstName $FormRegLastName\nEmail: $FormRegEmail\nPhone: $FormRegPhone\nIP: $ipaddress\n\n".WEBSITEURL ;
				funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',$FormRegEmail);


				//redirect them to the dashboard with flag to guide them through
				$strRefreshURL = PAGE_VERIFY."?do=confirmemail" ; //PAGE_LEDGER."?do=joined" ;

				//echo "strRefreshURL $strRefreshURL";
				header( 'Location: '. $strRefreshURL ); die(); //Make sure code after is not executed

			}//end new user created


		}else{ //errors found

			$Form_PageFrom = 		trim($_GET["page"]);
			$strData = "&email=$FormRegEmail"."&phonenumber=$FormRegPhone"."&firstname=$FormRegFirstName"."&lastname=$FormRegLastName"."&address=$FormRegAddress" ;
			if($Form_PageFrom){
				$strRedirectURL = $Form_PageFrom. "?error=$strError".$strData ;
			}else{
				$strRedirectURL = PAGE_SIGNUP. "?error=$strError".$strData ;
			}

			echo "$FormRegEmail , $FormRegPhone , $FormRegFirstName , $FormRegLastName , $FormRegAddress";

			// send  back to join page with errors
			header( 'Location: ' .$strRedirectURL );die(); //Make sure code after is not executed


		}//end if no errors

		break;







	//!CASE usernamecheck
	case "usernamecheck";

		$user_name = (funct_ScrubVars($_POST['user_name']));

		if($user_name){ //Get username


			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if( $stmt = $DB_MYSQLI->prepare("SELECT id FROM ".TBL_USERS." WHERE email = ? ") ) {

				$stmt -> bind_param("s", $user_name); //Bind parameters s - string, b - blob, i - int, etc
				$stmt -> execute(); //Execute it
				$stmt -> bind_result($intUserID); //bind results
				//$stmt -> fetch(); //fetch the value
				mysqli_stmt_store_result($stmt);
				$intTotalRowsFound = mysqli_stmt_num_rows($stmt);
				//echo "totalrows: $intTotalRowsFound <br>";
				if($intTotalRowsFound>0){
	        		echo "no"; //user name is not availble
				}else{ //Email found so ...
					echo "yes"; //user name is available
				}
				$stmt -> close(); //Close statement

			}else{
				echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
			}

		}//end if username found

		break;



	//!CASE login
	case "login":

		//sessions security check
		$strTimeInterval = $_SESSION['last_post'] + SECURITY_LOGIN_WAIT_SECONDS ;
		$intTime = $strTimeInterval - time() ;
		if(isset($_SESSION['ip']) && $_SESSION['last_post'] + SECURITY_LOGIN_WAIT_SECONDS > time()) die('slow down.... wait '.$intTime.' more seconds please.');

		//Get Form Post Data
		$username = 			funct_ScrubVars($_POST['email']);
		$password = 			funct_ScrubVars($_POST['password']);
		$strReturnURL = 		funct_ScrubVars($_POST["returnurl"]);

		//$remember = 			stripslashes($_POST["remember"]);
		//echo "username = " . $username . "<br>"; echo "password = " . $password . "<br>";

		if($username AND $password){

			if(PASSWORD_ENCRYPT){ //encrypt password
				$intUserID = functConfirmUserPass_hash($username, $password);
			}else{
				$intUserID = functConfirmUserPass($username, $password);
			}

			//echo "functConfirmUserPass - intUserID = " . $intUserID . "<br>";
			$errorMSG = ""; //Default login is good

			if(!$intUserID){ //username failure
				$errorMSG = "we could not find your user record... may be a database issue. admin has been emailed";
			}

			//If logging too fast
			if($intUserID == "toofast"){ //username failure
				$errorMSG = "logging in too fast";
			}

			//If username does not exist
			if($intUserID == "nousername"){ //username failure
				$errorMSG = "email not found";
			}

			//If username exists and password is incorrect then
			if($intUserID == "badpassword"){ //password failure
				$errorMSG = "Password is not right";
			}

		}//end if username and password provided



		//________________________________________________________________
		if(SECURITY_CAPCHACHECK){
			//capcha google
			include __ROOT__.'/inc/capcha/recaptchalib.php' ;
			$strChallenge = 		funct_ScrubVars($_POST["recaptcha_challenge_field"]);
	        $strResponse = 			funct_ScrubVars($_POST["recaptcha_response_field"]);
			$strServer = 			$_SERVER["REMOTE_ADDR"] ;
			$privatekey = 			SECURITY_CAPCHA_PRIVATEKEY ;

			$resp = recaptcha_check_answer($privatekey,$strServer,$strChallenge,$strResponse);

			if (!$resp->is_valid) { // What happens when the CAPTCHA was entered incorrectly
				$errorMSG = "The reCAPTCHA wasn't entered correctly. Try it again." .
			     "(" . $resp->error . ")" ;
			     //echo $errorMSG ; die;

			} else {
				// Your code here to handle a successful verification
				//echo "good";
			}
		}//end if capchacheck on
		//________________________________________________________________



		//If username and password are correct
		if($errorMSG==""){
			//echo "errorUsernameLogin = " . $errorUsernameLogin . "<br>";

			//Write Session & Cookies to Login User
			$strDate_LastLogin = functLoginUser($intUserID, $remember, $password);
			//echo "logged in msg = " .$strDate_LastLogin ;

			//$strQS = "?do=welcome" ;
			$strRefreshURL = PAGE_WALLET.$strQS ;
			//echo "url=".$strRefreshURL ;
			if($strReturnURL){$strRefreshURL = $strReturnURL;}
			header( 'Location: ' . $strRefreshURL ); die();

		}else{ //send back to login page with error

			$strQS = '?error='.$errorMSG."&email=".$username."#login" ;

			$Form_PageFrom = trim($_GET["page"]);
			if($Form_PageFrom){
				$strRedirectURL = $Form_PageFrom.$strQS  ;
			}else{
				$strRedirectURL = PAGE_HOME.$strQS ;
			}
			//echo "errormsg=".$errorMSG;

			// send  back to join page with errors
			header( 'Location: ' .$strRedirectURL );
			die(); //Make sure code after is not executed

		}

		break;




	//!CASE forgotpassword
	case "forgotpassword": //prepared statements locked down. select ,update
		//echo "strDo= " . $strDo;

		//Get Form Post Data
		$strEmailForgotForm = (funct_ScrubVars($_POST["forgot_email"]));

		if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
		if( $stmt = $DB_MYSQLI->prepare("SELECT id FROM ".TBL_USERS." WHERE email = ? ") ) {

			$stmt -> bind_param("s", $strEmailForgotForm); //Bind parameters s - string, b - blob, i - int, etc
			$stmt -> execute(); //Execute it
			$stmt -> bind_result($intUserID); //bind results
			//$stmt -> fetch(); //fetch the value
			mysqli_stmt_store_result($stmt);
			$intTotalRowsFound = mysqli_stmt_num_rows($stmt);
			//echo "totalrows: $intTotalRowsFound <br>";

			if($intTotalRowsFound<1){
        		$errorMSG = "Sorry, the  email: " .$strEmailForgotForm. " is not in our records"; //No Such Email in Database
			}else{ //Email found so ...

				while ($stmt->fetch()){
					//echo " $intUserID, $strFirstName, $strLastName, $strEmail <br>";

					//generate temp password
					$strPasswordTemp=createRandomKey_Num(12);

					//hash it
					$strPassword_hash = password_hash($strPasswordTemp, PASSWORD_DEFAULT); //PASSWORD_BCRYPT

					//update database
					//$query = "UPDATE ".TBL_USERS." SET password='$strPassword_hash' WHERE id = $intUserID " ;
					//echo "SQL STMNT = " . $query .  "<br>";
					//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
					if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
					if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET password = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
					if(!($stmt->bind_param('sd',								$strPassword_hash, $intUserID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
					if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}

					//send the user an email
					$strSubject = "coincafe.com Password Reset for $strEmailForgotForm";
					$strBody = "temp password: $strPasswordTemp \n ".
					"Please login here: ".WEBSITEFULLURLHTTPS.PAGE_SIGNIN." \n ".
					"Change your password here after " .WEBSITEFULLURLHTTPS.PAGE_SETTINGS." \n " ;
					funct_Mail_simple($strEmailForgotForm,$strSubject,$strBody,'',SUPPORT_EMAIL);

					$errorMSG = "A temporary password has been sent to " .$strEmailForgotForm. " Please check your email. sigin with the temp password and then change it."; //No Such Email in Database

				} //fetch for multiple rows
			}

			$stmt -> close(); //Close statement

		}else{
			echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error;
		}

		//echo "errorMSG=$errorMSG";
		//refresh join page with msg
		header( 'Location: '.PAGE_SIGNIN.'?error_forgot='.$errorMSG );
		die();

		break;



	//!CASE logout
	case "logout":

		$intTime=time();
		//update db last activity date
		//$query="UPDATE ".TBL_MEMBERS." SET lastactivity={$intTime} WHERE id=".DETECT_USERID ;
		//echo "SQL STMNT = " . $query .  "<br>"; //$_SESSION["SQL.do.php.logout"] = $query;
		//mysqli_query($DB_LINK, $query);
		functLogOutUser();

		header( 'Location: /' );
		die(); //Make sure code after is not executed

		break;



	//!CASE errornotloggedin
	case "errornotloggedin":
		$ErrorMSG = "You must be a Member and logged in to access " . $_SERVER['HTTP_REFERER'];
		break;


} //End Switch Statement

ob_flush(); //so we can redirect even after headers are sent

?>