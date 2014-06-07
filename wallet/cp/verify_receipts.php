<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";


//Define Page Values
//$strThisPage = 		PAGE_SETTINGS;
$intUserID = 			DETECT_USERID;

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";


$strDO = 						trim($_GET["do"]);

$intType = "verify_deposits"; //IMPORTANT - THIS IS THE SWITCH THAT IS USED IN LOADCONTENT.PHP  -John
$intMaxRecords = 100 ;//get from top


if($strDO=="addstatus"){ //!$strDO addstatus
	
	$orderid = 						funct_ScrubVars($_POST["orderid"]);
	$intAmount = 					funct_ScrubVars($_POST["amount"]);
	$status_id = 					funct_ScrubVars($_POST["statustype"]);
	$strNotes = 					funct_ScrubVars($_POST["notes"]);

	if($status_id){ $strSQL = " , status_id=$status_id, status_text='" . $status_name . "' " ; }

	//update orders table with latest status of order
	$query="UPDATE " . TBL_ORDERS . " SET fiat_deposited=$intAmount $strSQL WHERE orderid=".$orderid;
	//echo "<label>SQL STMNT = " . $query .  "</label><br>";
	mysqli_query($DB_LINK, $query) or die(mysqli_error());

	//redirect them to the print page
	//header( 'Location: ?id='.$orderid."&msg=".$strErrorMSG  ); die(); //Make sure code after is not executed

}//end do=add


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

	<title>Verify Deposits</title>
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


	
	
	//function to load more records at end
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
	

	
	
	
	//function to load records at beginning 
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


<p><h1><a class="right" href="verify_receipts.php">NEXT</a></h1></p>

		<?php 

			$strDo= "include";
			$intType = "verify_receipts"; //files - get from top
			//$intLastMSGID = 0; 
			//$intMaxRecords = 100 ;//get from top
			$intRecID = false;
			//$intUserID_viewer = $intMemberID ; 
			if($intShowEditMod){$intMod="1";}
			include __ROOT__.ADMIN_MOD_LOADCONTENT ;

		?>



    <script src="/js/jquery.js"></script>
    <script src="/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>


</body>
</html>