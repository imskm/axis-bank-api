<?php

declare(strict_types=1);

use AxisBankApi\BankApi;
use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use PHPUnit\Framework\TestCase;
use AxisBankApi\Exceptions\ResponsePayloadFailure;

/**
 * FundTransferTest15
 * RTGS transaction is initiated
 */
class FundTransferTest15 extends TestCase
{
	private static $last_transfer_txn_ref = ["978048", "604354", "469231"];
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

	// Verify if user tries with more than 1 CRN with the help of comma {,} separator
	public function test_case_15_query_for_multiple_crn()
	{
		// status is array: transferStatus() can fetch status of multiple transactions
		$status = $this->axis_bank->balance->transferStatus(self::$last_transfer_txn_ref);
		$this->assertIsArray($status);
		$this->assertCount(count(self::$last_transfer_txn_ref), $status);
		foreach ($status as $s) {
			$this->assertNotEmpty($s->utrNo);
		}
	}
}