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

	private $http_client;
	public $bankapi_config;

	public function __construct(BankApiConfig $config, HttpClient $http_client)
	{
		$this->bankapi_config = $config;

		$this->http_client 			= $http_client;
		// @TEMP
		$this->http_client->tempSetUpOldConfig($config);

		$this->balance 				= new BankBalance(
			$this->http_client,
			$config->request_channel_id,
			$config->bank_corpcode,
			$config->bank_corpaccnum,
			$config->request_uuid,
			$this->bankapi_config
		);
	}

	public function __get($property)
	{
		if (!in_array($property, $this->apis)) {
			throw new \Exception("Undefined property {$property} access");
		}


	}

}