<?php 

error_reporting(E_ALL & ~E_NOTICE);

require "session.php";

$strDo = 				trim($_GET['do']);
//echo "strDo= " . $strDo;
ini_set('display_errors',1); error_reporting(E_ERROR | E_PARSE);
//$intDebugFlag = true ;

switch ($strDo){
	
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

		
} //End Switch Statement

?>