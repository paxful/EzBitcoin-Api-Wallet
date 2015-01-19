
EzBit Api Server
====================================
Bitcoin RESTful API JSONRPC Wrapper - PHP
-----
Made to be the simplest, fastest way to get your own bitcoin wallet server up and running. Optimized for simplicity and speed towards a MVP. This more than a RPC wrapper it is a full database complete with logs and it does the most vital task of processing incoming transactions which befuddles many who first try and make a bitcoin service. We are using three of this API servers in our systems and it works beautifully and simply.


Features
-----
* Supports multiple accounts so the same server api be used for multiple projects.
* The Api mimicks <a href="http://blockchain.info/merchant">blockchain.info's merchant api</a> almost exactly so you can switch over easily.
* Supports Multiple Crypto Currencies. Just install the deamon of the coin, copy the super class file and you are set.
* Runs on code igniter php framework for small footprint, easy install, secure database orm and mvc model.


Security
-----
* Optional 256 sha hashed api calls
* Database ORM prepared statements and auto escaping via laravel eloquent model
* Full logging of all Api requests and all transactions
* TODO: race conditions prevention via php module ...


Requirements
-----
* Hardware: linux server with at least 4 gigs of ram. 
* Ubuntu 14 LTS preferred - (dedicated over virtual is preferred to avoid shared memory attacks)
* Laravel PHP framework (comes included)
* Composer 
* Nginx 1.7 or later
* Postgres 9.3
* PHP 5.4 or later + php-fpm +  Mcrypt module for PHP
* bare minimum linux command line skills. step by step guide included :)

* Update this server now uses Laravel, a much more robust framework than Codeigniter.

Install Guide
-----
Our goal with this is to introduce bitcoin developement to a whole new class of developers. Thus we have prepared step by step documentation to guide even the greenest newb through the once occulted bitcoin server install process.

* <a href="install/readme_install_bitcoind.md">Installing and Configuring BitcoinD step by step tutorial</a>
* <a href="install/readme_secure_bitcoind.md">How to Secure your BitcoinD server step by step tutorial</a>

	
update ubuntu 
--
	sudo apt-get update
	sudo apt-get upgrade

install nginx 1.7
--
https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-12-04

install php 5.4 + php-fpm
--

install postgres 9.4 *be sure to set localization to English and UTF8.
--
https://www.digitalocean.com/community/tutorials/how-to-install-and-use-postgresql-on-ubuntu-14-04


install composer
--
https://getcomposer.org/download/

Shortcut
--
or you can save yourself immense time and use laravel forge for $ a month https://forge.laravel.com


create a postgres Database named ezbitapi and assign a new username and password. use whatever gui you like pgadmin, navicat razorsql etc..
--

	#if you get a LC_CTYPE error when specify UTF8 then you installed postgres with the wrong locale
	#utf8 is not necessary but you should always change the locale before installing
	#http://crohr.me/journal/2014/postgres-rails-the-chosen-lc-ctype-setting-requires-encoding-latin1.html
	sudo locale-gen en_US.UTF-8
	sudo pico /etc/default/locale
		 LANG="en_US.UTF-8"
		 LANGUAGE="en_US.UTF-8"
		 LC_CTYPE="en_US.UTF-8"
		 LC_NUMERIC="en_US.UTF-8"
		 LC_TIME="en_US.UTF-8"
		 LC_COLLATE="en_US.UTF-8"
		 LC_MONETARY="en_US.UTF-8"
		 LC_MESSAGES="en_US.UTF-8"
		 LC_PAPER="en_US.UTF-8"
		 LC_NAME="en_US.UTF-8"
		 LC_ADDRESS="en_US.UTF-8"
		 LC_TELEPHONE="en_US.UTF-8"
		 LC_MEASUREMENT="en_US.UTF-8"
		 LC_IDENTIFICATION="en_US.UTF-8"
		 LC_ALL=en_US.UTF-8
		 #save file

get git server files
	git clone https://github.com/easybitz/EzBitcoin-Api-Wallet
	mv EzBitcoin-Api-Wallet ezbitapi

change ownership of all files to running as user and nginx user
	chown -R runningasuser:www-data ezbitapi

give full permissions to storage folder for logs
	sudo chmod -R 777 app/storage


update composer
--
	sudo composer self-update
	sudo composer update

	#if you get an error about mcrypt then install it

	apt-get install php5-mcrypt
		#if it still isn't found by composer then you need to link to the right mcrypt.so file
		sudo updatedb 
		locate mcrypt.ini
		#Should show it located at /etc/php5/mods-available
		locate mcrypt.so
		#Edit mcrypt.ini and change extension to match the path to mcrypt.so, example:
			extension=/usr/lib/php5/20121212/mcrypt.so
		#Create symbol links now
		ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/cli/conf.d/20-mcrypt.ini
		ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/apache2/conf.d/20-mcrypt.ini
		php5enmod mcrypt #- (optional since it may already be enabled)
		sudo service nginx restart���
		service php5-fpm restart

if error is reported about the DEBUG file then create a .env.php file in the root
	sudo pico .env.php

add the below settings
--
	<?php
	return array(
	 'DEBUG' => true, //false for production
	 'TESTNET' => true, //false for production
	 'CALLBACK_SECRET' => 'secretgoeshere',
	 'APP_SECRET' => '',
	 'PRIVATE_INVOICING' => true,
	 'ADMIN_EMAIL' => 'admin@domain.com',
	 'EMAIL_USERNAME' => 'apisendmail',
	 'EMAIL_PASSWORD' => 'emailpassword',
	 'DATABASE_DRIVER'   => 'pgsql',
	 'DATABASE_NAME'     => 'ezbitapi',
	 'DATABASE_USER'     => 'postgres',
	 'DATABASE_PASS'     => 'passwordgoeshere',
	);

rerun composer update
	sudo composer update

in your application create a secure guid (username) password and secret
--

run create database
	sudo php artisan migrate

seed tables with right values
	sudo php artisan db:seed

if you mess up and need to redo the sequence for the tables, after a botched export import from the old api server then
	
	su postgres
	psql
	\connect ezbitapi
	ALTER SEQUENCE balance_id_seq RESTART WITH 1;
	ALTER SEQUENCE crypto_types_id_seq RESTART WITH 1;
	ALTER SEQUENCE users_id_seq RESTART WITH 1;
	ALTER SEQUENCE addresses_id_seq RESTART WITH 2163; #only if importing addresses table. 2163 is the highest id you have + 1
	ALTER SEQUENCE invoice_addresses_id_seq RESTART WITH 2163; #only if importing invoices_addres atable 2163 is the highest id you have + 1

	#if you have existing data from the old codeigniter database it is easy to import in as very few database fields have changed, just map them to the right column names and import.
	
	#users table has callback and notify url's which must be updated
	#add a line to the users table with the guid, password and secret to be called from your application
	#callback url = "url of script in your application to process incoming transactions"
	#blocknotifyurl = "address to handle blocknotify "
	#rpc_connection = http://nikola:DU54293EBJV6JB@127.0.0.1:8332

configure nginx to add site
	sudo touch /usr/local/nginx/sites-available/ezbitapi
	sudo pico /usr/local/nginx/sites-available/ezbitapi


configure site
	server {
			listen   81;
			root /home/user/ezbitapi/public;
			index index.php;
			server_name 198.111.111.111;
	
			#ssl
			listen 443 ssl;
			ssl_certificate /etc/nginx/ssl/certificate.crt;
			ssl_certificate_key /etc/nginx/ssl/keyfile.key;
	
			location / {
				 try_files $uri $uri/ /index.php$is_args$args;
			}
	
			# pass the PHP scripts to FastCGI server listening on /var/run/php5-fpm$
			location ~ \.php$ {
					try_files $uri /index.php =404;
					fastcgi_pass unix:/var/run/php5-fpm.sock;
					fastcgi_index index.php;
					fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
					include fastcgi_params;
			}
	
			location ~* .(jpg|jpeg|png|gif|ico|css|js)$ {
					expires 365d;
			}
	}


make a new sym link for each site
--
	sudo ln -s /usr/local/nginx/sites-available/ezbitapi /usr/local/nginx/sites-enabled/ezbitapi
	sudo service nginx restart 
	service php5-fpm restart

add firewall rule to add access to apiserver from ip /port if necessary
--
	ufw status numbered #get status
	ufw delete [number] #drop rules
	ufw allow from 192.168.0.1 to any port 80 	#allow by ip and port
	ufw allow 80 								#ufw allow from anywhere to port
	ufw reload 									#restart firewall to make new rules take affects


update bitcoin core to fire callback to the apisevrer via wallet notify
--
add line to bitcoin.conf
	walletnotify=/home/user/.bitcoin/walletnotify.sh %s
	    	rpcallowip=*.*.*.* is now phased out so use cidr instead
	      	rpcallowip=0.0.0.0/0 #allow all ips NOT SECURE, use for testing only
	      	rpcallowip=192.168.0.0.1 #ip of app server that will call apiserver

restart bitcoin core
	bitcoin-cli stop
	bitcoind
	
create walletnotify.sh
	sudo pico walletnotify.sh
	
		#!/bin/bash
		F=/home/cryptoheat/walletnotify_btc_transaction_log
		D=`date +"%Y%m%d%H%M%S"`
		echo ${D} - ${1} >> ${F}
		curl 'http://127.0.0.1/api/callback?secret=secretgoeshere&txid='${1}
	
	  #http://127.0.0.1/api/callback is the url of your api server process route. all incoming btc sends will go to this address, be added to the api server log tables and then a callback will be fired to your web/app server process transaction url.

#####################################################################################


Please join us in making this solve even more problems for people
-----
* [BitCoinTalk.org thread](http://bitcointalk.org)
* [Reddit thread](http://reddit.com)
* Did you like the project? Donations are welcome 1NDQEjqmdiYG8VQ4u6Sd8oAyGrNtgAvUd8

