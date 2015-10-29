<?php

namespace Services\Blockchain\Response;

use Services\Common\Response;

class UnspentOutputsResponse extends Response {

	/**
	 * @var integer
	 */
	protected $minimumConfirms = 1;
	protected $total;
	protected $amountsSum = 0;
	protected $amountPairs = [];

	public function __construct(array $outputs, $confirms = 1)
	{
		$this->data = $outputs;
		$this->minimumConfirms = $confirms;
		$this->total = count($outputs);
		// do calculation only if at least 1 output
		if ($this->total > 0)
		{
			$amounts = array_column($outputs, 'amount');
			$this->amountsSum = array_sum($amounts);
			$amountPairs = [];
			foreach ($amounts as $amount)
			{
				$amount = (string) $amount; // cast float to string, because array_key_exists can handle only string or integer
				// if key exists, then add +1 to value that means that amount already was counted
				if (array_key_exists($amount, $amountPairs))
				{
					$amountPairs[$amount] = $amountPairs[$amount] + 1;
				}
				else
				{
					// new result, just add initial 1
					$amountPairs[$amount] = 1;
				}
			}
			$this->amountPairs = $amountPairs;
			ksort($this->amountPairs); // sort incrementing by amount
		}
	}

	/**
	 * @return int
	 */
	public function getMinimumConfirms()
	{
		return $this->minimumConfirms;
	}

	/**
	 * @return int
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Total sum of unspent outputs
	 * @return int
	 */
	public function getAmountsSum()
	{
		return $this->amountsSum;
	}

	/**
	 * returns ['amount' => 'number_of_occurrences']
	 * @return array
	 */
	public function getAmountPairs()
	{
		return $this->amountPairs;
	}
}