
BitcoinD HARDENING GUIDE

#login
ssh -p 432 cryptomanager@5.153.60.162
su root


#install phpmyadmin
#https://www.digitalocean.com/community/articles/how-to-install-and-secure-phpmyadmin-on-ubuntu-12-04
#https://www.digitalocean.com/community/articles/how-to-set-up-ssl-certificates-with-phpmyadmin-on-an-ubuntu-12-04-vps
sudo apt-get phpmyadmin

#edit apache conf
sudo pico /etc/apache2/apache2.conf
#add  phpmyadmin to conf file Goal: get https working
Include /etc/phpmyadmin/apache.conf
#restart apache
sudo service apache2 restart

#------------------
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
#password = sd6ejmyiCwEM7UMbEH

sudo service apache2 restart

#make phpmyadmin work with ssl
#https://www.digitalocean.com/community/articles/how-to-set-up-ssl-certificates-with-phpmyadmin-on-an-ubuntu-12-04-vps

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








#allow gui to be used via ssh so we can run secure lock down script ( only works via gui )
sudo apt-get install x11vnc
x11vnc -storepasswd sd6ejmyiCwEM7UMbEH
#/etc/apache2/sites-available
x11vnc -auth /var/run/lightdm/root/:0
#DOESN"T WORK!


#install firewall
sudo apt-get install ufw
sudo ufw allow ssh
sudo ufw allow 16432
sudo ufw allow http
sudo ufw allow https
sudo ufw allow 8332
sudo ufw allow 8333
sudo ufw allow 18332
sudo ufw enable

#secure shared memory
sudo pico /etc/fstab
#add line  then reboot
tmpfs     /dev/shm     tmpfs     defaults,noexec,nosuid     0     0


#SSH Hardening Disable Root access
sudo pico /etc/ssh/sshd_config
## add new ssh user
useradd sshuser
passwd sshuser CTmD7yRLyEEd1Pgud4sd6
visudo
sshuser ALL=(ALL) ALL

#Restart SSH server, open a Terminal Window and enter :
sudo /etc/init.d/ssh restart


#4. Protect su by limiting access only to admin group.
sudo groupadd admin
sudo usermod -a -G admin cryptomanager
sudo dpkg-statoverride --update --add root admin 4750 /bin/su




#5. Harden network with sysctl settings.
sudo pico /etc/sysctl.conf

# IP Spoofing protection
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1

# Ignore ICMP broadcast requests
net.ipv4.icmp_echo_ignore_broadcasts = 1

# Disable source packet routing
net.ipv4.conf.all.accept_source_route = 0
net.ipv6.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0
net.ipv6.conf.default.accept_source_route = 0

# Ignore send redirects
net.ipv4.conf.all.send_redirects = 0
net.ipv4.conf.default.send_redirects = 0

# Block SYN attacks
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 2048
net.ipv4.tcp_synack_retries = 2
net.ipv4.tcp_syn_retries = 5

# Log Martians
net.ipv4.conf.all.log_martians = 1
net.ipv4.icmp_ignore_bogus_error_responses = 1

# Ignore ICMP redirects
net.ipv4.conf.all.accept_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv6.conf.default.accept_redirects = 0

# Ignore Directed pings
net.ipv4.icmp_echo_ignore_all = 1

sudo sysctl -p




#6. Disable Open DNS Recursion and Remove Version Info  - BIND DNS Server.
sudo pico /etc/bind/named.conf.options
recursion no;
version "Not Disclosed";
#Restart BIND DNS server. Open a Terminal and enter the following :
sudo /etc/init.d/bind9 restart



#7. Prevent IP Spoofing.
sudo pico /etc/host.conf
order bind,hosts
nospoof on


#8. Harden PHP for security.
sudo pico /etc/php5/apache2/php.ini
#Add or edit the following lines an save :
disable_functions = exec,system,shell_exec,passthru
register_globals = Off
expose_php = Off
display_errors = Off
track_errors = Off
html_errors = Off
magic_quotes_gpc = Off
sudo /etc/init.d/apache2 restart

#9. Restrict Apache Information Leakage.
sudo pico /etc/apache2/conf.d/security
#Add or edit the following lines and save :
ServerTokens Prod
ServerSignature Off
TraceEnable Off
Header unset ETag
FileETag None
sudo /etc/init.d/apache2 restart








# 12. Scan logs and ban suspicious hosts - DenyHosts and Fail2Ban.
sudo apt-get install denyhosts
sudo pico /etc/denyhosts.conf

ADMIN_EMAIL = tech@getcoincafe.com
SMTP_HOST = localhost
SMTP_PORT = 25
#SMTP_USERNAME=foo
#SMTP_PASSWORD=bar
SMTP_FROM = DenyHosts nobody@localhost
#SYSLOG_REPORT=YES

sudo apt-get install fail2ban
sudo pico /etc/fail2ban/jail.conf





