<?php

//check to see if user is logged in and an admin
if(!DETECT_USERID){  
	
	header( 'Location: index.php?error=must be logged into the site' ); die(); //must be logged in

}
	//Get User Data from DataBase
	$query="SELECT * FROM " . TBL_USERS . " WHERE id = ". DETECT_USERID ;
	//echo "SQL STMNT = " . $query .  "<br>";
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
	$intUserID_hud=					$row["id"];
	$Password_hud=					$row["password"];
	$Email_hud=						$row["email"];
	$strFirstName_hud=				$row["first_name"];
	$strLastName_hud=				$row["last_name"];
	$strPhone_hud=					$row["cellphone"];
	$intAdmin=						$row["admin"];
	//echo "admin=".$intAdmin."<br>";
	
	if($_COOKIE["cpinp"]==SECURITY_ADMIN_PASSWORD){
		$strWelcomeAdmin = 1;
	}else{ 
		$strWelcomeAdmin=0 ;
		$strError ="wrong admin password" ;
	}
	
	//if so set welcome variable to on
	if($intAdmin){ 
		$strWelcomeAdmin=1 ;
	}else{ 
		$strWelcomeAdmin=0 ;
		$strError ="you are not an admin" ;
	}

	if($strWelcomeAdmin<1 AND $strServer!="dev"){ //if on dev bypass checks
		header( 'Location: index.php?error='.$strError ); die(); //must be logged in
	}

?>