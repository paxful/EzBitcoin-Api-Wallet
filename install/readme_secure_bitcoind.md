BitcoinD Server SecurityHARDENING GUIDE
====================================

### This [security guide](http://www.thefanclub.co.za/how-to/how-secure-ubuntu-1204-lts-server-part-1-basics) is for Ubuntu thus apt-get is used to install


### login to box -p 11422 specifies port 11422 - later on you will specify a ssh port, pick any random 4 or 5 digit port
    ssh -p 11422 cryptomanager@127.0.0.1
    su root



### Install firewall UFW
    sudo apt-get install ufw

Include this if you want the standard ssh port 22 to remain open if your host doesn't allow you change it
    sudo ufw allow ssh

This is if you want to set a random ssh port. 11422 is what we choose but choose any 5 digit random number that is not used
    sudo ufw allow 11422

Only allow this is you want unsecured http requests to be sent.
    sudo ufw allow http

    sudo ufw allow https
    sudo ufw allow 8332
    sudo ufw allow 8333
    sudo ufw allow 18332
    sudo ufw enable


### secure shared memory. vital on virtual servers
    sudo pico /etc/fstab
add line  then reboot
    tmpfs     /dev/shm     tmpfs     defaults,noexec,nosuid     0     0
Note : This only is works in Ubuntu 12.04 - For later Ubuntu versions replace /dev/shm with /run/shm


###SSH Security

Add new user for just ssh access
    useradd sshuser
    passwd sshuser passwordgoeshere
    visudo
    sshuser ALL=(ALL) ALL

Disable Root SSH access and change port
    sudo pico /etc/ssh/sshd_config

Edit the following lines. We change ssh port to 11422 but pick any random 5 digit number
    Port 11422
    Protocol 2
    PermitRootLogin no

Restart SSH server
    sudo /etc/init.d/ssh restart


### Protect su by limiting access only to admin group.
    sudo groupadd admin
    sudo usermod -a -G admin cryptomanager
    sudo dpkg-statoverride --update --add root admin 4750 /bin/su


### Harden network with sysctl settings.
    sudo pico /etc/sysctl.conf
Add the following to the conf file

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

reload sysctl
    sudo sysctl -p




### Disable Open DNS Recursion and Remove Version Info  - BIND DNS Server.
    sudo pico /etc/bind/named.conf.options
    recursion no;
    version "Not Disclosed";
Restart BIND DNS server. Open a Terminal and enter the following :
    sudo /etc/init.d/bind9 restart



### Prevent IP Spoofing.
    sudo pico /etc/host.conf
    order bind,hosts
    nospoof on


### Harden PHP for security.
    sudo pico /etc/php5/apache2/php.ini
Add or edit the following lines an save :
    disable_functions = exec,system,shell_exec,passthru
    register_globals = Off
    expose_php = Off
    display_errors = Off
    track_errors = Off
    html_errors = Off
    magic_quotes_gpc = Off
    sudo /etc/init.d/apache2 restart

### Restrict Apache Information Leakage.
    sudo pico /etc/apache2/conf.d/security
Add or edit the following lines and save :
    ServerTokens Prod
    ServerSignature Off
    TraceEnable Off
    Header unset ETag
    FileETag None
    sudo /etc/init.d/apache2 restart


### Web Application Firewall - [ModSecurity](http://www.thefanclub.co.za/how-to/how-install-apache2-modsecurity-and-modevasive-ubuntu-1204-lts-server)

### Protect from DDOS (Denial of Service) attacks - [ModEvasive](http://www.thefanclub.co.za/how-to/how-install-apache2-modsecurity-and-modevasive-ubuntu-1204-lts-server)


### Scan logs and ban suspicious hosts - DenyHosts and Fail2Ban.
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


### Check for rootkits - RKHunter and CHKRootKit.
    sudo apt-get install rkhunter chkrootkit
    sudo chkrootkit
    sudo rkhunter --update
    sudo rkhunter --propupd
    sudo rkhunter --check


### Scan open ports - Nmap.
    sudo apt-get install nmap
    nmap -v -sT localhost
    sudo nmap -v -sS localhost


### Audit your system security - Tiger
    sudo apt-get install tiger
    sudo tiger
    sudo less /var/log/tiger/security.report.*
