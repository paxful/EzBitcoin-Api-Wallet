<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
//$strThisPage =        PAGE_SETTINGS;

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";
//$intUserID =          DETECT_USERID;

//Get QueryString Values
$strDO =                    trim($_GET["do"]);

//need a do case for authorizing here
if($strDO=="authorizeque"){
  
  $intQueID =             funct_ScrubVars($_GET['queid']);
  //Get all post values, ckeck & clean them
  
  //get que info first
  $query="SELECT * FROM " . TBL_TRANSACTIONS_QUE . " WHERE  id = '" . $intQueID."' " ;
  //echo "SQL STMNT = " . $query .  "<br>";
  $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
  if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
    $intTransactionID =     $row["transaction_id"];
  }
  
  //get transaction info
  $query="SELECT * FROM " . TBL_TRANSACTIONS . " WHERE  transaction_id = '" . $intTransactionID."' ";
  //echo "SQL STMNT = " . $query .  "<br>";
  $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
  if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
    //records found so get details so we can recreate the send
    $strWalletHash =        $row["walletaddress_sentto"];
    $intBTCamt =            $row["crypto_amt"];
    $strNote =              $row["label"];
    $intMiningFee =         $row["crypto_miner_fee"];
    $strWalletFrom =        $row["walletaddress_from"];
    $intUserID_send=        $row["user_id"];
  }
  
  //user info $intUserID_send
  $query="SELECT * FROM " . TBL_USERS . " WHERE id = '" . $intUserID_send."' " ;
  //echo "SQL STMNT = " . $query .  "<br>";
  $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
  if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
    $strEmail_get=                      $row["email"];
    $strFirstName_get=                  $row["first_name"];
    $strLastName_get=                   $row["last_name"];
    $strPhone_get=                      $row["cellphone"];
    $intBalanceCrypto_receive=          $row["balance_btc"];

    //amsterdam takes 3 additional data fields 
    $strLabel = "$strFirstName_get $strLastName_get" ;
    $strLabel2 = $intUserID_send;
    $strLabel3 = $strEmail_get;
  }
  
  //send bitcoin
  if(SEND_THROUGH_WHICH_SYSTEM=="blockchain.info"){
      $strSendReturn = funct_Billing_SendBTC($strWalletHash, $intBTCamt, $strNote, $intMiningFee, $strWalletFrom); //web api
  }
  if(SEND_THROUGH_WHICH_SYSTEM=="amsterdam"){
      $strSendReturn = funct_Billing_SendBTC_CoinCafe($strWalletHash, $intBTCamt, $strNote, $intMiningFee, $strWalletFrom, $strLabel,$strLabel2,$strLabel3); //web api
  }
  
  //parse return array
  $strSendArry = explode("|", $strSendReturn);
  $strSendMsg=$strSendArry[0]; // message
  $strSendErr=$strSendArry[1]; // error
  $strSendHash=$strSendArry[2]; // txid - IT ONLY RETURNS A HASH IF SUCCESSFUL

  //update que record
  
  $query="UPDATE " . TBL_TRANSACTIONS_QUE . " SET status_id=1 , transaction_txid='$strSendHash' , transaction_address='$strWalletHash' WHERE id=".$intQueID ;
  //echo "SQL STMNT = " . $query .  "<br>";
  $strERRORUserMessage="Database update que record Error. Admin has been informed ".$strError_send ; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
  mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
  
  //update transaction
  $query="UPDATE " . TBL_TRANSACTIONS . " SET hash_transaction='$strSendHash' WHERE transaction_id=".$intTransactionID ;
  //echo "SQL STMNT = " . $query .  "<br>";
  $strERRORUserMessage="Database update transaction hash Error. Admin has been informed ".$strError_send ; $strERRORMessageAdmin="$strError_send \n SQL statement failed - $query "; 
  mysqli_query($DB_LINK, $query) or funct_die_with_grace(mysqli_error(), $strERRORUserMessage, $strERRORMessageAdmin, $strERRORPage)  ;
  $strStatus = 1 ;
  
  
  //email user and let them know that their transaction has officially gone through
  $strSubject = "Your $intBTCamt Bitcoins have been sent.";
  $strBody = "Thank you for using ".WEBSITENAME ; 
  funct_Mail_simple($strEmail_get,$strSubject,$strBody,'',$strEmail);
  
  echo "Bitcoins sent: Error= $strSendErr - MSG= $strSendMsg ";
}

?>