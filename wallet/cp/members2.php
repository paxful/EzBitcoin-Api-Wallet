<?php 
error_reporting(E_ERROR | E_PARSE); //ini_set('display_errors',2);
include $_SERVER['DOCUMENT_ROOT']."/inc/session.php";

//Define Page Values
//$strThisPage = 		PAGE_SETTINGS;
$intUserID = 			DETECT_USERID;

//check to see if user is logged in and an admin
include $_SERVER['DOCUMENT_ROOT']."/inc/checklogin.php";

//Get QueryString Values
$strDo = 				trim($_GET['do']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

	<title>Members</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.png" />
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="/css/foundation.css" />
<link rel="stylesheet" href="/css/custom.css" />
    <link rel="stylesheet" href="/webicons-master/webicons.css" />
    <script src="/js/modernizr.js"></script>

	<? if(!$intJquery){ $intJquery=1;?><script src="<?=JQUERYSRC?>" type="text/javascript"></script><? } ?>

<script type="text/javascript">

	$(document).ready(function(){
	

	
	}); //close ready function


</script>

</head>


<body onLoad="<?=$strOnBodyLoadJS?>">

<?php // include __ROOT__."/inc/hud.php"; ?>
    <p></p>

    <div class="row">
        <a href="index.php">Return to Admin Panel</a><br><br>
    </div>



        <table width="100%" border="0" align="left" cellpadding="2" cellspacing="0">
	        <tr>
				<td align="left"><h5>ID</h5></td>
				<td align="left"><h5>Name</h5></td>
	          	<td align="left"><h5>Contact</h5></td>
	          	<td align="left"><h5>Balance</h5></td>
	          	<td align="left"><h5>Joined</h5></td>
	          	<td align="left"><h5>Last</h5></td>
	        </tr>	
	
        <?php
        
        
        $query="SELECT * FROM ".TBL_USERS." WHERE id>0 AND (balance_btc>0 OR balance_btc<0) ORDER BY balance_btc DESC" ;
		//echo "SQLstmt=$query<br>";
		$rs = mysqli_query($DB_LINK, $query) or die(mysqli_error());
		$nr = 	mysqli_num_rows($rs); //Number of rows found with LIMIT in action
		$query0 = "Select FOUND_ROWS()";
		$rs0 = 	mysqli_query($DB_LINK, $query0) or die(mysqli_error());
		$row0 =	mysqli_fetch_array($rs0);
		$nr0 = 	$row0["FOUND_ROWS()"]; //Number of rows found without LIMIT in action
		if (($nr0 < 10) || ($nr < 10)){$b = $nr0;}else{$b = ($cps) + $rpp;}
		
		//begin loop
		while($row = mysqli_fetch_assoc($rs)){

		    $intMemberID=					$row["id"];
		    $strFromName=					$row["first_name"];
		    $strFromNameLast= 				$row["last_name"]; 
		    $strEmail= 						$row["email"]; 
		    $strPhone= 						$row["cellphone"]; 
		
		    $intAmtUSD= 					$row["balance"];
		    $strWalletTo= 					$row["btc_address"];
			$strDate= 						$row["date_joined"];
            
            //Added by John
            $balance_btc=                   $row["balance_btc"];
            if ($balance_btc==0) {$balance_btc="";}
            $lastlogin=                     $row["lastlogin"];
        ?>
        <tr><!-- SHOW USER_ID, NAME, EMAIL, PHONE, BTC BALANCE, DATE JOINED AND LAST LOGIN -->
			<td align="left"><a href="member_details.php?id=<?=$intMemberID?>"><?=$intMemberID?></a></td>
			<td align="left"><strong><?=$strFromName?> <?=$strFromNameLast?></strong></td>
			<td align="left"><?=$strEmail?> / <?=$strPhone?></td>
			<td align="left"><strong><?=number_format($balance_btc,8) ?></strong></td>
			<td align="left"><?=$strDate?></td>
			<td align="left"><?=$lastlogin?></td>
        </tr>
		<?php
		
		}//end while
		
		?>
    	</table>

    <script src="/js/jquery.js"></script>
    <script src="/js/foundation.min.js"></script>
    <script>
      $(document).foundation();
    </script>



</body>
</html>