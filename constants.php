<?php
/*
// Constants.php
// Written by: M.A.Y.
// make certain to have ?php and not just ? for every file or mysql connections will not work in certain places 
*/

//##############################
// Settings
define("JSONRPC_CONNECTIONSTRING_CC",		"https://A7sC5sYk1q4ef3XBoaqXYNq:9VnBT373isI1S3cA0cLqabs@5.153.60.162:8332"); //CoinCafe Custom RPC server at Softlayer
define("RETURN_OUTPUTTYPE", 				"json");
define("SUPPORT_EMAIL", 					"help@getcoincafe.com");
define("EMAIL_ADMIN", 						"admin@getcoincafe.com");
define("SERVER_IPADDRESS",					"5.153.60.162");
define("PASSWORD_ENCRYPT", 					""); //bcrypt 1
define("__ROOT__", 							$_SERVER['DOCUMENT_ROOT']); 
//##############################



//##############################
//Server Settings
include $_SERVER['DOCUMENT_ROOT']."/server.php"; //for server specific data $strServer value
define("SERVERTAG", 				$strServer); //from above
switch ($strServer){ // Server SiteWide Vars
	case "dev":
	define("MODE_UPGRADE", 				0);
	define("DEBUGMODE", 				1);
	define("WEBSITEURL", 				"local.ccapi");
	define("WEBSITEFULLURL", 			"http://".WEBSITEURL); //"http://".WEBSITEURL ;
	define("WEBSITEFULLURLHTTPS",		"http://".WEBSITEURL); //"https://".WEBSITEURL ;
	define("DB_SERVER", 				"localhost");
	define("DB_USER", 					"root");
	define("DB_PASS", 					"littles");
	define("DB_NAME", 					"coincafe_api");
	break;

	
	case "sl": //HostGator - awesome support, DMCA assholes
	define("MODE_UPGRADE", 				0);
	define("DEBUGMODE", 				0);
	define("WEBSITEURL", 				"coincafe.co");
	define("WEBSITEFULLURL", 			"http://" . WEBSITEURL);
	define("WEBSITEFULLURLHTTPS",		"https://".WEBSITEURL);
	define("DB_SERVER", 				"localhost");
	define("DB_USER", 					"root");
	define("DB_PASS", 					"3dFs7vRTj3U2");
	define("DB_NAME", 					"ccapi"); //
	break;
	

} //End Switch Statement
//##############################





//##############################
// Database settings
//##############################
$DB_LINK = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME) or die("Problem connecting: ".mysqli_error());
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