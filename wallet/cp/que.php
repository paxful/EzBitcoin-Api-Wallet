<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
//$strThisPage =    PAGE_SETTINGS;

//check to see if user is logged in and an admin
include __ROOT__.PATH_ADMIN."checklogin.php";
//$intUserID =      DETECT_USERID;

//Get QueryString Values
$strDO =          trim($_GET["do"]);

$intMemberID =        funct_ScrubVars($_GET['userid']);
$strFilter =          funct_ScrubVars($_GET['f']);
$sortby =             funct_ScrubVars($_GET['sort']);
$strSearchTXT =       funct_ScrubVars($_GET["searchtxt"]);
$strSearchType =      funct_ScrubVars($_GET["searchtype"]);

$intType = "transactionque"; //IMPORTANT - THIS IS THE SWITCH THAT IS USED IN LOADCONTENT.PHP  -John
$intMaxRecords = 300 ;//get from top

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

  <title>Transactions Queue</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width">
  
    <link rel="stylesheet" href="/css/foundation.css" />
    <link rel="stylesheet" href="/css/custom.css" />
    <link rel="stylesheet" href="/css/web.css" />

    <script src="/js/web.js"></script>
    <script src="<?=JQUERYSRC?>" type="text/javascript"></script>
    <script src="/js/soundmanager2-nodebug-jsmin.js"></script><script> soundManager.url = '/js/soundmanager2.swf'; soundManager.onready(function() {});</script>
    <? $intJquerySoundManager=1; ?>
    <script type="text/javascript">

      $(function(){
      
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

      function jsfunct_verifykyc(user_id){ 
        
        //ajax_do
        $.get("/cp/ajax_do.php?do=verifykyc&id=" + user_id, function(data){
          /* alert(data); */
          if (data = "ok") { new_msg = "Verified!"; alert(new_msg); }
        });
        
      }

    </script>

</head>

<body onLoad="<?=$strOnBodyLoadJS?>">

<?php include __ROOT__.PATH_ADMIN."hud.php"; ?>

  <p>Transaction Queue <?=$intMemberID?></p>

  <div class="row">
    <h5>Filter: 
      <a href="verify_kyc.php?f=all&sort=<?=$sortby?>">All in Queue</a> --- 
      <a href="verify_kyc.php?f=waiting&sort=<?=$sortby?>">Waiting</a> --- 
      <a href="verify_kyc.php?f=done&sort=<?=$sortby?>">Done</a> --- 
      <br>Sort:
      <a href="verify_kyc.php?sort=datenew&f=<?=$strFilter?>">Date New</a> / <a href="verify_kyc.php?sort=dateold&filter=<?=$strFilter?>">Old</a> --- 
      <a href="verify_kyc.php?sort=balancehigh2low&f=<?=$strFilter?>">Amount High</a> / <a href="verify_kyc.php?sort=balancelow2high&filter=<?=$strFilter?>">Low</a> ---
    </h5>
  </div>

  <p></p>
  <h4> Records: <span id="totalrecords"></span> <span id="totalrecordsshowing"></span> </h4>

  <!-- Replace all this code with loadcontent.php module call -->
  <table width="100%" border="0" align="left" cellpadding="3" cellspacing="0">
    <thead>
      <tr>
        <td align="left"><h4></h4></td>
        <td align="left"><h4>Queue</h4></td>
        <td align="left"><h4>Trans</h4></td>
        <td align="left"><h4>Type</h4></td>
        <td align="left"><h4>Status</h4></td>
        <td align="left"><h4>Date</h4></td>
      </tr>
    </thead>
    <tbody id="tabledata">
    <?php

      $strDo= "include";
      $intType = "transactionque";
      //$intLastMSGID = 0; 
      //$intMaxRecords = 100 ;//get from top
      $intRecID = false;
      $intUserID_viewer = $intMemberID ; 
      if($intShowEditMod){$intMod="1";}
      include __ROOT__.ADMIN_MOD_LOADCONTENT ;

    ?>
    </tbody>
  </table>

  <div style="height:100px;"></div>
  <p></p><p></p><p></p><br><br>
  <div style="position:fixed; bottom:100px; width:100%; text-align:center; z-index:11;">
    <center>
      <div id="loader_bottom" class="loader_anim" style="display:none;">
        <span style="position:absolute; left:10px; bottom:100px; width:100%; text-align:center;" class="txtNewsBubble">
          loading...
        </span>
      </div>
    </center>
  </div>

  <script src="/js/foundation.min.js"></script>
  <script>
    $(document).foundation();
  </script>

</body>
</html>