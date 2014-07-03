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

	<meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width">

    <link rel="icon" type="image/png" href="img/favicon.png" />

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/bootstrapValidator.min.css"/>

    <?php if(!$intJquery){ $intJquery=1;?><script src="<?php echo JQUERYSRC?>" type="text/javascript"></script><?php } ?>


    <SCRIPT LANGUAGE="JavaScript" type="text/javascript">

    $(document).ready(function() {
        $('#signin').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {

                password: {
                    message: 'The password is not valid',
                    validators: {
                        notEmpty: {
                            message: 'The password is required and cannot be empty'
                        },
                        stringLength: {
                            min: 6,
                            max: 30,
                            message: 'The password must be more than 6 and less than 30 characters long'
                        },
                        regexp: {
                            regexp: /^[a-zA-Z0-9_!@#$%^^&*]+$/,
                            message: 'The password can only consist of alphabetical, number and symbols like !@#$%^&*'
                        }
                    }
                },

                email: {
                    validators: {
                        notEmpty: {
                            message: 'The email is required and cannot be empty'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address'
                        }
                    }
                }

            }
        });


        $('#forgot').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {

                forgot_email: {
                    validators: {
                        notEmpty: {
                            message: 'The email is required and cannot be empty'
                        },
                        emailAddress: {
                            message: 'The input is not a valid email address'
                        }
                    }
                }

            }
        });



    });

</SCRIPT>
    
	<title>Sign In | <?php echo WEBSITENAME?></title>
    
</head>

<body onload="<?php echo $strOnBodyLoadJS?>">

<?php require "hud.php"; ?>

<div class="container-fluid">

    <!-- BEGIN MAIN AREA 8+4 COLUMNS -->
    <div class="row">

        <!-- LEFT SIDE USERNAME AND PASSWORD AREA -->
        <div class="col-xs-12 col-md-8">
avbar
            <h3>Sign in to <?php echo WEBSITENAME?></h3>
            <h4 style="color:darkred;"><?php echo $strError?></h4>

            <form role="form" name="signin" id="signin" method="post" action="<?php CODE_DO?>?do=login&page=signin.php">
                <div class="row">
                    <div class="col-xs-8 col-md-6">
                        <div class="form-group">
                            <input class="form-control" name="email" type="email" required id="email" placeholder="your email" value="<?php $strEmail?>">
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="password" type="password" required id="password" placeholder="select password">
                        </div>

                        <?
                        if(SECURITY_CAPCHACHECK){
                            include __ROOT__.'/inc/capcha/recaptchalib.php' ;
                            $publickey = SECURITY_CAPCHA_PUBLICKEY ;
                            echo recaptcha_get_html($publickey);
                        }
                        ?>
                        <br>

                        <button class="btn btn-primary btn-block" type="submit" onClick="return jsfunct_join();">Sign In </button>


                        <strong style="color:#FFF;"><?php echo $strError?></strong>
                        <br>
                        <h3>Don't have an account? <a href="signup.php">Sign up</a></h3>
                    </div>
                    <div class="col-xs-4 col-md-6 columns">
                    </div>
                </div>
            </form>


            <p></p><br><p></p>


            <h4>Forgot Password ?</h4>

                 <form role="form" action="<?php echo CODE_DO."?do=forgotpassword"?>&page=signin.php" method="POST" name="forgot" id="forgot">

                     <div class="row">
                        <div class="col-xs-6">

                            <div class="form-group">
                                <input class="form-control" type="text" required placeholder="email" name="forgot_email" id="forgot_email" style="" autocomplete="true" value="<?php echo $FormRegEmail?>">
                            </div>

                            <strong class="txtError"><?php echo $strError_forgot?></strong>
                            <h5 id="error_forgot"></h5>

                            <a href="javascript:;" onClick="return validateForgotPasswordForm();" style="text-decoration:none;">
                                <input type="submit" class="btn btn-primary btn-block" value="Forgot password" style="" />
                            </a>
                        </div>
                    </div>
                 </form>


        </div>
        <!-- END LEFT SIDE USERNAME AND PASSWORD AREA -->


        <!--SIDEBAR AREA-->
        <div class="col-xs-12 col-md-4">
            <a href="signup.php"><img src="img/wallet.png" /></a>
        </div>
        <!--END SIDEBAR AREA-->


    </div>
    <!-- END MAIN AREA 8+4 COLUMNS -->

</div>

<script src="js/bootstrap.min.js"></script>
<script src="js/angular.min.js"></script>
<script src="js/bootstrapValidator.min.js"></script>


</body>
</html>