<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	public function setUp()
	{
		parent::setUp();
		Artisan::call('migrate');
		Artisan::call('db:seed');
		Mail::pretend(true);
		Route::enableFilters();
		App::bind('Helpers\JsonRPCClientInterface', 'Helpers\DummyJsonRPCClient');
		App::bind('Helpers\DataParserInterface',    'Helpers\DummyDataParser');
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		return require __DIR__.'/../../bootstrap/start.php';
	}

}
