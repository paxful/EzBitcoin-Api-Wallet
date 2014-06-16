<?php
/*
// Constants.php
This wallet runs on the EZ API bitcoin server via RESTful interface.
Features:
*/

//##############################
//Wallet Settings
define("WEBSITENAME",                       "Ez Wallet");
define("LOGIN_ONJOIN",                      1);//login them in automatically when they join
define("LOGIN_SENDEMAILCODE",               1);//send them email when they join
define("CREATEADDRESS_ONJOIN",              0);//make a receiving address for members on wallet... otherwise give them one when they verify their email.
define("BANNEDWALLETREASONTXT",             "This address has been linked to illegal activities and has been banned."); //
define("RATE_HUD_EXCHANGE",                 "bitstamp"); //show rate in hud from coindesk bitstamp etc..
define("RATE_REFRESH_SECONDS",              "60"); //refresh crypto rate in hud every x secs -  60 seconds=1 minute
define("REFRESH_WALLET_SEC",                "30"); // how many seconds to refresh the wallet transactions
define("MAXCHAR_RECORDS_TRANSACTIONS",      25);// how many wallet transactions to show each time.
define("QRSCANAPP_IOS_URINAME",             "pic2shop://scan");//
define("QRSCANAPP_IOS_URL",                 "https://itunes.apple.com/us/app/pic2shop-barcode-scanner-qr/id308740640?mt=8");//
define("QRSCANAPP_DROID_URINAME",           "pic2shop://scan");//
define("QRSCANAPP_DROID_URL",               "https://play.google.com/store/apps/details?id=com.visionsmarts.pic2shop&hl=en");//


//##############################
//Crypto API Server Settings
define("WALLET_NEWADDRESS_HOST",            ""); // "blockchain.info" or "ezwallet" web api
define("JSONRPC_API_MERCHANT_URL",          ""); //connecting to a web api via get/post which does the rpc calls for us
define("JSONRPC_API_LOGIN",                 ""); //
define("JSONRPC_API_PASSWORD",              ""); //
define("JSONRPC_CONNECTIONSTRING",          ""); //only needed if connecting directly to a rpc server


//##############################
//Billing Settings ... only used if you want to raise or lower btc rate
define("RATE_HIKE_LUMP",                    0); //hike rate of crypto by this lump
define("RATE_HIKE_PERCENT",                 0.0); //hike crypto rate by percentage 0.1= 10%
define("RATE_MINIMUM_SELL",                 0); //rate never falls below
define("RATE_RANDOMIZER_MAX",               0); // ex 800 - 0-24 800-824
define("MININGFEE_FAST",                    0.001); //0.001
define("MININGFEE_NORMAL",                  0.0001); //0.0001 is now 0.00001 by default in bitcoind
define("MININGFEE_SLOW",                    0.00001); //0.00001


//##############################
//!SECURITY Settings
define("SECURITY_SENDOUTS_MUSTBE_APPROVED", "1");//an admin must approve all external sends sencrypto.php is where this check is /cp/que.php lists all in the que
define("SECURITY_LOGIN_WAIT_SECONDS",       "5");//seconds to wait in between each login
define("SECURITY_CAPCHACHECK",              "0");//force capcha check on or off signin NOT WORKING ON PRODUCTION.....
define("SECURITY_CAPCHA_PUBLICKEY",         "6LfryPESAAAAANWZGkE8SnScmNFo2QQ4z5QKHP7k");//
define("SECURITY_CAPCHA_PRIVATEKEY",        "6LfryPESAAAAAIo0c7MapKAEbNjPIh2JPNG0B-XJ");//
define("SECURITY_PASSWORD_LOOSE_LOGIN",     1); //allow them to login with verifyhash or literal password hash... insecure but done to allow legacy passwords
define("SECURITY_WRITE_PASSWORD",           1); //write password to cookie file hashed
define("SECURITY_ON_BLOCKCHAIN_ALLOWED",    0); //write password to cookie file hashed
define("SECURITY_ADMIN_LOGIN",              "B1gT1m38dM1nz"); //write password to cookie file hashed
define("SECURITY_ADMIN_PASSWORD",           "c01nc4f3!@#"); //write password to cookie file hashed
define("PASSWORD_ENCRYPT",                  "bcrypt"); //bcrypt 1


//##############################
// Email Constants - these specify what goes in the from field in the emails that the script sends to users, and whether to send a welcome email to newly registered users.
define("EMAIL_METHOD",                      ""); //no value means use default phpmail class. options: sendgrid, mailgun
define("EMAIL_SENDGRID_USERNAME",           "");
define("EMAIL_SENDGRID_PASSWORD",           "");
define("SUPPORT_EMAIL",                     "help@gmail.com");
define("EMAIL_FROM_NAME",                   "Bit Coin Wallet");
define("EMAIL_FROM_ADDR",                   "admin@gmail.com");
define("EMAIL_ADMIN",                       "admin@gmail.com");
define("EMAIL_WALLETSEND",                  "info@gmail.com");
define("EMAIL_TECH",                        "tech@gmail.com");
define("EMAIL_ADMIN_WEB",                   "");
define("EMAIL_ADMIN_ON_RECEIVE",            1); //send email to admin whenever any address gets funds
define("EMAIL_USER_ON_RECEIVE",             1);//do not send users an email when they get coins
define("EMAIL_WELCOME",                     true); //send welcome email on join
define("EMAIL_SMTPEXT",                     'enabled'); // enabled or disabled
define("EMAIL_SMTPHOST",                    'ssl://smtp.gmail.com');
define("EMAIL_SMTPPORT",                    '465'); //null
define("EMAIL_SMTPUSERNAME",                'info@gmail.com');
define("EMAIL_SMTPPASSWORD",                '');


//##############################
//Media & App Paths
define("PATH_ADMIN",                        "/cp/");
define("PATH_APPS",                         "/usr/local/bin/");
define("__ROOT__",                          $_SERVER['DOCUMENT_ROOT']);
define("PATH_MEDIA",                        "/media/");
define("PATH_TEMP",                         "/media/temp/"); //not used...
define("PATH_QRCODES",                      "/media/qrcodes/"); //available to public


//##############################
//Server Environment Settings .. add case statements here for different dev environments and production environments, staging etc...
require "server.php"; //for server specific data $strServer value
define("SERVERTAG",                 $strServer); //from above included file
switch ($strServer){ // Server SiteWide Vars
    case "dev":
    define("MODE_UPGRADE",              0);
    define("DEBUGMODE",                 1);
    define("WEBSITEURL",                "local.ezapi");
    define("WEBSITEFULLURL",            "http://".WEBSITEURL); //"http://".WEBSITEURL ;
    define("WEBSITEFULLURLHTTPS",       "http://".WEBSITEURL); //"https://".WEBSITEURL ;
    define("DB_SERVER",                 "localhost");
    define("DB_USER",                   "root");
    define("DB_PASS",                   "littles");
    define("DB_NAME",                   "db_wallet");
    define("JQUERYSRC",                 'js/jquery.min.js'); //latest jquery
    break;
} //End Switch Statement


//##############################
// Database Constants - these constants are required
$DB_MYSQLI = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if( $DB_MYSQLI->connect_errno ){
    error_log("Failed to connect to MySQLi: (" . $DB_MYSQLI->connect_errno . ") " . $DB_MYSQLI->connect_error);
    die('no db');
}
if( ! $DB_LINK = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME) ){
    error_log("Failed to connect to MySQL: ".mysqli_error());
    die('no db');
}
//Main content tables
define("TBL_USERS",                         "tbl_member");                  //holds all user records
define("TBL_WALLET_ADDRESSES",              "tbl_wallet_addresses");        //logging addresses for each user across crypto wallets
define("TBL_WALLET_BALANCES",               "tbl_wallet_balances");         //logging addresses for each user across crypto wallets
define("TBL_TRANSACTIONS",                  "tbl_transactions");            //holds all transactions
define("TBL_TRANSACTIONS_QUE",              "tbl_transactions_que");        //logs all send out requests for authorization
define("TBL_ESCROW",                        "tbl_escrow");                  //used for sending coins via email and other escrow services
define("TBL_ORDERS_CALLBACKS",              "tbl_callbacks");               //logging callbacks from api
define("TBL_BANNEDWALLETS",                 "tbl_banned_addresses");        //stores banned addresses
define("TBL_CURRENCY",                      "tbl_currency_fiat");           //stores all fiat currencies static
define("TBL_CURRENCY_CRYPTO",               "tbl_currency_crypto");         //stores all crypto and fiat currencies
define("TBL_RATES",                         "tbl_rates");                   //update rates for crypto from various exchanges ( 1 to many with tbl_currency )
define("TBL_COUNTRIES",                     "tbl_countries");               //static list of all nations


//##############################
// Static Pages
define("PAGE_HOME",                         "/wallet/");
define("PAGE_SIGNIN",                       "/wallet/signin.php");
define("PAGE_SIGNUP",                       "/wallet/signup.php");
define("PAGE_WALLET",                       "/wallet/wallet.php");
define("PAGE_SETTINGS",                     "/wallet/settings.php");
define("CODE_DO",                           "/wallet/do.php");
define("CODE_DOAJAX",                       "/wallet/ajax_do.php");
define("CODE_PROCESSORDER",                 "/wallet/processorder2.php");
define("MOD_SENDCRYPTO",                    "/wallet/sendcrypto.php");
define("MOD_LOADCONTENT",                   "/wallet/loadcontent.php");


//##############################
// Cookie Constants - these are the parameters
define("COOKIE_EXPIRE",                     1200 * 60 * 24 * 60 + time());  //1200 days by default
define("COOKIE_PATH",                       "/");  //Available in whole domain
define("COOKIE_DOMAIN",                     ".".WEBSITEURL);  //Available in whole domain ex .google.com
define("SESSION_USERID",                    "userid" ); //integer value of use record in database.. for fast lookups
define("SESSION_USERIDCODE",                "useridcode" ); //long 48 character hash for storing user value in cookie.
define("SESSION_EMAIL",                     "email" );
define("SESSION_PASSWORD",                  "hashsha256" ); //not used. we don't store password values in cookie
define("SESSION_LASTVISIT",                 "lastvisit" );
define("SESSION_REMEMBERFLAG",              "rememberflag" );
//define("DETECT_USERID",                   $_COOKIE[SESSION_USERID] ); //we now define this in sessions.php from a value passed by a function that uses the code.. for security purposes.
define("DETECT_USERIDCODE",                 isset($_COOKIE[SESSION_USERIDCODE]) ? $_COOKIE[SESSION_USERIDCODE] : NULL );
define("DETECT_EMAIL",                      isset($_COOKIE[SESSION_EMAIL]) ? $_COOKIE[SESSION_EMAIL] : NULL );
define("DETECT_PASSWORD",                   isset($_COOKIE[SESSION_PASSWORD]) ? $_COOKIE[SESSION_PASSWORD] : NULL );


?>