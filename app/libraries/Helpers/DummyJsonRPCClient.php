<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 16/01/2015
 * Time: 17:47
 */

namespace Helpers;


class DummyJsonRPCClient implements JsonRPCClientInterface {

	public function setRpcConnection( $url ) {
		// TODO: Implement setRpcConnection() method.
	}

	public function __call( $method, $params ) {
		if ($method == 'getnewaddress') {
			return 'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr';
		}
		if ($method == 'sendtoaddress') {
			return '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160';
		}
		if ($method == 'gettransaction') {
			return [
				'amount' => 1,
				'fee'    => -0.0002008,
				'txid' => '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160',
				'confirmations' => 0,
				'details' => [
					[
						'amount' => 0.1,
						'account' => 'xxx',
						'address'=> 'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr',
						'category' => 'xxx'
					],
					[
						'amount' => 0.4,
						'account' => 'xxx',
						'address'=> 'n21cjTZa59QcMBXFvoKx2WoRotBV9mErnJ',
						'category' => 'xxx'
					],
					[
						'amount' => 0.5,
						'account' => 'xxx',
						'address'=> 'mxKRETCDzCuLVLiw9MieJb8xFi1WhkQ9wY',
						'category' => 'xxx'
					]
				],
				'time' => 500,
				'timereceived' => 1000,
				'blockhash' => 'xxx',
				'blockindex' => 'xxx',
				'blocktime' => 'xxx'
			];
		}
		return null;
	}
}