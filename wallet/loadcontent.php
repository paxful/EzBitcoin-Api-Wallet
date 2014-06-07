<?php
//error_reporting(1); // Turn off all error reporting
error_reporting(E_ERROR | E_PARSE); ini_set('display_errors',1);
ob_start(); //so we can redirect even after headers are sent


//get variables
$strDo = 				trim($_GET['do']);

if($strDo=="ajax" || $strDo=="iframe"){
	
	require_once $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

	//calling file via ajax so get values from query string
	$intNewstID = 			funct_ScrubVars($_GET['newest_msg_id']); 
	$intLastMSGID = 		funct_ScrubVars($_GET['last_msg_id']); 
	$intMaxRecords = 		funct_ScrubVars($_GET['maxrecords']);
	$intType = 				funct_ScrubVars($_GET["type"]); //1=songs, 2=pics, 3=ringtones, 7=albums, 9=people
	$intUserID2 = 			funct_ScrubVars($_GET["user2"]); //for me.php , comments
	$sortby = 				funct_ScrubVars($_GET["sort"]);
	$intFilter = 			funct_ScrubVars($_GET["f"]); if(!$intFilter){$intFilter=0;}//show all types
	//$strSearchTXT = 		functCleanSQLText(trim($_GET["searchtxt"]));
	$intMod =				funct_ScrubVars($_GET["m"]); //is this user a moderator?
	$intRecID = 			funct_ScrubVars($_GET["recid"]); //to return single cell of record just uploaded	
	$intUserID_viewer = 	funct_ScrubVars($_GET["viewer"]); //userid of user currently viewing the content	
	
	if($strDo=="iframe"){ ?>
		<link href="/css/web.css" media="screen" rel="stylesheet" type="text/css">
        <script src="/js/web.js" type="text/javascript"></script>
	<? }

}else{ //file is being included and values are preset 
	
	
	//...? nothing.. values should already be set in included file
	
}

//security check...
$intUserID_viewer = DETECT_USERID; //always get current user from function-cookie

if(!$intUserID_viewer){$intUserID_viewer = DETECT_USERID; }//if no viewer provided then get it from cookie

//sql injection defend

//$intUserID2 = mysqli_real_escape_string($DB_LINK,$intUserID2);
//$intKeyID = mysqli_real_escape_string($DB_LINK,$intKeyID);
//$intUserID1 = mysqli_real_escape_string($DB_LINK,$intUserID1);

if($strSearchTXT){ 
	//$intFilter=false ; 
	$intType="1"; //files
	if($sortby=="feat"){ $sortby="latest";} //set the sort to latest if they have not selected a default
}

if($intUserID_viewer=="2" OR $intUserID_viewer=="169"){$intMod=1;} //techz,copyright are mods
if(!$intLastMSGID){$intLastMSGID=0;} //for virgin page call
if(!$intMaxRecords){$intMaxRecords=MAXCHAR_RECORDS_TRANSACTIONS;} //limit records to 30

if($sortby==""){$sortby ="latest" ;} //default sort by date
if($sortby=="latest"){$strOrderBySTMT =" date_added DESC " ;} //latest
if($sortby=="oldest"){$strOrderBySTMT =" date_added ASC " ;} //oldest
if($sortby=="big"){$strOrderBySTMT =" filesize DESC " ;} //top
if($sortby=="small"){$strOrderBySTMT =" filesize ASC " ;} //top
if($sortby=="top"){$strOrderBySTMT =" count_downloads DESC " ;} //top
if($sortby=="bot"){$strOrderBySTMT =" count_downloads ASC " ;} //bottom
if($sortby=="type"){$strOrderBySTMT =" ext ASC " ;} //bottom
if($sortby=="feat"){$strOrderBySTMT =" stickymod ASC " ;} //bottom
if($sortby=="price"){$strOrderBySTMT =" date_added DESC  " ;} //bottom

//Switch statement for types of content
switch ($intType){
	//!CASE TRANSACTIONS
	case "transactions": //list of transactions in wallet.php

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords ;
	$cps = 			$intLastMSGID ;
    $a =			$cps + 1 ;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){ 
		$strWhereSTMT = $strWhereSTMT. " AND ( user_id=$intUserID_viewer ) " ;  //OR user_id_sentto=$intUserID_viewer 
	}else{ 
		die;  //not logged in then show nothing! SECURITY
	}
	
	if($intRecID){ $strWhereSTMT =" AND transaction_id=$intRecID " ;} //return single record	
	if($sortby=="top"){ $strOrderBySTMT =" crypto_amt DESC " ;}
	if($sortby=="new"){ $strWhereSTMT = $strWhereSTMT. " AND transaction_id>$intNewstID " ; $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}
	$strOrderBySTMT = " datetime_created DESC ";
    //$intLastMSGID = 0 ; 
	//$intMaxRecords = 1000 ;
	
    $query="SELECT * ".
	" FROM ".TBL_TRANSACTIONS." ".
	" WHERE transaction_id>0 AND ( status>0 OR type='buy' ) $strWhereSTMT ".
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords " ;
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
		$intRowCount=					$intRowCount + 1 ;
		
		$intOrderID=					$row["order_id"];
		$strOrderCode=					$row["order_code"];
	
		$strType=						$row["type"];
		$intStatus=						$row["status"]; //0 1 2
		$status_name=					$row["status_msg"]; //0 1 2
		
		$strCryptoType=					$row["cryptotype"]; //BTC LTC DGE
		$strCryptoAmt=					$row["crypto_amt"];
		$strCryptoRate_usd=				$row["crypto_rate_usd"];
		
		$currency_id=					$row["currency_id"];
		$currency_code=					$row["currency_code"];
		$balance_prev=					$row["balance_prev"];
				if($balance_prev<=0){$balance_prev=$strCryptoAmt;}

		$balance_curr=					$row["balance_curr"];
		$debit=							$row["debit"];
		$credit=						$row["credit"];

		
		$intCryptoMiningFee=			$row["crypto_miner_fee"]; //
		$intCryptoTotalOutFlow=			$row["crypto_total_outflow"]; //
		
		
		$intFiatRate=					$row["fiat_rate"]; //1=USD, 0.7=USD/EUR
		$strFiatType=					$row["fiat_type"]; //USD EUR
		$strFiatAmt=					$row["fiat_amt"]; // 
		$intFiatValue = $strCryptoAmt * $strCryptoRate_usd ;

	    $strWalletSentTo= 				$row["walletaddress_sentto"];
	    $strWalletFrom= 				$row["walletaddress_from"];
        
        if ($strWalletFrom=="") {$strWalletFrom="External wallet address";}
        
		$strTransactionHash=			$row["hash_transaction"];
		
		$strUserName_from =				$row["sender_name"];
		$strUserEmail_from =			$row["sender_email"];
		if ($strUserEmail_from OR $strUserName_from) {$strWalletFrom=$strUserEmail_from." ".$strUserName_from;}
		
		$intUserGive=					$row["user_id"];
		$intUserRecieve=				$row["user_id_sentto"];
		$strLabel=						$row["label"];

		//Prepend a Label to the label  ;-p  -John
		if (!$strLabel) {} else {$strLabel = '"'.$strLabel.'"' ;}

	
		$strDate= 						$row["datetime_created"];
		$strDate_formatted = date("Y-m-d H:i", ($strDate));
		$strDate_formatted_nice = functNiceTimeDif_int($strDate);
		
		$strLinkText="";
		$strCyptoColor = "#000000"; //black
				
/* 		if($intUserGive==$intUserID_viewer OR $debit){ //send */
		if($debit>0){ //send
			//$strCryptoAmt = $intCryptoTotalOutFlow ;
			//if(!$intCryptoTotalOutFlow){$strCryptoAmt=$strCryptoAmt;}
			$strCryptoText = "-";
			$strCryptoAmt = $debit;
			$strCyptoColor = "#000000"; //black
			$strTypeImgSrc = "/img/arrowup.png";
			$strAddressShow = $strWalletSentTo;	
			$strLinkText = "From me to <small>".$strAddressShow."</small>";
			//$strLabel = $strLabel
			$strLink = "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}
/* 		if($intUserRecieve==$intUserID_viewer OR $credit){ //receive */
		if($credit>0){ //receive
			$strCryptoText = "+";
			$strCryptoAmt = $credit;
			$strCyptoColor = "#009900"; //green
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLinkText = "Received from $strUserEmail_from <small>".$strAddressShow."</small>";
			$strLink =  "#";
			$strLinkModal = ' data-reveal-id="myModal" data-reveal-ajax="'.WEBSITEFULLURLHTTPS.MOD_LOADCONTENT.'?do=ajax&type=transactions&recid='.$intTransactionID.'"';
		}
		
		if($strType=='buy'){ //buy
			$strCryptoText = "+";
			$strCyptoColor = "#996600"; //brown
			$strTypeImgSrc = "/img/arrow_down.png";
			$strAddressShow = $strWalletFrom;
			$strLink = PAGE_RECEIPT."?c=".$strOrderCode ;
			$intFiatValue = "";
			$strLinkModal = "";
			//$strLabel = "";
			if($intStatus<>8){
				//is not filled so.. italizes font.. no plus sign and make dark grey the amount
				$strCryptoText = "";
				$strCyptoColor = "#666666";
			}

			//lookup status_name from tbl_statuses
			$query="SELECT * FROM " . TBL_STATUSES . " WHERE status_id = $intStatus ";
			//echo "SQL STMNT = " . $query . "<br>";
/* 			$rs9 = mysqli_query($DB_LINK, $query); $row9=mysqli_fetch_array($rs9) ; */
/* 			$strStatusText=					$row9["status_name"]; */

/* 			$strLinkText = "Bought $".$strFiatAmt." worth of Bitcoin - Confirmation Page <br> Status: $status_name" ; */
			$strLinkText = "Purchased Bitcoin<br>Status: $status_name" ;
		}
		
		
		if($intRecID){	//Pulling up single transaction details for modal
    ?>
	<div class="row">
		<h4>Transaction ID: <strong><?=$strTransactionHash?></strong></h4> <br>

        <div class="small-4 columns">
      
			Crypto Amount: <br>
			Mining Fee: <br>
			Crypto Type: <br>
			Fiat Value: <br>
			Fiat Rate: <br>
			From: <br>
			To: <br>
			Date: <br>
			CoinCafe ID: <br>
        </div>
		<div class="small-8 columns">
			<strong style="color:<?=$strCyptoColor?>;"><?=$strCryptoText?><?=rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong><br>
			<?=$intCryptoMiningFee?><br>
			<?=$strCryptoType?><br>
			$<?=money_format('%i', $intFiatValue) ?><br>
			<?=$intFiatRate?><br>
			<?=$strWalletFrom?><br>
			<?=$strWalletSentTo?><br>
			<?=$strDate_formatted?><br>
			
			<? if($strTransactionHash){ ?>
			<input id="transactionhash_<?=$intTransactionID?>" type="text" class="" style="display:inline-block; width:200px; height:25px; font-size:12px; padding:5px; border-width:0px;" value="<?=$strTransactionHash?>" size="16" maxlength="50" />
			<script> $("#transactionhash_<?=$intTransactionID?>").focus(function() { var $this = $(this);$this.select(); $this.mouseup(function() { $this.unbind("mouseup"); return false; });	}); </script>
			<? }else{ 
				//no transaction id so instead give them a link to a block explorer so they can track it down themselves
			?>
			<a href="https://blockchain.info/address/<?=$strWalletSentTo?>" target="_blank">find transaction id here</a>
			<? } ?>
			
        </div>

    </div>

	<? }else{ // Regular activity/transactions list on wallet.php 
		
		if($intTransactionID<=2625){
			
			$balance_curr = "" ;
			
			if($intTransactionID==2625){
				$strLinkText = "";
				continue; //skips to the next record. we only need this for accounting on send and receieve anyway
			}
		}
		
	?>
	    <tr>
			<td align="left"><medium><?=$strDate_formatted?> ET</medium> <br><small>(<?=$strDate_formatted_nice?>)</small></td>
			<td align="left"><a href="<?=$strLink?>"<?=$strLinkModal?>><?=$strLinkText?></a><br>
			<?=$strLabel?>
			<? if($strTransactionHash){ ?>
			<input id="transactionhash_<?=$intTransactionID?>" type="text" class="" style="display:inline-block; width:200px; height:25px; font-size:12px; padding:5px; border-width:0px;" value="<?=$strTransactionHash?>" size="16" maxlength="50" />
			<script> $("#transactionhash_<?=$intTransactionID?>").focus(function() { var $this = $(this);$this.select(); $this.mouseup(function() { $this.unbind("mouseup"); return false; });	}); </script>
			<? }else{ 
				//no transaction id so instead give them a link to a block explorer so they can track it down themselves
			?>
			<a href="https://blockchain.info/address/<?=$strWalletSentTo?>" target="_blank"><small>find transaction id here</small></a>
			<? } ?>
			</td>
<!--			<td align="left"><a href="<?=$strLink?>"<?=$strLinkModal?>><?=$strLinkText?></a><br><?=$strLabel?></td>-->

<!-- 			COMMENTING THIS OUT TO HIDE THE USD VALUE IN THE TRANSACTIONS LIST -John -->
			<td align="left"><span style="color:<?=$strCyptoColor?>;"><?=$strCryptoText?><?=number_format($strCryptoAmt,8)?> </span></td>
			<td align="left"><span style="color:#000000;"><?php if($balance_curr!=0){$balance_curr_display=number_format($balance_curr,8); echo "$balance_curr_display";} else {echo "";}?></span></td>
<!-- 			<td align="left"><strong style="color:<?=$strCyptoColor?>;"><?=$strCryptoText?><?=rtrim(number_format($strCryptoAmt,8),0)?></strong></td> -->
	    </tr>
	<?
		}//end if intRecID
	}// while loop end

	break;
	
	
	
	//!CASE ORDERS
	case "orders": //orders

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords ;
	$cps = 			$intLastMSGID ;
    $a =			$cps + 1 ;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){ $strWhereSTMT = $strWhereSTMT. " AND from_id=$intUserID_viewer " ;}
	//if($intRecID){ $strWhereSTMT =" AND transaction_id=$intRecID " ;} //return single record	
	//if($sortby=="top"){ $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($sortby=="new"){ $strWhereSTMT = $strWhereSTMT. " AND orderid>$intNewstID " ; $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}
	$strOrderBySTMT = " date DESC " ;
    $intLastMSGID = 0 ; 
	$intMaxRecords = 10 ;
	
    $query="SELECT * ".
	" FROM ".TBL_ORDERS." ".
	" WHERE orderid>0 $strWhereSTMT ".
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords " ;
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
		$strOrderCode=					$row["ordercode"];
	
		$strType=						$row["type"];
		$intStatus=						$row["status"];
		
		$strCryptoType=					$row["cryptotype"];
		$strCryptoAmt=					$row["amt_btc"];
		$strCryptoRate_usd=				$row["btc_rate"];		
		$intFiatValue = $strCryptoAmt * $strCryptoRate_usd ;
		$FiatToConvert =				$row["fiat_to_convert"];		

		$strTransactionHash=			$row["hash_transaction"];
		//if "coincade" then it is an internal transaction
		
		$intUserGive=					$row["user_id"];
		$intUserRecieve=				$row["user_id_sentto"];
		
		$strLabel=						$row["label"];
		
		$intDateTime= 					$row["time"];
		$strDate= 						$row["date"];
		$strDate_formatted = date("Y-m-d H:i", strtotime($strDate));
		$strDate_formatted_nice = functNiceTimeDif_int($intDateTime);
    ?>
	    <tr>
			<td align="left"><medium><?=$strDate_formatted_nice?></medium><br><?=$strDate_formatted?></td>
			<td align="left"><a href="<?=PAGE_RECEIPT?>?c=<?=$strOrderCode?>" style="font-size:24px;"><?=$intOrderID?></a><br><?=$strLabel?></td>
<!-- 			COMMENTING THIS OUT TO HIDE THE USD VALUE IN THE TRANSACTIONS LIST -John -->
<!-- 			<td align="left"><strong><?=rtrim(number_format($strCryptoAmt,8),0)?> BTC</strong> <br> $<?=money_format('%i', $intFiatValue) ?></td> -->
			<td align="left"><strong><?=rtrim(number_format($strCryptoAmt,8),0)?></strong></td>
	    </tr>
	<?
	
	}// while loop end

	break;
	
	
	//!CASE UPLOADS
	case "uploads": //uploads

	@ $rpp;        	//Records Per Page
    @ $cps;        	//Current Page Starting row number
    @ $lps;        	//Last Page Starting row number
    @ $a;        	//will be used to print the starting row number that is shown in the page
    @ $b;        	//will be used to print the ending row number that is shown in the page
	$rpp = 			$intMaxRecords ;
	$cps = 			$intLastMSGID ;
    $a =			$cps + 1 ;
	$b = 			$intLastMSGID ;//this is to fix the iframe src call javascript error on the homepage
    $lps = 			$cps - $rpp ; //Calculating the starting row number for previous page

	$strWhereSTMT =""; //reset this just incase loadcontent was called as an include before
	if($intUserID_viewer){ $strWhereSTMT = $strWhereSTMT. " AND usernameid=$intUserID_viewer " ;}
	if($intRecID){ $strWhereSTMT =" AND uploadid=$intRecID " ;} //return single record	
	//if($sortby=="top"){ $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($sortby=="new"){ $strWhereSTMT = $strWhereSTMT. " AND orderid>$intNewstID " ; $strOrderBySTMT =" crypto_amt DESC " ;}
	//if($strSearchTXT){ $strWhereSTMT = $strWhereSTMT. " AND private<1 " ;}
	$strOrderBySTMT = " date_added DESC " ;
    $intLastMSGID = 0 ; 
	$intMaxRecords = 10 ;
	
    $query="SELECT * ".
	" FROM ".TBL_UPLOADS." ".
	" WHERE uploadid>0 $strWhereSTMT ".
	" ORDER BY $strOrderBySTMT LIMIT $intLastMSGID,$intMaxRecords " ;
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
		
		//we show the users the thumbnails only as the full images are for admins only
		$strImgPath=PICTURETHUMBPATH.$strOrderCode.".jpg" ;
		
		
    ?>
	    <img src="<?=$strImgPath?>" width="100" height="100" />
	<?
	
	}// while loop end

	break;


} //switch end
//############

if($strDo!="iframe" AND !$intRecID){ ?>	
	<script>
    $(document).ready(function(){ 
		<? if($intNewstID){ ?>intNewestID = <?=$intNewstID?>;<? } ?>
		<? if($intRecID){ ?>intLastRecord=<?=$intRecID?>;<? } ?>
		<? if($b){ ?>intLastRecord=<?=$b?>;<? } ?>
		<? if($nr0){ //set total records on txt  ?>intTotalRecords=<?=$nr0?> ;<? } ?>
		<? /*
		jsfunct_Alert_Debug( 'strLoadContentAjaxURL= ' + strLoadContentAjaxURL
			+ ' <br> intLastRecord='+ intLastRecord
			+ ' <br> intTotalRecord='+ intTotalRecords );
		*/ ?>
    });
    </script>	
<? } ?>