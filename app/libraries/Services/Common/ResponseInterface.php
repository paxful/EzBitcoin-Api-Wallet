<?php

namespace Services\Common;


interface ResponseInterface {
	/**
	 * @return boolean
	 */
	public function isSuccessful();
	/**
	 * @return boolean
	 */
	public function getStatus();
	/**
	 * @return string|integer
	 */
	public function getStatusCode();
	/**
	 * @return string
	 */
	public function getMessage();
	/**
	 * @param boolean
	 */
	public function setStatus($status);
	/**
	 * @param string|integer
	 */
	public function setStatusCode($code);
	/**
	 * @param string
	 */
	public function setMessage($message);
	/**
	 * @return array
	 */
	public function asArray();
	/**
	 * @return string
	 */
	public function asJson();
	/**
	 * @return string
	 */
	public function asString();
}