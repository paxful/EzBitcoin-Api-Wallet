<?php
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

// copies records from one table to the other table

$strLiveFlag = 1 ;
$intTotalCount = 0;
$intCopiedCount = 0;

echo "running script <br>";

$DB_LINK1 = mysqli_connect("localhost","root","littles","cc_from") ; //to be copied FROM
$DB_LINK2 = mysqli_connect("localhost","root","littles","cc_to") ; //to be copied TO


	//check database to see if member is in the database 
	$query="SELECT * FROM ".TBL_USERS." ORDER BY id ASC " ;
	//echo "SQL STMNT = " . $query .  "<br><br>";
	$rs = mysqli_query($DB_LINK1, $query);
	//if(mysqli_num_rows($rs)>0){ 
	while( $row = mysqli_fetch_assoc($rs) ){
	
	//loop through all member records and see if address is in text file
		$intTotalCount = $intTotalCount + 1 ;
		$intUserID=					$row["id"]; 				//important
		$intBalanace=				$row["balance"]; 				//important
		$intBalanceBTC=				$row["balance_btc"]; 			//important
		$intBalancePrev=			$row["balance_prev"]; 		//important
		$intBalanceCurr=			$row["balance_curr"]; 			//important
		$strNameFirst=				$row["first_name"];
		$strNameLast=				$row["last_name"];

			//echo "$intUserID " ;
			//select from second database
			$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
			//echo "SQL STMNT = " . $query .  "<br>";
			$rs2 = mysqli_query($DB_LINK2, $query) or die(mysqli_error()); $row2=mysqli_fetch_array($rs2) ;
			$intBalanace2=				$row2["balance"]; 				//important
			$intBalanceBTC2=			$row2["balance_btc"]; 			//important
			$intBalancePrev2=			$row2["balance_prev"]; 		//important
			$intBalanceCurr2=			$row2["balance_curr"]; 			//important
			
			
			
			if($intBalanceBTC!=$intBalanceBTC2){
			
				if(!$intBalanace){$intBalanace=0;}
				if(!$intBalanceBTC){$intBalanceBTC=0;}
				if(!$intBalancePrev){$intBalancePrev=0;}
				if(!$intBalanceCurr){$intBalanceCurr=0;}	
				
				echo "<h3>$intUserID</h3> <strong>$strNameFirst $strNameLast</strong> bal=$intBalanace  btc=$intBalanceBTC prev=$intBalancePrev curr=$intBalanceCurr" ;
			
				//update address with ''
				$query="UPDATE " . TBL_USERS . " SET ".
				"balance=$intBalanace, ".
				"balance_btc=$intBalanceBTC, ".
				"balance_prev=$intBalancePrev, ".
				"balance_curr=$intBalanceCurr ".
				"WHERE id=".$intUserID ;
				//echo "SQL STMNT = " . $query .  "<br>";
				if($strLiveFlag){
					mysqli_query($DB_LINK2, $query) or die(mysqli_error()); 
					echo " Copied! <br>";
				}else{ 
					echo " test not live <br>";
				}
				$intCopiedCount = $intCopiedCount + 1 ;
				
			}


		//echo "<br>";

	}//while loop

echo "<br><br> <strong>TOTAL=$intTotalCount - COPIEd=$intCopiedCount </strong>";



?>