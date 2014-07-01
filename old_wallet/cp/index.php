<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

ob_start(); //so we can redirect even after headers are sent


include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

$strDo = 				trim($_GET["do"]); 
$errorMSG = 			htmlspecialchars(trim($_GET["error"])); //set error msg manually in query

//log them in automatically
if($_COOKIE["cpinp"]==SECURITY_ADMIN_PASSWORD){ header( 'Location: '.PATH_ADMIN.'dashboard.php' ); die(); }


if($strDo=="login"){
	
	
		//Get Form Post Data
		$username = 			htmlspecialchars(trim(stripslashes($_POST['email'])));
		$password = 			htmlspecialchars(trim(stripslashes($_POST['password'])));
		$strReturnURL = 		trim($_POST["returnurl"]);
		//$remember = 			stripslashes($_POST["remember"]);
		//echo "username = " . $username . "<br>"; echo "password = " . $password . "<br>"; echo "userid=".DETECT_USERID."<br>";
		$username = mysqli_real_escape_string($DB_LINK, $username);
		$password = mysqli_real_escape_string($DB_LINK, $password);
		$strReturnURL = mysqli_real_escape_string($DB_LINK, $strReturnURL);
		
		if(!$username OR !$password) { 
			$errorMSG = "....wassap.. ha8x0r???? admin has been emailed";
		}
			
		$intUserID = DETECT_USERID;

		if(!$intUserID){ //username failure
			$errorMSG = "we could not find your user record... may be a database issue. admin has been emailed";
		}

		//If username does not exist
		if($username != SECURITY_ADMIN_LOGIN){ //username failure
			$errorMSG = "username wrong";
		}
		
		//If username exists and password is incorrect then 
		if($password != SECURITY_ADMIN_PASSWORD){ //password failure
			$errorMSG = "Password is not right";
		}
		
		//If username and password are correct
		if($errorMSG==""){
		
			//write password
			setcookie("cpinp" , $password , COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
			header( 'Location: '.PATH_ADMIN.'dashboard.php' ); die();
		}else{ 
			//echo "error=".$errorMSG ;
		}
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
	<meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   
	<? if(!$intJquery){ $intJquery=1;?><script src="<?=JQUERYSRC?>" type="text/javascript"></script><? } ?>

    <link rel="stylesheet" href="/wallet/css/foundation.css" />
<link rel="stylesheet" href="/wallet/css/custom.css" />
    <script src="/wallet/js/modernizr.js"></script>
	
<SCRIPT LANGUAGE="JavaScript">
	<!--


	//-->
</SCRIPT>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>admin</title>
    
</head>

<body onload="<?=$strOnBodyLoadJS?>">

<?php // include __ROOT__."/inc/hud.php"; ?>

<p></p>	
<div class="row">

	<div class="medium-8 small-12 columns">
		<h1>Administration</h1>
		<h4><?=$errorMSG?></h4>
		<form name="checkout" id="checkout" method="post" action="?do=login">
		<h4><?=$strErrorMSG?></h4>
		<form name="checkout" id="checkout" method="post" action="<?=CODE_DO?>?do=login&page=/cp/">
			<p></p>
			<div class="row">
			    <div class="small-4 columns">
		          <input name="email" type="text" required id="email" placeholder="username" value="<?=$strEmail?>" style="width:200px;">
			    </div>
			</div>
			<div class="row">
			    <div class="small-4 columns">
		          <input name="password" type="password" required id="password" placeholder="password" style="width:200px;">
			    </div>
			</div>
			<div class="row">
				<div class="small-4 columns">
					<button type="submit">Sign In</button>
				</div>
			</div>
		</form>
	</div>
		
	<!--SIDEBAR AREA-->
	<div class="medium-4 small-12 columns">

	</div>
	
</div>


<script src="/wallet/js/jquery.js"></script>
<script src="/wallet/js/foundation.min.js"></script>
<script src="/wallet/js/foundation/foundation.abide.js"></script>
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