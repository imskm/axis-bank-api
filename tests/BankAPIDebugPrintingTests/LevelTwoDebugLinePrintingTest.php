<?php

declare(strict_types=1);


use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;

/**
 * LevelTwoDebugLinePrintingTest class
 */
class LevelTwoDebugLinePrintingTest extends TestCase
{
	private static $last_transfer_txn_ref;
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

	private $verbosity;

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
		$this->verbosity = 2;
		$http_client = new HttpClient(
			self::$key_filepath,
			self::$key_password,
			self::$cert_filepath,
			self::$client_id,
			self::$client_secret,
			$this->verbosity
		);

		$bank_api_config = new BankApiConfig(
			self::$key,
			self::$request_uuid,
			self::$request_channel_id,
			self::$bank_corpcode,
			self::$bank_corpaccnum,
			self::$base_api_url
		);
		$bank_api_config->verbosityLevel($this->verbosity);
		$this->axis_bank = new BankApi($bank_api_config, $http_client);
	}

	public function tearDown(): void
	{
		$this->axis_bank = null;
	}

	public function test_get_balance_with_no_debug_output()
	{
		$balance = $this->axis_bank->balance->get();

		$this->assertSame($this->verbosity, $this->axis_bank->bankapi_config->verbosityLevel());
		$this->assertTrue((bool) $balance);
	}
}
