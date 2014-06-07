<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
//$strThisPage = 		PAGE_SETTINGS;
$intUserID = 			DETECT_USERID;

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";


//Get QueryString Values
$strFilter = 				trim($_GET['f']);
$sortby = 					trim($_GET['sort']);
$strSearchTXT = 			trim($_GET["searchtxt"]);
$strSearchType = 			trim($_GET["searchtype"]);

$intType = "ordersdeposited";
$intMaxRecords = 100 ;//get from top



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

	<title>Orders</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
    <script src="/js/modernizr.js"></script>

<? if(!$intJquery){ $intJquery=1;?><script src="<?=JQUERYSRC?>" type="text/javascript"></script><? } ?>




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


<body onLoad="<?=$strOnBodyLoadJS?>">

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>


<p></p>


    	<h5>Filter: 
        <a href="orders_deposited.php?f=all&sort=<?=$sortby?>">All Orders</a> --- 
        
        <a href="orders_deposited.php?f=receipt&sort=<?=$sortby?>"><strong>Receipt Provided</strong></a> --- 
        <a href="orders_deposited.php?f=openorders&sort=<?=$sortby?>"><strong>Open Orders</strong></a> --- 
        <a href="orders_deposited.php?f=filledorders&sort=<?=$sortby?>"><strong>Filled Orders</strong></a> --- 
        <a href="orders_deposited.php?f=filledorderscheck&sort=<?=$sortby?>"><strong>Filled Check</strong></a> --- 
        <a href="orders_deposited.php?f=allchecks&sort=<?=$sortby?>"><strong>Checks w Deposits</strong></a> --- 

        <a href="orders_deposited.php?f=cash&sort=<?=$sortby?>">Cash</a> --- 
        <a href="orders_deposited.php?f=bofa&sort=<?=$sortby?>">BofA</a> --- 
        <a href="orders_deposited.php?f=capitalone&sort=<?=$sortby?>">Capital One</a> --- 
        <a href="orders_deposited.php?f=chase&sort=<?=$sortby?>">Chase</a> --- 
        <a href="orders_deposited.php?f=wire&sort=<?=$sortby?>">Wire</a> --- 
        <a href="orders_deposited.php?f=inperson&sort=<?=$sortby?>">In Person</a>
        <a href="orders_deposited.php?f=check&sort=<?=$sortby?>">Check</a>

        <br>Sort:         
        <a href="orders_deposited.php?sort=datenew&f=<?=$strFilter?>">Date New</a> / <a href="orders_deposited.php?sort=dateold&filter=<?=$strFilter?>">Old</a> --- 
        <a href="orders_deposited.php?sort=amthigh2low&f=<?=$strFilter?>">Amt High</a> / <a href="orders_deposited.php?sort=amtlow2high&filter=<?=$strFilter?>">Low</a> ---
        <a href="orders_deposited.php?sort=namea&f=<?=$strFilter?>">Name A</a> / <a href="orders_deposited.php?sort=namez&filter=<?=$strFilter?>">Name Z</a>
    	</h5>
    	<h4> Records: <span id="totalrecords"></span> <span id="totalrecordsshowing"></span> </h4>
    

<p></p>


<!-- Replace all this code with loadcontent.php module call -->
	 <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
		<thead>
	        <tr>
				<td align="left" width=""><strong>Order ID</strong></td>
				
				<td align="left" width=""><strong>Status</strong></td>
				<td align="left" width=""><strong>Receipts</strong></td>
				
				
<!-- 				<td align="left" width=""><strong>Order Date (ET) /<br>Upload Date</strong></td> -->
				<td align="left" width="" style="width:100px"><strong>Order Date</strong></td>
				<td align="left" width=""><strong>Customer</strong></td>
				<td align="left" width=""><strong>Transfer Type</strong></td>
<!-- 	          	<td align="left" width=""><strong>Revenue /<br>Sold</strong></td> -->
<!-- 	          	<td align="left" width=""><strong>Method / Bank<br>Bank Fee / Tip</strong></td> -->
<!-- 	          	<td align="left" width=""><strong>To Deposit /<br>To Convert</strong></td> -->
	          	<!--<td align="left" width=""><strong>BTC or USD?</strong></td>-->
	          	<td align="left" width=""><strong>Deposited</strong></td>
	          	<td align="left" width=""><strong>Fiat to<br>Deposit</strong></td>
<!-- 	          	<td align="left" width=""><strong>Curr. BTC /<br>Curr. Rate</strong></td> -->
<!-- 	          	<td align="left" width=""><strong>BTC to Send /<br>Rate <small>last receipt uploaded</small></strong></td> -->
<!-- 	          	<td align="left" width=""><strong>Which Wallet /<br>Miner Fee</strong></td> -->
<!-- 	          	<td align="left" width=""><strong>BTC<br>Outflow</strong></td> -->
<!-- 	          	<td align="left" width=""><strong>Hash DateTime (UTC) /<br># Confirms</strong></td> -->
	        </tr>
  		</thead>
		<tbody id="tabledata">
		<?php 

			$strDo= "include";
			//$intType = "orders"; //files - get from top
			//$intLastMSGID = 0; 
			//$intMaxRecords = 100 ;//get from top
			$intRecID = false;
			//$intUserID_viewer = $intMemberID ; 
			if($intShowEditMod){$intMod="1";}
			include __ROOT__.ADMIN_MOD_LOADCONTENT ;

		?>
		</tbody>
    </table>

	
	    	
	<p></p><p></p><p></p><br><br>
	<div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;"><center>
		<div id="loader_bottom" class="loader_anim" style="display:none;">
		    <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
		    loading...</span>
		</div></center>
	</div>


    <script src="/js/jquery.js"></script>
    <script src="/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>


</body>
</html>