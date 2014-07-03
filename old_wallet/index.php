<?php

require "inc/session.php";

// If we are on production, ensure page is ssl encrypted for entering in credit card info
// Todo: move to global include
if(SERVERTAG=="hg" || SERVERTAG=="prod"){ 
  if($_SERVER["HTTPS"] != "on"){
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
  }
}else{ 
  error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
}

if(DETECT_USERID){ 
  header("Location: ".PAGE_WALLET);
}

$strEmail = (funct_GetandCleanVariables($_GET['email']));
$strError = (funct_GetandCleanVariables($_GET['error']));

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo WEBSITENAME?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon.png" />
      <script src="<?php echo JQUERYSRC?>" type="text/javascript"></script>

      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/custom.css" rel="stylesheet" />
      <link rel="stylesheet" href="css/bootstrapValidator.min.css"/>



    <script>

      $(document).ready(function(){
        <?php if(MODE_UPGRADE){ ?>
          $('#upgrademodal').modal('show');
        <?php } ?>
      });

      $(document).ready(function() {
          $('#registerForm').bootstrapValidator({
              message: 'This value is not valid',
              feedbackIcons: {
                  valid: 'glyphicon glyphicon-ok',
                  invalid: 'glyphicon glyphicon-remove',
                  validating: 'glyphicon glyphicon-refresh'
              },
              fields: {

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
      });

    </script>

  </head>
  <body>

    <?php require "hud.php"; ?>

    <div class="row container-fluid">

      <!-- BEGIN 1st COLUMN 5 WIDE -->
      <div class="col-xs-4  hide-for-small-only">
        <img src="img/wallet.png" width="300" height="300" />
      </div>
      <!-- END 1st COLUMN -->

      <!-- BEGIN 2nd COLUMN 5 WIDE -->
      <div class="col-xs-8 col-md--8">

        <h3>Get your free wallet</h3>

        <small>Already registered? <a href="<?php echo PAGE_SIGNIN ; ?>">Sign in</a></small><br><br>

        <form role="form" name="registerForm" id="registerForm" method="post" action="<?php echo CODE_DO?>?do=join">
          <div class="form-group">
          <input class="form-control" name="email" type="text" id="email" placeholder="your email" value="<?php $strEmail?>">
          </div>

          <button type="submit" class="btn btn-primary btn-block">Create Free Wallet</button><br>
          <strong style="color:#FFF;"><?php echo $strError?></strong>
        </form>

          <h4>Open Source BitCoin  Wallet</h4>

          <div>Made to be the simplest, fastest way to deploy a bitcoin wallet and introducing bitcoin to a whole new class of developers. </div>
          <br><br>

          <h4>Features</h4>
          <ul>
              <li>Supports over 100 fiat currencies.</li>
              <li>Supports Multiple Crypto Currencies.</li>
              <li>Runs on code igniter php framework for small footprint, easy install, secure database orm and mvc model.</li>
          </ul>

          <br><br>

          <h4>Security</h4>
          <ul>
              <li>Hashed passwords</li>
              <li>Protection against brute Force Logins</li>
              <li>Database ORM model used prepared statements to avoid sql injection attacks</li>
              <li>Protection against Security Scanners such as acutex etc..</li>
          </ul>

          <br><br>

          <h4>Requirements</h4>
          <ul>
              <li><a href="">LAMP</a> - Linux Apache MySql PHP platform. (comes installed by default on most linux servers)</li>
              <li>PDO mysql php module</li>
              <li>MYSQLI php module</li>
              <li>mcrypt php module</li>
              <li><a href="">Code Igniter PHP framework</a> (comes included)</li>
              <li>Ez Wallet BitCoin API or Blockchain.info API</li>
          </ul>

          <br><br>

          <h4>Install Guide</h4>
          <ul>
              <li><a href="http://github.com/">Get the Source Code Here</a></li>
          </ul>

          <br><br>

          <div>Please join us in making this solve even more problems for people</div>
          <br>
          <ul>
              <li><a href="http://github.com/">GitHub</a></li>
              <li><a href="http://bitcointalk.org">BitCoinTalk.org thread</a></li>
              <li><a href="http://bitcointalk.org">Reddit thread</a></li>
              <li>Donate to the cause </li>
          </ul>

      </div>
      <!-- END 2nd COLUMN -->


    </div>


    <!--MAIN CONTENT AREA-->

    <br><br><br><br><br><br>


    <script src="js/bootstrap.min.js"></script>
    <script src="js/angular.min.js"></script>
    <script src="js/bootstrapValidator.min.js"></script>

  </body>
</html>
