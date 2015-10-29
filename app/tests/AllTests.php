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

		$addressModel = Address::whereAddress('mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr')->first();
		$this->assertEquals(1, $addressModel->user_id);
		$this->assertEquals(1, $addressModel->crypto_type_id);
		$this->assertEquals(0, $addressModel->balance);
		$this->assertEquals('xxx', $addressModel->label);
		$this->assertEquals('mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr', $addressModel->address);
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
		$this->assertEquals(20080, $transactionModel->network_fee);
		// to, amount, note
		// address valid
		// user has enough balance
		//

	}

	public function testSendMany()
	{
		$userBalance = Balance::find(1);
		$userBalance->balance = 8900000000; // 89 BTC !
		$userBalance->save();

		// TODO compile some addresses

		$recipients = [
			'recipients' => json_encode([
				'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr' => 4500000,
				'n21cjTZa59QcMBXFvoKx2WoRotBV9mErnJ' => 5500000,
				'mxKRETCDzCuLVLiw9MieJb8xFi1WhkQ9wY'=> 6500000,
			])];

		$response = $this->call('POST', 'api/7xDsRLyXEd1PgJ6Glrhs6d/sendmany?password=strong_pass_plz', $recipients);
		$jsonResult = json_decode($response->getContent());
	}

	public function testStaticAddressCallback()
	{

		Address::insertNewAddress([
			'user_id'        => 1,
			'address'        => 'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr',
			'label'          => 'xxx',
			'crypto_type_id' => 1
		]);

		$queryString = http_build_query([
			'cryptotype'    => 1,
			'secret'        => 'testbtc123',
			'txid'          => '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160',
			'userid'        => 1,
			'time'          => 'xxx',
		]);

		$response = $this->call('GET', 'api/callback?'.$queryString);
		$result = $response->getContent();
		$this->assertEquals('*ok*', $result);
		/* check addresses */
		$address1 = Address::whereAddress('mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr')->first();
		$address2 = Address::whereAddress('n21cjTZa59QcMBXFvoKx2WoRotBV9mErnJ')->first();
		$address3 = Address::whereAddress('mxKRETCDzCuLVLiw9MieJb8xFi1WhkQ9wY')->first();

		$this->assertEquals('mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr', $address1->address);
		$this->assertEquals(10000000, $address1->balance);
		$this->assertEquals(40000000, $address2->balance);
		$this->assertEquals(50000000, $address3->balance);
	}

	public function testInvoiceAddressCallback()
	{
		InvoiceAddress::saveInvoiceAddress([
			'address'               => 'mrcpH23MHKweJmzNWNbPKMxtVKMJYVpKgr',
			'destination_address'   => null,
			'invoice_amount'        => 0,
			'label'                 => 'invoice',
			'callback_url'          => 'http://dummy.url.com',
			'forward'               => 0,
			'crypto_type_id'        => 1,
			'user_id'               => 1,
		]);

		$queryString = http_build_query([
			'cryptotype'    => 1,
			'secret'        => 'testbtc123',
			'txid'          => '151f9b43343c5cd4f2064b5ac2a722f67cc53a845d05cdf9979379fa4ed19160',
			'userid'        => 1,
			'time'          => 'xxx',
		]);

		$response = $this->call('GET', 'api/callback?'.$queryString);
		$result = $response->getContent();
		$this->assertEquals('*ok*', $result);

	}

	public function testReceiveInvoiceAddress()
	{
		$callbackurl = 'https://callbackurl.com';
		$response = $this->call('GET', "api/receive?method=create&callback=$callbackurl&label=xxx&forward=0&userid=1&secret=testbtc123");
		$jsonResult = json_decode($response->getContent());
		$this->assertEquals('https://callbackurl.com', $jsonResult->callback_url);

	}

}
