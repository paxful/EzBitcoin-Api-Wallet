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
			return '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160';
		}
		if ($method == 'sendtoaddress') {
			return '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160';
		}
		return null;
	}
}