<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>api</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.ico" />

    <link rel="stylesheet" href="foundation/css/foundation.css" />
    <script src="foundation/js/modernizr.js"></script>

</head>
<body style="">


<div class="row">
    <div class="small-12 columns">
        <h2>EzBit Api</h2>
        <h4>Bitcoin RESTful API JSONRPC Wrapper - PHP</h4><br>

        <div>Made to be the simplest, fastest way to get your own bitcoin wallet server up and running. Optimized for simplicity and speed towards a MVP. We include a sample wallet web app as a sample application.</div>
        <br>

        <h4>Features</h4>
        <ul>
            <li>Supports multiple accounts so the same server api be used for multiple projects.</li>
            <li>The Api mimicks <a href="http://blockchain.info/merchant">blockchain.info's merchant api</a> almost exactly so you can switch over easily. </li>
            <li>Supports Multiple Crypto Currencies. Just install the deamon of the coin, copy the super class file and you are set.</li>
            <li>Runs on code igniter php framework for small footprint, easy install, secure database orm and mvc model.</li>
        </ul>
        <br>

        <h4>Requirements</h4>
        <ul>
            <li>hardware: linux server with at least 4 gigs of ram. Ubuntu 12.04 LTS preferred</li>
            <li><a href="">LAMP</a> - Linux Apache MySql PHP platform. (comes installed by default on most linux servers)</li>
            <li><a href="">Code Igniter PHP framework</a> (comes included)</li>
            <li>bare minimum linux command line skills. step by step guide included :)</li>
        </ul>
        <h4>Install Guide</h4>
        <div>Our goal with this is to introduce bitcoin developement to a whole new class of developers. Thus we have prepared step by step documentation to guide even the greenest newb through the once occulted bitcoin server install process.</div>
        <ul>
            <li><a href="install/readme_installbitcoind.txt">Installing and Configuring BitcoinD step by step tutorial</a></li>
            <li><a href="install/readme_securebitcoind.txt">How to Secure your BitcoinD server step by step tutorial</a></li>
            <li><a href="install/db_api.sql.txt">API Database .SQL file</a></li>
            <li><a href="install/db_wallet.sql.txt">Wallet Database .SQL file</a></li>
            <li><a href="/merchant/test.php">Merchant API demo</a></li>
            <li><a href="/cp/">Merchant API Control Panel</a></li>
            <li><a href="/wallet/">Wallet demo</a> currently serving over 40,000 users</li>
        </ul>

        <br><br>

        <div>Please join us in making this solve even more problems for people</div>
        <br>
        <ul>
            <li><a href="http://github.com/">GitHub</a></li>
            <li><a href="http://bitcointalk.org">BitCoinTalk.org thread</a></li>
            <li><a href="http://bitcointalk.org">Reddit thread</a></li>
            <li>Donate to the cause </li>
        </ul>


    </div>

</div>



<script src="foundation/js/foundation.min.js"></script>
<script src="foundation/js/foundation/foundation.abide.js"></script>
<script src="foundation/js/foundation/foundation.reveal.js"></script>

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
</script>

</body>
</html>