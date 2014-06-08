<?php
/*
// Constants.php
// Written by: M.A.Y.
// make certain to have ?php and not just ? for every file or mysql connections will not work in certain places
*/

//##############################
//Billing Settings
define("WIRE_BANK_ID",                      5); // 5= FARGO 6=AMALGAMTED 3=BOA

define("CC_CUT_FAST",                       1); //percentage to take from fast transactions, deposit, check, wire, meet in person
define("CC_FEE",                            0.06); // Fee
define("RATE_HIKE_LUMP",                    0); //hike rate of crypto by this lump
define("RATE_HIKE_PERCENT",                 0.0); //hike crypto rate by percentage 0.1= 10%
define("RATE_MINIMUM_SELL",                 0); //rate never falls below
define("RATE_RANDOMIZER_MAX",               0); // ex 800 - 0-24 800-824
define("CRYPTO_PERCENT_HIKE",               8); //percentage to charge cryptolocker peeps
define("DEBIT_PERCENT_HIKE",                7); //percentage to charge debit
define("MININGFEE_FAST",                    0.001); //0.001
define("MININGFEE_NORMAL",                  0.0001); //0.0001
define("MININGFEE_SLOW",                    0.00001); //0.00001

define("RATE_HUD_EXCHANGE",                 "bitstamp"); //show rate in hud from coindesk bitstamp or gox
define("RATE_REFRESH_SECONDS",              "60"); //refresh rates every 60 seconds=1 minute
define("REFRESH_WALLET_SEC",                "30"); // how many seconds to refresh the wallet transactions

//Controls where new wallets are made and where funds are sent from
define("SEND_ACTIVE",                       true); // to turn off send athorization
define("SEND_THROUGH_WHICH_SYSTEM",         "amsterdam"); // blockchain.info , amsterdam
define("WALLET_NEWADDRESS_HOST",            "amsterdam"); //amsterdam, blockchain.info

//we don't need to have this here now as we only speak to it via the webapi
define("JSONRPC_CONNECTIONSTRING_CC",       "https://A7sC5sYk1q4ef3XBoaqXYNq:9VnBT373isI1S3cA0cLqabs@5.153.60.162:8332"); //CoinCafe Custom RPC server at Softlayer   COMPROMISED!! -John
define("JSONRPC_API_LOGIN",                ""); //in users tbl
define("JSONRPC_API_PASSWORD",             ""); //
define("JSONRPC_API_MERCHANT_URL",         "https://5.153.60.162/merchant/"); //
define("JSONRPC_API_SECRET",               "n00n3z");
define("JSONRPC_API_SECRET",               ""); //
//##############################




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






//##############################
//Site Settings
define("WEBSITENAME",                       "Wallet");
define("TITLE_END",                         " | ");
define("HUD_PROGRESSBAR_WIDTH",             "100");
define("SUPPORT_EMAIL",                     "help@gmail.com");
define("EMAIL_FROM_NAME",                   "Coin Cafe");
define("EMAIL_FROM_ADDR",                   "admin@gmail.com");
define("EMAIL_ADMIN",                       "admin@gmail.com");
define("EMAIL_WALLETSEND",                  "info@gmail.com");
define("EMAIL_TECH",                        "tech@gmail.com");
define("EMAIL_ADMIN_WEB",                   "");
define("EMAIL_ADMIN_ON_RECEIVE",            "1");
define("EMAIL_USER_ON_RECEIVE",             0);//do not send users an email when they get coins
define("EMAIL_SENDGRID_USERNAME",           "");
define("EMAIL_SENDGRID_PASSWORD",           "");
define("SERVER_IPADDRESS",                  "162.144.93.87");

define("MAXCHAR_RECORDS_TRANSACTIONS",      5);// how many transactions to show each time.
define("LOGIN_ONJOIN",                      1);//login them in automatically when they join
define("LOGIN_SENDEMAILCODE",               1);//login them in automatically when they join
define("WALLET_NOTICE",                     0); //show a modal with a message found in /walletnotice.php should just be html
//define("BLOAT_ORDERID",                   23425); //add this value to all order id's
define("CREATEFULLWALLET_ONJOIN",           0); //make a full wallet for members on blockchain.info
define("CREATEADDRESS_ONJOIN",              0); //make a recieving address for members on blockchain.info
define("BANNEDWALLETREASONTXT",             "This address has been linked to illegal activities and has been banned. Your IP address has been logged and will be reported to the authorities should they ask for it."); //make a recieving address for members on blockchain.info
define("TEXTDATA_EMAIL_ORDERSENT",          "/inc/email_ordersent.txt"); // used on /cp/order_details.php

//##############################


//##############################
//Media & App Paths
define("PATH_ADMIN",                        "/cp/");
define("PATH_APPS",                         "/usr/local/bin/");
define("__ROOT__",                          $_SERVER['DOCUMENT_ROOT']);
define("PATH_MEDIA",                        "/media/");
define("PATH_TEMP",                         "/media/temp/"); //not used...
define("PROFILEPICTUREPATH",                "/media/profilepics/"); //not used
define("PATH_QRCODES",                      "/media/qrcodes/"); //available to public
define("PATH_UPLOADS",                      "/cp/uploads/"); //PROTECT THIS FOLDER. ADMINS ONLY
define("PATH_RECEIPTS",                     "/media/receipts/");//PROTECT THIS FOLDER. ADMINS ONLY
define("PATH_KYC",                          "/media/kyc/");//PROTECT THIS FOLDER. ADMINS ONLY
define("PICTURETHUMBPATH",                  "/media/thumb/"); //only this folder should be available to the public
define("RECEIPTSTHUMBPATH",                 "/media/thumb_receipts/"); //not used now
//##############################


//##############################
//Server Settings
require "server.php"; //for server specific data $strServer value
define("SERVERTAG",                 $strServer); //from above
switch ($strServer){ // Server SiteWide Vars
    case "dev":
    define("BITSTAMP_API_KEY",          "Bdy4KpHGOKzNQ4W3gpyfOWXlBi1N36gz"); //locked down to production ip address
    define("BITSTAMP_API_SECRET",       "zBWuoRFoyBiQSGTYM8Yug4pm6Ilj2RAn");
    define("MODE_UPGRADE",              0);
    define("DEBUGMODE",                 1);
    define("WEBSITEURL",                "local.coincafe");
    define("WEBSITEFULLURL",            "http://".WEBSITEURL); //"http://".WEBSITEURL ;
    define("WEBSITEFULLURLHTTPS",       "http://".WEBSITEURL); //"https://".WEBSITEURL ;
    define("DB_SERVER",                 "localhost");
    define("DB_USER",                   "root");
    define("DB_PASS",                   "littles");
    define("DB_NAME",                   "cc_to");
    define("JQUERYSRC",                 'js/jquery.min.js'); //latest jquery
    break;


} //End Switch Statement
//##############################

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
define("TBL_USERS",                         "tbl_member");                  //
define("TBL_TRANSACTIONS",                  "tbl_transactions");            //
define("TBL_WALLET_ADDRESSES",              "tbl_wallet_addresses");        //logging
define("TBL_TRANSACTIONS_QUE",              "tbl_transactions_que");
define("TBL_ESCROW",                        "tbl_escrow");                  //
define("TBL_ORDERS_CALLBACKS",              "tbl_orders_callbacks");        //logging callbacks from api
define("TBL_BANNEDWALLETS",                 "tbl_bannedwallets");           //
define("TBL_COUNTRIES",                     "tbl_countries");               //static
define("TBL_RATES",                         "tbl_rates");                   //update rates for crypto
define("TBL_CURRENCY",                      "tbl_currency_fiat");           //
//##############################


//##############################
// Static Pages
define("PAGE_HOME",                         "/wallet/");
define("PAGE_SIGNIN",                       "signin.php");
define("PAGE_SIGNUP",                       "signup.php");
define("PAGE_WALLET",                       "index.php");
define("PAGE_SETTINGS",                     "settings.php");
define("PAGE_ERROR",                        "error.php");
define("PAGE_VERIFY",                       "verify.php");
define("CODE_DO",                           "do.php");
define("CODE_DOAJAX",                       "ajax_do.php");
define("CODE_PROCESSORDER",                 "processorder2.php");
define("MOD_SENDCRYPTO",                    "sendcrypto.php");
define("MOD_LOADCONTENT",                   "loadcontent.php");
//##############################




//##############################
// Email Constants - these specify what goes in the from field in the emails that the script sends to users, and whether to send a welcome email to newly registered users.
define("EMAIL_WELCOME",                     true);
define("EMAIL_SMTPEXT",                     'enabled'); // enabled or disabled
define("EMAIL_SMTPHOST",                    'ssl://smtp.gmail.com');
define("EMAIL_SMTPPORT",                    '465'); //null
define("EMAIL_SMTPUSERNAME",                'info@gmail.com');
define("EMAIL_SMTPPASSWORD",                '');
//##############################


//##############################
// Cookie Constants - these are the parameters
define("COOKIE_EXPIRE",                     1200 * 60 * 24 * 60 + time());  //1200 days by default
define("COOKIE_PATH",                       "/");  //Available in whole domain
define("COOKIE_DOMAIN",                     ".".WEBSITEURL);  //Available in whole domain ex .google.com

define("SESSION_USERID",                    "userid" );
define("SESSION_USERIDCODE",                "useridcode" );
define("SESSION_EMAIL",                     "email" );
define("SESSION_PASSWORD",                  "hashsha256" );
define("SESSION_LASTVISIT",                 "lastvisit" );
define("SESSION_REMEMBERFLAG",              "rememberflag" );

//define("DETECT_USERID",                   $_COOKIE[SESSION_USERID] ); //we now define this in sessions.php from a value passed by a function that uses the code.. for security purposes.

define("DETECT_USERIDCODE",                 isset($_COOKIE[SESSION_USERIDCODE]) ? $_COOKIE[SESSION_USERIDCODE] : NULL );
define("DETECT_EMAIL",                      isset($_COOKIE[SESSION_EMAIL]) ? $_COOKIE[SESSION_EMAIL] : NULL );
define("DETECT_PASSWORD",                   isset($_COOKIE[SESSION_PASSWORD]) ? $_COOKIE[SESSION_PASSWORD] : NULL );

//define("PROFILEPICURL",                   PROFILEPICTUREPATH . DETECT_USERID . ".jpg");
//##############################


// #####  VESTIGIAL DEFINITIONS  ######
define("ALL_LOWERCASE",                     false);
define("MAX_IDLE_TIME",                     3); // Define how long the maximum amount of time the session can be inactive.


?>