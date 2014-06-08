<?php
$strHomeLink = PAGE_HOME;
if(DETECT_USERID){ 
  $strHomeLink = PAGE_WALLET;
}
?>

<!--TOP NAVIGATION BAR-->
<div class="">
  <div class="row">
    <nav class="top-bar" style="background-color:#ccc;">
      <ul class="title-area" style="background-color:#ccc;">
        <li class="name">
          <h1><a href="<?=$strHomeLink?>" style="background-color:#666;">Wallet</a></h1>
        </li>
        <li class="toggle-topbar menu-icon">
          <a href="#"><span>Menu</span></a>
        </li>
      </ul>

        <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left">
            <!-- <li><a href="faq.php" style="background-color:#663300;">F.A.Q.</a></li> -->

        <?php if(DETECT_USERID){
        
        $strUserIDhud = funct_ScrubVars(DETECT_USERID);
        
          //Get User Data from DataBase
          $query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $strUserIDhud ;
          //echo "SQL STMNT = " . $query .  "<br>";
          $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
          $intUserID_hud=           $row["id"];
          $Password_hud=            $row["password"];
          $Email_hud=               $row["email"];
          $strFirstName_hud=        $row["first_name"];
          $strLastName_hud=         $row["last_name"];
          $strPhone_hud=            $row["cellphone"];
          
          //$strWelcomeName = $Email_hud ;
          if($strFirstName_hud){$strWelcomeName = $strFirstName_hud ;}

        ?>
          <li><a href="<?=PAGE_WALLET?>" style="background-color:#cc6633;"><strong>My Wallet</strong></a></li>
                <li class="divider"></li>
                
                <li class="has-dropdown">
                <a href="<?=PAGE_SETTINGS?>" style="background-color:#cc6633;">Account</a>
                <ul class="dropdown">
                  <li><a href="<?=PAGE_SETTINGS?>">Settings</a></li>
                  <li><a href="<?=PAGE_VERIFY?>">Verify</a></li>
                </ul>
            </li>
          
          
          <li><a href="<?=CODE_DO?>?do=logout" style="background-color:#cc6633;">Logout</a></li>
          
        <?php }else{ ?>
        
          <li><a href="signin.php" style="background-color:#ff9900;">Sign In</a></li>
                <li class="divider"></li>
          <li><a href="signup.php" style="background-color:#ff9900;">Create Account</a></li>
        
        <?php } ?>
        
        
        <?php

        //get prices of btc for hud
        $strCrypto="btc"; $strExchange=RATE_HUD_EXCHANGE;
        $intRate_hud = funct_Billing_GetRate($strCrypto,$strExchange);
        ?>

        <?php if($intRate_hud){ ?><li><a href="#" style="background-color:#666;"><strong>BTC $<?=number_format($intRate_hud,2)?></strong></a></li><?php } ?>

        </ul>
          
        </section>
        <!-- END LEFT NAV BAR SECTION -->
        
        
    </nav>
  </div>
</div>

<div id="window_alert" class="alertwindow" style="display:none; position:fixed; left:5px; top:5px; width:90%; min-height:60px; z-index:10;"><span id="window_alert_txt" class="txtRPG_Actions"></span></div>

