
EzBit Api
====================================
Bitcoin RESTful API JSONRPC Wrapper - PHP
-----
Made to be the simplest, fastest way to get your own bitcoin wallet server up and running. Optimized for simplicity and speed towards a MVP. We include a sample wallet web app as a sample application.


Features
-----
* Supports multiple accounts so the same server api be used for multiple projects.
* The Api mimicks <a href="http://blockchain.info/merchant">blockchain.info's merchant api</a> almost exactly so you can switch over easily.
* Supports Multiple Crypto Currencies. Just install the deamon of the coin, copy the super class file and you are set.
* Runs on code igniter php framework for small footprint, easy install, secure database orm and mvc model.


Security
-----
* Encrypted passwords
* Optional 256 sha hashed api calls
* Database ORM prepared statements and auto escaping via code igniter
* Reject security scanner requests ex. acutex etc.. The first thing Russian and Chinese hackers do is scan with these to find vulnerabilities
* Full logging of all Api requests and all transactions


Requirements
-----
* Hardware: linux server with at least 4 gigs of ram. Ubuntu 12.04 LTS preferred - (dedicated over virtual is preferred to avoid shared memory attacks)
* LAMP - Linux Apache MySql PHP platform. (comes installed by default on most linux servers)
* Code Igniter PHP framework (comes included)
* Apache 2.2 or later
* PHP 5.3 or later
* PDO for Mysql Apache PHP module
* Mcrypt module for Apache PHP
* bare minimum linux command line skills. step by step guide included :)


Install Guide
-----
Our goal with this is to introduce bitcoin developement to a whole new class of developers. Thus we have prepared step by step documentation to guide even the greenest newb through the once occulted bitcoin server install process.

* <a href="install/readme_install_bitcoind.md">Installing and Configuring BitcoinD step by step tutorial</a>
* <a href="install/readme_secure_bitcoind.md">How to Secure your BitcoinD server step by step tutorial</a>
* <a href="install/db_api.sql.md">API Database .SQL file</a>
* <a href="install/db_wallet.sql.md">Wallet Database .SQL file</a>
* <a href="/merchant/test.php">Merchant API demo</a>
* <a href="/wallet/">Wallet demo</a> currently serving over 40,000 users



Please join us in making this solve even more problems for people
-----
* [BitCoinTalk.org thread](http://bitcointalk.org)
* [Reddit thread](http://reddit.com)
* Donate to the cause


