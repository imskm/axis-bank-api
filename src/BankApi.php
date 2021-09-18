<?php

namespace AxisBankApi;

use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;

class BankApi
{
	const PROPNAME_GET_BALANCE 	= "GetAccountBalance";
	const URL_GET_STATUS 		= "/acct-recon/get-status";
	const URL_GET_BALANCE 		= "/acct-recon/get-balance";
	const URL_BENEFICIARY_REG 	= "/payee-mgmt/beneficiary-registration";
	const URL_BENEFICIARY_ENQ 	= "/payee-mgmt/beneficiary-enquiry";
	const URL_FUND_TRANSFER 	= "/payments/transfer-payment";

	public $balance;
	protected $apis = [
		"balance", "benficiary"
	];

	private $client_id;
	private $client_secret;
	private $request_uuid;
	private $request_channel_id;
	private $key;
	private $bank_corpcode;
	private $bank_corpaccnum;

	private $cert_filepath;
	private $privkey_filepath;
	private $privkey_password;

	private $http_client;

	public $bankapi_config;

	public function __construct(BankApiConfig $config, HttpClient $http_client)
	{
		$this->bankapi_config = $config;

		$this->key 					= $config->key;
		$this->request_uuid 		= $config->request_uuid;
		$this->request_channel_id 	= $config->request_channel_id;
		$this->bank_corpcode 		= $config->bank_corpcode;
		$this->bank_corpaccnum 		= $config->bank_corpaccnum;

		$this->http_client 			= $http_client;
		// @TEMP
		$this->http_client->tempSetUpOldConfig($config);

		$this->balance 				= new BankBalance(
			$this->http_client,
			$this->request_channel_id,
			$this->bank_corpcode,
			$this->bank_corpaccnum,
			$this->request_uuid,
			$this->bankapi_config
		);
	}

	public function configureClientCertificate($cert_filepath, $key_filepath, $key_password)
	{
		$this->cert_filepath 		= $cert_filepath;
		$this->privkey_filepath 	= $key_filepath;
		$this->privkey_password 	= $key_password;

		$this->http_client->configureClientCertificate(
			$this->cert_filepath,
			$this->privkey_filepath,
			$this->privkey_password
		);
	}

	public function getBalance()
	{
		$headers = [
			"Content-Type: application/json",
			"X-IBM-Client-Id: {$client_id}",
			"X-IBM-Client-Secret: {$client_secret}",
		];

		$request_body_subheader = [
			"SubHeader" => [
				"requestUUID" 			=> $request_uuid,
				"serviceRequestId" 		=> "OpenAPI",
				"serviceRequestVersion" => "1.0",
				"channelId" 			=> $request_channel_id,
			],
		];
		$body = [
			"GetAccountBalanceRequest" => $request_body_subheader,
			"GetAccountBalanceRequestBody" => [
				"channelId" 	=> $request_channel_id,
				"corpCode" 		=> $bank_corpcode,
				"corpAccNum" 	=> $bank_corpaccnum,
			],
		];
	}

	public function __get($property)
	{
		if (!in_array($property, $this->apis)) {
			throw new \Exception("Undefined property {$property} access");
		}


	}

}