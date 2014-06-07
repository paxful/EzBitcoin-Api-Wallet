<?
$strDo=($_GET['do']);
if($strDo=="searchhud"){

	include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

	
	//Get Form Post Data
	$strSearchText = funct_ScrubVars($_GET['searchtxt']);
	$strSearchText = mysqli_real_escape_string($DB_LINK, $strSearchText);
	
	$strSearchType = funct_ScrubVars($_GET['searchtype']);
	$strSearchType = mysqli_real_escape_string($DB_LINK, $strSearchType);
	
	if($strSearchType=="name"){ header( 'Location: '.PATH_ADMIN.'members.php?searchtype='.$strSearchType.'&searchtxt='.$strSearchText ); die(); }
	if($strSearchType=="userid"){ header( 'Location: '.PATH_ADMIN.'members.php?searchtype='.$strSearchType.'&searchtxt='.$strSearchText ); die(); }
	if($strSearchType=="orderid"){ header( 'Location: '.PATH_ADMIN.'orders.php?searchtype='.$strSearchType.'&searchtxt='.$strSearchText ); die(); }
	if($strSearchType=="depositamt"){ header( 'Location: '.PATH_ADMIN.'orders.php?searchtype='.$strSearchType.'&searchtxt='.$strSearchText ); die(); }

}
?>

<!--TOP NAVIGATION BAR-->
<nav class="top-bar" data-topbar data-options="is_hover: false" style="background-color:#666666;">
    <ul class="title-area" style="background-color:#666666;">
        <li class="name">
            <h1><a href="<?=PATH_ADMIN?>" style="background-color:#000000;">CC Admin</a></h1>
        </li>
        <li class="toggle-topbar menu-icon">
            <a href="#"><span>Menu</span></a>
        </li>
    </ul>

    <section class="top-bar-section">
    <!-- Left Nav Section -->
    <ul class="left">

		<?php if(DETECT_USERID){ 
		

		?>
			
		
<!-- 			<li><a href="<?=ADMIN_DASH?>" style="background-color:#2ba6cb;"><strong>DashBoard</strong></a></li> -->
            <li class="divider"></li>
            <li><a href="<?=PATH_ADMIN."members.php"?>" style=""><strong>Users</strong></a></li>
            <li><a href="<?=PATH_ADMIN."orders.php"?>" style=""><strong>Orders</strong></a></li>
            <li><a href="<?=PATH_ADMIN."transactions.php"?>" style=""><strong>TXNS</strong></a></li>
            <li><a href="https://5.153.60.162/list_transactions.php" style="" target="_blank"><strong>Ams</strong></a></li>


            
            <li class="divider"></li>
			<li><a href="<?=CODE_DO?>?do=logout" style="background-color:#663300;">Logout</a></li>
			
			
			<li class="has-form right"> 
            	<div class="row collapse">
            		<form name="searchform" id="searchform" method="get" action="hud.php?do=search">
	            		
	            		<div class="row">
		            		<div class="small-6 columns"> 
				            	<input name="searchtxt" id="searchtxt" type="text" value="<?=$strSearchText?>" placeholder="Search"> 
				            </div> 
				            <div class="small-4 columns"> 
			            		<select name="searchtype">
						          <option value="name">Name</option>
						          <option value="userid">userid</option>
						          <option value="orderid">OrderID</option>
						          <option value="depositamt">Fiat to Deposit</option>
						        </select>
		            		</div>
				            <div class="small-2 columns">
								<input name="do" type="hidden" value="searchhud">

				                 <button type="submit" onClick="return jsfunct_Search();">GO</button>
				            </div>
	            		</div>
	            		
            		</form>
	            </div> 
            </li>
			
					
		<?php } ?>
		
		
		<?php

		//get prices of btc for hud
		$strCrypto="btc"; $strExchange=RATE_HUD_EXCHANGE;
		$intRate_hud = funct_Billing_GetRate($strCrypto,$strExchange);
		?>

		<?php if($intRate_hud){ ?><li><a href="<?=PAGE_HOME?>" style="background-color:#663300;" target="_blank"><strong>$<?=number_format($intRate_hud,2)?></strong></a></li><?php } ?>

    </ul>
      
    </section>
    <!-- END LEFT NAV BAR SECTION -->
    
    <SCRIPT LANGUAGE="JavaScript">
	<!--
	function jsfunct_Search() {

		var okSoFar=true

		if (document.searchform.searchtxt.value=="") {
			alert("Enter a search term please.")
			document.searchform.searchtxt.focus(); okSoFar=false; return false;
		}

		if (okSoFar==true) {
			document.getElementById('searchform').submit();
		}
		
	  return true;
	}
	

	//-->
</SCRIPT>
    
</nav>

<div id="window_alert" class="alertwindow" style="display:none; position:fixed; left:5px; top:5px; width:90%; min-height:60px; z-index:10;"><span id="window_alert_txt" class="txtRPG_Actions"></span></div>

