<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 16/01/2015
 * Time: 17:01
 */

namespace Helpers;


interface JsonRPCClientInterface {

	public function setRpcConnection($url);

	public function __call($method,$params);
}