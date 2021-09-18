<?php

declare(strict_types=1);


use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;

/**
 * InitialAPITest class
 */
class InitialAPITest extends TestCase
{
	public function test_get_balance()
	{
		define("ROOT", dirname(dirname(__DIR__)));
		$key_filepath 			= ROOT . "/temp/cert/jplive-key.key";
		$key_password 			= getenv("BANKAPI_PRIVKEY_PASSWD");
		$cert_filepath			= ROOT . "/temp/cert/jplive-cert-chain.pem";
		$client_id 				= getenv("BANKAPI_CLIENT_ID");
		$client_secret 			= getenv("BANKAPI_CLIENT_SECRET");
		$key 					= getenv("BANKAPI_ENC_KEY");
		$request_uuid 			= getenv("BANKAPI_REQUEST_UUID");
		$request_channel_id 	= getenv("BANKAPI_CHANNEL_ID");
		$bank_corpcode 			= getenv("BANKAPI_CORPCODE");
		$bank_corpaccnum		= getenv("BANKAPI_CORPACCNUM");
		$base_api_url 			= getenv("BANKAPI_BASE_URL");

		$http_client = new HttpClient(
			$key_filepath,
			$key_password,
			$cert_filepath,
			$client_id,
			$client_secret
		);

		$bank_api_config = new BankApiConfig(
			$key,
			$request_uuid,
			$request_channel_id,
			$bank_corpcode,
			$bank_corpaccnum,
			$base_api_url
		);

		// var_dump($request_uuid, $request_channel_id, $key);
		// $this->assertTrue(true);
		// return;

		$axis_bank = new BankApi($bank_api_config, $http_client);

		/*
		$axis_bank = new BankApi(
			$key,
			$client_id,
			$client_secret,
			$request_uuid,
			$request_channel_id,
			$bank_corpcode,
			$bank_corpaccnum,
			$base_api_url
		);

		$axis_bank->configureClientCertificate(
			$cert_filepath,
			$key_filepath,
			$key_password
		);
		*/

		$balance = $axis_bank->balance->get();

		$this->assertTrue(true);
	}
}