<?php

require "session.php";

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

<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buy Bitcoins<?=TITLE_END?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />

    <link rel="stylesheet" href="css/foundation.css" />
    <link rel="stylesheet" href="css/custom.css" />

    <script src="js/modernizr.js"></script>
    <script src="<?=JQUERYSRC?>" type="text/javascript"></script>
    <script>

      <?php /* HOME PAGE JS SETUP - TODO: MOVE TO INCLUDE 'home.js' */ ?>

      $(document).ready(function(){
        <? if(MODE_UPGRADE){ ?>
          $('#upgrademodal').foundation('reveal', 'open');
        <? } ?>
      });

      function jsfunct_join() {
        document.getElementById('checkout').action = '<?=CODE_DO?>?do=join';
        var okSoFar=true
        if (document.checkout.password.value=="") {
          okSoFar=false
          //alert("Enter a Password Please. at least 8 characters, 1 upper case, 1 number, 1 symbol")
          document.checkout.password.focus()
          return false;
        }
        if (okSoFar==true) {
          document.getElementById('checkout').submit();
        }
      }

    </script>

  </head>
  <body>

    <?php require "hud.php"; ?>

    <p><br></p><br>


    <div class="row">

      <!-- BEGIN 1st COLUMN 5 WIDE -->
      <div class="medium-4 columns hide-for-small-only">
        <img src="img/wallet.png" width="300" height="300" />
      </div>
      <!-- END 1st COLUMN -->

      <!-- BEGIN 2nd COLUMN 5 WIDE -->
      <div class="small-8 medium-8 columns">

        <h3>Get your free wallet</h3>

        <small>Already registered? <a href="/signin.php">Sign in</a></small><br><br>

        <form data-abide name="checkout" id="checkout" method="post" action="<?=CODE_DO?>?do=join">
          <input name="email" type="email" required id="email" placeholder="your email" value="<?=$strEmail?>">
          <small class="error">Please enter a proper email.</small>
          <button type="submit" class="button small">Create Free Wallet</button><br>
          <strong style="color:#FFF;"><?=$strError?></strong>
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
              <li>mcrypt php module</li>
              <li><a href="">Code Igniter PHP framework</a> (comes included)</li>
              <li>Works with the Ez Wallet BitCoin API</li>
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
