<?php 

?>

<!doctype html>
<html class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>api</title>

      <link rel="stylesheet" href="/css/foundation.css" />
      <script src="/js/modernizr.js"></script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/img/favicon.ico" />
    
  </head>    
  <body style="">
    

<p></p>
      
<div class="row">
    <div class="small-12 columns">
        <h2>Ez Api</h2>
        <h4>Bitcoin RESTful API RPC Wrapper - PHP</h4><br>

        <div>Made to be the simplest, fastest way to get your own bitcoin wallet server up and running. <br><br>
            Can be used for multiple projects.
            The Api mimicks blockchain.info's almost exactly. It returns JSON. <br>
            We include a sample wallet web app as a sample application. If you are looking for tons of features or airtight security, look elsewhere.
            For simplicity and speed towards a MVP this works.
        </div><br>

        <ul>
            <li><a href="install/readme_installbitcoind.txt">Installing BitcoinD Guide - step by step</a></li>
            <li><a href="install/readme_securebitcoind.txt">How to Secure your BitcoinD server Guide</a></li>
            <li><a href="install/db_api.sql.txt">API Database sql install</a></li>
            <li><a href="install/db_wallet.sql.txt">Wallet Database sql install</a></li>
            <li><a href="/merchant/">Merchant API</a> - <a href="/cp/">Control Panel</a></li>
            <li><a href="/wallet/">Wallet</a></li>

        </ul>

        <br><br><br>
        If you'd like us to add features then let us know.
        BTC talks:

    </div>

</div>

<script src="/js/jquery.min.js"></script>
<script src="/js/foundation.min.js"></script>
<script src="/js/foundation/foundation.abide.js"></script>
<script src="/js/foundation/foundation.reveal.js"></script>

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
                //password : /(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/
            }
        });

  </body>
</html>
