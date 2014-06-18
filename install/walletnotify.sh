#!/bin/bash
F=/home/walletnotify_btc_transaction.log
D=`date +"%Y%m%d%H%M%S"`
echo ${D} - ${1} >> ${F}
curl -k 'https://127.0.0.1/merchant/?do=callback&local=1&txid='%s