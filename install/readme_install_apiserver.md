
Install EzBit API Server WebApp
====================================

If using Cpanel or Plesk then simply add an account.
The WebApp can be on the same box as bitcoind or a different server. We recommend a different server.

### Requirements
* Apache 2.2 +
* PHP 5.3 +
* MySQL
* mcrypt php module
* PDO php module
* PHPMyAdmin

### To setup site
* install database import from db_api.sql.txt
* edit config /codeigniter/config/constants.php


###Following Install instructions are for Ubuntu

Install phpmyadmin
https://www.digitalocean.com/community/articles/how-to-install-and-secure-phpmyadmin-on-ubuntu-12-04
https://www.digitalocean.com/community/articles/how-to-set-up-ssl-certificates-with-phpmyadmin-on-an-ubuntu-12-04-vps
    sudo apt-get phpmyadmin

edit apache conf
    sudo pico /etc/apache2/apache2.conf
#add  phpmyadmin to conf file Goal: get https working
    Include /etc/phpmyadmin/apache.conf
#restart apache
    sudo service apache2 restart

#Lockdown phpmyadmin
    sudo pico /etc/phpmyadmin/apache.conf

Under the directory section, add the line “AllowOverride All” under “Directory Index”, making the section look like this:
    <Directory /usr/share/phpmyadmin>
    Options FollowSymLinks
    DirectoryIndex index.php
    AllowOverride All
    [...]

    sudo pico /usr/share/phpmyadmin/.htaccess
#add to file
    AuthType Basic
    AuthName "Restricted Files"
    AuthUserFile /etc/apache2/passwords/.htpasswd
    Require valid-user


    sudo htpasswd -c /etc/apache2/passwords/.htpasswd username
#password = passwordgoeshere

    sudo service apache2 restart

#make phpmyadmin work with ssl
https://www.digitalocean.com/community/articles/how-to-set-up-ssl-certificates-with-phpmyadmin-on-an-ubuntu-12-04-vps

    sudo a2enmod default-ssl
    sudo service apache2 restart
    sudo mkdir /etc/apache2/ssl
    sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt
    sudo pico /etc/apache2/sites-available/default-ssl

    <VirtualHost *:443>
    ServerAdmin webmaster@localhost
    ServerName example.com:443
    . . .

    SSLEngine on
    SSLCertificateFile /etc/apache2/ssl/apache.crt
    SSLCertificateKeyFile /etc/apache2/ssl/apache.key

#Force SSL Within PhpMyAdmin
    sudo pico /etc/phpmyadmin/config.inc.php
    $cfg['ForceSSL'] = true;
    sudo a2ensite default
    sudo service apache2 restart


#if you need to disable a site after a mistake
    sudo a2dissite mynewsite
    sudo /etc/init.d/apache2 restart




### secure site

* Add http authentication to phpmyadmin
* Make site https only
* In config/config.php choose testnet or not, also enable or disable email logging
* In config/email.php set mail server parameters if u enabled email logging
* In config/constants.php set DEBUG_API to true if you want to see whole debug and profiler




