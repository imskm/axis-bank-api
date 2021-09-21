<?php

declare(strict_types=1);

use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;
use AxisBankApi\Exceptions\ResponsePayloadFailure;

/**
 * FundTransferTest6
 * RTGS transaction is initiated
 */
class FundTransferTest6 extends TestCase
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

	// NEFT transaction is initiated
	public function test_case_6_NEFT_transfer()
	{
		echo "\n" . __FUNCTION__ . "\n";
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

		$axis_bank = new BankApi($bank_api_config, $http_client);

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
			$axis_bank->balance->to($bank_account)->transferNEFT($txn_amount)
		);
		$this->assertNotNull($this->axis_bank->balance->txn_ref);

		// Needed by test_transfer_status()
		self::$last_transfer_txn_ref = $this->axis_bank->balance->txn_ref;
	}

	// NEFT status check of last transaction
	public function test_case_6_NEFT_check_transfer_status()
	{
		// Sleep for 5 seconds here, let the bank server update the status of last
		// transaction
		sleep(5);
		// status is array: transferStatus() can fetch status of multiple transactions
		$status = $this->axis_bank->balance->transferStatus(self::$last_transfer_txn_ref);
		$this->assertIsObject($status);
		$this->assertNotEmpty($status->utrNo);
	}
}