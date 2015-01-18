<?php

class AllTests extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testBalance()
	{
		/* initial empty balance */
		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/balance?password=strong_pass_plz');
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals(0, $jsonResult->balance);
		$this->assertEquals(1, $jsonResult->crypto_type);


	}

	public function testValidateTx()
	{
		/* validate incorrect tx */
		/*$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/validate-transaction?password=strong_pass_plz&txid=xxx');
		$jsonResult = json_decode($response->getContent());
		$this->assertStringStartsWith('#validateTransaction: get transaction exception', $jsonResult->error);*/

		/* now validate correct tx */
		$txId = '075b23645199bfc3448d15ec0c9be9547f5da9f9394c0d6fe2ebb71230dae9d6';

		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/validate-transaction?password=strong_pass_plz&txid='.$txId);
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals(true, $jsonResult->is_valid);
		$this->assertEquals($txId, $jsonResult->tx_id);
	}

	public function testValidateAddress()
	{
		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/validate-address?password=strong_pass_plz&address=xxx');
		$jsonResult = json_decode($response->getContent());
	}

	public function testNewAddress()
	{
		// create address for user internally
		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/new-address?password=strong_pass_plz&label=xxx');
		$jsonResult = json_decode($response->getContent());

		// check crypto type id, user id, label, balance 0
		$this->assertEquals('xxx', $jsonResult->label);

		$addressModel = Address::find(1);
		$this->assertEquals(1, $addressModel->user_id);
		$this->assertEquals(1, $addressModel->crypto_type_id);
		$this->assertEquals(0, $addressModel->balance);
		$this->assertEquals('xxx', $addressModel->label);
		$this->assertEquals('151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160', $addressModel->address);
	}

	/**
	 * Test here generating invoice address, user address, payment and callback
	 */
	public function testPaymentNotEnoughBalance()
	{
		// fail on not enough balance
		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/payment?password=strong_pass_plz&to=mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr&amount=50000&note=xxx');
		$this->assertResponseStatus(200);
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals('#payment: ' . NO_FUNDS, $jsonResult->error);
	}

	public function testPayment()
	{
		$userBalance = Balance::find(1);
		$userBalance->balance = 75000;
		$userBalance->save();

		$this->assertEquals(75000, $userBalance->balance);

		$response = $this->call('GET', 'api/7xDsRLyXEd1PgJ6Glrhs6d/payment?password=strong_pass_plz&to=mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr&amount=50000&note=xxx');
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals("Sent 0.0005, crypto type id: 1 to mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr", $jsonResult->message);

		$transactionModel = Transaction::find(1);
		$this->assertEquals('151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160', $transactionModel->tx_id);
		$this->assertEquals('151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160', $transactionModel->tx_id);
		// to, amount, note
		// address valid
		// user has enough balance
		//

	}

	public function testCallback()
	{
		$response = $this->call('GET', 'api/callback?cryptotype=1&secret=xx&txid=xx&userid=1&time=xxx');

	}

	public function testReceiveInvoiceAddress()
	{
		$callbackurl = 'https://callbackurl.com';
		$response = $this->call('GET', "api/receive?method=create&callback=$callbackurl&label=xxx&forward=0&userid=1&secret=testbtc123");
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals('https://callbackurl.com', $jsonResult->callback_url);

	}

}
