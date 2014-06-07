<?php
/*
// Constants.php
// Written by: M.A.Y.
// make certain to have ?php and not just ? for every file or mysql connections will not work in certain places 
*/

//##############################
// Settings
define("RETURN_OUTPUTTYPE", 				"json");
define("SUPPORT_EMAIL", 					"help@gmail.com");
define("EMAIL_ADMIN", 						"admin@gmail.com");
define("SERVER_IPADDRESS",					"127.0.0.1"); //
define("PASSWORD_ENCRYPT", 					""); //bcrypt 1
define("__ROOT__", 							$_SERVER['DOCUMENT_ROOT']); 
define("LOADCONTENT",						"/inc/loadcontent.php");
//##############################



//##############################
//Server Settings

define("SERVERTAG", 				$strServer); //from above
switch ($strServer){ // Server SiteWide Vars

	case "dev":
	define("MODE_UPGRADE", 				0);
	define("DEBUGMODE", 				1);
	define("WEBSITEURL", 				"local.ezapi");
	define("WEBSITEFULLURL", 			"http://".WEBSITEURL); //"http://".WEBSITEURL ;
	define("WEBSITEFULLURLHTTPS",		"http://".WEBSITEURL); //"https://".WEBSITEURL ;
	define("DB_SERVER", 				"localhost");
	define("DB_USER", 					"root");
	define("DB_PASS", 					"littles");
	define("DB_NAME", 					"easybitz_api");
	define("JSONRPC_CONNECTIONSTRING_CC","http://61141261cRe2Epu0qOFU:L2iFnU14rf0r3v3r832W@37.58.86.163:8332"); //61141261cRe2Epu0qOFU / L2iFnU14rf0r3v3r832W 37.58.86.163
	define("JQUERYSRC",					'/js/jquery.min.js'); //latest jquery
	define("JQUERYUISRC",				'/js/jqueryui.min.1.9.2.js'); //1.9.2 doesn't break blueimp upload and still allows drag and resize
	break;

	
	case "artur": //
	define("MODE_UPGRADE", 				0);
	define("DEBUGMODE", 				0);
	define("WEBSITEURL", 				"127.0.0.1");
	define("WEBSITEFULLURL", 			"http://".WEBSITEURL);
	define("WEBSITEFULLURLHTTPS",		"https://".WEBSITEURL);
	define("DB_SERVER", 				"localhost"); //
	define("DB_USER", 					"root");
	define("DB_PASS", 					"pass");
	define("DB_NAME", 					"api"); //
	define("JSONRPC_CONNECTIONSTRING_CC","http://pass:user@127.0.0.1:8332"); //
	define("JQUERYSRC",					'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
	define("JQUERYUISRC",				'//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');//must upgrade to latest blueimp to use latest jqueryiu	
	break;
	

} //End Switch Statement
//##############################


//##############################
// Database settings
//##############################
//echo $strServer." ".DB_SERVER." ".DB_USER." ".DB_PASS." ".DB_NAME ;
$DB_LINK = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME) or die("Problem connecting: ".mysqli_error());
//Main content tables
define("TBL_USERS",  						"tbl_api_users");					//
define("TBL_WALLET_ADDRESSES",				"tbl_api_addresses");		//
define("TBL_TRANSACTIONS",  				"tbl_api_transactions");			// 
define("TBL_LOGS",  						"tbl_api_logs");			// 
//##############################




//##############################
// Email Constants - these specify what goes in the from field in the emails that the script sends to users, and whether to send a welcome email to newly registered users.
define("EMAIL_WELCOME", 					true);
define("EMAIL_SMTPEXT", 					'enabled'); // enabled or disabled
define("EMAIL_SMTPHOST", 					'ssl://smtp.gmail.com');
define("EMAIL_SMTPPORT", 					'465'); //null
define("EMAIL_SMTPUSERNAME", 				'tech@getcoincafe.com');
define("EMAIL_SMTPPASSWORD", 				'l1ttl3s7781');
//##############################

?>