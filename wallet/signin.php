<?php
require "inc/session.php";


//$strErrorMSG = 			trim($_GET["msg"]); //set error msg manually in query
$strErrorMSG = 			(funct_GetandCleanVariables($_GET["msg"])); //set error msg manually in query
$strEmail = 			(funct_GetandCleanVariables($_GET['email']));
$strError = 			(funct_GetandCleanVariables($_GET['error']));
$strError_forgot = 		(funct_GetandCleanVariables($_GET['error_forgot']));


$_SESSION['last_post'] = time();
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

//if(!$strErrorMSG){ $strErrorMSG=$strError;}

if(!$strEmail){ $strEmail= $_COOKIE[SESSION_EMAIL] ; }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png" />
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

		if (document.signup.password.value=="") {
			alert("Enter a Password Please.")
			document.signup.password.focus(); okSoFar=false; return false;
		}

		if (okSoFar==true) {
			document.getElementById('signup').submit();
		}
		
	  return true;
	}
	
	function validateForgotPasswordForm() {

		var okSoFar=true

		if (document.forgot.forgot_email.value=="") {
			alert("Enter your email address please.")
			document.forgot.forgot_email.focus(); okSoFar=false; return false;
		}

		if (okSoFar==true) {
			document.getElementById('forgot').submit();
		}
		
	  return true;
	}
	
	//-->
</SCRIPT>
    
	<title>Sign In<?=TITLE_END?></title>
    
</head>

<body onload="<?=$strOnBodyLoadJS?>">

<?php require "hud.php"; ?>

<p></p>

<!-- BEGIN MAIN AREA 8+4 COLUMNS -->
<div class="row">

    <!-- LEFT SIDE USERNAME AND PASSWORD AREA -->
	<div class="small-12 medium-8 columns">
	
		<h3>Sign in to <?=WEBSITENAME?></h3>
		<h4 style="color:darkred;"><?=$strError?></h4>
		
		<form name="signup" id="signup" method="post" action="<?=CODE_DO?>?do=login&page=signin.php">
            <div class="row">
                <div class="small-8 medium-6 columns">
                    <input name="email" type="email" required id="email" placeholder="your email" value="<?=$strEmail?>">
                    <input name="password" type="password" required id="password" placeholder="select password">

                    <?
                    if(SECURITY_CAPCHACHECK){
                    	include __ROOT__.'/inc/capcha/recaptchalib.php' ;
						$publickey = SECURITY_CAPCHA_PUBLICKEY ;
						echo recaptcha_get_html($publickey);
					}
                    ?>

                    <button type="submit" onClick="return jsfunct_join();">Sign In </button>
                    <strong style="color:#FFF;"><?=$strError?></strong>
                    <br><h3>Don't have an account? <a href="signup.php">Sign up</a></h3>
                </div>
                <div class="small-4 medium-6 columns">
                </div>
            </div>
		</form>
		
		
		
		
		<p></p><br><p></p>
		

		<h4>Forgot Password ?</h4>

             <form action="<?=CODE_DO."?do=forgotpassword"?>&page=signin.php" method="POST" name="forgot" id="forgot">
                
                 <div class="row">
				    <div class="large-6 columns">
                        <input type="text" required placeholder="email" name="forgot_email" id="forgot_email" style="" autocomplete="true" value="<?=$FormRegEmail?>">
                        
                        <strong class="txtError"><?=$strError_forgot?></strong>
                        <h5 id="error_forgot"></h5>
                        

                        
                        <a href="javascript:;" onClick="return validateForgotPasswordForm();" style="text-decoration:none;">
                            <input type="submit" class="button" value="Forgot password" style="" />
                        </a>
                    </div>
                </div>
             </form>
		
	
	</div>	
    <!-- END LEFT SIDE USERNAME AND PASSWORD AREA -->
	
	
    <!--SIDEBAR AREA-->
	<div class="small-12 medium-4 columns">
        <a href="signup.php"><img src="img/wallet.png" /></a>
	</div>
    <!--END SIDEBAR AREA-->
	
	
</div>
<!-- END MAIN AREA 8+4 COLUMNS -->



<script src="js/jquery.min.js"></script>
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
	    //password : /(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/

    }
  });
</script>


</body>
</html>