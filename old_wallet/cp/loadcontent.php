<?php
//error_reporting(1); // Turn off all error reporting
error_reporting(E_ERROR | E_PARSE); ini_set('display_errors',1);
ob_start(); //so we can redirect even after headers are sent


//get variables
if(!$strDo){
	$strDo = 				trim($_GET['do']);
}

if($strDo=="ajax" || $strDo=="iframe"){

	require_once $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

	//check to see if user is logged in and an admin
	//include __ROOT__.PATH_ADMIN."checklogin.php";

	//calling file via ajax so get values from query string
	$intNewstID = 			trim($_GET['newest_msg_id']);
	$intLastMSGID = 		trim($_GET['last_msg_id']);
	$intMaxRecords = 		trim($_GET['maxrecords']);
	$intType = 				trim($_GET["type"]); //1=songs, 2=pics, 3=ringtones, 7=albums, 9=people
	$intUserID2 = 			trim($_GET["user2"]); //for me.php , comments
	$sortby = 				trim($_GET["sort"]);
	$strFilter = 			trim($_GET["f"]);
	//if(!$intFilter){$intFilter=0;}//show all types
	$strSearchTXT = 		trim($_GET["searchtxt"]);
	$strSearchType = 		trim($_GET["searchtype"]);

	$intMod =				trim($_GET["m"]); //is this user a moderator?
	$intRecID = 			trim($_GET["recid"]); //to return single cell of record just uploaded
	$intUserID_viewer = 	trim($_GET["viewer"]); //userid of user currently viewing the content

	if($strDo=="iframe"){ ?>
		<link href="/wallet/css/web.css" media="screen" rel="stylesheet" type="text/css">
        <script src="/wallet/js/web.js" type="text/javascript"></script>
	<?php }

}else{ //file is being included and values are preset

	//...? nothing.. values should already be set in included file

}


//security check...
//$intUserID_viewer = DETECT_USERID; //always get current user from function-cookie
//if(!$intUserID_viewer){$intUserID_viewer = DETECT_USERID; }//if no viewer provided then get it from cookie

//sql injection defend
//$intUserID2 = mysqli_real_escape_string($DB_LINK,$intUserID2);
//$intKeyID = mysqli_real_escape_string($DB_LINK,$intKeyID);
//$intUserID1 = mysqli_real_escape_string($DB_LINK,$intUserID1);

if(!$intMaxRecords){$intMaxRecords=100;}

//if($intUserID_viewer=="2" OR $intUserID_viewer=="169"){$intMod=1;} //techz,copyright are mods
if(!$intLastMSGID){$intLastMSGID=0;} //for virgin page call



//###################################################################################################################
//BEGIN SWITCH statement for types of content
//###################################################################################################################
switch ($intType){





//###################################################################################################################
//!CASE $intType "TRANSACTIONS"
//###################################################################################################################
	case "transactions": //list of transactions in wallet.php

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){
		$strWhereSTMT = $strWhereSTMT. " AND ( user_id=$intUserID_viewer OR user_id_sentto=$intUserID_viewer ) ";
	}else{
		//die;  //not logged in then show nothing! SECURITY
	}

	if($intRecID){ $strWhereSTMT =" AND transaction_id=$intRecID " ;} //return single record
	if($sortby=="top"){ $strOrderBySTMT =" crypto_amt DESC " ;}
	if($sortby=="new"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_id>$intNewstID " ; $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}
	$strOrderBySTMT = " datetime_created DESC ";
    //$intLastMSGID = 0;
	//$intMaxRecords = 1000;

    $query="SELECT * ".
	" FROM ".TBL_TRANSACTIONS." ".
	//" WHERE transaction_id>0 AND ( status>0 OR type='buy' ) $strWhereSTMT ".    <--   ORIGINAL STATEMENT  -John
	" WHERE transaction_id>0 AND ( status>=0 OR type='buy' ) $strWhereSTMT ". //	  <--   MY NEW STATEMENT FOR ALL STATUSES  -John
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";


	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		$intOrderID=					$row["order_id"];
		$strOrderCode=					$row["order_code"];

		$strType=						$row["type"];
		$intStatus=						$row["status"]; //0 1 2
		$status_name=					$row["status_msg"]; //0 1 2

		$strCryptoType=					$row["cryptotype"]; //BTC LTC DGE
		$strCryptoAmt=					$row["crypto_amt"];
		$strCryptoRate_usd=				$row["crypto_rate_usd"];

		$intCryptoMiningFee=			$row["crypto_miner_fee"]; //
		$intCryptoTotalOutFlow=			$row["crypto_total_outflow"]; //


		$intFiatRate=					$row["fiat_rate"]; //1=USD, 0.7=USD/EUR
		$strFiatType=					$row["fiat_type"]; //USD EUR
		$strFiatAmt=					$row["fiat_amt"]; //
		$intFiatValue = $strCryptoAmt * $strCryptoRate_usd;

	    $strWalletSentTo= 				$row["walletaddress_sentto"];
	    $strWalletFrom= 				$row["walletaddress_from"];
        if ($strWalletFrom=="") {$strWalletFrom="External wallet address";}
		$strTransactionHash=			$row["hash_transaction"];

		$intUserGive=					$row["user_id"];
		$intUserRecieve=				$row["user_id_sentto"];
		$strLabel=						$row["label"];

		//Prepend a Label to the label  ;-p  -John
		if (!$strLabel) {} else {$strLabel = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>Label:</small> ".$strLabel ;}


		$date_joined= 						$row["datetime_created"];
		$date_joined_formatted = date("Y-m-d H:i", ($date_joined));
		$date_joined_formatted_nice = functNiceTimeDif_int($date_joined);

		$strLinkText="";

		if($intUserGive==$intUserID_viewer){ //send
			//$strCryptoAmt = $intCryptoTotalOutFlow;
			//if(!$intCryptoTotalOutFlow){$strCryptoAmt=$strCryptoAmt;}
			$strCryptoText = "-";
			$strCyptoColor = "#cc0000";
			$strTypeImgSrc = "/img/arrowup.png";
			$strAddressShow = $strWalletSentTo;
			$strLinkText = "From me to <small>".$strAddressShow."</small>";
			//$strLabel = $strLabel
			$strLink = "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}
		if($intUserRecieve==$intUserID_viewer){ //receive
			$strCryptoText = "+";
			$strCyptoColor = "#00cc33";
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLinkText = "Received from <small>".$strAddressShow."</small>";
			$strLink =  "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}
		if($strType=='buy'){ //buy
			$strCryptoText = "+";
			$strCyptoColor = "#996600";
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLink = PAGE_RECEIPT."?c=".$strOrderCode;
			$intFiatValue = "";
			$strLinkModal = "";
			//$strLabel = "";
			//lookup status_name from tbl_statuses
			$query="SELECT * FROM " . TBL_STATUSES . " WHERE status_id = $intStatus ";
			//echo "SQL STMNT = " . $query . "<br>";
/* 			$rs9 = mysqli_query($DB_LINK, $query); $row9=mysqli_fetch_array($rs9) ; */
/* 			$strStatusText=					$row9["status_name"]; */
/* 			$strLinkText = "Bought $".$strFiatAmt." worth of Bitcoin - Confirmation Page <br> Status: $status_name"; */
			$strLinkText = "Purchased Bitcoin - Confirmation Page<br>Status: $status_name";
		}

		if($intRecID){	//Pulling up single transaction details for modal
    ?>
	<div class="row">
		<h4>Transaction ID: <?php $intTransactionID?></h4><br>

        <div class="small-4 columns">
			Crypto Amount: <br>
			Mining Fee: <br>
			Crypto Type: <br>
			Fiat Value: <br>
			Fiat Rate: <br>
			From: <br>
			To: <br>
			Date: <br>
			Transaction Hash: <br>
        </div>
		<div class="small-8 columns">
			<strong style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong><br>
			<?php $intCryptoMiningFee?><br>
			<?php $strCryptoType?><br>
			$<?php money_format('%i', $intFiatValue) ?><br>
			<?php $intFiatRate?><br>
			<?php $strWalletFrom?><br>
			<?php $strWalletSentTo?><br>
			<?php $date_joined_formatted?><br>
			<?php $strTransactionHash?> <br>
        </div>

    </div>

	<?php }else{ // Regular activity/transactions list on wallet.php ?>
	    <tr>
			<td align="left"><strong><?php $intTransactionID?></strong></td>
			<td align="left"><strong><?php $strType?></strong></td>
			<td align="left"><?php $intOrderID?></td>
			<td align="left"><medium><?php $date_joined_formatted?> ET</medium> <small>(<?php $date_joined_formatted_nice?>)</small><br><a href="<?php $strLink?>"<?php $strLinkModal?>><?php $strLinkText?></a><br><?php $strLabel?></td>
<!--			<td align="left"><a href="<?php $strLink?>"<?php $strLinkModal?>><?php $strLinkText?></a><br><?php $strLabel?></td>-->
<!-- 			COMMENTING THIS OUT TO HIDE THE USD VALUE IN THE TRANSACTIONS LIST -John -->
<!-- 			<td align="left"><strong style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong> <br> $<?php money_format('%i', $intFiatValue) ?></td> -->
			<td align="left"><a href="ajax_do.php?do=setstatus1&txid=<?php $intTransactionID?>"><?php $intStatus?></a></td>
			<td align="left"><?php $status_name?></td>
			<td align="left"><span style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?></span></td>
	    </tr>
	<?
		}//end if intRecID
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "TRANSACTIONS"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "TRANSACTIONS_ORDER"
//###################################################################################################################
	case "transactions_order": //list of transactions in wallet.php

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){
		$strWhereSTMT = $strWhereSTMT. " AND ( user_id=$intUserID_viewer OR user_id_sentto=$intUserID_viewer ) ";
	}else{
		//die;  //not logged in then show nothing! SECURITY
	}

	if($intRecID){ $strWhereSTMT =" AND transaction_id=$intRecID " ;} //return single record
	if($sortby=="top"){ $strOrderBySTMT =" crypto_amt DESC " ;}
	if($sortby=="new"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_id>$intNewstID " ; $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}
	$strOrderBySTMT = " datetime_created DESC ";
    //$intLastMSGID = 0;
	//$intMaxRecords = 1000;

    $query="SELECT * ".
	" FROM ".TBL_TRANSACTIONS." ".
	//" WHERE transaction_id>0 AND ( status>0 OR type='buy' ) $strWhereSTMT ".    <--   ORIGINAL STATEMENT  -John
	" WHERE transaction_id>0 AND ( status>=0 OR type='buy' ) AND order_id=$order_id $strWhereSTMT ". //	  <--   MY NEW STATEMENT FOR ALL STATUSES  -John
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";


	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		$order_id=						$row["order_id"];
		$strOrderCode=					$row["order_code"];
		
		$debit		  =					$row["debit"];
		$credit		  =					$row["credit"];
		$balance_prev =					$row["balance_prev"];
		$balance_curr =					$row["balance_curr"];		

		$strType=						$row["type"];
		$intStatus=						$row["status"]; //0 1 2
		$status_name=					$row["status_msg"]; //0 1 2

		$strCryptoType=					$row["cryptotype"]; //BTC LTC DGE
		$strCryptoAmt=					$row["crypto_amt"];
		$strCryptoRate_usd=				$row["crypto_rate_usd"];

		$intCryptoMiningFee=			$row["crypto_miner_fee"]; //
		$intCryptoTotalOutFlow=			$row["crypto_total_outflow"]; //


		$intFiatRate=					$row["fiat_rate"]; //1=USD, 0.7=USD/EUR
		$strFiatType=					$row["fiat_type"]; //USD EUR
		$strFiatAmt=					$row["fiat_amt"]; //
		$intFiatValue = $strCryptoAmt * $strCryptoRate_usd;

	    $strWalletSentTo= 				$row["walletaddress_sentto"];
	    $strWalletFrom= 				$row["walletaddress_from"];
        if ($strWalletFrom=="") {$strWalletFrom="External wallet address";}
		$strTransactionHash=			$row["hash_transaction"];

		$intUserGive=					$row["user_id"];
		$intUserRecieve=				$row["user_id_sentto"];
		$strLabel=						$row["label"];

		//Prepend a Label to the label  ;-p  -John
		if (!$strLabel) {} else {$strLabel = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>Label:</small> ".$strLabel ;}


		$date_joined= 						$row["datetime_created"];
		$date_joined_formatted = date("Y-m-d H:i", ($date_joined));
		$date_joined_formatted_nice = functNiceTimeDif_int($date_joined);

		$strLinkText="";

		if($intUserGive==$intUserID_viewer){ //send
			//$strCryptoAmt = $intCryptoTotalOutFlow;
			//if(!$intCryptoTotalOutFlow){$strCryptoAmt=$strCryptoAmt;}
			$strCryptoText = "-";
			$strCyptoColor = "#cc0000";
			$strTypeImgSrc = "/img/arrowup.png";
			$strAddressShow = $strWalletSentTo;
			$strLinkText = "From me to <small>".$strAddressShow."</small>";
			//$strLabel = $strLabel
			$strLink = "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}
		if($intUserRecieve==$intUserID_viewer){ //receive
			$strCryptoText = "+";
			$strCyptoColor = "#00cc33";
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLinkText = "Received from <small>".$strAddressShow."</small>";
			$strLink =  "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}

		if($strType=='buy'){ //buy
			$strCryptoText = "+";
			$strCyptoColor = "#996600";
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLink = PAGE_RECEIPT."?c=".$strOrderCode;
			$intFiatValue = "";
			$strLinkModal = "";
			//$strLabel = "";

			//lookup status_name from tbl_statuses
			$query="SELECT * FROM " . TBL_STATUSES . " WHERE status_id = $intStatus ";
			//echo "SQL STMNT = " . $query . "<br>";
/* 			$rs9 = mysqli_query($DB_LINK, $query); $row9=mysqli_fetch_array($rs9) ; */
/* 			$strStatusText=					$row9["status_name"]; */

/* 			$strLinkText = "Bought $".$strFiatAmt." worth of Bitcoin - Confirmation Page <br> Status: $status_name"; */
			$strLinkText = "Purchased Bitcoin - Confirmation Page<br>Status: $status_name";
		}


		if($intRecID){	//Pulling up single transaction details for modal
    ?>
	<div class="row">
		<h4>Transaction ID: <?php $intTransactionID?></h4><br>

        <div class="small-4 columns">

			Crypto Amount: <br>
			Mining Fee: <br>
			Crypto Type: <br>
			Fiat Value: <br>
			Fiat Rate: <br>
			From: <br>
			To: <br>
			Date: <br>
			Transaction Hash: <br>
        </div>
		<div class="small-8 columns">
			<strong style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong><br>
			<?php $intCryptoMiningFee?><br>
			<?php $strCryptoType?><br>
			$<?php money_format('%i', $intFiatValue) ?><br>
			<?php $intFiatRate?><br>
			<?php $strWalletFrom?><br>
			<?php $strWalletSentTo?><br>
			<?php $date_joined_formatted?><br>
			<?php $strTransactionHash?> <br>
        </div>

    </div>

	<?php }else{ // Regular activity/transactions list on wallet.php ?>
	    <tr>
			<td align="left"><strong><?php $intTransactionID?></strong></td>
			<td align="left"><medium><?php $date_joined_formatted?> ET</medium> <small>(<?php $date_joined_formatted_nice?>)</small><br><a href="<?php $strLink?>"<?php $strLinkModal?>><?php $strLinkText?></a><br><?php $strLabel?></td>
<!--			<td align="left"><a href="<?php $strLink?>"<?php $strLinkModal?>><?php $strLinkText?></a><br><?php $strLabel?></td>-->
			<td align="left"><?php $credit?></td>
			<td align="left"><?php $balance_prev?></td>
			<td align="left"><?php $balance_curr?></td>

<!-- 			COMMENTING THIS OUT TO HIDE THE USD VALUE IN THE TRANSACTIONS LIST -John -->
<!-- 			<td align="left"><strong style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong> <br> $<?php money_format('%i', $intFiatValue) ?></td> -->
			<td align="left"><a href="ajax_do.php?do=setstatus1&txid=<?php $intTransactionID?>"><?php $intStatus?></a></td>
			<td align="left"><?php $status_name?></td>
			<td align="left"><span style="color:<?php $strCyptoColor?>;"><?php $strCryptoText?><?php rtrim(number_format($strCryptoAmt,8),0)?></span></td>
	    </tr>
	<?
		}//end if intRecID
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "TRANSACTIONS_ORDER"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "ORDERS"
//###################################################################################################################
	case "orders": //orders

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    if($strSearchType=="orderid"){ $strWhereSTMT = $strWhereSTMT. " AND orderid=".$strSearchTXT ;}
    if($strSearchType=="depositamt"){ $strWhereSTMT = $strWhereSTMT. " AND amt_usd= $strSearchTXT " ;}
    if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( from_namelast LIKE'%$strSearchTXT%' OR from_name LIKE'%$strSearchTXT%' ) " ;}
    if($strSearchType=="status"){ $strWhereSTMT = $strWhereSTMT. " AND status_text LIKE'%$strSearchTXT%'" ;}



    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " AND status_id>0 ";}
	if($strFilter=="receipt"){ $strWhereSTMT = $strWhereSTMT. " AND no_receipts_uploaded>0 AND status_id<>8 ";}
	if($strFilter=="openorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id<>8 ";}
	if($strFilter=="filledorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 ";}
	if($strFilter=="filledorderscheck"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 AND type='check' ";}
	if($strFilter=="allchecks"){ $strWhereSTMT = $strWhereSTMT. " AND type='check' AND fiat_deposited>0";}


	if($strFilter=="cash"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' ";}
	if($strFilter=="wire"){ $strWhereSTMT = $strWhereSTMT. " AND type='wire' ";}
	if($strFilter=="inperson"){ $strWhereSTMT = $strWhereSTMT. " AND type='inperson' ";}
	if($strFilter=="bofa"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashbofa' AND fiat_deposited>0";}
	if($strFilter=="capitalone"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashcapital' ";}
	if($strFilter=="chase"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=4 ";}
	if($strFilter=="check"){ $strWhereSTMT = $strWhereSTMT. " AND type='check' ";}


	$strOrderBySTMT = " date DESC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" date DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" date ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" amt_usd DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" amt_usd ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" from_namelast DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" from_namelast ASC " ;}


    $query="SELECT * ".
	" FROM ".TBL_ORDERS." ".
	" WHERE orderid>0 $strWhereSTMT ".
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";


	echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=						$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=					$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=			$row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = number_format($fiat_deposited - $real_fee,2);


	    ?>

		<tr>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a><br><a href="/receipt.php?c=<?php $strOrderCode?>"><?php $strOrderCode?></a></td>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>"><?php $status_text?></a></td>
			<td align="left" align="center"><?php $no_receipts_uploaded?><br>
				<a href="<?php $strReceiptImgSrc_link?>" target="_blank"><img src="<?php $strReceiptImgSrc?>" width="32" height="32" /></a>
			</td>
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br><?php date("Y-m-d H:i", strtotime($date_joinedUploaded))?></td> -->
            <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?></td>
            <td align="left"><?if($intFromUserID){ }?><a href="member_details.php?id=<?php $intFromUserID?>"><?php $intFromUserID?></a> <?php $first_name?> <?php $last_name?><br><?php $strType?></td>
          	<td align="left">Revenue: <?php $revenue?><br>Sold: <?php $crypto_sold?></td>
<!--             <td align="left"><?php $strType?> <?php $strBankName?><br><?php $bank_fee?> tip: <?php $intTipUSD?></td> -->
          	<td align="left"><?php $fiat_to_deposit?></td>
<!--        <td align="left">B or U</td> -->
          	<td align="left">est <?php $crypto_est_at_time_of_order?><br>est <?php $rate_at_time_of_order?></td>
<!--           	<td align="left">curr <?php $BTCcurrentAmt?><br>curr <?php $intCurrentBTCrate?></td> -->
            <!-- The final values are calculated or typed in from the Order Details page -->
<!--             <td align="left">Sold <?php $crypto_sold?><br>at <?php $rate_sold_at?></td> -->
<!--           	<td align="left"><?php $sold_from_account?><br>miner <?php $BTCMinerFee?></td> -->
<!--           	<td align="left"><?php $BTCoutflow?></td> -->
<!--           	<td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br>confs</td> -->
        </tr>
	<?
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "ORDERS"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "CUMBERLAND"
//###################################################################################################################
	case "cumberland":

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    $query="SELECT * FROM ".TBL_ORDERS." WHERE orderid>0 AND exchange='Cumberland' ORDER BY exfill_date DESC";


	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=						$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=					$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=			$row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];
            $exfill_date=					$row["exfill_date"];
            $exfill_btc=					$row["exfill_btc"];
            $rate_bitstamp=					$row["rate_bitstamp"];
            $exfill_cost=					$row["exfill_cost"];
            $exfill_fee=					$row["exfill_fee"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = number_format($fiat_deposited - $real_fee,2);


			$cumberland_offer = $rate_bitstamp * 1.025;
			$cumberland_total = $exfill_cost + $exfill_fee;
			$cumberland_offer = number_format($cumberland_offer,2);

	    ?>

		<tr>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a></td>
			<td align="left"><?php $exfill_date?> ET</td>
			<td align="left"><?php $exfill_btc?></td>
			<td align="left"><?php $rate_bitstamp?></td>
			<td align="left"><?php $cumberland_offer?></td>
			<td align="left"><?php $cumberland_total?></td>
        </tr>
	<?
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "CUMBERLAND"
//###################################################################################################################







//###################################################################################################################
//!CASE $intType "TRANSACTIONQUE"
//###################################################################################################################
	case "transactionque": //waiting send outs

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){ $strWhereSTMT = $strWhereSTMT. " AND user_id=$intUserID_viewer " ;}
	if($intRecID){ $strWhereSTMT =" AND uploadid=$intRecID " ;} //return single record

    $intLastMSGID = 0;
	//$intMaxRecords = 300;

    $query="SELECT * ".
	" FROM ".TBL_TRANSACTIONS_QUE." ".
	" WHERE transaction_id>0 $strWhereSTMT ".
	" ORDER BY date_created DESC LIMIT $intLastMSGID,$intMaxRecords ";
	echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intQueID=						$row["id"];
		if($intRowCount==0){ $intNewstID=$intQueID;}
		$intRowCount=					$intRowCount + 1;
		
		$strTransactionType=			$row["transaction_type"];
		$intTransactionID=				$row["transaction_id"];
		$intTransactionAmount=			$row["transaction_amt"];
		$intStatusID=					$row["status_id"];
		$intUserID = 					$row["user_id"];
		$strDate = 						$row["date_created"];
		
		$strTransaction_txid = 			$row["transaction_txid"];
		$strTransaction_address = 		$row["transaction_address"];
		$strIPAddress = 				$row["ipaddress"];
		$strLocation =					$row["location"];
		
		if(!$strLocation AND $strIPAddress){
		
			//get ip's info - limit to 1000 a day
			$json_data = file_get_contents('http://ipinfo.io/'.$strIPAddress.'/geo');
			$json_feed = json_decode($json_data);
			$strCity = $json_feed->city;
			$strRegion = $json_feed->region;
			$strCountry = $json_feed->country;
			
			//update location
			$strLocation = "$strCity, $strRegion, $strCountry";
			$query="UPDATE " . TBL_TRANSACTIONS_QUE . " SET location='".$strLocation."'".
			" WHERE id=".$intQueID ;
			//echo "SQL STMNT = " . $query .  "<br>";
			mysqli_query($DB_LINK, $query);
		}
		
		
    ?>
	<tr>
		<td align="left">
			<?php if(!$strTransaction_txid){ ?>
			<a href="do.php?do=authorizeque&queid=<?php $intQueID?>">Authorize <?php $intTransactionAmount?> BTC</a>
			<?php } else {echo "$intTransactionAmount";} ?>
		</td>
		<td align="left"><?php $intQueID?> QueID</td>
		<td align="left"><?php $intTransactionID?> <?php $strTransactionType?></td>
		<td align="left">user: <a href="member_details.php?id=<?php $intUserID?>"><?php $intUserID?></a></td>
		<td align="left"><?php $intStatusID?> (<?php $strTransaction_txid?>)<br><?php $strTransaction_address?></td>
		<td align="left"><?php $strDate?><br><?php $strLocation?></td>
    </tr>
	<?

	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "TRANSACTIONQUE"
//###################################################################################################################









//###################################################################################################################
//!CASE $intType "UPLOADS"
//###################################################################################################################
	case "uploads": //orders

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page



	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){ $strWhereSTMT = $strWhereSTMT. " AND usernameid=$intUserID_viewer " ;}
	if($intRecID){ $strWhereSTMT =" AND uploadid=$intRecID " ;} //return single record



	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}

    $intLastMSGID = 0;
	$intMaxRecords = 100;

    $query="SELECT * ".
	" FROM ".TBL_UPLOADS." ".
	" WHERE uploadid>0 $strWhereSTMT ".
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";
	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intOrderID=					$row["orderid"];
		if($intRowCount==0){ $intNewstID=$intOrderID;}
		$intRowCount=					$intRowCount + 1;
		$strOrderCode=					$row["keylink"];
		$strExt=					$row["ext"];
		$strImgPath=PATH_UPLOADS.$strOrderCode.".".$strExt;


    ?>
	    <img src="<?php $strImgPath?>" width="100" height="100" />
	<?

	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "UPLOADS"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "MEMBERS"
//###################################################################################################################
	case "members": //users

		@ $rpp;        	//Records Per Page
	    @ $cps;        	//Current Page Starting row number
	    @ $lps;        	//Last Page Starting row number
	    @ $a;        	//will be used to print the starting row number that is shown in the page
	    @ $b;        	//will be used to print the ending row number that is shown in the page
		$rpp = 			$intMaxRecords;
		$cps = 			$intLastMSGID;
	    $a =			$cps + 1;
		$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
	    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

        if($strSearchType=="userid"){ $strWhereSTMT = $strWhereSTMT. " AND id=".$strSearchTXT ;}
        if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( last_name LIKE'%$strSearchTXT%' OR first_name LIKE'%$strSearchTXT%' ) " ;}

	    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " ";}
		if($strFilter=="withbalance"){ $strWhereSTMT = $strWhereSTMT. " AND balance_btc>0 ";}
		if($strFilter=="withsends"){ $strWhereSTMT = $strWhereSTMT. " AND ( count_externalsends>0 OR count_externalsends>0 ) ";}

		$strOrderBySTMT = " date_joined DESC ";
		if($sortby=="datenew"){ $strOrderBySTMT =" date_joined DESC " ;}
		if($sortby=="dateold"){ $strOrderBySTMT =" date_joined ASC " ;}
		if($sortby=="balancehigh2low"){ $strOrderBySTMT =" balance_btc DESC " ;}
		if($sortby=="balancelow2high"){ $strOrderBySTMT =" balance_btc ASC " ;}
		if($sortby=="namea"){ $strOrderBySTMT =" last_name DESC " ;}
		if($sortby=="namez"){ $strOrderBySTMT =" last_name ASC " ;}

        //echo "sortby= $sortby<br>";
        //$intLastMSGID = 0 ; $intMaxRecords = 1000;

        //local.coincafe/cp/loadcontent.php?do=ajax&type=members&f=all&sort=

        $query="SELECT * ".
		" FROM ".TBL_USERS." ".
		" WHERE id>0 $strWhereSTMT ".
		" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";
		//echo "SQLstmt=$query<br>";

		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
		$query0 = "Select FOUND_ROWS()";
		$rs0 = 	mysqli_query($DB_LINK, $query0) or die(mysqli_error());
		$row0 =	mysqli_fetch_array($rs0);
		$nr0 = 	$row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
		if (($nr0 < $intMaxRecords) || ($nr < $intMaxRecords)){$b = $nr0;}else{$b = ($cps) + $rpp;}
		//if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}


		/* ?>
		<h4>Total Rows Found: <?php $nr0?></h4><br>
		<?php */
		$intRowCount=0;

		//begin loop
		while($row = mysqli_fetch_assoc($rs)){

		    $user_id=						$row["id"];
		    if($intRowCount==0){ $intNewstID=$user_id;} //get very first record id

			$intRowCount=					$intRowCount + 1;

		    $first_name=					$row["first_name"];
		    $last_name= 					$row["last_name"];
		    $email_address= 				$row["email"];
		    $cellphone= 					$row["cellphone"];
		    $verification_id= 				$row["verification_id"];

		    $strImageSrcKYC= PATH_KYC.$user_id.".png";

		    $balance_usd= 					$row["balance"];
		    $strWalletTo= 					$row["btc_address"];
			$date_joined= 					$row["date_joined"];

            //Added by John
            $balance_btc=                   $row["balance_btc"];
            if ($balance_btc==0) {$balance_btc="";}
        ?>
        <tr><!-- SHOW USER_ID, NAME, EMAIL, PHONE, BTC BALANCE, AND DATE JOINED -->
			<td align="left"><a href="member_details.php?id=<?php $user_id?>"><?php $user_id?></a></td>
			<td align="left"><a href="<?php $strImageSrcKYC?>" target="_new"><?php $verification_id?><!--<img src="<?php $strImageSrcKYC?> " width="100" height="100" />--></a></td>
			<td align="left"><a href="member_details.php?id=<?php $user_id?>"><h4><?php $first_name?> <?php $last_name?> </h4></a></td>
			<td align="left"><a href="mailto:<?php $email_address?>;"><?php $email_address?></a> / <?php $cellphone?></td>
			<td align="left"><strong><?php number_format($balance_btc,8) ?></strong></td>
			<td align="left"><?php $date_joined?> <?php $strDo?></td>
        </tr>
		<?php

		}//end while
		//if(!$intNewstID){$intNewstID=0;}
		$intLastMSGID = $user_id;


	break;
//###################################################################################################################
//END CASE $intType "MEMBERS"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "VERIFY_KYC"
//###################################################################################################################
	case "verify_kyc": //users with ID uploaded sorted newest user at top

		@ $rpp;        	//Records Per Page
	    @ $cps;        	//Current Page Starting row number
	    @ $lps;        	//Last Page Starting row number
	    @ $a;        	//will be used to print the starting row number that is shown in the page
	    @ $b;        	//will be used to print the ending row number that is shown in the page
		$rpp = 			$intMaxRecords;
		$cps = 			$intLastMSGID;
	    $a =			$cps + 1;
		$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
	    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

        if($strSearchType=="userid"){ $strWhereSTMT = $strWhereSTMT. " AND id=".$strSearchTXT ;}
        if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( last_name LIKE'%$strSearchTXT%' OR first_name LIKE'%$strSearchTXT%' ) " ;}

	    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " ";}
		if($strFilter=="withbalance"){ $strWhereSTMT = $strWhereSTMT. " AND balance_btc>0 ";}
		if($strFilter=="withsends"){ $strWhereSTMT = $strWhereSTMT. " AND ( count_externalsends>0 OR count_externalsends>0 ) ";}

		$strOrderBySTMT = " date_joined DESC ";
		if($sortby=="datenew"){ $strOrderBySTMT =" date_joined DESC " ;}
		if($sortby=="dateold"){ $strOrderBySTMT =" date_joined ASC " ;}
		if($sortby=="balancehigh2low"){ $strOrderBySTMT =" balance_btc DESC " ;}
		if($sortby=="balancelow2high"){ $strOrderBySTMT =" balance_btc ASC " ;}
		if($sortby=="namea"){ $strOrderBySTMT =" last_name DESC " ;}
		if($sortby=="namez"){ $strOrderBySTMT =" last_name ASC " ;}

        //echo "sortby= $sortby<br>";
        //$intLastMSGID = 0 ; $intMaxRecords = 1000;

        //local.coincafe/cp/loadcontent.php?do=ajax&type=members&f=all&sort=

        $query="SELECT *".
		" FROM ".TBL_USERS.
		" WHERE id>0 AND verification_id>0 and kyc=0 $strWhereSTMT".
		" ORDER BY id DESC LIMIT $intLastMSGID,$intMaxRecords";
		//echo "xxSQLstmt=$query<br>";

		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
		$query0 = "Select FOUND_ROWS()";
		$rs0 = 	mysqli_query($DB_LINK, $query0) or die(mysqli_error());
		$row0 =	mysqli_fetch_array($rs0);
		$nr0 = 	$row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
		if (($nr0 < $intMaxRecords) || ($nr < $intMaxRecords)){$b = $nr0;}else{$b = ($cps) + $rpp;}
		//if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}


		/* ?>
		<h4>Total Rows Found: <?php $nr0?></h4><br>
		<?php */
		$intRowCount=0;

		//begin loop
		while($row = mysqli_fetch_assoc($rs)){

		    $user_id=					$row["id"];
		    if($intRowCount==0){ $intNewstID=$user_id;} //get very first record id

			$intRowCount=					$intRowCount + 1;

		    $first_name=					$row["first_name"];
		    $last_name= 					$row["last_name"];
		    $customer_name = $first_name ." ". $last_name;
		    $email_address= 				$row["email"];
//		    $cellphone= 					$row["cellphone"];
		    $verification_id= 				$row["verification_id"];

		    $strImageSrcKYC= PATH_KYC.$user_id.".jpg";

//		    $balance_usd= 					$row["balance"];
//		    $strWalletTo= 					$row["btc_address"];  NOT USED SO I COMMENTED IT OUT -John
//			$date_joined= 					$row["date_joined"];

            //Added by John
            $balance_btc=                   $row["balance_btc"];
            if ($balance_btc==0) {$balance_btc="";}

        ?>
        <tr><!-- SHOW USER_ID, NAME, ID UPLOADS, VERIFY LINK, EXCEPTION LINK -->
			<td align="left"><a href="member_details.php?id=<?php $user_id?>" target="_blank"><?php $user_id?></a></td>
			<td align="left"><?php $customer_name?></td>
			<td align="left"><?php $email_address?></td>
			<td align="left"><?php


	        $query2="SELECT * ".
			" FROM ".TBL_UPLOADS." ".
			" WHERE usernameid='$user_id' AND frompage='id' ";
			//echo "SQLstmt=$query2<br>";


			$rs2 = mysqli_query($DB_LINK, $query2) or die(mysqli_error());


	 		while($row2 = mysqli_fetch_assoc($rs2)){

		    $keylink=					$row2["keylink"];
			$ext=						$row2["ext"];
			$filename = PATH_UPLOADS.$keylink.".".$ext;

			?>


			<a href="<?php $filename?>" target="_new">ID </a>
			<?php
				} //end while
			?>
			</td>
			<td align="left"><a href="javascript:jsfunct_verifykyc('<?php $user_id?>');">Verify</a></td>
			<td align="left"><a href="#">Exception</a></td>
<!-- 			<td align="left"><?php $date_joined?> <?php $strDo?></td> -->
        </tr>
		<?php

		}//end while
		//if(!$intNewstID){$intNewstID=0;}
		$intLastMSGID = $user_id;


	break;
//###################################################################################################################
//END CASE $intType "VERIFY_KYC"
//###################################################################################################################





//###################################################################################################################
//!CASE $intType "VERIFY_DEPOSITS"
//###################################################################################################################
	case "verify_deposits":

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    if($strSearchType=="orderid"){ $strWhereSTMT = $strWhereSTMT. " AND orderid=".$strSearchTXT ;}
    if($strSearchType=="depositamt"){ $strWhereSTMT = $strWhereSTMT. " AND amt_usd= $strSearchTXT " ;}
    if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( from_namelast LIKE'%$strSearchTXT%' OR from_name LIKE'%$strSearchTXT%' ) " ;}
    if($strSearchType=="status"){ $strWhereSTMT = $strWhereSTMT. " AND status_text LIKE'%$strSearchTXT%'" ;}


    
	if($strFilter<>"all" AND $strFilter){ $strWhereSTMT = $strWhereSTMT. " AND bankid=$strFilter ";}
	if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " ";}


	$strOrderBySTMT = " DAY(fiat_deposit_date) desc, amt_usd ASC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" fiat_deposit_date DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" fiat_deposit_date ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" amt_usd DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" amt_usd ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" from_namelast DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" from_namelast ASC " ;}


    $query="SELECT * FROM tbl_orders INNER JOIN tbl_uploads ON tbl_uploads.orderid = tbl_orders.orderid"
	." WHERE uploadid<>0 AND (status_id=3 OR status_id=14 OR status_id=9 OR status_id=17 OR status_id=27 OR status_id=31) AND tbl_orders.orderid>0 $strWhereSTMT ".
	" GROUP BY tbl_orders.orderid ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";


	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		//if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=						$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=					$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];
	        $fiat_deposit_date=				$row["fiat_deposit_date"];
			$fiat_deposited_user=			$row["fiat_deposited_user"];

	        


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=			$row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = number_format($fiat_deposited - $real_fee,2);






	    ?>

		<tr>
			<!-- 			
			-->
			<td align="left"><?php $fiat_deposit_date_display?></td>
			<td align="left"><?php $status_text?></td>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a><br><a href="/receipt.php?c=<?php $strOrderCode?>"><?php $strOrderCode?></a></td>
			<td align="left"><?php $strType?></td>
			<td align="left" align="center">

				<?php
		        $query2="SELECT * FROM ".TBL_UPLOADS." WHERE orderid=$intOrderID ORDER BY date_added DESC " ;
				//echo "SQLstmt=$query2<br>";
				$rs2 = mysqli_query($DB_LINK, $query2) or die(mysqli_error());
				$nr2 = 	mysqli_num_rows($rs2); //Number of rows found with LIMIT in action

				while($row = mysqli_fetch_assoc($rs2)){

				    $intUploadID=					$row["uploadid"];
					$strExt=						$row["ext"];
					$strKeyLink=					$row["keylink"];
					$strFilePath= PATH_UPLOADS.$strKeyLink.".".$strExt ;
					$strFileSrc= $strFilePath ;
					if(file_exists(__ROOT__.$strFilePath)){
						//echo "exists" ;
						if($strExt=="pdf"){$strFileSrc = "/img/files/pdf.png";}
					}else{ $strFilePath="#"; $strFileSrc="/img/x_red.png";}
		        ?>
				<a href="<?php $strFilePath?>" target="_new"><img src="<?php $strFileSrc?>" width="50" height="50" /></a>
				<?php

				}//end while
				
				
				//If the date is null, then display blank instead of 1969-12-31
				$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date));
				if($fiat_deposit_date==""){$fiat_deposit_date_display="no deposit date";} else {$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date_display));}
				?>

			</td>
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br><?php date("Y-m-d H:i", strtotime($date_joinedUploaded))?></td> -->
          	
          	<td align="left"><?php $fiat_to_deposit?></td>
            <td align="left">$<?php $fiat_deposited_user?></td>
            
            
            <td align="left"><?if($intFromUserID){ }?><a href="member_details.php?id=<?php $intFromUserID?>"><?php $intFromUserID?></a> <?php $first_name?> <?php $last_name?></td>
<!--           	<td align="left">Revenue: <?php $revenue?><br>Sold: <?php $crypto_sold?></td> -->
<!--             <td align="left"><?php $strType?> <?php $strBankName?><br><?php $bank_fee?> tip: <?php $intTipUSD?></td> -->
<!--        <td align="left">B or U</td> -->
          	<td align="left">est <?php $crypto_est_at_time_of_order?><br>est <?php $rate_at_time_of_order?></td>
<!--           	<td align="left">curr <?php $BTCcurrentAmt?><br>curr <?php $intCurrentBTCrate?></td> -->
            <!-- The final values are calculated or typed in from the Order Details page -->
<!--             <td align="left">Sold <?php $crypto_sold?><br>at <?php $rate_sold_at?></td> -->
<!--           	<td align="left"><?php $sold_from_account?><br>miner <?php $BTCMinerFee?></td> -->
<!--           	<td align="left"><?php $BTCoutflow?></td> -->
<!--           	<td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br>confs</td> -->
        </tr>
	<?
	}// while loop end

	break;

//###################################################################################################################
//END CASE $intType "VERIFY_DEPOSITS"
//###################################################################################################################









//###################################################################################################################
//!CASE $intType "VERIFY_DEPOSITS_ORIGINAL"
//###################################################################################################################
	case "verify_deposits_original":

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    if($strSearchType=="orderid"){ $strWhereSTMT = $strWhereSTMT. " AND orderid=".$strSearchTXT ;}
    if($strSearchType=="depositamt"){ $strWhereSTMT = $strWhereSTMT. " AND amt_usd= $strSearchTXT " ;}
    if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( from_namelast LIKE'%$strSearchTXT%' OR from_name LIKE'%$strSearchTXT%' ) " ;}
    if($strSearchType=="status"){ $strWhereSTMT = $strWhereSTMT. " AND status_text LIKE'%$strSearchTXT%'" ;}


    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " AND status_id>0 ";}
	if($strFilter=="receipt"){ $strWhereSTMT = $strWhereSTMT. " AND no_receipts_uploaded>0 AND status_id<>8 ";}
	if($strFilter=="openorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id<>8 ";}
	if($strFilter=="filledorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 ";}
	if($strFilter=="filledorderscheck"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 AND type='check' ";}
	if($strFilter=="allchecks"){ $strWhereSTMT = $strWhereSTMT. " AND type='check'";}


	if($strFilter=="cash"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' ";}
	if($strFilter=="wire"){ $strWhereSTMT = $strWhereSTMT. " AND type='wire' ";}
	if($strFilter=="inperson"){ $strWhereSTMT = $strWhereSTMT. " AND type='inperson' ";}
	if($strFilter=="bofa"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashbofa' AND fiat_deposited>0";}
	if($strFilter=="capitalone"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashcapital' ";}
	if($strFilter=="chase"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=4 ";}
	if($strFilter=="check"){ $strWhereSTMT = $strWhereSTMT. " AND type='check' ";}


	$strOrderBySTMT = " date DESC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" date DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" date ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" amt_usd DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" amt_usd ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" from_namelast DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" from_namelast ASC " ;}


    $query="SELECT * FROM tbl_orders INNER JOIN tbl_uploads ON tbl_uploads.orderid = tbl_orders.orderid"
	." WHERE uploadid<>0 AND (status_id=3 OR status_id=14 OR status_id=9 OR status_id=17 OR status_id=27) AND tbl_orders.orderid>0 $strWhereSTMT ".
	" GROUP BY tbl_orders.orderid ORDER BY tbl_orders.fiat_deposit_date desc, tbl_orders.type desc LIMIT $intLastMSGID,$intMaxRecords ";


	echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		//if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=						$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=					$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];
	        $fiat_deposit_date=				$row["fiat_deposit_date"];


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=			$row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = number_format($fiat_deposited - $real_fee,2);






	    ?>

		<tr>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a><br><a href="/receipt.php?c=<?php $strOrderCode?>"><?php $strOrderCode?></a></td>
			<td align="left"><?php $status_text?></td>
			<td align="left" align="center">

				<?php
		        $query2="SELECT * FROM ".TBL_UPLOADS." WHERE orderid=$intOrderID ORDER BY date_added DESC " ;
				//echo "SQLstmt=$query2<br>";
				$rs2 = mysqli_query($DB_LINK, $query2) or die(mysqli_error());
				$nr2 = 	mysqli_num_rows($rs2); //Number of rows found with LIMIT in action

				while($row = mysqli_fetch_assoc($rs2)){

				    $intUploadID=					$row["uploadid"];
					$strExt=						$row["ext"];
					$strKeyLink=					$row["keylink"];
					$strFilePath= PATH_UPLOADS.$strKeyLink.".".$strExt ;
					$strFileSrc= $strFilePath ;
					if(file_exists(__ROOT__.$strFilePath)){
						//echo "exists" ;
						if($strExt=="pdf"){$strFileSrc = "/img/files/pdf.png";}
					}else{ $strFilePath="#"; $strFileSrc="/img/x_red.png";}
		        ?>
				<a href="<?php $strFilePath?>" target="_new"><img src="<?php $strFileSrc?>" width="50" height="50" /></a>
				<?php

				}//end while
				
				
				//If the date is null, then display blank instead of 1969-12-31
				$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date));
				if($fiat_deposit_date==""){$fiat_deposit_date_display="no deposit date";} else {$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date_display));}
				?>

			</td>
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br><?php date("Y-m-d H:i", strtotime($date_joinedUploaded))?></td> -->
          	<td align="left"><?php $strType?></td>
          	<td align="left"><?php $fiat_to_deposit?></td>
            <td align="left">$<?php $fiat_deposited?><br><?php $fiat_deposit_date_display?></td>
            <td align="left"><?if($intFromUserID){ }?><a href="member_details.php?id=<?php $intFromUserID?>"><?php $intFromUserID?></a> <?php $first_name?> <?php $last_name?></td>
<!--           	<td align="left">Revenue: <?php $revenue?><br>Sold: <?php $crypto_sold?></td> -->
<!--             <td align="left"><?php $strType?> <?php $strBankName?><br><?php $bank_fee?> tip: <?php $intTipUSD?></td> -->
<!--        <td align="left">B or U</td> -->
          	<td align="left">est <?php $crypto_est_at_time_of_order?><br>est <?php $rate_at_time_of_order?></td>
<!--           	<td align="left">curr <?php $BTCcurrentAmt?><br>curr <?php $intCurrentBTCrate?></td> -->
            <!-- The final values are calculated or typed in from the Order Details page -->
<!--             <td align="left">Sold <?php $crypto_sold?><br>at <?php $rate_sold_at?></td> -->
<!--           	<td align="left"><?php $sold_from_account?><br>miner <?php $BTCMinerFee?></td> -->
<!--           	<td align="left"><?php $BTCoutflow?></td> -->
<!--           	<td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br>confs</td> -->
        </tr>
	<?
	}// while loop end

	break;

//###################################################################################################################
//END CASE $intType "VERIFY_DEPOSITS_ORIGINAL"
//###################################################################################################################










//###################################################################################################################
//!CASE $intType "VERIFY_RECEIPTS"
//###################################################################################################################
	case "verify_receipts":

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=1;} //limit records to 30
	
	if($orderid){ $strSQLWhere = " tbl_orders.orderid= $orderid "; }else{  $strSQLWhere = " tbl_orders.fiat_deposited<1 " ; }

    $query="SELECT * FROM ".TBL_ORDERS." INNER JOIN tbl_uploads ON tbl_uploads.orderid = tbl_orders.orderid ".
	" WHERE $strSQLWhere AND tbl_orders.status_id<>20".
	" GROUP BY tbl_orders.orderid ORDER BY tbl_uploads.date_added DESC LIMIT 0,1 ";

	echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intOrderID=					$row["orderid"];
	    $strOrderCode=					$row["ordercode"];
		$strType=						$row["type"];
		$status_id=						$row["status"];
		$status_type=					$row["status_text"];
		
		$fiat_deposited=				$row["fiat_deposited"];
		
		//show form
		?>
		
		<div class="row">
		
			<div class="medium-9 small-12 columns">
				All Pictures for ORDERID: <?php $intOrderID?><br>
				<?
				
				//list all uploads 
		        $query2="SELECT * FROM ".TBL_UPLOADS." WHERE orderid=$intOrderID ORDER BY date_added DESC " ;
				//echo "SQLstmt=$query2<br>";
				$rs2 = mysqli_query($DB_LINK, $query2) or die(mysqli_error());
				$nr2 = 	mysqli_num_rows($rs2); //Number of rows found with LIMIT in action
		
				while($row = mysqli_fetch_assoc($rs2)){
		
				    $intUploadID=					$row["uploadid"];
					$strExt=						$row["ext"];
					$strKeyLink=					$row["keylink"];
					$strFilePath= PATH_UPLOADS.$strKeyLink.".".$strExt ;
					$strFileSrc= $strFilePath ;
					if(file_exists(__ROOT__.$strFilePath)){
						//echo "exists" ;
						if($strExt=="pdf"){$strFileSrc = "/img/files/pdf.png";}
						
					}else{ 
						$strFilePath="#"; $strFileSrc="/img/x_red.png";
					}
		        ?>
				<a href="<?php $strFilePath?>" target="_new"><img src="<?php $strFileSrc?>" width="" height="" /></a>
				<?php
				}
				?>

			</div>
			
			
		
			<div class="medium-3 small-12 columns">
				
				<div class="panel radius">
					<form data-abide name="checkout" id="checkout" method="post" action="?do=addstatus&id=<?php $orderid?>">
					<h3>Add Status</h3>
	
						<input name="amount" id="amount" type="text" value="<?php $fiat_deposited?>" placeholder="Amount Deposited">
														
						<input name="orderid" type="hidden" value="<?php $intOrderID?>">

					    
		                <select name="statustype">
		                <option value="">Select Status</option>

				        <?php
		
				        $query="SELECT * ".
						" FROM ".TBL_STATUSES." ".
						" WHERE status_type='Order' or status_type='Verification'".
						" ORDER BY status_type ASC, status_name ASC" ;
						echo "SQLstmt=$query<br>";
						$rs3 = mysqli_query($DB_LINK, $query);
						$nr3 = 	mysqli_num_rows($rs3); //Number of rows found with LIMIT in action
						//begin loop
						while($row3 = mysqli_fetch_assoc($rs3)){
							$status_id=							$row3["status_id"];
							$status_type=						$row3["status_type"];
						    $status_name=						$row3["status_name"];
				        ?>
		<!-- 						<input type="radio" name="statustype" value="<?php $status_id?>" id="<?php $status_id?>" required><label for="<?php $status_id?>"><?php $status_name?></label><br> -->
						
						
						<option value="<?php echo $status_id ?>"><?php echo $status_type." - ".$status_name ?></option>
		<!-- 						<option value="<?php echo $status_id ?>"<?php if($status_id) { echo " selected " ;} ?>><?php echo $status_name ?></option> -->
		
						
						
						<?php
		
						}//end while
		
						?>
		                </select>

					<br>
					<div class="row">
					    <div class="small-12 columns">
					      <label>Notes</label>
					      <textarea name="notes" placeholder="notes"></textarea>
					    </div>
					</div>
		
					<div class="row">
						<div class="small-12 columns">
							<button type="submit" class="tiny">Add Status </button>
						</div>
					</div>
					<strong style="color:#FFF;"><?php $strError?></strong>
				</form>
				</div>
			</div>
		
		</div>
		
		
		
	<?

	}// while loop end

	break;

//###################################################################################################################
//END CASE $intType "VERIFY_RECEIPTS"
//###################################################################################################################













//###################################################################################################################
//!CASE $intType "ORDERS_EXFILLED"
//###################################################################################################################
	case "orders_exfilled": //orders

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    if($strSearchType=="orderid"){ $strWhereSTMT = $strWhereSTMT. " AND orderid=".$strSearchTXT ;}
    if($strSearchType=="depositamt"){ $strWhereSTMT = $strWhereSTMT. " AND amt_usd= $strSearchTXT " ;}
    if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( from_namelast LIKE'%$strSearchTXT%' OR from_name LIKE'%$strSearchTXT%' ) " ;}
    if($strSearchType=="status"){ $strWhereSTMT = $strWhereSTMT. " AND status_text LIKE'%$strSearchTXT%'" ;}



    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " AND status_id>0 ";}
	if($strFilter=="receipt"){ $strWhereSTMT = $strWhereSTMT. " AND no_receipts_uploaded>0 AND status_id<>8 ";}
	if($strFilter=="openorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id<>8 ";}
	if($strFilter=="filledorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 ";}


	if($strFilter=="cash"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' ";}
	if($strFilter=="wire"){ $strWhereSTMT = $strWhereSTMT. " AND type='wire' ";}
	if($strFilter=="inperson"){ $strWhereSTMT = $strWhereSTMT. " AND type='inperson' ";}
	if($strFilter=="bofa"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=3 ";}
	if($strFilter=="capitalone"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=2 ";}
	if($strFilter=="chase"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=4 ";}


	$strOrderBySTMT = " date DESC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" date DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" date ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" amt_usd DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" amt_usd ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" from_namelast DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" from_namelast ASC " ;}


    $query="SELECT * ".
	" FROM ".TBL_ORDERS." ".
	" WHERE orderid>0 AND total_btc>0 $strWhereSTMT ".
	" ORDER BY orderid DESC LIMIT $intLastMSGID,$intMaxRecords ";
/* 	" ORDER BY exfill_date DESC, orderid DESC LIMIT $intLastMSGID,$intMaxRecords "; */


	//echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=	$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=					$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details
            $exfill_btc=					$row["exfill_btc"];
            $exfill_date=					$row["exfill_date"];
            $exchange=						$row["exchange"];
            $exfill_cost=					$row["exfill_cost"];
            $exfill_fee=					$row["exfill_fee"];

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=            $row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = $fiat_deposited - $real_fee;
			
			//Calculate current estimate of BTC to fill
			$actual_our_fee_usd = number_format($fiat_deposited * CC_FEE,2);
			$actual_fiat_to_convert = $fiat_deposited - $bank_fee - $actual_our_fee_usd;

			//Calculate profit
			$profit_usd = $revenue - $exfill_cost - $exfill_fee;
			$profit_btc = $exfill_btc - $crypto_sold;
			$profit_margin_usd = number_format($profit_usd / $revenue * 100,2);
			$profit_margin_btc = number_format($profit_btc / $crypto_sold * 100,2);
			if($profit_margin_btc!="0") {$displayprofit="($profit_margin_btc%)";}
			//echo "$profit_usd ($profit_margin_usd%)<br>$profit_btc $displayprofit";



            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            $revenue=		number_format($revenue,2);



	    ?>

		<tr>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>"><?php $status_text?></a></td>
<!--
			<td align="left" align="center"><?php $no_receipts_uploaded?><br>
				<a href="<?php $strReceiptImgSrc_link?>" target="_blank"><img src="<?php $strReceiptImgSrc?>" width="32" height="32" /></a>
			</td>
-->
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br><?php date("Y-m-d H:i", strtotime($date_joinedUploaded))?></td> -->
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?></td> -->
<!--             <td align="left"><?if($intFromUserID){ }?><?php $intFromUserID?> <?php $first_name?> <?php $last_name?><br><a href="member_details.php?id=<?php $intFromUserID?>"><?php $strType?></a></td> -->
<!--           	<td align="left">BTC <?php $crypto_est_at_time_of_order?></td> -->
          	<td align="left"><?php $crypto_est_at_time_of_order?><br>$ <?php $fiat_to_deposit?></td>
          	<td align="left"><?if($intFromUserID){ }?><?php $intFromUserID?> <a href="member_details.php?id=<?php $intFromUserID?>"><?php $first_name?></a> <?php $last_name?></td>
<!--           	<td align="left">Conv: <?php $actual_fiat_to_convert?></td> -->
			<td align="left"><?php $strType?></td>
			<td align="left">Order <a href="orders_details.php?id=<?php $intOrderID?>"><?php $intOrderID?></a></td>
          	<td align="left"><?php $exfill_date?></td>
          	<td align="left"><?php $exchange?></td>
          	<td align="left"><?php $exfill_btc?></td>
          	<td align="left"><?php $profit_usd?> (<?php $profit_margin_usd?>%)</td>
          	<td align="left"><?php $profit_btc?> <?php $displayprofit?></td>
<!--             <td align="left"><?php $strType?> <?php $strBankName?><br><?php $bank_fee?> tip: <?php $intTipUSD?></td> -->
<!--           	<td align="left">Dep: <?php $fiat_to_deposit?><br>Conv: <?php $intUSDtoConvert?></td> -->
<!--        <td align="left">B or U</td> -->
<!--           	<td align="left">est <?php $crypto_est_at_time_of_order?><br>est <?php $rate_at_time_of_order?></td> -->
<!--           	<td align="left">curr <?php $BTCcurrentAmt?><br>curr <?php $intCurrentBTCrate?></td> -->
            <!-- The final values are calculated or typed in from the Order Details page -->
<!--             <td align="left">Sold <?php $crypto_sold?><br>at <?php $rate_sold_at?></td> -->
<!--           	<td align="left"><?php $sold_from_account?><br>miner <?php $BTCMinerFee?></td> -->
<!--           	<td align="left"><?php $BTCoutflow?></td> -->
<!--           	<td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br>confs</td> -->
        </tr>
	<?
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "ORDERS_EXFILLED"
//###################################################################################################################







//###################################################################################################################
//!CASE $intType "ORDERSDEPOSITED"
//###################################################################################################################
	case "ordersdeposited":

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords;
	$cps = 			$intLastMSGID;
    $a =			$cps + 1;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30


    if($strSearchType=="orderid"){ $strWhereSTMT = $strWhereSTMT. " AND orderid=".$strSearchTXT ;}
    if($strSearchType=="depositamt"){ $strWhereSTMT = $strWhereSTMT. " AND amt_usd= $strSearchTXT " ;}
    if($strSearchType=="name"){ $strWhereSTMT = $strWhereSTMT. " AND ( from_namelast LIKE'%$strSearchTXT%' OR from_name LIKE'%$strSearchTXT%' ) " ;}
    if($strSearchType=="status"){ $strWhereSTMT = $strWhereSTMT. " AND status_text LIKE'%$strSearchTXT%'" ;}



    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " AND status_id>0 ";}
	if($strFilter=="receipt"){ $strWhereSTMT = $strWhereSTMT. " AND no_receipts_uploaded>0 AND status_id<>8 ";}
	if($strFilter=="openorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id<>8 ";}
	if($strFilter=="filledorders"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 ";}
	if($strFilter=="filledorderscheck"){ $strWhereSTMT = $strWhereSTMT. " AND status_id=8 AND type='check' ";}
	if($strFilter=="allchecks"){ $strWhereSTMT = $strWhereSTMT. " AND type='check' AND fiat_deposited>0";}


	if($strFilter=="cash"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' ";}
	if($strFilter=="wire"){ $strWhereSTMT = $strWhereSTMT. " AND type='wire' ";}
	if($strFilter=="inperson"){ $strWhereSTMT = $strWhereSTMT. " AND type='inperson' ";}
	if($strFilter=="bofa"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashbofa' AND fiat_deposited>0";}
	if($strFilter=="capitalone"){ $strWhereSTMT = $strWhereSTMT. " AND type='cashcapital' ";}
	if($strFilter=="chase"){ $strWhereSTMT = $strWhereSTMT. " AND type='cash' AND bankid=4 ";}
	if($strFilter=="check"){ $strWhereSTMT = $strWhereSTMT. " AND type='check' ";}


	$strOrderBySTMT = " date DESC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" date DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" date ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" amt_usd DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" amt_usd ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" from_namelast DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" from_namelast ASC " ;}


    $query="SELECT * ".
	" FROM ".TBL_ORDERS." ".
	" WHERE orderid>0 AND fiat_deposited>0 $strWhereSTMT ".
	" ORDER BY type DESC, date DESC LIMIT $intLastMSGID,$intMaxRecords ";


	echo "SQLstmt=$query<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action
	$query0 = "Select FOUND_ROWS()";
    $rs0 = mysqli_query($DB_LINK, $query0) or die(mysqli_error());
    $row0 =	mysqli_fetch_array($rs0);
    $nr0 = $row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
    if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
	$intRowCount=0;
	while($row = mysqli_fetch_assoc($rs)){

	    $intTransactionID=				$row["transaction_id"];
		if($intRowCount==0){ $intNewstID=$intTransactionID;} //get very first record id
		$intRowCount=					$intRowCount + 1;

		    $intOrderID=					$row["orderid"];
			//$strOrderID = BLOAT_ORDERID + $intOrderID;
		    $strOrderCode=					$row["ordercode"];
			$strType=						$row["type"];

			$strStatus=						$row["status"];
			$status_text=					$row["status_text"];
			/*
			if(!$strStatus){$strStatus="";$strStatusImgsrc="/img/bg_white_50.png";}
			if($strStatus==2){$strStatus="Coin Sent";$strStatusImgsrc="/img/anim_dot_green.png";} //finished coin sent
			if($strStatus==1){$strStatus="waiting..";$strStatusImgsrc="/img/anim_dot_orange.gif";} //receipt uploaded. waiting
			if($strStatus==9){$strStatus="cancelled";$strStatusImgsrc="/img/anim_dot_red.png";} //cancelled
			*/

			$intFromUserID= 				$row["from_id"];

		    $first_name=					$row["from_name"];
		    $last_name= 					$row["from_namelast"];
		    $email_address= 				$row["from_email"];
		    $cellphone= 					$row["from_phone"];
		    $strMessage= 					$row["from_message"];
		    $fiat_to_deposit=             	$row["amt_usd"];
			$crypto_est_at_time_of_order=						$row["amt_btc"];
		    $intTipUSD= 					$row["tip_usd"];
		    $intOurFeeUSD= 					$row["our_fee_usd"];
		    $intOurFeeBTC= 					$row["our_fee_btc"];
		    $intOurFeePER= 					$row["our_fee_percent"];
		    $bank_fee=						$row["bank_fee"];
			//$intUSDtoConvert=        		$row["total_usd"];
            $intUSDtoConvert = $fiat_to_deposit - $bank_fee - $intTipUSD;

	        $fiat_deposited=                $row["fiat_deposited"];


			$BTCMinerFee=           		$row["btc_miner_fee"];
			$rate_at_time_of_order=			$row["btc_rate"];
            //$BTCsold=                       $row["btc_sold];  <-- add this in once the logic is complete in order details

			$intBankID= 					$row["bankid"];
		    $strBankName= 					$row["bank_name"];
		    $strWalletTo= 					$row["hash_to"];

			$date_joined= 					$row["date"];
            $date_joinedUploaded=			$row["date_uploaded"];

            $no_receipts_uploaded=			$row["no_receipts_uploaded"];
            $last_receipt_upload_time=      $row["last_receipt_upload_time"];
            $rate_at_time_of_last_receipt_upload=   $row["rate_at_time_of_last_receipt_upload"];

			//if($no_receipts_uploaded){ //can either be png lgeacy or extension of file uploaded, or pdf

				$strReceiptImgSrc= "/img/bg_white_50.png";
				$strReceiptImg= PATH_RECEIPTS.$intFromUserID.".";
				//echo __ROOT__.$strReceiptImg."png"." <br>";
				if(file_exists(__ROOT__.$strReceiptImg."png")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."png" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."jpeg")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."jpeg" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."gif")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc = $strReceiptImg."gif" ; $strReceiptImgSrc_link = $strReceiptImgSrc ;}
				if(file_exists(__ROOT__.$strReceiptImg."pdf")){ echo __ROOT__.$strReceiptImg."png"." exists!<br>"; $strReceiptImgSrc_link = $strReceiptImg."pdf" ; $strReceiptImgSrc = "/img/files/pdf.png" ;}

			//}else{
			//	$strReceiptImgSrc= "/img/bg_white_50.png";
			//}


            //These two variables will come into play when the new Orders table with new column names is created
            //$rate_sold_at=                  $row["rate_sold_at"];
            //$crypto_sold=                   $row["crypto_sold"];
            //In the meantime, this will be used:
            //NEED TO CHECK ACCURACY OF THESE 2 VARIABLES BELOW!!!!
            $rate_sold_at=                  $row["btc_rate_final"];
            $crypto_sold=                   $row["total_btc"];
            $sold_from_account=             $row["sold_from_account"];
            $crypto_miner_fee=              $row["crypto_miner_fee"];
            $crypto_outflow=                $row["crypto_outflow"];



            //If the current rate is higher than the estimated rate, use the current rate
            if ($rate_at_time_of_order > $intCurrentBTCrate)
                {
                $BTCsoldRate = $rate_at_time_of_order;
                }
                else
                {
                $BTCsoldRate = $intCurrentBTCrate;
                }


            //Format BTC to 8 decimal places and USD to 2 decimal places
            //$BTCsold =              number_format($intUSDtoConvert / $BTCsoldRate,8,'.','');
            $BTCsold =              number_format($BTCsold,8,'.','');
            $BTCoutflow =            number_format($BTCsold + $BTCMinerFee,8,'.','');
            $BTCcurrentAmt =     number_format($intUSDtoConvert / $intCurrentBTCrate,8,'.','');
			$crypto_est_at_time_of_order =             number_format($crypto_est_at_time_of_order,8,'.','');
            $BTCMinerFee =          number_format($BTCMinerFee,8,'.','');
			$rate_at_time_of_order =         number_format($rate_at_time_of_order,2);
            $bank_fee =           number_format($bank_fee,2);
            $intUSDtoConvert =      number_format($intUSDtoConvert,2);
		    $intTipUSD =              number_format($intTipUSD,2);
            $BTCsoldRate =              number_format($BTCsoldRate,2);
		    $fiat_to_deposit =    number_format($fiat_to_deposit,2);
            $intCurrentBTCrate =    number_format($intCurrentBTCrate,2);
            
            
            //Calculate revenue
			if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
			$revenue = number_format($fiat_deposited - $real_fee,2);


	    ?>

		<tr>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a><br><a href="/receipt.php?c=<?php $strOrderCode?>"><?php $strOrderCode?></a></td>
			<td align="left"><a href="orders_details.php?id=<?php $intOrderID?>"><?php $status_text?></a></td>
			<td align="left" align="center"><?php $no_receipts_uploaded?><br>
				<a href="<?php $strReceiptImgSrc_link?>" target="_blank"><img src="<?php $strReceiptImgSrc?>" width="32" height="32" /></a>
			</td>
<!--             <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br><?php date("Y-m-d H:i", strtotime($date_joinedUploaded))?></td> -->
            <td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?></td>
            <td align="left"><?if($intFromUserID){ }?><a href="member_details.php?id=<?php $intFromUserID?>" target="_blank"><?php $intFromUserID?></a> <?php $first_name?> <?php $last_name?></td>
            <td align="left"><?php $strType?><br><a href="orders_details.php?id=<?php $intOrderID?>" target="_blank"><?php $intOrderID?></a></td>
<!--           	<td align="left">Revenue: <?php $revenue?><br>Sold: <?php $crypto_sold?></td> -->
<!--             <td align="left"><?php $strType?> <?php $strBankName?><br><?php $bank_fee?> tip: <?php $intTipUSD?></td> -->
<!--           	<td align="left">Dep: <?php $fiat_to_deposit?><br>Conv: <?php $intUSDtoConvert?></td> -->
<!--        <td align="left">B or U</td> -->
          	<td align="left">Dep: <?php $fiat_deposited?></td>
          	<td align="left"><?php $fiat_to_deposit?></td>
<!--           	<td align="left">curr <?php $BTCcurrentAmt?><br>curr <?php $intCurrentBTCrate?></td> -->
            <!-- The final values are calculated or typed in from the Order Details page -->
<!--             <td align="left">Sold <?php $crypto_sold?><br>at <?php $rate_sold_at?></td> -->
<!--           	<td align="left"><?php $sold_from_account?><br>miner <?php $BTCMinerFee?></td> -->
<!--           	<td align="left"><?php $BTCoutflow?></td> -->
<!--           	<td align="left"><?php date("Y-m-d H:i", strtotime($date_joined))?><br>confs</td> -->
        </tr>
	<?
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "ORDERSDEPOSITED"
//###################################################################################################################








//###################################################################################################################
//END SWITCH on $intType
//###################################################################################################################
} //switch end
?>

<script>
    $(document).ready(function(){

		<?if($strDo!="iframe"){ ?>
			<?php if($intNewstID){ ?>intNewestID = <?php $intNewstID?>;<?php } ?>
			<?php if($intLastMSGID){ ?>intLastRecord=<?php $intLastMSGID?>;<?php } ?>
			<?php if($b){ ?>intLastRecord=<?php $b?>; //alert('lastrecordid='+intLastRecord); <?php } ?>
			<?php if($nr0){ //set total records txt  ?>intTotalRecords= <?php $nr0?> ; //alert('totalrecords= ' + intTotalRecords );<?php } ?>
			<?php if($nr AND $intLastMSGID){ //set records shown txt  ?>intTotalRecordsShowing= intTotalRecordsShowing + <?php $nr?> ; //alert('totalrecords showing= ' + intTotalRecordsShowing );<?php } ?>
		<?php } ?>

	    <?if($strDo=="include"){ ?>
	    	$("#totalrecords").html('<?php $nr0?>');
	    <?php } ?>

    });
    </script>