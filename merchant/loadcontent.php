<?php
//error_reporting(1); // Turn off all error reporting
error_reporting(E_ERROR | E_PARSE); ini_set('display_errors',1);
ob_start(); //so we can redirect even after headers are sent

//get variables
if(!$strDo){
	$strDo = trim($_GET['do']);
}
//echo "sfgsd";
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

	if($strDo=="iframe"){ 
		//add css and js files needed for page to display
	?>

	<? }

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
//!BEGIN CASE $intType "TRANSACTIONS"
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

	//search
    if($strSearchType=="txid"){ $strWhereSTMT = $strWhereSTMT. " AND txid='".$strSearchTXT."'" ;}
    if($strSearchType=="userid"){ $strWhereSTMT = $strWhereSTMT. " AND user_id= '$strSearchTXT' " ;}
	if($strSearchType=="amount"){ $strWhereSTMT = $strWhereSTMT. " AND crypto_amount= $strSearchTXT " ;}
	if($strSearchType=="address"){ $strWhereSTMT = $strWhereSTMT. " AND ( address_to= '$strSearchTXT' OR address_from= '$strSearchTXT' ) " ;}
    if($strSearchType=="username"){ $strWhereSTMT = $strWhereSTMT. " AND ( label2 LIKE'%$strSearchTXT%' ) " ;}

	//filter
    if($strFilter=="all"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_id>0 ";}
	if($strFilter=="sends"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_type=='send' ";}
	if($strFilter=="receives"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_type=='get' ";}
	
	//sort 
	//echo "sortby: $sortby <br>";
	$strOrderBySTMT = " date_created DESC ";
	if($sortby=="datenew"){ $strOrderBySTMT =" date_created DESC " ;}
	if($sortby=="dateold"){ $strOrderBySTMT =" date_created ASC " ;}
	if($sortby=="amthigh2low"){ $strOrderBySTMT =" crypto_amount DESC " ;}
	if($sortby=="amtlow2high"){ $strOrderBySTMT =" crypto_amount ASC " ;}
	if($sortby=="namea"){ $strOrderBySTMT =" label2 DESC " ;}
	if($sortby=="namez"){ $strOrderBySTMT =" label2 ASC " ;}


	if($intUserID_viewer){
		$strWhereSTMT = $strWhereSTMT. " AND user_id=$intUserID_viewer ";
	}else{
		//die;  //not logged in then show nothing! SECURITY
	}

	if($intRecID){ $strWhereSTMT =" AND transaction_id=$intRecID " ;} //return single record


    $query="SELECT * ".
	" FROM ".TBL_TRANSACTIONS." ".
	//" WHERE transaction_id>0 AND ( status>0 OR type='buy' ) $strWhereSTMT ".    <--   ORIGINAL STATEMENT  -John
	" WHERE transaction_id>0 $strWhereSTMT ". //	  <--   MY NEW STATEMENT FOR ALL STATUSES  -John
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords ";

	echo "SQLstmt= $query <br>";
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
		
		$strType=						$row["transaction_type"];
		$strMethod=						$row["method"];

		$strAddress_To=					$row["address_to"];
		$strAddress_From=				$row["address_from"];
		$strAccount_To=					$row["account_to"];
		$strAccount_From=				$row["account_from"];
		
		if($strAddress_To){$strAddress=$strAddress_To;}
		if($strAddress_From){$strAddress=$strAddress_From;}
		
		$strTXID=						$row["txid"];
		$strCryptoAmt=					$row["crypto_amount"];
		$strCryptoType=					$row["crypto_type"]; //BTC LTC DGE		
		$intConfirmations=				$row["confirmations"];
		$strMethod=					$row["method"];
		$strIpaddress=				$row["ipaddress"];
		$strResponse=				$row["response"];
		$strResponseCallback=		$row["response_callback"];
		$intCallbackStatus=			$row["callback_status"];
		$strBlockHash=				$row["block_hash"];
		$intBlockIndex=				$row["block_index"];
		$intBlockTime=				$row["block_time"];
		$strTXtime=					$row["tx_time"];
		$strTXtimereceived=			$row["tx_timereceived"];
		$strTXcategory=				$row["tx_category"];
		$strAddressAccount=			$row["address_account"];
		$intCryptoBalanceCurrent=	$row["crypto_balancecurrent"];
		$intCryptoPreviousBalance=	$row["crypto_previousbalance"];
		$intCryptoBitCoinDBalance=	$row["crypto_bitcoindbalance"];
		$intCredit=					$row["credit"];
		$intDebit=					$row["debit"];
		$strMessage=				$row["messagetext"];
		$strLabel=					$row["label"];
		$strLabel2=					$row["label2"];
		$strLabel3=					$row["label3"];
		
		//Prepend a Label to the label  ;-p  -John
		if (!$strLabel) {} else {$strLabel = "<small>Label:</small> ".$strLabel ;}
		
		$date_updated=				$row["date_updated"];
		$date_created= 				$row["date_created"];
		$date_created_formatted =	 date("y-m-d H:i", ($date_created));
		$date_created_formatted_nice = functNiceTimeDif($date_created);

		$strLinkText="$strLabel $strLabel2 $strLabel3 ";

		if($strType=="send"){ //send
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
		if($strType=="receive" OR $strType=="get"){ //receive
			$strCryptoText = "+";
			$strCyptoColor = "#00cc33";
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLinkText = "Received from <small>".$strAddressShow."</small>";
			$strLink = "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}


		if($intRecID){	//Pulling up single transaction details for modal
    ?>
	<div class="row">
		<h4>Transaction ID: <?=$intTransactionID?></h4><br>

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
			<strong style="color:<?=$strCyptoColor?>;"><?=$strCryptoText?><?=rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong><br>
			<?=$intCryptoMiningFee?><br>
			<?=$strCryptoType?><br>
			$<?=money_format('%i', $intFiatValue) ?><br>
			<?=$intFiatRate?><br>
			<?=$strWalletFrom?><br>
			<?=$strWalletSentTo?><br>
			<?=$date_joined_formatted?><br>
			<?=$strTransactionHash?> <br>
        </div>

    </div>

	<? }else{ // Regular activity/transactions list on wallet.php ?>
	
	    <tr>
			<td align="left">
				<a href="<?=$strLink?>" <?=$strLinkModal?>><?=$strLinkText?></a><br>
				<?=$strLabel?><br>
				<?=$strAddress?><br>
				"<?=$strMessage?>"<br>
				<small><?=$strTXID?></small>
			</td>
			<td align="left"><medium>
				<?=$date_created_formatted?> ET</medium><br>
				<small>(<?=$date_created_formatted_nice?>)</small><br>
				<?=$strLabel?>
			</td>
			
			<td align="left">
				<strong style="color:<?=$strCyptoColor?>;"><?=$strCryptoText?><?=abs(rtrim(number_format($strCryptoAmt,8),0))?></strong>
			</td>

			<td align="left">
				<?=$strType?> <?=$strMethod?>
			</td>
	    </tr>
	    <tr><td><br></td></tr>
	<?
		}//end if intRecID
	}// while loop end

	break;
//###################################################################################################################
//END CASE $intType "TRANSACTIONS"
//###################################################################################################################








//###################################################################################################################
//END SWITCH on $intType
//###################################################################################################################
} //switch end
?>


	<script>
    $(document).ready(function(){

		<?if($strDo!="iframe"){ ?>
			<? if($intNewstID){ ?>intNewestID = <?=$intNewstID?>;<? } ?>
			<? if($intLastMSGID){ ?>intLastRecord=<?=$intLastMSGID?>;<? } ?>
			<? if($b){ ?>intLastRecord=<?=$b?>; //alert('lastrecordid='+intLastRecord); <? } ?>
			<? if($nr0){ //set total records txt  ?>intTotalRecords= <?=$nr0?> ; //alert('totalrecords= ' + intTotalRecords );<? } ?>
			<? if($nr AND $intLastMSGID){ //set records shown txt  ?>intTotalRecordsShowing= intTotalRecordsShowing + <?=$nr?> ; //alert('totalrecords showing= ' + intTotalRecordsShowing );<? } ?>
		<? } ?>

	    <?if($strDo=="include"){ ?>
	    	$("#totalrecords").html('<?=$nr0?>');
	    <? } ?>

    });
    </script>
    
    
    