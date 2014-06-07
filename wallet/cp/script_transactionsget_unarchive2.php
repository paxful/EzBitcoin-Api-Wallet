<?php
//error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    

error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

$strLiveFlag = 1 ;

$file = __ROOT__.PATH_TEMP.'list_transactions.json' ;
$intTotalCount = 0;
$intFoundCount = 0;
$intKeptCount = 0;
$intUnArchivedCount = 0 ;


//SELECT * FROM `tbl_transactions` WHERE walletaddress_sentto>"" OR walletaddress_from>"" GROUP BY walletaddress_sentto, walletaddress_from


$query="SELECT * FROM ".TBL_TRANSACTIONS." ".
" WHERE walletaddress_sentto>'' OR walletaddress_from>'' GROUP BY walletaddress_sentto, walletaddress_from ";
//echo "SQLstmt=$query<br>";
$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
$nr = mysqli_num_rows($rs); //Number of rows found with LIMIT in action

$intRowCount=0;
while($row = mysqli_fetch_assoc($rs)){

   $strTo=				$row["walletaddress_sentto"];
   $strFrom=			$row["walletaddress_from"];
   
   $strName=			$row["sender_name"];
   $intUserID=			$row["user_id"];
   $intUserID_to=		$row["user_id_sentto"];
   $strType=			$row["type"];
   $intCryptoAmt=		$row["crypto_amt"];

   $intTotalCount = $intTotalCount + 1 ;
	
   echo $strTo." - ".$strType." - ".$strFrom." - ".$strName." - ".$intCryptoAmt ;

	//unarchive that address
	if($strLiveFlag){ 
		if($strTo){
			echo " unarchiving... ".$strResponse." " ;
			$strResponse = funct_Billing_UnArchiveAddress($strTo); 
			if($strResponse==$strTo){ 
				echo " <strong>DONE</strong>" ; 
				$intUnArchivedCount = $intUnArchivedCount +1 ;
			}
		}
		if($strFrom){
			echo " unarchiving... ".$strResponse." " ;
			$strResponse = funct_Billing_UnArchiveAddress($strFrom); 
			if($strResponse==$strFrom){ 
				echo " <strong>DONE</strong>" ; 
				$intUnArchivedCount = $intUnArchivedCount +1 ;	
			}
		}
	}

	echo " <br>" ;

}//end for each loop through array

echo "<br><br> <strong>TOTAL=$intTotalCount -  UnArchived=$intUnArchivedCount </strong>";

/* */
?>