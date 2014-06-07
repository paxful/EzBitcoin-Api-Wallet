<?php
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

<div class="row" style="">

    <div class="small-12 columns">
		<!--balance-->
            <div style="text-align:right;">
                <h3><strong id="txtBTCbalance"><?=$intBalance_BTC?> BTC</strong></h3>
<!-- 				<br><small id="txtFIATbalance"><small>(approx. $<?=number_format($intBalance_BTC_usd,2)?> USD)</small></small></h3> -->
            </div>
	</div>
	
	
</div>

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";

//!GET do,type,id,c
$strDO = 						trim($_GET["do"]);
$strType = 						trim($_GET["type"]);
$orderid = 						trim($_GET["id"]);
$strOrderCode = 				trim($_GET["c"]);
$orderid = mysqli_real_escape_string($DB_LINK, $orderid);
$strOrderCode = mysqli_real_escape_string($DB_LINK, $strOrderCode);

$strErrorMSG = 			trim($_GET["msg"]); //set error msg manually in query


//!$strDo fullfillorder
if($strDO=="fullfillorder"){ //update order, update member balance, send email

	//get data orderid , fiat deposited, btc to send, email text , rate btc at the time
	$intFiatDeposited = trim($_POST["balance_user"]);$intFiatDeposited = mysqli_real_escape_string($DB_LINK,$intFiatDeposited);
	$intBTCtoSend = trim($_POST["userid"]); $intBTCtoSend = mysqli_real_escape_string($DB_LINK, $intBTCtoSend);
	$strEmailTxt = trim($_POST["userid"]); $strEmailTxt = mysqli_real_escape_string($DB_LINK, $strEmailTxt);

	//get order info
	if($orderid){ //get from database
		$query="SELECT * FROM " . TBL_ORDERS . " WHERE orderid=" . $orderid." " ;
	    //echo "<label>SQL STMNT = " . $query .  "</label><br>";
	    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
		    $orderid=				$row["orderid"];
		    $intUserID=				$row["from_id"];
		}
	}

	//get member info
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = ".$intUserID ;
	//echo "<label>SQL STMNT = " . $query .  "</label><br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$strEmail=							$row["email"];
	$strFirstName=						$row["first_name"];
	$strLastName=						$row["last_name"];
	$cc_wallet_address=					$row["wallet_btc"];

	//update order table
	if($orderid){
		$query="UPDATE " . TBL_ORDERS . " SET fiat_deposited=".$intBalance." WHERE orderid=".$orderid ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}

	//update member table with new balance etc..
	if($intUserID){
		$query="UPDATE " . TBL_USERS . " SET balance_btc='".$intBalance."' WHERE id=".$intUserID ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}

	//send email to user
	$strSubject = "Your Coin Cafe Order Status has Changed";
	$strBody = "Hi $strFromName,  \n\n ";
	$strBody = $strBody ."Thank you for your business!\n\n-John and Ray, Coin Cafe owners\n";
	funct_Mail_simple(EMAIL_ORDERS, $strSubject, $strBody);

	//send email to orders table
	$strSubject = "Your Coin Cafe Order Status has Changed";
	$strBody = "Hi $strFromName,  \n\n ";
	$strBody = $strBody ."Thank you for your business!\n\n-John and Ray, Coin Cafe owners\n";
	funct_Mail_simple(EMAIL_ORDERS, $strSubject, $strBody);

	$strError = "Order Completed";

}//END $strDo fullfillorder





//!$strDo update_balance
if($strDO=="update_balance"){

	$balance_entered = 						trim($_POST["balance_entered"]);
	$balance_entered = mysqli_real_escape_string($DB_LINK, $balance_entered);
	$intUserID = 						trim($_POST["userid"]);
	$intUserID = mysqli_real_escape_string($DB_LINK, $intUserID);
	$orderid = 						trim($_POST["orderid"]);
	$orderid = mysqli_real_escape_string($DB_LINK, $orderid);


	//Get some information from the orders table
	$query="SELECT * FROM " . TBL_ORDERS . " WHERE orderid=" . $orderid." " ;
    //echo "<label>SQL STMNT = " . $query .  "</label><br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs); }
    $balance_prev=					$row["balance_prev"];
    $balance_new=					$row["balance_new"];


	if($intUserID){
		$query="UPDATE " . TBL_USERS . " SET balance_btc=$balance_entered WHERE id=".$intUserID ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}

	if($orderid){
		$query="UPDATE " . TBL_ORDERS . " SET balance_new=$balance_entered WHERE orderid=".$orderid ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}

}


//!$strDo confirm_deposit
if($strDO=="confirm_deposit"){

	$fiat_deposited =		trim($_POST["fiatdeposited"]); //removes leading and trailing spaces
	$fiat_deposited =		mysqli_real_escape_string($DB_LINK, $fiat_deposited); //prevents mySQL injection attacks
	$fiat_deposited =		htmlspecialchars(trim($_POST["fiatdeposited"])); //prevents XSS attacks
	$fiat_deposit_date =		trim($_POST["fiat_deposit_date"]); //removes leading and trailing spaces
	$fiat_deposit_date =		mysqli_real_escape_string($DB_LINK, $fiat_deposit_date); //prevents mySQL injection attacks
	$fiat_deposit_date =		htmlspecialchars(trim($_POST["fiat_deposit_date"])); //prevents XSS attacks
	$orderid =				trim($_POST["orderid"]);
	$orderid =				mysqli_real_escape_string($DB_LINK, $orderid);
	$user_id =				trim($_POST["userid"]);
	$user_id =				mysqli_real_escape_string($DB_LINK, $user_id);
	$crypto_sold =			trim($_POST["crypto_sold"]);
	$crypto_sold =			mysqli_real_escape_string($DB_LINK, $crypto_sold);


	//If the crypto sold amount is not entered by the order processing person, then don't insert into the database because it will cause an error
	if($crypto_sold) {
		$set_crypto_sold_sql = ", crypto_sold=$crypto_sold";
	}

	//If the fiat deposit date is not entered by the order processing person, then don't insert into the database because it will cause an error
	if($fiat_deposit_date) {
		$fiat_deposit_date_sql = ", fiat_deposit_date='$fiat_deposit_date'";
	}


	//Store in the database the actual fiat deposited, the actual crypto sold, and what their previous balance was before this order
	if($orderid){
		$query="UPDATE " . TBL_ORDERS . " SET fiat_deposited='$fiat_deposited' $set_crypto_sold_sql $fiat_deposit_date_sql $set_balance_prev_sql WHERE orderid=".$orderid ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}
}


//!$strDo exfilled
if($strDO=="exfilled"){

	$exfill_btc = 						trim($_POST["exfill_btc"]);
	$exfill_btc = mysqli_real_escape_string($DB_LINK, $exfill_btc);
	$orderid = 						trim($_POST["orderid"]);
	$orderid = mysqli_real_escape_string($DB_LINK, $orderid);
	$exchange = 						trim($_POST["exchange"]);
	$exchange = mysqli_real_escape_string($DB_LINK, $exchange);
	$exfill_cost = 						trim($_POST["exfill_cost"]);
	$exfill_cost = mysqli_real_escape_string($DB_LINK, $exfill_cost);
	$exfill_fee = 						trim($_POST["exfill_fee"]);
	$exfill_fee = mysqli_real_escape_string($DB_LINK, $exfill_fee);
	$deposit_type = 						trim($_POST["deposit_type"]);
	$deposit_type = mysqli_real_escape_string($DB_LINK, $deposit_type);
	$fiat_deposited = 						trim($_POST["fiat_deposited"]);
	$fiat_deposited = mysqli_real_escape_string($DB_LINK, $fiat_deposited);
	$crypto_sold = 						trim($_POST["crypto_sold"]);
	$crypto_sold = mysqli_real_escape_string($DB_LINK, $crypto_sold);
	$exfill_date = 						trim($_POST["exfill_date"]);
	$exfill_date = mysqli_real_escape_string($DB_LINK, $exfill_date);
	$rate_bitstamp = 						trim($_POST["rate_bitstamp"]);
	$rate_bitstamp = mysqli_real_escape_string($DB_LINK, $rate_bitstamp);
	$intAmtBTC = 						trim($_POST["btc_ordered"]);
	$intAmtBTC = mysqli_real_escape_string($DB_LINK, $intAmtBTC);



	$rate_bitstamp = number_format(funct_Billing_GetBTCPrice_BitStamp(),2);

	echo "exchange: $exchange<br>stamp: $rate_bitstamp<br>";

	//Calculate revenue
	if ($deposit_type=="wire") {$real_fee=15;} else {$real_fee=0;}
	$revenue = $fiat_deposited - $real_fee;

	echo "btc sold: $exfill_btc<br><br>";

	//Cumberland Logic
	if($exchange=="Cumberland") {
		if(!$exfill_btc){$exfill_btc = $intAmtBTC;} //locking the amount of BTC that the customer wanted
		if(!$exfill_date){$exfill_date = date('Y-m-d H:i');}
		$exfill_cost = $exfill_btc * $rate_bitstamp * 1.025; //Cumberland cost
		$exfill_fee = 0;
	}
	echo "btc sold: $exfill_btc<br><br>";

	//Calculate Coin Cafe Profit
	if(!$exfill_fee) {$exfill_fee="0";}
	$profit_usd = $revenue - $exfill_cost - $exfill_fee;
	$profit_btc = $exfill_btc - $crypto_sold;
	
	echo "cost: $exfill_cost<br>exfill_date: $exfill_date<br>fee: $exfill_fee<br>";
	
	if($orderid){
		$query="UPDATE " . TBL_ORDERS . " SET profit_usd='$profit_usd', profit_btc='$profit_btc', rate_bitstamp='$rate_bitstamp', exfill_btc='$exfill_btc', exchange='$exchange', exfill_cost='$exfill_cost', exfill_fee='$exfill_fee', exfill_date='$exfill_date' WHERE orderid=".$orderid ;
		echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
	}
}
//END $strDo exfilled





//!$strDo FILL_ORDER_TXN
//if ?do=FILL_ORDER_TXN, then this creates a new record in the transactions table with a credit to the user of amount of BTC they purchased
if($strDO=="FILL_ORDER_TXN"){

	$orderid = 						trim($_GET["id"]);
	$orderid = mysqli_real_escape_string($DB_LINK, $orderid);
	$user_id = 						trim($_GET["uid"]);
	$user_id = mysqli_real_escape_string($DB_LINK, $user_id);
	$balance_new = 						trim($_GET["balance_new"]);
	$balance_new = mysqli_real_escape_string($DB_LINK, $balance_new);
	$balance_prev = 						trim($_GET["balance_prev"]);
	$balance_prev = mysqli_real_escape_string($DB_LINK, $balance_prev);


	//Get some information from the orders table
	$query="SELECT * FROM " . TBL_ORDERS . " WHERE orderid=" . $orderid." " ;
    //echo "<label>SQL STMNT = " . $query .  "</label><br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs); }
    $crypto_sold=					$row["crypto_sold"];
    $balance_prev=					$row["balance_prev"];
    $fiat_deposited=				$row["fiat_deposited"];
    $ordercode=						$row["ordercode"];
    $txn=							$row["txn"];


    if (!$txn) { //Do all this if there is no transaction in the transactions table already

		//Get some information from the users table
		$query="SELECT * FROM " . TBL_USERS . " WHERE id = ".$user_id ;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
		$email=							$row["email"];
		$cellphone=						$row["cellphone"];
		$first_name=					$row["first_name"];
		$last_name=						$row["last_name"];
		$name = $first_name." ".$last_name;
		$wallet_location=				$row["wallet_location"];
		$balance_curr=					$row["balance_btc"];
	
		//update user table with new balance
		if($user_id){
			$query="UPDATE " . TBL_USERS . " SET balance_btc=$balance_new WHERE id=".$user_id ;
			//echo "<label>SQL STMNT = " . $query .  "</label><br>";
			mysqli_query($DB_LINK, $query) or die(mysqli_error());
		}
		
		//Create a new record in the transactions table with the details of this new purchase
		$status_msg = "Order $orderid filled. $crypto_sold BTC Purchased";
		if(!$balance_prev){$balance_prev="0";}
	
		$query="INSERT INTO ".TBL_TRANSACTIONS." ".
		"(user_id,	credit,			balance_prev,	balance_curr,	sender_name,	sender_email,	sender_phone,	type,	cryptotype,	crypto_amt,		fiat_amt,			fiat_type,	datetime_created,	datetime,	status,	status_msg,		wallet_location,	order_id,	order_code	) VALUES ".
		"($user_id,	$crypto_sold,	$balance_prev,	$balance_new,	'$name',		'$email',		'$cellphone',	'buy',	'btc',		$crypto_sold,	$fiat_deposited,	'USD',		UNIX_TIMESTAMP(),	NOW(),		1,		'$status_msg',	'$wallet_location',	$orderid,	'$ordercode') ";
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());
		
		//Update the order table with the flag that a record in the Transactions table was created
		$query="UPDATE " . TBL_ORDERS . " SET txn=1 WHERE orderid=".$orderid;
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());

	
		if($strErrorMSG) {$strErrorMSG = $strErrorMSG."<br>Order Filled. Customer balance is now $balance_new and a new transaction was created in the Transactions table.";}
		else {$strErrorMSG = "Order Filled. Customer balance is now $balance_new and a new transaction was created in the Transactions table.";}
		
	} else { //Otherwise spit out an error saying there is already a record in the transactions table for this order
		$strErrorMSG = "There is already a record in the Transactions table! No record created in tbl_transactions";
	}

} //END $strDo FILL_ORDER_TXN





//!DB Get all the relevant information needed to display on this page
//get bank name and other info
if($orderid){ //get from database
	$query="SELECT * FROM " . TBL_ORDERS . " WHERE orderid=" . $orderid." " ;
    //echo "<label>SQL STMNT = " . $query .  "</label><br>";
    $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
    if(mysqli_num_rows($rs)>0){ $row=mysqli_fetch_array($rs);
	    $orderid=					$row["orderid"];

		//$orderid = BLOAT_ORDERID + $orderid ;

	    $strOrderCode=					$row["ordercode"];
		$strType=						$row["type"];
	    $user_id=						$row["from_id"];
	    $status_id=						$row["status_id"];

	    $strFromName=					$row["from_name"];
	    $strFromNameLast= 				$row["from_namelast"];
	    $strEmail= 						$row["from_email"];
	    $strPhone= 						$row["from_phone"];
	    $strMessage= 					$row["from_message"];
	    $strDate_meetinperson= 			$row["date_meetinperson"];

	    $reason_for_purchase=			$row["reason_for_purchase"];

		$balance_prev=					$row["balance_prev"];
	    $balance_new=					$row["balance_new"];

	    $fiatToDeposit= 			    $row["amt_usd"];
		$intAmtBTC=						$row["amt_btc"];

		$crypto_sold=					$row["crypto_sold"];
		$exchange=						$row["exchange"];
		$exfill_btc=					$row["exfill_btc"];
		$exfill_cost=					$row["exfill_cost"];
		$exfill_fee=					$row["exfill_fee"];
		$exfill_date=					$row["exfill_date"];
		$rate_bitstamp=					$row["rate_bitstamp"];

	    $intTipUSD= 					$row["tip_usd"];
	    $our_fee_usd= 					$row["our_fee_usd"];
	    $our_fee_btc= 					$row["our_fee_btc"];
	    $our_fee_percent= 				$row["our_fee_percent"];
	    $bank_fee=						$row["bank_fee"];
		$fiat_to_convert=             	$row["total_usd"];
        $fiat_deposited=                $row["fiat_deposited"];
        $fiat_deposited_user=           $row["fiat_deposited_user"];
        $fiat_deposit_date=				$row["fiat_deposit_date"];

		$intBankID= 					$row["bankid"];
	    $bank_name= 					$row["bank_name"];

        $rate_at_time_of_order=                     $row["btc_rate"];
        $crypto_est_at_time_of_order=               $row["amt_btc"];
        $no_receipts_uploaded=                      $row["no_receipts_uploaded"];
        $first_receipt_upload_time=                 $row["first_receipt_upload_time"];
        $rate_at_time_of_first_receipt_upload=      $row["rate_at_time_of_first_receipt_upload"];
        $last_receipt_upload_time=                  $row["last_receipt_upload_time"];
        $rate_at_time_of_last_receipt_upload=       $row["rate_at_time_of_last_receipt_upload"];


	    $hash_to= 						$row["hash_to"];




	//get info from users table
	if($user_id) {
		$query="SELECT * FROM " . TBL_USERS . " WHERE id = $user_id ";
		//echo "SQL STMNT = " . $query . "<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
		$user_balance=					$row["balance_btc"];
	}

	if($crypto_sold) {$balance_new=$balance_prev+$crypto_sold;}





	}
}









if($user_id){
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = $user_id ";
	//echo "SQL STMNT = " . $query . "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$cc_wallet_address=				$row["wallet_btc"];
	$user_balance=					$row["balance_btc"];
}


//!$strDO sendemailcoinssent
if($strDO=="sendemailcoinssent"){ //send email to user after coins sent
	$strEmail = 					trim($_POST["email"]);
	$strSubject = 						trim($_POST["subject"]);
	$strEmailText = 					(trim($_POST["text"]));
	$orderid = 						trim($_POST["orderid"]);
	$strEmail = mysqli_real_escape_string($DB_LINK, $strEmail);
	$strSubject = mysqli_real_escape_string($DB_LINK, $strSubject);
	$strEmailText = mysqli_real_escape_string($DB_LINK, $strEmailText);
	$orderid = mysqli_real_escape_string($DB_LINK, $orderid);

	$strEmailText = str_replace( '\r\n', " \n ", $strEmailText );
	$strEmailText = str_replace( '\r', " ", $strEmailText );

	funct_Mail_simple($strEmail, $strSubject, $strEmailText, EMAIL_ORDERS, SUPPORT_EMAIL);

	$strErrorMSG = "Email Sent, Order Filled" ;
}


//Added by John to send an update email to customer regarding their order
//!$strDO sendstatusemail
if($strDO=="sendstatusemail"){ //send email to user updating them of their order status
	$strForm_email = 					trim($_POST["email"]);
	$strSubject = 						trim($_POST["subject"]);
	$strEmailText = 					(trim($_POST["text"]));
	$orderid = 						trim($_POST["orderid"]);
	$strEmail = mysqli_real_escape_string($DB_LINK, $strEmail);
	$strSubject = mysqli_real_escape_string($DB_LINK, $strSubject);
	$strEmailText = mysqli_real_escape_string($DB_LINK, $strEmailText);
	$orderid = mysqli_real_escape_string($DB_LINK, $orderid);

	//send email to customer
	if($strForm_email){ //email chest owner
/* 		$strFromName = $strFromName." ".$strForm_NameLast ; //use customer name */
		$strEmailType = "text/plain" ;
		$strSubject = "Your Coin Cafe Order $orderid Status has been updated";
		$strReceiptURL = WEBSITEFULLURLHTTPS."/".PAGE_RECEIPT."?c=$strOrderCode" ;
		$strEmailText = "Hi $strFromName,\n\n".
		"Your order status has changed. Please review it here:\n\n".$strReceiptURL."\n\n";

		$strEmailText = $strEmailText ."Thank you for your business!\n\n-John and Ray, Coin Cafe owners";
	}
	//########## DB end

	$strErrorMSG = 	"Status Update Email sent to $strEmail ";

	$strEmailText = str_replace( '\r\n', " \n ", $strEmailText );
	$strEmailText = str_replace( '\r', " ", $strEmailText );

	funct_Mail_simple($strEmail, $strSubject, $strEmailText, EMAIL_ORDERS, SUPPORT_EMAIL);

	$strError = "Email Sent" ;
}

//Added by John to send an update email to Cumberland informing them of an order
//!$strDO sendorderemailcumberland
if($strDO=="sendorderemailcumberland"){ //send email to Cumberland updating them of a new order

$order_id = funct_ScrubVars($_POST["orderid"]) ;
$email = funct_ScrubVars($_POST["email"]) ;
$exfill_date = funct_ScrubVars($_POST["exfill_date"]) ;
$exfill_btc = funct_ScrubVars($_POST["exfill_btc"]) ;
$rate_bitstamp = funct_ScrubVars($_POST["rate_bitstamp"]) ;
$exfill_cost = funct_ScrubVars($_POST["exfill_cost"]) ;
$exfill_fee = funct_ScrubVars($_POST["exfill_fee"]) ;

$revenue_cumberland = $exfill_cost + $exfill_fee;
$rate_cumberland = $rate_bitstamp * 1.025;
$rate_cumberland = number_format($rate_cumberland,2);

	//send email to Cumberland
	$strEmailType = "text/plain" ;
	$strSubject = "New Coin Cafe order filled $exfill_btc BTC sold for $$revenue_cumberland";
	$strEmailText = "Hi Mike and Wally,\n\n".
	"A new order was filled:\n\n".
	"Txn: $order_id\nDate / Time: $exfill_date ET\nBTC sold: $exfill_btc\nBitstamp Offer: $rate_bitstamp\nCumberland Offer: $rate_cumberland\nTotal USD: $revenue_cumberland\n\n";

	$strEmailText = $strEmailText ."Thank you for being a valued supplier to Coin Cafe!\n\n-John";
	//########## DB end

	$strErrorMSG = 	"Email sent to Cumberland!";

	$strEmailText = str_replace( '\r\n', " \n ", $strEmailText );
	$strEmailText = str_replace( '\r', " ", $strEmailText );

	funct_Mail_simple(EMAIL_CUMBERLAND, $strSubject, $strEmailText, EMAIL_ORDERS, EMAIL_ORDERS);
	funct_Mail_simple(EMAIL_ORDERS, $strSubject, $strEmailText, EMAIL_ORDERS, EMAIL_ORDERS);

	$strError = "Email Sent" ;
}


if($strDO=="addstatus"){ //!$strDO addstatus

	$status_id = 					trim($_POST["statustype"]);
	$status_id = mysqli_real_escape_string($DB_LINK, $status_id);
	$strNotes = 						trim($_POST["notes"]);
	$strNotes = mysqli_real_escape_string($DB_LINK, $strNotes);

	//lookup status_name from tbl_statuses
	$query="SELECT * FROM " . TBL_STATUSES . " WHERE status_id = $status_id ";
	//echo "SQL STMNT = " . $query . "<br>";
	$rs = mysqli_query($DB_LINK, $query); $row=mysqli_fetch_array($rs) ;
	$status_name=					$row["status_name"];




	//insert into database
	$intStatus = 0 ;
	$query = "INSERT INTO ".TBL_ORDERS_DETAILS.
	" ( orderid,		status_id,		statustxt,		notes,		date ) VALUES ".
	" ( $orderid,	$status_id,	'$status_name',	'$strNotes', NOW() ) " ;
	//echo "SQL STMNT = " . $query .  "<br><br><br>";
	mysqli_query($DB_LINK, $query);


	//!Need to remember why the hell this status_id<>8 is here... -John
	if($status_id<>"8") {
		//update transactions table so that user can see status in their wallet.php
		$query="UPDATE " . TBL_TRANSACTIONS . " SET status=$status_id, status_msg='" . $status_name . "' WHERE order_id=$orderid AND type='buy'";
		//echo "<label>SQL STMNT = " . $query .  "</label><br>";
		mysqli_query($DB_LINK, $query) or die(mysqli_error());

	}


	//update orders table with latest status of order
	$query="UPDATE " . TBL_ORDERS . " SET status_id=$status_id, status_text='" . $status_name . "' WHERE orderid=".$orderid;
	//echo "<label>SQL STMNT = " . $query .  "</label><br>";
	mysqli_query($DB_LINK, $query) or die(mysqli_error());

	$strReceiptURL = WEBSITEFULLURLHTTPS."/".PAGE_RECEIPT."?c=$strOrderCode" ;

	//send email to customer
	if($strForm_email){ //email chest owner
		$strFromName = $strForm_NameFirst." ".$strForm_NameLast ; //use customer name
		$strEmailType = "text/plain" ;
		$strSubject = "Your Coin Cafe Order Status has Changed";
		$strBody = "Hi $strFromName,  \n\n ".
		"  \n\n".
		"This is the link to your receipt:\n\n".$strReceiptURL."\n\nPlease follow the instructions.\n\n";

		//different reciepts for wire, check, deposit
		//$strBody = $strBody ."Your got a donation $".$strTotal_money."  \n " ;

		$strBody = $strBody ."Thank you for your business!\n\n-John and Ray, Coin Cafe owners\n";
		//funct_Mail_simple($strEmail, $strSubject, $strBody);
/* 		funct_Mail_simple(EMAIL_ORDERS, $strSubject, $strBody); */
		//$strEmailResponse = functSendEmail($strForm_email, $strSubject, $strBody, $strFromName, EMAIL_FROM_NAME, EMAIL_FROM_ADDR);
	}
	//########## DB end

	$strErrorMSG = 	"Status Updated!";

	//redirect them to the print page
	//header( 'Location: ?id='.$orderid."&msg=".$strErrorMSG  ); die(); //Make sure code after is not executed


    //Fiat and Crypto number formatting




}//end do=add


//Get current BTC rate
$strCrypto="btc";
$strExchange=RATE_HUD_EXCHANGE;
$current_rate = funct_Billing_GetRate($strCrypto,$strExchange);



//Calculate current estimate of BTC to fill
$actual_our_fee_usd = number_format($fiat_deposited * CC_FEE,2);
echo "actual_our_fee_usd: $actual_our_fee_usd<br><br>";
$actual_fiat_to_convert = $fiat_deposited - $bank_fee - $actual_our_fee_usd;
echo "actual_fiat_to_convert: $actual_fiat_to_convert<br><br>";
$current_btc_to_fill = number_format($actual_fiat_to_convert / $current_rate,8);
echo "current_btc_to_fill: $current_btc_to_fill<br><br>";

//Calculate revenue
if ($strType=="wire") {$real_fee=15;} else {$real_fee=0;}
$revenue = $fiat_deposited - $real_fee;

//Determine order confirmation url
$confirmation_url = WEBSITEFULLURLHTTPS."/".PAGE_RECEIPT."?c=".$strOrderCode ;

//If the status of this order is 8, then display a message warning not to mess with it
if($strErrorMSG) { echo "DEBUG $strErrorMSG"; }

if($strErrorMSG and ($status_id=="8" or $status_id=="31")){$strErrorMSG=$strErrorMSG."<br>Order has been filled, don't mess with it!";}
//if($status_id=="8" or $status_id=="31"){$strErrorMSG="Order has been filled, don't mess with it!";}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Order <?=$orderid?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
<meta charset="utf-8">
<meta name="description" content="<?=$strPageTitle?>">
<meta name="viewport" content="width=device-width">

<!-- Favicon -->
<link rel="icon" type="image/png" href="/img/favicon.png" />

<link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
<script src="/js/modernizr.js"></script>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<script src="/js/web.js" type="text/javascript"></script>

<script>


	$(document).ready(function(){
	}); //close ready function



</script>



</head>

<body onLoad="<?=$strOnBodyLoadJS?>" class="" style="">

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>


<p></p>

    <div class="row">
		<div class="small-12 medium-6 columns">

			<? if($strErrorMSG){ ?><h4><font color="#ff0000"><strong><?=$strErrorMSG?></strong></font></h4><br><? } ?>

            <table>
                <thead>
                    <tr>
                        <th width="200"><h4><a href="?id=<?=$orderid?>">Order <?=$orderid?></a></h4><br>
                        
							<form data-abide action="?" method="GET">
								<input name="id" type="number" id="right-label" value="" required placeholder="Order ID">
							</form>

                        
                        </th>
                        <th width="400">
	                                                	<div class="row">
                        		<div class="small-6 columns">
                        			Instructions:<br>
                        			Deposit Method:<br>
                        			BTC:<br>
                        			USD:<br>
                        			Reason:<br>
<!--
									CC Wallet:<br>
									<?=$cc_wallet_address?><br>
-->
                        		</div>
                        		<div class="small-6 columns">
                        			<a href="<?=$confirmation_url?>" target="_blank"><?=$strOrderCode?></a><br>
			                        <strong><?=$strType?></strong><br>
			                        <?=$crypto_est_at_time_of_order?><br>
			                        <?=$fiatToDeposit?><br>
                        			<?=$reason_for_purchase?>
                        		</div>
                        	</div>

                        </th>
                    </tr>
                </thead>
                <tbody>
                        <td><h5><?=$user_id?> <a href="member_details.php?id=<?=$user_id?>" target="_blank"><?=$strFromName?></a> <?=$strFromNameLast?></h5></td>
                        <td>
                        	<div class="row">
                        		<div class="small-4 columns">
									Email:<br>
									Phone:<br>
									Message:<br>
                        		</div>
                        		<div class="small-8 columns">
                        			<?=$strEmail?><br>
                        			<?=$strPhone?><br>
                        			<?=$strMessage?>
                        		</div>
                        	</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Order Details:</td>
                        <td>
                        	<div class="row">
                        		<div class="small-6 columns">
                        			Actual Deposited:<br>
                        			Deposit Date:<br>
                        			Bank Fee:<br>
                        			Coin Cafe Fee:<br>
                        			Revenue:<br>
                        			Exchange:<br>
                        			ExFill Date:
                        		</div>
                        		<div class="small-6 columns">
                        			<strong><?=$fiat_deposited?></strong><br>
                        			<?php 
										//If the date is null, then display blank instead of 1969-12-31
										$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date));
										if($fiat_deposit_date==""){$fiat_deposit_date_display="no deposit date";} else {$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date_display));}
										echo $fiat_deposit_date_display;
                        			?><br>
                        			<?=$bank_fee?><br>
                        			<?=$actual_our_fee_usd?> (<?=(float)($our_fee_percent*100)?>%)<br>
                        			<?=number_format($revenue,2)?><br>
                        			<?=$exchange?><br>
                        			<?=$exfill_date?>
                        		</div>
                        	</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Deposits:<br><a href="orders_exfilled.php">orders_exfilled</a></td>
                        <td>
                        	<div class="row">
                        		<div class="small-6 columns">
                        			Need to deposit:<br>
                        			Deposited (user):<br>
                        			Actual deposited:<br>
                        			Profit USD:<br>
                        			Profit BTC:
                        		</div>
                        		<div class="small-6 columns">
                        			<?=$fiatToDeposit?><br>
                        			<?=$fiat_deposited_user?><br>
                        			<?=$fiat_deposited?><br>
                        			<?php
	                        			$profit_usd = $revenue - $exfill_cost - $exfill_fee;
	                        			$profit_btc = $exfill_btc - $crypto_sold;
	                        			$profit_margin_usd = number_format($profit_usd / $revenue * 100,2);
	                        			$profit_margin_btc = number_format($profit_btc / $crypto_sold * 100,2);
	                        			if($profit_margin_btc!="0") {$displayprofit="($profit_margin_btc%)";}
	                        			echo "$profit_usd ($profit_margin_usd%)<br>$profit_btc $displayprofit";
                        			?>
                        		</div>
                        	</div>
						</td>
                    </tr>
                    <tr>
                    	<td>Ordered:</td>
                        <td>
                        	<div class="row">
                        		<div class="small-6 columns">
                        			Order rate:<br>
                        			Rate Bitstamp:<br>
                        			Order BTC:<br>
                        			Curr BTC:<br>
                        			$ to Convert:
                        		</div>
                        		<div class="small-6 columns">
									<?=$rate_at_time_of_order?><br>
									<?=$rate_bitstamp?><br>
									<?=$crypto_est_at_time_of_order?><br>
									<?=$current_btc_to_fill?><br>
                        			<?=$actual_fiat_to_convert?>
                        		</div>
                        	</div>
                        </td>
                    </tr>

					<tr>
						<td colspan="2">
							<!-- !FORM confirm_deposit -->
						<!-- Confirm Fiat Deposited Amount -->
					         <div class="name-field">
					             <div class="row">
					                 <div class="small-4 columns">
					     <form data-abide action="?id=<?=$orderid?>&userid=<?=$user_id?>&do=confirm_deposit" method="POST">
					                     <label for="right-label" class="right inline">Actual fiat deposited:<br><br>Fiat deposit date:<br><br><br>Actual BTC sold:</label>
					                 </div>
					                 <div class="small-4 columns">
					                     <input name="fiatdeposited" type="number" id="right-label" value="<?=$fiat_deposited?>" required placeholder="$0.00">
										 <input name="fiat_deposit_date" type="datetime" value="<?php if($fiat_deposit_date) {$fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date)); $fiat_deposit_date_display=date("Y-m-d", strtotime($fiat_deposit_date_display)); echo $fiat_deposit_date_display;} else {echo date("Y-m-d");}?>" id="right-label" placeholder="datetime ET">
     				                     <input name="crypto_sold" type="number" id="right-label" value="<?=$crypto_sold?>" placeholder="Amt BTC">
					                     <input name="orderid" type="hidden" value="<?=$orderid?>">
										 <input name="userid" type="hidden" value="<?=$user_id?>">
					                 </div>
					                 <div class="small-4 columns">
					                     <button type="submit" class="tiny">Confirm</button>
					                     <button type="submit" class="tiny" name="fiat_deposit_date" value="date("Y-m-d")">Today</button>
					                     <button type="submit" class="tiny" name="fiat_deposit_date" value="date("Y-m-d", time() - 60 * 60 * 24)">Yesterday</button>
					     </form>
					                 </div>
					             </div>
					         </div>
						</td>
					</tr>

					<tr>
						<td colspan="2">
						<!-- !FORM exfilled -->
						<!-- Populate database with amount of crypto that was bought at exchange -->
					         <div class="name-field">
					             <div class="row">
					                 <div class="small-4 columns">
					     <form data-abide action="?id=<?=$orderid?>&userid=<?=$user_id?>&do=exfilled" method="POST">
					                     <label for="right-label" class="right inline">Actual BTC Filled at <?php if(!$exchange){echo "Exchange";} else {echo $exchange;}?>:<br><br>$ Cost:<br><br><br>$ Fee:<br><br><br>ExFill Date:</label>
					                 </div>
					                 <div class="small-4 columns">
										<input required type="number" name="exfill_btc" value="<?=$exfill_btc?>" id="right-label" placeholder="BTC Amount">
										<input type="hidden" name="orderid" value="<?=$orderid?>">
										<input type="hidden" name="btc_ordered" value="<?=$intAmtBTC?>">
										<input type="hidden" name="rate_bitstamp" value="<?=$rate_bitstamp?>">
										<input name="userid" type="hidden" value="<?=$user_id?>">
										<input name="deposit_type" type="hidden" value="<?=$strType?>">
										<input name="fiat_deposited" type="hidden" value="<?=$fiat_deposited?>">
										<input name="crypto_sold" type="hidden" value="<?=$crypto_sold?>">
										<input required type="number" name="exfill_cost" value="<?=$exfill_cost?>" id="right-label" placeholder="$ Cost">
										<input required type="number" name="exfill_fee" value="<?=$exfill_fee?>" id="right-label" placeholder="$ Fee">
										<input required type="datetime" name="exfill_date" value="<?php if($exfill_date) {$exfill_date_display=date("Y-m-d H:i", strtotime($exfill_date)); $exfill_date_display=date("Y-m-d H:i", strtotime($exfill_date_display)); echo $exfill_date_display;} else {echo date("Y-m-d H:i");} ?>" id="right-label" placeholder="datetime ET">
<!--
										<input required type="radio"  name="exchange" value="Bitstamp" <?php if($exchange=="Bitstamp") {echo "checked";} ?> id="bitstamp"><label for="bitstamp">Bitstamp</label><br>
										<input required type="radio"  name="exchange" value="ANX" <?php if($exchange=="ANX") {echo "checked";} ?> id="anx"><label for="anx">ANX</label>
-->
					                 </div>
					                 <div class="small-4 columns">
					                     <button type="submit" class="tiny" name="exchange" value="<?php if($exchange){echo "$exchange";}?>">Update</button>
					                     <button type="submit" class="tiny" name="exchange" value="Bitstamp">Bitstamp</button>
					                     <button type="submit" class="tiny" name="exchange" value="ANX">ANX</button>
					                     <button type="submit" class="tiny" name="exchange" value="Cumberland">Cumberland</button>
					     </form>
					                 </div>
					     <label>Bitstamp: <?=$rate_bitstamp?></label>
					             </div>
					         </div>
						</td>
					</tr>


<!--
                    <tr>
                        <td>tbl_orders.hash_to</td>
                        <td><?=$hash_to?></td>
                    </tr>
-->
					<?php if($strType=="inperson"){ ?>
                        <tr>
                            <td>Date Coming:</td>
                            <td><?=$strDate_meetinperson?></td>
                        </tr>
					<?php } ?>
<!--
                    <tr>
                        <td>No. Receipts Uploaded</td>
                        <td><?=$no_receipts_uploaded?></td>
                    </tr>
-->
                    <tr>
<!--                     	!SEND Cumberland Email -->
                        <td>Send fill notice to Cumberland</td>
                        <td>
							<form name="sendorderemailcumberland" id="sendorderemailcumberland" method="post" action="?id=<?=$orderid?>&do=sendorderemailcumberland">
								<input name="orderid" type="hidden" value="<?=$orderid?>">
								<input name="exfill_date" type="hidden" value="<?=$exfill_date?>">
								<input name="exfill_btc" type="hidden" value="<?=$exfill_btc?>">
								<input name="rate_bitstamp" type="hidden" value="<?=$rate_bitstamp?>">
								<input name="exfill_cost" type="hidden" value="<?=$exfill_cost?>">
								<input name="exfill_fee" type="hidden" value="<?=$exfill_fee?>">
								<input type="submit" class="button tiny" value="Send Cumberland Email" />
							</form>
                        </td>
                    </tr>
                    <tr>
                    <tr>
<!--                     	!SEND Status Update -->
                        <td>Send status email to customer</td>
                        <td>
							<form name="sendstatusemail" id="sendstatusemail" method="post" action="?id=<?=$orderid?>&do=sendstatusemail">
								<input name="orderid" type="hidden" value="<?=$orderid?>">
								<input name="email" type="hidden" value="<?=$strEmail?>">
								<input type="submit" class="button tiny" value="Send Status Email" />
							</form>
                        </td>
                    </tr>
                    <tr>
                        <td>1st Receipt / Rate<br>Last Receipt / Rate</td>
                        <td><?=$first_receipt_upload_time?> / <?=$rate_at_time_of_first_receipt_upload?><br><?=$last_receipt_upload_time?> / <?=$rate_at_time_of_last_receipt_upload?></td>
                    </tr>

                </tbody>
            </table>


			<p></p><p></p>


			<div class="radius panel">
			<!-- SEND CONFIRM EMAIL -->
				<form name="sendemail" id="sendemail" method="post" action="?id=<?=$orderid?>&do=sendemailcoinssent">
					<? //get text from text file

					$fh = fopen( __ROOT__.TEXTDATA_EMAIL_ORDERSENT,'r');
					while ($line = fgets($fh)) {
					  $strFileData = $strFileData.$line ;
					}
					fclose($fh);

					//replace __NAME__ __ORDERNUMBER__ __CRYPTOSENT__ __SUPPORTEMAIL__ __SUPPORTPHONE__ __URL__
					$strFileData = str_replace('__NAME__',$strFromName." ".$strFromNameLast,$strFileData) ;
					$strFileData = str_replace('__ORDERNUMBER__',$orderid,$strFileData) ;
					$strFileData = str_replace('__CRYPTOSENT__',$crypto_sold,$strFileData) ;
					$strFileData = str_replace('__URL__',WEBSITEFULLURLHTTPS.PAGE_WALLET,$strFileData) ;
					$strFileData = str_replace('__SUPPORTPHONE__',SUPPORT_PHONENUMBER,$strFileData) ;
					$strFileData = str_replace('__SUPPORTEMAIL__',SUPPORT_EMAIL,$strFileData) ;

					?>
					<h4>Send Confirm Email</h4>
					<p></p>
					<div class="row">
					    <div class="medium-4 small-12 columns">
				          <input name="email" type="email" id="email" placeholder="your email" value="<?=$strEmail?>" style="width:400px;">
					    </div>
					</div>
					<div class="row">
					    <div class="medium-4 small-12 columns">
						     <input name="subject" type="text" id="subject" value="Your <?=WEBSITENAME?> order <?=$orderid?> has been filled" required placeholder="$0.00" style="width:400px;">
					    </div>
					</div>
					<div class="row">
					    <div class="medium-4 small-12 columns">
						  <textarea name="text" style="width:400px; height:250px;">
							<?=$strFileData?>
						  </textarea>
					    </div>
					</div>
					<div class="row">
						<div class="medium-4 small-12 columns">
							<input name="orderid" type="hidden" value="<?=$orderid?>">
							<input type="submit" class="button" value="Send email now" />
						</div>
					</div>
					<strong style="color:#FFF;"><?=$strError?></strong>
				</form>
			</div>

		</div>


























		<!--SIDEBAR AREA-->
        <div class="small-12 medium-6 columns">

<!--
			<div class="panel radius" id="UploadPicDiv">
				<p>Current BTC Rate </p>
				<?php
					$intRate = funct_Billing_GetRate();
					$intCurrentCryptoAtCurrentRate = $fiat_to_convert / $intRate ;

				?>
				<h4>$<?=number_format($intRate,2)?></h4><br>

				<?=number_format($intCurrentCryptoAtCurrentRate,8)?> BTC estimated
			</div>
-->

			<!-- LIST ALL RECEIPTS -->
			<div class="panel radius" id="UploadPicDiv">

				<!-- List all uploads here from uploads table -->
				<?php
		        $query="SELECT * ".
				" FROM ".TBL_UPLOADS." ".
				" WHERE orderid=$orderid  ".
				" ORDER BY date_added DESC " ;
				//echo "SQLstmt=$query<br>";
				$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
				$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
				?>
				<?=$nr?> total receipt uploads <br>

				<?
				while($row = mysqli_fetch_assoc($rs)){

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
				<a href="<?=$strFilePath?>" target="_new"><img src="<?=$strFileSrc?>" width="100" height="100" /></a>
				<?php

				}//end while

				?>
		       </div>


		       <!-- LIST ALL RECEIPTS -->
			<div class="panel radius" id="UploadPicDiv">

				<!-- List all uploads here from uploads table -->
				<?php
		        $query="SELECT * ".
				" FROM ".TBL_UPLOADS." ".
				" WHERE usernameid=$user_id AND frompage='id' ".
				" ORDER BY date_added DESC " ;
				//echo "SQLstmt=$query<br>";
				$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
				$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
				?>
				<?=$nr?> total id uploads <br>

				<?
				while($row = mysqli_fetch_assoc($rs)){

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
				<a href="<?=$strFilePath?>" target="_new"><img src="<?=$strFileSrc?>" width="100" height="100" /></a>
				<?php

				}//end while

				?>
		       </div>


				<div class="panel radius">
				<!-- LIST MEMBER INFO -->
			     <h3><a href="member_details.php?id=<?=$user_id?>">Member ID <?=$user_id?></a></h3>

					<!-- !FORM update_balance -->
					<!-- Update the user's balance with arbitrary amount that is typed in -->
				         <div class="name-field">

				             <div class="row">
				                 <div class="small-4 columns">
				     <form data-abide action="?id=<?=$orderid?>&userid=<?=$user_id?>&do=update_balance" method="POST">
				                     <label for="left-label" class="left inline">member balance</label>
				                 </div>
				                 <div class="small-8 columns">
				                     <input name="balance_entered" type="number" id="balance_entered" value="<?=$user_balance?>" required placeholder="BTC Amt">
				                     <input name="userid" type="hidden" value="<?=$user_id?>">
				                     <input name="orderid" type="hidden" value="<?=$orderid?>">
				                 </div>
				             </div>
							 <div class="row">
							     <div class="small-12 columns">
				                     <button type="submit" class="tiny">Update Balance with arbitrary amount</button>
				     </form><br>

<!-- 				     Add buttons that update the balance with the correct amount -->
				     <?php if($crypto_sold) {$balance_new=$balance_prev+$crypto_sold;} ?>
<!-- 				     !CALL $strDo FILL_ORDER_TXN -->
				     <a href="?id=<?=$orderid?>&do=FILL_ORDER_TXN&uid=<?=$user_id?>&balance_new=<?=$balance_new?>&balance_prev=<?=$balance_prev?>" class="button tiny">Update Balance to <?=$balance_new?> and fulfill order in Txn Table</a>


					 <?php
					 	echo "<br><label>Balance before this purchase was: $balance_prev</label>";
					 	if($crypto_sold) {$balance_new=$balance_prev+$crypto_sold; echo "<label>Balance after this purchase should be: $balance_new</label>";}
					 	if($user_balance!=$balance_prev) {echo "<label>Original user balance was $balance_prev, current is $user_balance Why??</label>";}
					 	echo "<br><label>CC wallet: $cc_wallet_address</label>";
					 ?>


<!--
 							<form name="sendstatusemail" id="sendstatusemail" method="post" action="?id=<?=$orderid?>&do=sendstatusemail">
								<input name="orderid" type="hidden" value="<?=$orderid?>">
								<input name="email" type="hidden" value="<?=$strEmail?>">
								<input type="submit" class="button tiny" value="Send Status Email" />
							</form>
-->



				                 </div>
							 </div>
				         </div>










				</div>


            	<div class="panel radius">
 				<form data-abide name="checkout" id="checkout" method="post" action="?do=addstatus&id=<?=$orderid?>">
					<h3>Add Status</h3>
					<div class="row">
					    <div class="small-12 columns">
                        <select name="statustype">
				        <?php

				        $query="SELECT * ".
						" FROM ".TBL_STATUSES." ".
						" WHERE status_type='Order' or status_type='Verification'".
						" ORDER BY status_type ASC, status_name ASC" ;
						echo "SQLstmt=$query<br>";
						$rs = mysqli_query($DB_LINK, $query);
						$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
						//begin loop
						while($row = mysqli_fetch_assoc($rs)){
							$status_id=							$row["status_id"];
							$status_type=						$row["status_type"];
						    $status_name=						$row["status_name"];
				        ?>
<!-- 						<input type="radio" name="statustype" value="<?=$status_id?>" id="<?=$status_id?>" required><label for="<?=$status_id?>"><?=$status_name?></label><br> -->
						
						
						<option value="<?php echo $status_id ?>"><?php echo $status_type." - ".$status_name ?></option>
<!-- 						<option value="<?php echo $status_id ?>"<?php if($status_id) { echo " selected " ;} ?>><?php echo $status_name ?></option> -->

						
						
						<?php

						}//end while

						?>
                        </select>

					    </div>
					</div>
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
					<strong style="color:#FFF;"><?=$strError?></strong>
				</form>
				</div>


			    <!-- BEGIN LOWER STATUS HISTORY AREA -->
			    <div class="row">
			        <div class="small-12 columns">
						<table>
					        <tr>
								<td align="left"><h4>Status</h4></td>
					          	<td align="left"><h4>Notes</h4></td>
								<td align="left"><h4>Date</h4></td>
					        </tr>

				        <?php

				        $strOrderBySTMT = " date DESC ";
				        $query="SELECT * ".
						" FROM ".TBL_ORDERS_DETAILS." ".
						" WHERE orderid=$orderid ".
						" ORDER BY $strOrderBySTMT " ;
						//echo "SQLstmt=$query<br>";
						$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
						$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
						//begin loop
						while($row = mysqli_fetch_assoc($rs)){
							$intType_status=						$row["status"];
							$strType_statustxt=						$row["statustxt"];
						    $strNotes_status=						$row["notes"];
							$strDate_status= 						$row["date"];
				        ?>
				        <tr>
							<td align="left"><?=$strType_statustxt?></td>
				          	<td align="left"><?=$strNotes_status?></td>
				          	<td align="left"><?=$strDate_status?></td>
				        </tr>
						<?php

						}//end while

						?>
				    	</table>

			            </div>
			        </div>
			        <!-- END LOWER STATUS HISTORY AREA -->




















        </div>
        <!-- END SIDEBAR AREA -->

	</div>
    <!-- END MAIN 6+6 AREA -->
    
    
    
    
    
    
    
    <!-- 					LIST OF TRANSACTIONS RELATED TO THIS ORDER HERE -->
			    <div class="row">
			        <div class="small-12 columns">

		            <h3>Transactions</h3><br>
		            
		                 <!--transactions-->
						 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
							<thead>
					        <tr>
					          	<td align="left" width="6%"><strong>Tx</strong></td>
								<td align="left" width="45%"><strong>Date</strong></td>
								<td align="left" width="6%"><strong>Credit</strong></td>
								<td align="left" width="6%"><strong>Prev</strong></td>
								<td align="left" width="6%"><strong>New</strong></td>
					          	<td align="left" width="6%"><strong>Status</strong></td>
					          	<td align="left" width="19%"><strong>Status</strong></td>
					          	<td align="left" width="6%"><strong>Amount</strong></td>
					        </tr>
					  		</thead>
							<tbody id="tabledata">
							<?php 
							if($user_id){
								$strDo= "include";
								$sortby="top";
								$order_id = $orderid;
								$intType = "transactions_order";
								//$intLastMSGID = 0; 
								$intMaxRecords = 100 ; //get from top
								$intRecID = false;
								$intUserID_viewer = $user_id ; 
								if($intShowEditMod){$intMod="1";}
								include __ROOT__.ADMIN_MOD_LOADCONTENT ;
							}
							?>
							</tbody>
					    </table>

			            </div>
			        </div>
			        
<!-- 					END LIST OF TRANSACTIONS RELATED TO THIS ORDER HERE -->





<script src="/js/jquery.js"></script>
<script src="/js/foundation.min.js"></script>
<script src="/js/foundation/foundation.abide.js"></script>
<script>
  $(document)
  .foundation()
  .foundation('abide', {
    patterns: {
		alpha: /[a-zA-Z]+/,
	    alpha_numeric : /[a-zA-Z0-9]+/,
	    integer: /-?\d+/,
	    number: /-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?/,

	    // generic password: upper-case, lower-case, number/special character, and min 8 characters
	    password : /(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/

    }
  });
</script>


</body>
</html>