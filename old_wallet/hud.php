<?php
$strHomeLink = PAGE_HOME;
if(DETECT_USERID){ 
  $strHomeLink = PAGE_WALLET;
}
?>

<!--TOP NAVIGATION BAR-->
<nav class="navbar navbar-default" role="navigation">
    <div class="container-fluid">

        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?=$strHomeLink?>"><?=WEBSITENAME?></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active"><a href="<?php echo PAGE_WALLET ;?>">Wallet</a></li>
            </ul>


            <?php if(DETECT_USERID){

            $strUserIDhud = funct_GetandCleanVariables(DETECT_USERID);

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
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="<?php echo PAGE_SETTINGS ?>" class="dropdown-toggle" data-toggle="dropdown">Hi <?php echo $Email_hud ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo PAGE_SETTINGS ?>">Settings</a></li>
                        <li class="divider"></li>
                        <li><a href="<?php echo CODE_DO."?do=logout" ?>">Logout</a></li>
                    </ul>
                </li>
            </ul>
            <?php }else{ ?>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="signin.php">Sign In</a></li>
                <!--
                <li class="divider"></li>
                <li><a href="signup.php">Create Account</a></li>-->
            </ul>
            <?php } ?>

        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<div id="window_alert" class="alertwindow" style="display:none; position:fixed; left:5px; top:5px; width:90%; min-height:60px; z-index:10;"><span id="window_alert_txt" class="txtRPG_Actions"></span></div>
