<?php
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";    
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

// copies records from one table to the other table

$strLiveFlag = 1 ;
$intTotalCount = 0;
$intCopiedCount = 0;

echo "running script <br>";

$DB_LINK1 = mysqli_connect("localhost","root","mysql","cc_from") ; //to be copied FROM
$DB_LINK2 = mysqli_connect("localhost","root","mysql","cc_to") ; //to be copied TO


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
		$email=						$row["email"];
		$password=					$row["password"];

			//echo "$intUserID " ;
			//select from second database
			$query="SELECT * FROM " . TBL_USERS . " WHERE id = $intUserID ";
			//echo "SQL STMNT = " . $query .  "<br>";
			$rs2 = mysqli_query($DB_LINK2, $query) or die(mysqli_error()); $row2=mysqli_fetch_array($rs2) ;
			$email2=			$row2["email"]; 			//important
			$password2=			$row2["password"]; 			//important
			
			
			
//			if($password!=$password2){
			if($password2=="test1234"){
							
				echo "<h3>$intUserID</h3> <strong>$strNameFirst $strNameLast</strong> email=$email  pass=$password" ;
			
				//update address with ''
				$query="UPDATE " . TBL_USERS . " SET ".
				"email='$email', ".
				"password='$password' ".
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

echo "<br><br> <strong>TOTAL=$intTotalCount - COPIED=$intCopiedCount </strong>";



?>