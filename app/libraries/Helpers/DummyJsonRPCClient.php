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

	public function __call( $method, $params )
	{
		if ($method == 'getnewaddress')
		{
			return 'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr';
		}
		if ($method == 'sendtoaddress')
		{
			return '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160';
		}
		if ($method == 'gettransaction')
		{
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
		if ($method == 'listunspent')
		{
			return [
				[
					"txid" => "4d035579c5e2cf7450cee0ff5b4830503cfc0757ccb646b9ce370281f2782c06",
					"vout" => 3,
					"address" => "1BPTSxXgAN1WDZuUyqzUYf1mAp7efAH6i1",
					"scriptPubKey" => "76a91471f07c7daf58565a1d9af7286fa969e6c8726ac188ac",
					"amount" => 0.03315519,
					"confirmations" => 2,
					"spendable" => true,
				],
				[
					"txid" => "c82bad4457e097c54ca9a9880183941d9ae4eb2ce904e854f0d4bd3765959e0a",
					"vout" => 3,
					"address" => "1GPpYABY28WK26Sn32As9jsaAGVxrdMBTo",
					"account" => "",
					"scriptPubKey" => "76a914a8dab850c3593cfd146c97381bb66e8346f940e088ac",
					"amount" => 1,
					"confirmations" => 10,
					"spendable" => true,
				],
				[
					"txid" => "8325510e4459d0afb6c7b3d71d611f27e8a4c2b4d67f1a825aa1e368c553581d",
					"vout" => 16,
					"address" => "1FSS9cUamabwhBohAFdn96DL3xhmMVdfF",
					"account" => "",
					"scriptPubKey" => "76a91402bb0dea91c7b817edd8766ac38e496e88f2ef9d88ac",
					"amount" => 1,
					"confirmations" => 16,
					"spendable" => true,
				],
				[
					"txid" => "dd3ca84120c8190944df02e9540ea408540396fe7b001fc672ecb5c3e59cfe1e",
					"vout" => 0,
					"address" => "1GBeJg4VEZNed6hWfsqdHhMXHba9Hx96uj",
					"account" => "",
					"scriptPubKey" => "76a914a68d2fd0f43b1dc44d6ec8bbbd4acf7cae3381a788ac",
					"amount" => 1.43677173,
					"confirmations" => 99,
					"spendable" => true,
				],
				[
					"txid" => "25646e1cdd8fae5f532f7ee52db2cd1a7e069c1408829fe8791f9722ce7c3829",
					"vout" => 1,
					"address" => "14cweqz8Qn7sgr9WDbP99WmSbjCp1DvGF2",
					"account" => "",
					"scriptPubKey" => "76a91427b48ec00125f3edc76759361f04b0ca7ea77d0d88ac",
					"amount" => 1.01498258,
					"confirmations" => 136,
					"spendable" => true,
				],
			];
		}
		return null;
	}
}