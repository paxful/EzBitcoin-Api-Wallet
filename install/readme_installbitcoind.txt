
BitcoinD Ubuntu Linux Install Guide
##########################################

####install bitcoind via PPA  (ubuntu only ) - can be from an untrusted place.. be wary of ppa
sudo aptitude install python-software-properties
sudo add-apt-repository ppa:bitcoin/bitcoin
sudo aptitude update
sudo aptitude install bitcoind
cd /root/.bitcoin
pico bitcoin.conf


####install bitcoind via source
sudo apt-get install -y git-core build-essential libssl-dev libboost-all-dev libdb4.8-dev libdb4.8++-dev libgtk2.0-dev
git clone https://github.com/bitcoin/bitcoin.git
git checkout v0.9.0
cd /root && mv /root/.bitcoin/bitcoin /root/ && cd bitcoin
./autogen.sh && ./configure --with-gui=no && make
pico bitcoin.conf


###add settings to file bitcoin.conf
rpcallowip=10.1.*.* #by default 128.0.0.1 is always allowed. add other ips as needed
# rpcssl=1 # this forces bitcoind to always use ssl
rpcport 8832
server=1
daemon=1
rpcuser=usernamegoeshere
rpcpassword=passwordgoeshere
#notify email of failures stops etc
alertnotify=echo %s | mail -s "BitcoinD Alert" alertme@gmail.com
#call bash script on each incoming receive - can be anywhere the user running bitcoind has access to or same folder bitcoind is
walletnotify=/home/crypto/walletnotify_btc.sh %s


### create bash script that execute everytime an address on bitcoind recieves funds.
touch /home/crypto/walletnotify_btc.sh
#!/bin/bash
F=/home/walletnotify_btc_transaction_log
D=`date +"%Y%m%d%H%M%S"`
#writes out logfile with date
echo ${D} - ${1} >> ${F}
#calls a url with a script, can be local or remote.
curl 'http://127.0.0.1/walletnotify.php?transactionhash='%s


#############################################
## Things to do after install

#test install - shoud see 0.000000
bitcoind getbalance

## Bitcoind will be mostly sluggish and unresponsive during this time as it will be downloading 20+ gigs of the blockchain files, just wait or copy them from elsewhere


## IF you get errors, it’s probably because of lack of memory. To resolve this error you can add more space to the swapfile.
#internal compiler error: Killed (program cc1plus)

type ‘dmesg’

#[ 1377.575785] Out of memory: Kill process 12305 (cc1plus) score 905 or sacrifice child
#[ 1377.575800] Killed process 12305 (cc1plus) total-vm:579928kB, anon-rss:546144kB, file-rss:0kB

#If you see output like this, your machine does not have enough memory to compile. You can fix this by adding more swap. To add a 1gb swap file, in /swapfile:

sudo dd if=/dev/zero of=/swapfile bs=64M count=16
sudo mkswap /swapfile
sudo swapon /swapfile

#After compiling, remove swapfile:

sudo swapoff /swapfile
sudo rm /swapfile


#create symlink
ln -s /opt/bitcoin-0.9.0 /opt/bitcoin-latest/bin

#add directory to path
export PATH=$PATH:/opt/bitcoin-0.9.0

#add directory to path for ALL users
pico /etc/environment
#add at end ex :/opt/bitcoind-latest
#Then add two new lines for MANPATH and INFOPATH.
#After editing /etc/environment, log out and back in, and check that e.g. echo "$MANPATH" outputs the value you added.

#restart shell for changes to take
source .bashrc

#add bitcoind to startup
sudo touch /etc/init.d/runbitcoind
sudo chmod +x /etc/init.d/runbitcoind
pico /etc/init.d/runbitcoind
#add
#!/bin/bash
#/opt/bitcoin-latest/bitcoind
sudo update-rc.d /etc/init.d/runbitcoind defaults
# may have to do in folder -> sudo update-rc.d runbitcoind defaults

#bitcoind and bitcoin-cli is installed in /usr/bin/
#sym link to /usr/bin/

#change root pass
passwd {userName}

## we need a cron job to encrypt and backup the wallet.dat file, via email, ftp etc....


#############################################
# Common commands guide for basic administration

# see if bitcoind is running
ps ax | grep bitcoin | grep -v grep

#get process info of pid.
top -p 10622

#kill process
sudo pkill -9 -f bitcoind
sudo bitcoind -deamon
openssl s_client -connect 5.153.60.162:8332

#To get a list of accounts on the system, execute bitcoind
bitcoind listreceivedbyaddress 0 true

#get a list of transactions
bitcoind listtransactions




##if you mess things up...
#copy old install with blockchain files to home directory for user in case we need to copy over the blockchaindb files for faster install, takes days otherwise
mv /root/.bitcoin /home/cryptomanager/ && chown -R cryptomanager:cryptomanager /home/cryptomanager/.bitcoin

#delete all files recursive in directory
rm -rf ./bitcoin

#copy directories recurssively
cp -av /root/.bitcoin /home/cryptouser/

