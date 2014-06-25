<?php

ob_start(); //so we can redirect even after headers are sent

require "inc/session.php";

//error_reporting(E_ALL & ~E_NOTICE);

//Get Fresh Page QueryString Variables
$strDo = 			trim($_GET['do']);
$intUserID = 		funct_GetandCleanVariables(DETECT_USERID);
//$strChestKey = DETECT_CHESTKEY ;

//echo "strDo= " . $strDo;
switch ($strDo){



    //making a new address - called from
    //!CASE newpublickey
    case "newpublickey": //send email confirm code

        $intUserID = funct_GetandCleanVariables($_POST["userid"]);

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


    //called from
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

        //if wallet address does not exist then make them a new receiving wallet address
        if(!$strWalletBTCaddress){
            //make them a new  address
            $strWalletBTCaddress = funct_MakeWalletAddressUpdate($intUserID);
        }

        if($strWalletBTCaddress){
            $strError = "Receive Address created." ;
        }else{
            $strError = "ERROR - Receive Address NOT created. Please try again later.. sorry" ;
        }


        header( 'Location: '. PAGE_WALLET.'?error='.$strError ); die(); //Make sure code after is not executed

    break;


    //called from wallet.pho
    case "getbalance": //!case "getbalance"

        $strFiatType = 					(funct_GetandCleanVariables($_GET["fiat"])) ;
        $strCryptoType = 				(funct_GetandCleanVariables($_GET["crypto"])) ;
        $intOwnerID = 					(funct_GetandCleanVariables($_GET["userid"])) ;

        if($intOwnerID != $intUserID_fromcode){
            // 401 redirect
            if($intUserID_fromcode == ''){
                error_log('AUTH ISSUE: balance requested from user who is not logged in');
                // todo: ajax fail should redirect to login page
            } else {
                error_log('AUTH ISSUE: balance requested from unauthorized user');
                // todo: ajax fail should redirect to login page
            }
            header("HTTP/1.1 401 Unauthorized");
            exit();
        }

        //get usr info
        $query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $intOwnerID ;
        //echo "SQL STMNT = " . $query .  "<br>";
        $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
        $intBalanceCrypto=				$row["balance_btc"];

        //get balances
        $intBalance = $intBalanceCrypto ;

        //get fiat / crypto symbol
        $intCryptoRate = funct_Billing_GetRate($strCryptoType);
        $intFiatBalance = $intBalance * $intCryptoRate ;

        echo $intBalance.",".$intFiatBalance ;

    break;



    //!CASE sendemailcode
    case "sendemailcode": //send email confirm code

        //send email
        funct_SendEmailCode($intUserID);

        //make them a new  address
        $strWalletBTCaddress = funct_MakeWalletAddressUpdate($intUserID);


        $strError = "Email sent!";
        //redirect to settings page
        header( 'Location: '. PAGE_WALLET ); die(); //Make sure code after is not executed
    break;



    //!CASE confirmemailcode
    case "confirmemailcode":
        //get code from form
        $strCode = 					funct_GetandCleanVariables($_GET['emailcode']);
        $strEmail = 				funct_GetandCleanVariables($_GET['email']);
        $intUserID = 				funct_GetandCleanVariables($_GET['id']);

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
                    header( 'Location: '. CODE_DO.'?do=confirmemail&error=Code is not for your account!.' );

                }else{

                    header( 'Location: '. PAGE_WALLET.'?do=emailverified' );
                    die(); //Make sure code after is not executed
                }

            }//end if logged in


        }else{ //email code does NOT match

            $strDo="confirmemail" ;  //echo $strDo ;
            //$strError = "Email Code does not match or no such member exists";
            header( 'Location: '. CODE_DO.'?do=confirmemail&error=Email code incorrect. '." $strCode_DB == $strCode " );
            die;
        }

    break;


    

    //!CASE sendphonecode
    case "sendphonecode": //send phone code via sms

        $strNumber = funct_GetandCleanVariables($_POST["phone"]);
        $strError = funct_SendPhoneCode($intUserID,$strNumber);

        //redirect to settings page
        header( 'Location: '. PAGE_SETTINGS.'?error_testphone='.$strError ); die(); //Make sure code after is not executed

    break;



    //!CASE confirmphonecode
    case "confirmphonecode": //confirm phone code via sms

        //get code from form
        $strCode = 				funct_GetandCleanVariables($_POST['phonecode']);

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










    //called from settingss
    //!CASE update
    case "update":

        //Get all post values, ckeck & clean them
        $strPassword = 				funct_GetandCleanVariables($_POST['password']);
        $strEmail = 				funct_GetandCleanVariables($_POST['email']);
        $intCountryID = 			funct_GetandCleanVariables($_POST['country']);
        $intCurrencyID = 			funct_GetandCleanVariables($_POST['currency']);
        $strNameFirst = 			funct_GetandCleanVariables($_POST['namefirst']);
        $strNameLast = 				funct_GetandCleanVariables($_POST['namelast']);
        $strAddress = 				funct_GetandCleanVariables($_POST['address']);
        $strAddress2 = 				funct_GetandCleanVariables($_POST['address2']);
        $strCity = 					funct_GetandCleanVariables($_POST['cityname']);
        $strState = 				funct_GetandCleanVariables($_POST['state']);
        $strPostal = 				funct_GetandCleanVariables($_POST['postal']);
        $strCellPhone = 			funct_GetandCleanVariables($_POST['cellphone']);
        $strCellPhone_code = 		funct_GetandCleanVariables($_POST['cellphone_countrycode']);
        //$intMiningFee = 			funct_GetandCleanVariables($_POST['miningfee']);

        if($intCountryID<1){ $intCountryID=0 ;}
        if($intCurrencyID<1){ $intCurrencyID=0 ;}
        if(!$intMiningFee){ $intMiningFee=MININGFEE_NORMAL; }


        if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
        if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET cellphone = ?, country_phonecode = ?, first_name = ?, last_name = ?, address = ?, address2 = ?, cityname = ?, state = ?, postal = ?, country_id = ?, currency_id = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
        if(!($stmt->bind_param('sssssssssddd',					$strCellPhone, $strCellPhone_code, $strNameFirst, $strNameLast, $strAddress, 		$strAddress2, $strCity, $strState, $strPostal, $intCountryID, $intCurrencyID, $intUserID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
        if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}

        //EMAIL_WALLETSEND
        header( 'Location: '.PAGE_SETTINGS."?error=Account Updated!" ); die();

    break;





    //called from settings or wallet
    //!CASE updatepassword
    case "updatepassword":

        //Get all post values
        $strPassword_old = 			funct_GetandCleanVariables($_POST['passwordold']);
        $strPassword = 				funct_GetandCleanVariables($_POST['password']);
        $strPassword2 = 			funct_GetandCleanVariables($_POST['password2']);

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







	//!CASE join
	case "join": //Register User BEGIN ----------------------------------------------------------------------------------------------------

		//Get Form Post Data - auto protect against xss and sql injection
		$FormRegEmail = 		funct_GetandCleanVariables($_POST["email"]);
		$FormRegPhone = 		funct_GetandCleanVariables($_POST["phonenumber"]);
		$FormRegFirstName = 	funct_GetandCleanVariables($_POST["firstname"]);
		$FormRegLastName = 		funct_GetandCleanVariables($_POST["lastname"]);
		$FormRegAddress = 		funct_GetandCleanVariables($_POST["address"]);
		$FormRegpassword = 		funct_GetandCleanVariables($_POST["password"]);
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


            //#######################################################
            #CREATE NEW USER
			$strDateTime=date("Y-m-d H:i:s");
			if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
			if(!($stmt = $DB_MYSQLI->prepare("INSERT INTO ".TBL_USERS."(password, 		email,		 	cellphone, 		first_name, 	last_name, 		address,		date_joined) VALUES (?,?,?,?,?,?,?)") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
			if(!($stmt->bind_param('sssssss', 							$FormRegpassword,$FormRegEmail,$FormRegPhone,$FormRegFirstName,$FormRegLastName,$FormRegAddress,$strDateTime ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
			if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error; }
			$intNewRecordID = mysqli_insert_id($DB_MYSQLI);



            // create new member successful, do other operations
			if($intNewRecordID > 0 ){

                //generate unique code for email confirmation
				$intCode=createRandomKey_Num(12);
				$strEmailLink = WEBSITEFULLURLHTTPS.PAGE_WALLET."?do=confirmemailcode&emailcode=$intCode&id=$intNewRecordID&email=$FormRegEmail" ;

                //auto login
				if(LOGIN_ONJOIN){
					//Write Session & Cookies to Login User
					$strRememberFlag = "1" ; //Remember username and password by default
					functLoginUser($intNewRecordID, $strRememberFlag, $FormRegpassword);
					$_SESSION["justjoined"] = time();//set flag for new user
				}

                //auto create bitcoin address on join
				if(CREATEADDRESS_ONJOIN){ //create a new wallet address for them
					$strWalletAddress = funct_MakeWalletAddressUpdate($intUserID);
				}

                //send new email code on join
				if(LOGIN_SENDEMAILCODE){ //update member record with new confirm code
					//$query = "UPDATE ".TBL_USERS." SET emailcode='$intCode' WHERE id = $intNewRecordID " ;
					//echo "SQL STMNT = " . $query . "<br>";
					//$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
					if($DB_MYSQLI->connect_errno) { echo "Failed to connect to MySQL: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error; }
					if(!($stmt = $DB_MYSQLI->prepare("UPDATE ".TBL_USERS." SET emailcode = ? WHERE id = ? ") )) { echo "Prepare failed: (" . $DB_MYSQLI->errno . ") " . $DB_MYSQLI->error; }
					if(!($stmt->bind_param('sd',								$intCode, $intNewRecordID ) )) { echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error; }
					if(!($stmt->execute())) { echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;}
					$strEmailCodeText = "Your ".WEBSITENAME." Verification Code is \n ".$intCode." \n".$strEmailLink ;


                    //send them an email
                    $strSubject = "Welcome to ".WEBSITENAME." ".$FormRegFirstName." ".$FormRegLastName;
                    $strBody = "We're happy to have you as a member. \n\n ".
                        "\n\nPlease verify your email here: ".$strEmailLink.
                        "\n\n-Thank you \n ".EMAIL_FROM_NAME."  \n ".WEBSITEFULLURLHTTPS ;
                    funct_Mail_simple($FormRegEmail,$strSubject,$strBody);
				}



                /*
				//send the admin an email on each new user
				$ipaddress = $_SERVER['REMOTE_ADDR'];
				$strSubject = "New Member ".$intNewRecordID." ".$FormRegFirstName." ".$FormRegLastName;
				$strBody = "User ID: $intNewRecordID\nName: $FormRegFirstName $FormRegLastName\nEmail: $FormRegEmail\nPhone: $FormRegPhone\nIP: $ipaddress\n\n".WEBSITEURL ;
				funct_Mail_simple(SUPPORT_EMAIL,$strSubject,$strBody,'',$FormRegEmail);
                */

				//redirect them to the dashboard with flag to guide them through
				$strRefreshURL = PAGE_WALLET ; //."?do=confirmemail" ; //PAGE_LEDGER."?do=joined" ;

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

		$user_name = (funct_GetandCleanVariables($_POST['user_name']));

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
		$username = 			funct_GetandCleanVariables($_POST['email']);
		$password = 			funct_GetandCleanVariables($_POST['password']);
		$strReturnURL = 		funct_GetandCleanVariables($_POST["returnurl"]);

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
			$strChallenge = 		funct_GetandCleanVariables($_POST["recaptcha_challenge_field"]);
	        $strResponse = 			funct_GetandCleanVariables($_POST["recaptcha_response_field"]);
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
		$strEmailForgotForm = (funct_GetandCleanVariables($_POST["forgot_email"]));

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




    /*
     //for claiming coins sent via email - not tested
     //!CASE claimcoins
     case "claimcoins":
     //let them claim their coins
         $strEmail = 	funct_GetandCleanVariables($_POST['email']);
         $strCode = 		funct_GetandCleanVariables($_POST['code']);

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
     */


} //End Switch Statement

ob_flush(); //so we can redirect even after headers are sent

?>