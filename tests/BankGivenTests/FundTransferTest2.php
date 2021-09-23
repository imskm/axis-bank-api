<?php

declare(strict_types=1);

use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;
use AxisBankApi\Exceptions\ResponsePayloadFailure;

/**
 * FundTransferTes2
 */
class FundTransferTest2 extends TestCase
{
	private $axis_bank;

	private static $key_filepath;
	private static $key_password;
	private static $cert_filepath;
	private static $client_id;
	private static $client_secret;

	private static $key;
	private static $request_uuid;
	private static $request_channel_id;
	private static $bank_corpcode;
	private static $bank_corpaccnum;
	private static $base_api_url;

	public static function setUpBeforeClass(): void
	{
		define("ROOT", dirname(dirname(__DIR__)));
		self::$key_filepath 		= ROOT . "/temp/cert/jplive-key.key";
		self::$key_password 		= getenv("BANKAPI_PRIVKEY_PASSWD");
		self::$cert_filepath		= ROOT . "/temp/cert/jplive-cert-chain.pem";
		self::$client_id 			= getenv("BANKAPI_CLIENT_ID");
		self::$client_secret 		= getenv("BANKAPI_CLIENT_SECRET");
		self::$key 					= getenv("BANKAPI_ENC_KEY");
		self::$request_uuid 		= getenv("BANKAPI_REQUEST_UUID");
		self::$request_channel_id 	= getenv("BANKAPI_CHANNEL_ID");
		self::$bank_corpcode 		= getenv("BANKAPI_CORPCODE");
		self::$bank_corpaccnum		= getenv("BANKAPI_CORPACCNUM");
		self::$base_api_url 		= getenv("BANKAPI_BASE_URL");
	}

	public function setUp(): void
	{
		$http_client = new HttpClient(
			self::$key_filepath,
			self::$key_password,
			self::$cert_filepath,
			self::$client_id,
			self::$client_secret
		);

		$bank_api_config = new BankApiConfig(
			self::$key,
			self::$request_uuid,
			self::$request_channel_id,
			self::$bank_corpcode,
			self::$bank_corpaccnum,
			self::$base_api_url
		);
		$this->axis_bank = new BankApi($bank_api_config, $http_client);
	}

	public function tearDown(): void
	{
		$this->axis_bank = null;
	}

	// Error for invalid checksum
	// @NOTE This test is not possible to generate since the checksum calculation is
	// done in the library so to test this I need to deliberately wrong the checksum
	// calculation function so that it will generate invalid checksum.
	// But if I do this then all the test except this one will start to fail.
	// Therefore this test will always fail, you need to test it seperately
	public function test_case_2()
	{
		echo "\n" . __FUNCTION__ . "\n";
		$bank_account = [
			"acct_holder" 	=> "RANCO INDUSTRIES",
			"acct_number" 	=> "914020013977038",
			"bene_code" 	=> "RAI15235", // @NOTE This can be alpha numeric code upto 30 chars (this can be your internal user id)
			"bank_ifsc"		=> "SBIN0007959",
			"bank_name"		=> "STATE BANK OF INDIA",
		];
		// Convert/Cast associative array to PHP standard object
		$bank_account = (object) $bank_account;
		$txn_amount = 500;

		$this->assertTrue(
			$this->axis_bank->balance->to($bank_account)->transfer($txn_amount)
		);
	}
}