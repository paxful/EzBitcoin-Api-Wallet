<?php

namespace Helpers;

/*
					COPYRIGHT

Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>

This file is part of JSON-RPC PHP.

JSON-RPC PHP is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

JSON-RPC PHP is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with JSON-RPC PHP; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

use Exception;
use stdClass;

/**
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 *
 * @author sergio <jsonrpcphp@inservibile.org>
 */
class BitcoinCoreJsonRPCClient implements JsonRPCClientInterface
{

	/**
	 * Debug state
	 *
	 * @var boolean
	 */
	private $debug;

	/**
	 * The server URL
	 *
	 * @var string
	 */
	private $url;
	/**
	 * The request id
	 *
	 * @var integer
	 */
	private $id;
	/**
	 * If true, notifications are performed instead of requests
	 *
	 * @var boolean
	 */
	private $notification = false;

	/**
	 * Takes the connection parameters
	 *
	 * @param string $url
	 * @param boolean $debug
	 */
	public function __construct($url = null, $debug = false)
	{
		// server URL
		$this->url = $url;
		// proxy
		empty($proxy) ? $this->proxy = '' : $this->proxy = $proxy;
		// debug state
		empty($debug) ? $this->debug = false : $this->debug = true;
		// message id
		$this->id = 1;
	}

	/**
	 * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
	 *
	 * @param boolean $notification
	 */
	public function setRPCNotification($notification)
	{
		empty($notification) ?
			$this->notification = false
			:
			$this->notification = true;
	}

	public function setRpcConnection($url)
	{
		// server URL
		$this->url = $url;
	}

	/**
	 * Performs a jsonRCP request and gets the results as an array
	 *
	 * @param string $method
	 * @param array $params
	 *
	 * @return array
	 * @throws Exception
	 */
	public function __call($method, $params)
	{

		// check
		if (!is_scalar($method)) {
			throw new Exception('Method name has no scalar value');
		}

		// check
		if (is_array($params)) {
			// no keys
			$params = array_values($params);
		} else {
			throw new Exception('Params must be given as array');
		}

		// sets notification or request task
		if ($this->notification) {
			$currentId = NULL;
		} else {
			$currentId = $this->id;
		}

		// prepares the request
		$request = array(
			'method' => $method,
			'params' => $params,
			'id' => $currentId
		);
		$request = json_encode($request);
		$this->debug && $this->debug .= '***** Request *****' . "\n" . $request . "\n" . '***** End Of request *****' . "\n\n";
		// performs the HTTP POST
		$opts = array('http' => array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/json',
			'content' => $request,
			'ignore_errors' => true
		));
		$context  = stream_context_create($opts);
		if ($fp = fopen($this->url, 'r', false, $context)) {
			$response = '';
			while ($row = fgets($fp)) {
				$response .= trim($row) . "\n";
			}
			$this->debug && $this->debug .= '***** Server response *****' . "\n" . $response . '***** End of server response *****' . "\n";
			$response = json_decode($response, true);
		} else {
			throw new Exception('Unable to connect to ' . $this->url);
		}

		// debug output
		if ($this->debug) {
			echo nl2br($debug);
		}

		// final checks and return
		if (!$this->notification) {
			// check
			if ($response['id'] != $currentId) {
				throw new Exception('Incorrect response id (request id: ' . $currentId . ', response id: ' . $response['id'] . ')');
			}
			if (array_key_exists('error', $response) && !is_null($response['error'])) {
				//throw new Exception('Request error: '.$response['error']);

				//adding this line might make it bitcoinD specific.....
				throw new Exception('Request error: ' . $response['error']['code'] . ' - ' . $response['error']['message']);
			}

			return $response['result'];
		} else {
			return true;
		}
	}
}










//2.0

/**
 * Class to call remote methods via protocol JSON-RPC 2.0
 * Includes server and client functionality
 *
 * According to official JSON-RPC 2.0 specification
 * http://groups.google.com/group/json-rpc/web/json-rpc-2-0
 * Excluding "notifications" and "batch mode"
 *
 * Usage example:
 *
 * 1. Server
 *
 * class JsonRpcServer
 * {
 * public function add( $a, $b )
 * {
 * return $a + $b;
 * }
 * }
 *
 * $server = new JsonRpc( new JsonRpcServer() );
 * $server->process();
 *
 * 2. Client
 *
 * $client = new JsonRpc( 'http://[SERVER]/json_rpc_server.php' );
 * $result = $client->add( 2, 2 ); // returns 4
 *
 * @author ptrofimov
 */
class JsonRpc
{
	const JSON_RPC_VERSION = '2.0';

	private $_server_url, $_server_object;

	public function __construct($pServerUrlOrObject)
	{
		if (is_string($pServerUrlOrObject)) {
			if (!$pServerUrlOrObject) {
				throw new Exception('URL string can\'t be empty');
			}
			$this->_server_url = $pServerUrlOrObject;
		} elseif (is_object($pServerUrlOrObject)) {
			$this->_server_object = $pServerUrlOrObject;
		} else {
			throw new Exception('Input parameter must be URL string or server class object');
		}
	}

	public function __call($pMethod, array $pParams)
	{
		if (is_null($this->_server_url)) {
			throw new Exception('This is server JSON-RPC object: you can\'t call remote methods');
		}
		$request = new stdClass();
		$request->jsonrpc = self::JSON_RPC_VERSION;
		$request->method = $pMethod;
		$request->params = $pParams;
		$request->id = md5(uniqid(microtime(true), true));
		$request_json = json_encode($request);
		$ch = curl_init();
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL => $this->_server_url, CURLOPT_HEADER => 0, CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $request_json, CURLOPT_RETURNTRANSFER => 1
			)
		);
		$response_json = curl_exec($ch);
		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch), curl_errno($ch));
		}
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			throw new Exception(sprintf(
				'Curl response http error code "%s"',
				curl_getinfo($ch, CURLINFO_HTTP_CODE)
			));
		}
		curl_close($ch);
		$response = $this->_parseJson($response_json);
		$this->_checkResponse($response, $request);
		return $response->result;
	}

	public function process()
	{
		if (is_null($this->_server_object)) {
			throw new Exception('This is client JSON-RPC object: you can\'t process request');
		}
		ob_start();
		$request_json = file_get_contents('php://input');
		$response = new stdClass();
		$response->jsonrpc = self::JSON_RPC_VERSION;
		try {
			$request = $this->_parseJson($request_json);
			$this->_checkRequest($request);
			$response->result = call_user_func_array(
				array($this->_server_object, $request->method),
				$request->params
			);
			$response->id = $request->id;
		} catch (Exception $ex) {
			$response->error = new stdClass();
			$response->error->code = $ex->getCode();
			$response->error->message = $ex->getMessage();
			$response->id = null;
		}
		ob_clean();
		echo json_encode($response);
	}

	private function _parseJson($pData)
	{
		$data = json_decode($pData, false, 32);
		if (is_null($data)) {
			throw new Exception('Parse error', -32700);
		}
		return $data;
	}

	private function _checkRequest($pObject)
	{
		if (!is_object($pObject) || !isset($pObject->jsonrpc) || $pObject->jsonrpc !== self::JSON_RPC_VERSION || !isset(
			$pObject->method
		) || !is_string($pObject->method) || !$pObject->method || (isset(
			$pObject->params
		) && !is_array($pObject->params)) || !isset($pObject->id)) {
			throw new Exception('Invalid Request', -32600);
		}
		if (!is_callable(array($this->_server_object, $pObject->method))) {
			throw new Exception('Method not found', -32601);
		}
		if (is_null($pObject->params)) {
			$pObject->params = array();
		}
	}

	private function _checkResponse($pObject, $pRequest)
	{
		if (!is_object($pObject) || !isset($pObject->jsonrpc) || $pObject->jsonrpc !== self::JSON_RPC_VERSION || (!isset(
			$pObject->result
		) && !isset($pObject->error)) || (isset($pObject->result) && (!isset(
			$pObject->id
		) || $pObject->id !== $pRequest->id)) || (isset($pObject->error) && (!is_object(
			$pObject->error
		) || !isset($pObject->error->code) || !isset($pObject->error->message)))) {
			throw new Exception('Invalid Response', -32600);
		}
		if (isset($pObject->error)) {
			throw new Exception($pObject->error->message, $pObject->error->code);
		}
	}
}
