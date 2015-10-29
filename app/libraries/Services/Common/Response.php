<?php

namespace Services\Common;


class Response implements ResponseInterface  {
	/**
	 * @var boolean
	 */
	protected $status = true;

	/**
	 * @var integer
	 */
	protected $statusCode;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var array
	 */
	protected $data = [];

	public function __construct()
	{

	}

	/**
	 * @return boolean
	 */
	public function isSuccessful()
	{
		return $this->getStatus();
	}

	/**
	 * @return boolean
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param boolean $status
	 * @return $this
	 */
	public function setStatus($status)
	{
		$this->status = ($status);
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * @param integer $code
	 * @return $this
	 */
	public function setStatusCode($code)
	{
		$this->statusCode = (int) $code;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return array
	 */
	public function asArray()
	{
		return [
			'status' => $this->getStatus(),
			'status_code' => $this->getStatusCode(),
			'message' => $this->getMessage(),
			'data' => $this->getData(),
		];
	}

	/**
	 * @return string
	 */
	public function asJson()
	{
		$array = $this->asArray();

		if (!empty($array))
		{
			foreach ($array as $key => $value)
			{
				if (json_encode($value, true) == '<b>FALSE</b>') {
					$array[$key] = 'Cannot be converted to json';
				}
			}
		}

		return json_encode($this->asArray(), true);
	}

	/**
	 * @return string
	 */
	public function asString()
	{
		$textArray = [];
		$textArray[] = 'Response status: "' . (($this->getStatus()) ? 'Successful' : 'Failed') . '"';

		if ((int) $this->getStatusCode() > 0)
		{
			$textArray[] = 'Status code: "' . $this->getStatusCode() . '"';
		}

		if (strlen($this->getMessage()) > 0)
		{
			$textArray[] = 'Message: "' . $this->getMessage() . '"';
		}

		return implode('. ', $textArray);
	}
}