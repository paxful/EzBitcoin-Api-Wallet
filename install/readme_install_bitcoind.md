
BitcoinD Ubuntu Linux Install Guide
====================================


### install on Ubuntu via PPA
Ubuntu PPA's can be from an untrusted source be careful and check the site. Currently only BlueMatt updates the bitcoin Core PPA

    sudo apt-get install software-properties-common python-software-properties
    sudo add-apt-repository ppa:bitcoin/bitcoin
    sudo aptitude update
    sudo aptitude install bitcoind
    cd /root/.bitcoin



### install via source on linux CentOS etc...
Will usually install to /root/.bitcoin but you can move it elsewhere as long as you update the path

If you are on Ubuntu or Debain and want to install from source then first upgrade apt-get and then install the dependancies.

    sudo apt-get update
    sudo apt-get upgrade -y
    sudo apt-get install -y git-core build-essential libssl-dev libboost-all-dev libdb++-dev libgtk2.0-dev

For CentOS etc... the above may have to be fetched with [Yum](http://www.tecmint.com/20-linux-yum-yellowdog-updater-modified-commands-for-package-mangement/)

    yum update
    yum -y install git libssl

    git clone https://github.com/bitcoin/bitcoin.git
    cd bitcoin
    git checkout v0.9.2
    cd /root && mv /root/.bitcoin/bitcoin /root/ && cd bitcoin
    ./autogen.sh && ./configure --with-gui=no && make

if the above command doesn't work, ubuntu 14.0 then run the below commands to install missing dependancies for autoconf

    sudo apt-get install build-essential
    sudo apt-get install libtool autotools-dev autoconf
    sudo apt-get install libssl-dev

If it complains about an incompatible version of berkley db then run this line to ignore it

    ./autogen.sh && ./configure --with-gui=no --with-incompatible-bdb && make




### Install on Mac OSX
Can be done with homebrew or port. here we use port for automatica dependecy install. First we update our macport definitions

     sudo port selfupdate
     sudo port upgrade outdated

Installing the dependencies using MacPorts is very straightforward.

    sudo port install boost db48@+no_java openssl miniupnpc autoconf pkgconfig automake

Clone the github tree to get the source code and go into the directory.

    git clone https://github.com/bitcoin/bitcoin.git
    cd bitcoin

Build bitcoind (and Bitcoin-Qt, if configured):

    ./autogen.sh
    ./configure
    make

It is a good idea to build and run the unit tests, too:

    make check



### Install Windows - Double click the exe please ...




### Configure BitcoinD
    pico /root/.bitcoin/bitcoin.conf

Add settings to file bitcoin.conf - by default 128.0.0.1 is always allowed. add other ips as needed

    rpcallowip=10.1.*.*

This forces bitcoind to always use ssl

    rpcssl=1
    rpcport 8832
    server=1
    daemon=1
    rpcuser=usernamegoeshere
    rpcpassword=passwordgoeshere

notify email of failures stops etc

    alertnotify=echo %s | mail -s "BitcoinD Alert" alertme@gmail.com

**IMPORTANT** call bash script on each incoming receive - can be anywhere the user running bitcoind has access to or same folder bitcoind is

    walletnotify=/home/crypto/walletnotify_btc.sh %s

save file with alt-o and exit with alt-w in pico


### IMPORTANT:  Being notified when funds arrive - **This is the part that no one has any idea how to do
Create a bash script that runs everytime an address on bitcoind gets funds.

    touch /root/.bitcoin/walletnotify_btc.sh
    pico /root/.bitcoin/walletnotify_btc.sh

Add the following to the file bash script . writes out logfile with date and calls a php script on the API Server to process the order.

    #!/bin/bash
    F=/home/walletnotify_btc_transaction_log
    D=`date +"%Y%m%d%H%M%S"`
    echo ${D} - ${1} >> ${F}
    curl 'http://127.0.0.1/walletnotify.php?transactionhash='%s

Calls a url with a script, can be local or remote. below url is on the local box. change it to where ever you install the API server site.




### Things to do after install

If you installed as root and want to run bitcoind as root then continue on to **Run BitcoindD** otherwise you need to change the ownership of the bitcoind files to the user that will be running bitcoind.
Change ownership of bitcoind to a new user to avoid running it as root, recommended for security.
Move (or copy) from /root./bitcoind to a directory of your choosing ex. /opt/bitcoin-0.9.2

    mv /root/.bitcoin /opt/bitcoin-0.9.2/
    cp -av /root/.bitcoin /opt/bitcoin-0.9.2/

Create user cryptomanager to run bitcoind

    useradd cryptomanager
    passwd cryptomanager

Assign ownership of bitcoind files to new user

    chown -R cryptomanager:cryptomanager /opt/bitcoin-0.9.2/


Open Bitcoind Ports. Bitcoin uses ports 8332 and 8333 on TCP IP and UDP.
If your host has them closed then be certain to open them and if you have a firewall installed be certain that it allows those ports incoming and outgoing on both TCPIP and UDP



### Run BitcoinD -
BitcoinD will now begin download the block chain files which can take over a day. Will be sluggish and unresponsive during this time as it will be downloading 20+ gigs of the blockchain files, just wait or copy them from elsewhere

    bitcoind

**waiting....**
Optional - For faster install copy blockchain files new install. You must have these files from a previous install. takes days otherwise

    cp -av /root/.bitcoin/blocks /opt/bitcoin-0.9.2/blocks/

Once all .dat files are copied over run bitcoind with the -rescan command to verify the blockchain files

    bitcoind -rescan

Test install - should see 0.000000

    bitcoind getbalance

Create symlink tolatest folder.. makes upgrading easier so you don't have to keep changing the man path files for each upgrade etc..

    ln -s /opt/bitcoin-0.9.2 /opt/bitcoin-latest/bin

Add directory to path

    export PATH=$PATH:/opt/bitcoin-0.9.2

Add directory to path for ALL users

    pico /etc/environment

Add at end ex :/opt/bitcoind-latest
Then add two new lines for MANPATH and INFOPATH.
After editing /etc/environment, log out and back in, and check that e.g. echo "$MANPATH" outputs the value you added.

Restart shell for changes to take

    source .bashrc

Add bitcoind to startup

    sudo touch /etc/init.d/runbitcoind
    sudo chmod +x /etc/init.d/runbitcoind
    pico /etc/init.d/runbitcoind

Add to /etc/init.d/runbitcoind

    #!/bin/bash
    #/opt/bitcoin-latest/bitcoind
    sudo update-rc.d /etc/init.d/runbitcoind defaults
May have to do in folder -> sudo update-rc.d runbitcoind defaults

**Note**: bitcoind and bitcoin-cli is installed in /usr/bin/  Bitcoin-cli is the executable that handles all RPC calls!



# **To Do**
*Need a cron job to encrypt and backup the wallet.dat file, via email, ftp etc....






## If you mess things up...

### delete all files recursive in directory

    rm -rf ./bitcoin


## Common commands guide for basic bitcoind administration

### see if bitcoind is running

    ps ax | grep bitcoin | grep -v grep

###get process info of pid.

    top -p 10622

###kill process

    sudo pkill -9 -f bitcoind
    sudo bitcoind -deamon

###To get a list of accounts on the system, execute bitcoind

    bitcoind listreceivedbyaddress 0 true

###get a list of transactions

    bitcoind listtransactions




### If you get errors installing
it’s probably because of lack of memory. To resolve this error you can add more space to the swapfile. 4 gigs of ram is required
example: _internal compiler error: Killed (program cc1plus)_

    type ‘dmesg’

If you see output like this, your machine does not have enough memory to compile. You can fix this by adding more swap. To add a 1gb swap file, in /swapfile:

    sudo dd if=/dev/zero of=/swapfile bs=64M count=16
    sudo mkswap /swapfile
    sudo swapon /swapfile

After compiling, remove swapfile:

    sudo swapoff /swapfile
    sudo rm /swapfile



