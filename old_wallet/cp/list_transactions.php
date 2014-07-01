<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

$strDO = 					trim($_GET["do"]); 
$intMemberID = 1 ; //coincafe

//we need to authenticate the logged in user somehow


//Get QueryString Values
$strFilter = 				trim($_GET['f']);
$sortby = 					trim($_GET['sort']);
$strSearchTXT = 			trim($_GET["searchtxt"]);
$strSearchType = 			trim($_GET["searchtype"]);

$intType = "orders";
$intMaxRecords = 100 ;//get from top

$strErrorMSG = 					trim($_GET["msg"]); //set error msg manually in query


if($strDO=="update"){
	
	$intMemberID = 				funct_FormVarSecurity($_POST['userid']);

	//Update Database 
	$query = "UPDATE ".TBL_USERS." SET " .
	//"password='$strPassword', ".
	"email='$strEmail', ".
	"cellphone='$strCellPhone', ".
	"country_phonecode='$strCellPhone_code', ".
	"first_name='$strNameFirst', ".
	"last_name='$strNameLast', ".
	"address='$strAddress', ".
	"address2='$strAddress2', ".
	"cityname='$strCity', ".
	"state='$strState', ".
	"postal='$strPostal', ". 
	"country_id=$intCountryID, ".
	"currency_id=$intCurrencyID, ".
	
	"sendlocked=$intSendLocked, ".
	"balance_btc=$strCryptoBalance, ".
	
	"paypalemail='$strPayPalEmail', ".
	"btc_address='$strBTCaddress', ".
	"bank_account='$strBankaccount', ".
	"bank_routing='$strBankrouting', ".
	"lastlogin=NOW() ".
	"WHERE id = $intMemberID " ;
	//echo "SQL STMNT = " . $query .  "<br>"; //"cityid=$intCityID, ". //"city='$strCityName', ". //"regionid=$intCityRegionID, ". 
	$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
	
	
}




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> </title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
<meta charset="utf-8">
<meta name="description" content="">
<meta name="viewport" content="width=device-width">

<!-- Favicon -->
<link rel="icon" type="image/png" href="/img/favicon.png" />

<link rel="stylesheet" href="/wallet/css/foundation.css" />
<script src="/wallet/js/modernizr.js"></script>

<script src="<?=JQUERYSRC?>" type="text/javascript"></script>
<link rel="stylesheet" href="/wallet/css/web.css" />



<script type="text/javascript">

	$(document).ready(function(){
	
		var bSuppressScroll = false ;
		intLastRecord = 0 ;
		intNewestID = 0 ;
		intNewestID_old = 0 ;
		intTotalRecords = 0 ;
		intTotalRecordsShowing = 0 ;
		
		strLoadContentAjaxURL = "&do=ajax&maxrecords=<?=$intMaxRecords?>&type=<?=$intType?>&f=<?=$strFilter?>&sort=<?=$sortby?>&searchtxt=<?=$strSearchTXT?>&searchtype=<?=$strSearchType?>";


		//Call more records on scroll to bottom //  this is jumpy ...
		$(window).scroll(function(){
			if ( ( $(window).scrollTop() +  $(window).height() == $(document).height()  ) && bSuppressScroll == false ){
				//alert('at end of page');
				jsfunct_LoadMoreRecords();
				window.bSuppressScroll = true;
				
			}
		}); //close $(window).scroll(function(){
		
		//autorefresh 
		//var auto_refresh = setInterval( function () { jsfunct_LoadLatestRecords(); }, <?=REFRESH_WALLET_SEC * 1000 ?>); // refresh every * milliseconds 10000= 10 seconds

	
	}); //close ready function


	
	
	//!function LoadMoreRecords to load more records at end
	function jsfunct_LoadMoreRecords(){
		$("#loader_bottom").fadeIn(1000);
		strPostString = "<?=ADMIN_MOD_LOADCONTENT?>?last_msg_id=" + intLastRecord + strLoadContentAjaxURL ;
		//alert(strPostString);
		
		$.post(strPostString,
			function(data){
				if (data != "") {
					//code to get color box working with ajax content
					
					var $html = $(data);
					$('#tabledata').append( $html ) ;
					window.bSuppressScroll = false; //allow more records to be loaded
					//strNoMoreRecords = "<div class='cell1  box_chestfade' style=''><span class='txtRPG_Actions'>no more records</span></div>";
					
					//$("#totalrecords").html(intTotalRecords);
					$("#totalrecordsshowing").html(intTotalRecordsShowing);


					if(intLastRecord>=intTotalRecords){
						//jsfunct_Alert('files loaded'); // last=' + intLastRecord + ' ttl=' + intTotalRecords );
						soundManager.play('new member','/sounds/beep.mp3');//play sound
	
					}
				}
				//$('div#last_msg_loader').empty();
		}); $("#loader_bottom").fadeOut(2000);
	}; //close last_msg_funtion		
	

	
	
	
	//!function LoadLatestRecords to load records at beginning 
	function jsfunct_LoadLatestRecords(){ 
	//pass lastest record id or latest int timestamp and get back records most recent and add them to the page
		//alert('newestid= ' + intNewestID);
		if(intNewestID){ intNewestID_old=intNewestID ;} //store first record id, freshest
		
		$.post("<?=ADMIN_MOD_LOADCONTENT?>?do=ajax&newest_msg_id=" + intNewestID + strLoadContentAjaxURL , function(data){
			if (data != "") {
				//var $html = $(data);
				//prepend container
				$('#tabledata').prepend( data );
				$("#totalrecords").html(intTotalRecords);
				$("#totalrecordsshowing").html(intTotalRecordsShowing);
			}
		});
		
		//if js id is greater than it was before the get then play a sound and show alert
		if(intNewestID > intNewestID_old){ //new transaction incoming so... give feedback
			//document.getElementById('window_get_alert_txt').innerHTML = 'You Got Coin!';
			//$('#window_get_alert').fadeIn(500).delay(intDelay).fadeOut(500); //animate it
			soundManager.play('new member','/sounds/beep.mp3');//play sound
		}
		
		//update total records with dynamic var
		//functjs_Refresh_RecordsCount();
	}

</script>


</head>

<body onLoad="<?=$strOnBodyLoadJS?>" class="" style="">


	<div class="row">
		<div class="small-12 columns"> 
			<form name="searchform" id="searchform" method="get" action="?do=search">
	        	<input style="width:30%;" name="searchtxt" id="searchtxt" type="text" value="<?=$strSearchText?>" placeholder="Search"> 
	    		<select name="searchtype" style="width:20%;">
		          <option value="txid">TXID</option>
		          <option value="userid">userid</option>
		          <option value="username">user name</option>
		          <option value="amount">amount</option>
		          <option value="address">address</option>
		        </select>
		        <input name="do" type="hidden" value="searchhud">
				<button type="submit" onClick="return jsfunct_Search();">search</button>
			</form>
        </div>
	</div>


<p></p>
<div class="row">
    <div class="small-12 columns"> 
		
		<h5>Filter: 
	    <a href="?f=all&sort=<?=$sortby?>">All</a> - 
	    
	    <a href="?f=receives&sort=<?=$sortby?>"><strong>Gets</strong></a> - 
	    <a href="?f=sends&sort=<?=$sortby?>"><strong>Sends</strong></a> 
	
	    <br>Sort: 
	    <a href="?sort=datenew&f=<?=$strFilter?>">Date New</a> / <a href="?sort=dateold&filter=<?=$strFilter?>">Old</a> --- 
	    <a href="?sort=amthigh2low&f=<?=$strFilter?>">Amt High</a> / <a href="?sort=amtlow2high&filter=<?=$strFilter?>">Low</a> ---
	    <a href="?sort=namea&f=<?=$strFilter?>">Name A</a> / <a href="?sort=namez&filter=<?=$strFilter?>">Name Z</a>
		</h5>
		<h4> Records: <span id="totalrecords"></span> <span id="totalrecordsshowing"></span> </h4>
	    
    </div>
</div>


	<!-- BEGIN MAIN+SIDEBAR CONTENT AREA 8+4 COLUMNS -->
	<div class="row">
	
	    <!-- BEGIN MAIN CONTENT AREA 8 OF 12 COLUMNS -->
		<div class="small-12 medium-12 large-12 columns">
            
                 <!--transactions-->
				 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
					<thead>
			        <tr>
			          	<td align="left" width="50%"><h5></h5></td>
						<td align="left" width="20%"><h5>Date</h5></td>
			          	<td align="left" width="10%"><h5>Amount</h5></td>
			          	<td align="left" width="20%"><h5>Type</h5></td>
			        </tr>
			  		</thead>
					<tbody id="tabledata">
					<?php 
					if($intMemberID){ //chestid specified and not a new chest
						$strDo = "include";
						//$sortby = "top";
						$intType = "transactions"; //files - get from top
						//$intLastMSGID = 0; 
						$intMaxRecords = 100 ; //get from top
						$intRecID = false;
						//$intUserID_viewer = $intMemberID ; 
						if( $intShowEditMod){$intMod="1" ;}
						include __ROOT__.LOADCONTENT ;
					}
					?>
					</tbody>
			    </table>

		</div>
	    <!-- END MAIN CONTENT AREA 8 OF 12 COLUMNS -->

	</div>



	    	
	<p></p><p></p><p></p><br><br>
	<div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;"><center>
		<div id="loader_bottom" class="loader_anim" style="display:none;">
		    <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
		    loading...</span>
		</div></center>
	</div>


    <script src="/wallet/js/jquery.js"></script>
    <script src="/wallet/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>

</body>
</html>