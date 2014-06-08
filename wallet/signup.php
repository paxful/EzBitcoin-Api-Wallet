<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);

require "session.php";

$strError = 			(funct_GetandCleanVariables($_GET["error"])); //set error msg manually in query
$strEmail = 			(funct_GetandCleanVariables($_GET["email"])); //set error msg manually in query
$strPhone = 			(funct_GetandCleanVariables($_GET["phonenumber"])); //set error msg manually in query
$strFirst = 			(funct_GetandCleanVariables($_GET["firstname"])); //set error msg manually in query
$strLast = 				(funct_GetandCleanVariables($_GET["lastname"])); //set error msg manually in query
$strAddress = 			(funct_GetandCleanVariables($_GET["address"])); //set error msg manually in query

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
	<meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width">
   
	<? if(!$intJquery){ $intJquery=1;?><script src="<?=JQUERYSRC?>" type="text/javascript"></script><? } ?>

    <link rel="stylesheet" href="css/foundation.css" />
    <script src="js/modernizr.js"></script>
	
<SCRIPT LANGUAGE="JavaScript">
	<!--
	function jsfunct_join() {

		var okSoFar=true

		if (document.signup.email.value=="") {
			alert("Enter your email address please.")
			document.signup.email.focus(); okSoFar=false; return false;
		}
		
		if (document.signup.phonenumber.value=="") {
			alert("Enter a working mobile phone number Please. You will have to confirm it later.")
			document.signup.phonenumber.focus(); okSoFar=false; return false;
		}
		
		if (document.signup.firstname.value=="") {
			alert("Enter your first name.")
			document.signup.firstname.focus(); okSoFar=false; return false;
		}
		
		if (document.signup.lastname.value=="") {
			alert("Enter your last name.")
			document.signup.lastname.focus(); okSoFar=false; return false;
		}
		/*
		if (document.signup.address.value=="") {
			alert("Enter your mailing address.")
			document.signup.address.focus(); okSoFar=false; return false;
		}
		*/
		if (document.signup.password.value=="") {
			alert("Enter a Password Please. at least 8 characters, 1 upper case, 1 number, 1 symbol")
			document.signup.password.focus(); okSoFar=false; return false;
		}

		if (document.signup.password.value!=document.signup.password2.value) {
			alert("Passwords do not match")
			document.signup.password2.focus(); okSoFar=false; return false;
		}

		if (okSoFar==true) {
			document.getElementById('signup').submit();
		}
		
	  //return true;
	}
	
	//-->
</SCRIPT>
    
	<title>Sign Up</title>
    
</head>

<body onload="<?=$strOnBodyLoadJS?>">

<?php require "hud.php"; ?>

<p></p>	

<div class="row">
	<div class="small-12 medium-8 columns">
	
		<h3>Sign Up for <?=WEBSITENAME?></h3>
		<h4><?=$strError?></h4>

        <form data-abide name="signup" id="signup" method="post" action="<?=CODE_DO?>?do=join&page=/signup.php">
			<div class="row">
			    <div class="small-8 medium-6 columns">
            <div class="firstname-field">
              <input name="firstname" type="text" required id="firstname" placeholder="first name (must match your ID)" value="<?=$strFirst?>">
              <small class="error">Enter first name</small>
            </div>
            <div class="lastname-field">
              <input name="lastname" type="text" required id="lastname" placeholder="last name (must match your ID)" value="<?=$strLast?>">
              <small class="error">Enter last name</small>
            </div>
            <div class="phone-field">
              <input name="phonenumber" type="text" required id="phonenumber" placeholder="mobile phone number" value="<?=$strPhone?>">
              <small class="error">Enter valid phone</small>
            </div>
            <!--<input name="address" type="text" required id="address" placeholder="address" value="<?=$strAddress?>">-->
            <div class="email-field">
              <input name="email" type="email" required id="email" placeholder="your email" value="<?=$strEmail?>">
              <small class="error">Enter valid email</small>
            </div>
            <div class="password-field">
              <input name="password" type="password" required id="password" placeholder="select password">
              <small class="error">Passwords must be at least 8 characters with 1 capital letter, 1 number, and one special character.</small>
            </div>
            <div class="password2-field">
              <input name="password2" type="password" required id="password2" placeholder="confirm password">
              <small class="error">Passwords must match.</small>
            </div>
            <button type="submit" class="small">Sign Up & Create Free Wallet</button>
<!--             <strong style="color:#FFF;"><?=$strError?></strong> -->
            <br><small>Already have an account? <a href="/signin.php">Sign in</a></small>
			    </div>
			</div>
		</form>		
		
	</div>	
	
<!--SIDEBAR AREA-->        
	<div class="small-12 medium-4 columns">
		<?php //include __ROOT__."/panel_side_contact.php"; ?>
	</div>
	
	
</div>


<script src="js/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script src="js/foundation/foundation.abide.js"></script>
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