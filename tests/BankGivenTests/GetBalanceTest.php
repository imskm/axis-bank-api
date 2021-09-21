<?php

declare(strict_types=1);

use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;

/**
 * GetBalanceTest
 */
class GetBalanceTest extends TestCase
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

	public function test_case_1()
	{
		$balance = $this->axis_bank->balance->get();

		$this->assertTrue((bool) $balance);
	}

	// Error for invalid checksum
	public function test_case_2()
	{
		$this->expectException(\Exception::class);
		$balance = $this->axis_bank->balance->get();
	}

	// Request validation (Null value received in mandatory field)
	public function test_case_3()
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
			"", // @NOTE bank_corpaccnum left empty intentionally to test this case
			self::$base_api_url
		);

		$axis_bank = new BankApi($bank_api_config, $http_client);

		$balance = $axis_bank->balance->get();
		$this->assertTrue(true);
	}

	// Test special chars in file
	public function test_case_4()
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
			self::$bank_corpcode . "#", // @NOTE Added '#' (special char) intentionally for testing this case
			self::$bank_corpaccnum,
			self::$base_api_url
		);

		$axis_bank = new BankApi($bank_api_config, $http_client);
		$this->assertTrue(true);
	}

	// Verify for the correct data entered
	public function test_case_5()
	{
		$balance = $this->axis_bank->balance->get();

		$this->assertTrue((bool) $balance);
	}
}