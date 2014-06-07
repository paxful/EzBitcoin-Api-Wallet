<?
$strHomeLink = "/";
if(DETECT_USERID){ 
  $strHomeLink = PAGE_WALLET;
}
?>

<!--TOP NAVIGATION BAR-->
<div class="nav_bkg">
  <div class="row">
    <nav class="top-bar" data-topbar data-options="is_hover: false" style="background-color:#663300;">
      <ul class="title-area" style="background-color:#663300;">
        <li class="name">
          <h1><a href="<?=$strHomeLink?>" style="background-color:#663300;">Coin Cafe</a></h1>
        </li>
        <li class="toggle-topbar menu-icon">
          <a href="#"><span>Menu</span></a>
        </li>
      </ul>

        <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left">
      <? if(DETECT_USERID){ ?>
          <li><a href="<?=PAGE_WELCOME?>" style="background-color:#2ba6cb;">Buy Bitcoin</a></li>
            <li class="has-dropdown"><a href="#" style="background-color:#004080;">How To</a>
                <ul class="dropdown">
                    <li><a href="/buyinperson.php">Buy in Person (same day)</a></li>
                    <li><a href="/buybitcoinswithwire.php">Buy with a Wire Transfer (same day)</a></li>
                    <li><a href="/buybitcoinswithcash.php">Buy with Cash (next day)</a></li>
                    <li><a href="/buybitcoinswithcheck.php">Buy with Cashier's Check</a></li>
                </ul>
            </li>
      <? }else{ ?>
            <li class="has-dropdown"><a href="#" style="background-color:#663300;">Buy Bitcoins...</a>
                <ul class="dropdown">
                    <li><a href="/buyinperson.php">in Person (same day)</a></li>
                    <li><a href="/buybitcoinswithwire.php">with a Wire Transfer (same day)</a></li>
                    <li><a href="/buybitcoinswithcash.php">with Cash (next day)</a></li>
                    <li><a href="/buybitcoinswithcheck.php">with Cashier's Check</a></li>
    <!--                 <li><a href="<?=PAGE_ERROR?>?error=debitpassword">with a Debit Card</a></li> -->
                </ul>
            </li>
      <? } ?>
            <li><a href="/faq.php" style="background-color:#663300;">F.A.Q.</a></li>
            <li><a href="/fees.php" style="background-color:#663300;">Fees/Policies</a></li>
    <!--         <li><a href="/policies.php" style="background-color:#663300;">Policies</a></li> -->
            <li><a href="/contactus.php" style="background-color:#663300;">Contact</a></li>
            <!--<li><a href="/aboutus.php" style="background-color:#663300;">About Us</a></li>-->

        <?php if(DETECT_USERID){ 
        
        $strUserIDhud = funct_ScrubVars(DETECT_USERID);
        
          //Get User Data from DataBase
          $query="SELECT * FROM " . TBL_USERS . " WHERE id = ". $strUserIDhud ;
          //echo "SQL STMNT = " . $query .  "<br>";
          $rs = mysqli_query($DB_LINK, $query) or die(mysqli_error()); $row=mysqli_fetch_array($rs) ;
          $intUserID_hud=           $row["id"];
          $Password_hud=            $row["password"];
          $Email_hud=             $row["email"];
          $strFirstName_hud=          $row["first_name"];
          $strLastName_hud=         $row["last_name"];
          $strPhone_hud=            $row["cellphone"];
          
          //$strWelcomeName = $Email_hud ;
          if($strFirstName_hud){$strWelcomeName = $strFirstName_hud ;}
        
        ?>
          <li><a href="<?=PAGE_WALLET?>" style="background-color:#2ba6cb;"><strong>My Wallet</strong></a></li>
                <li class="divider"></li>
                
                <li class="has-dropdown">
                <a href="<?=PAGE_SETTINGS?>" style="background-color:#2ba6cb;">Account</a>
                <ul class="dropdown">
                  <li><a href="<?=PAGE_SETTINGS?>">Settings</a></li>
                  <li><a href="<?=PAGE_VERIFY?>">Verify</a></li>
                </ul>
            </li>
          
          
          <li><a href="<?=CODE_DO?>?do=logout" style="background-color:#663300;">Logout</a></li>
          
        <?php }else{ ?>
        
          <li><a href="/signin.php" style="background-color:#2ba6cb;">Sign In</a></li>
                <li class="divider"></li>
          <li><a href="/signup.php" style="background-color:#2ba6cb;">Create Account</a></li>
        
        <?php } ?>
        
        
        <?php

        //get prices of btc for hud
        $strCrypto="btc"; $strExchange=RATE_HUD_EXCHANGE;
        $intRate_hud = funct_Billing_GetRate($strCrypto,$strExchange);
        ?>

        <?php if($intRate_hud){ ?><li><a href="#" style="background-color:#663300;"><strong>BTC $<?=number_format($intRate_hud,2)?></strong></a></li><?php } ?>

        </ul>
          
        </section>
        <!-- END LEFT NAV BAR SECTION -->
        
        
    </nav>
  </div>
</div>
<div id="window_alert" class="alertwindow" style="display:none; position:fixed; left:5px; top:5px; width:90%; min-height:60px; z-index:10;"><span id="window_alert_txt" class="txtRPG_Actions"></span></div>

<? if(SERVERTAG!="dev"){ //do not use this code on dev ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-46817860-1', 'coincafe.com');
  ga('send', 'pageview');

</script>

<!-- begin olark code -->
<script data-cfasync="false" type='text/javascript'>/*<![CDATA[*/window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){
f[z]=function(){
(a.s=a.s||[]).push(arguments)};var a=f[z]._={
},q=c.methods.length;while(q--){(function(n){f[z][n]=function(){
f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={
0:+new Date};a.P=function(u){
a.p[u]=new Date-a.p[0]};function s(){
a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){
hd="head";return["<",hd,"></",hd,"><",i,' onl' + 'oad="var d=',g,";d.getElementsByTagName('head')[0].",j,"(d.",h,"('script')).",k,"='",l,"//",a.l,"'",'"',"></",i,">"].join("")}var i="body",m=d[i];if(!m){
return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){
b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{
b.contentWindow[g].open()}catch(w){
c[e]=d[e];o="javascript:var d="+g+".open();d.domain='"+d.domain+"';";b[k]=o+"void(0);"}try{
var t=b.contentWindow[g];t.write(p());t.close()}catch(x){
b[k]=o+'d.write("'+p().replace(/"/g,String.fromCharCode(92)+'"')+'");d.close();'}a.P(2)};ld()};nt()})({
loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
/* custom configuration goes here (www.olark.com/documentation) */
olark.identify('7586-250-10-9268');/*]]>*/</script><noscript><a href="https://www.olark.com/site/7586-250-10-9268/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a></noscript>
<!-- end olark code -->
<? } ?>
