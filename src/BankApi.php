<?php

namespace AxisBankApi;

use AxisBankApi\HttpClient;
use AxisBankApi\BankApiConfig;
use AxisBankApi\Interceptors\RequestInterceptor;
use AxisBankApi\Interceptors\ResponseInterceptor;

class BankApi
{
	const PROPNAME_GET_BALANCE 	= "GetAccountBalance";
	const PROPNAME_FUND_TRANSFER = "TransferPayment";
	const PROPNAME_GET_STATUS 	= "GetStatus";

	const URL_GET_STATUS 		= "/acct-recon/get-status";
	const URL_GET_BALANCE 		= "/acct-recon/get-balance";
	const URL_BENEFICIARY_REG 	= "/payee-mgmt/beneficiary-registration";
	const URL_BENEFICIARY_ENQ 	= "/payee-mgmt/beneficiary-enquiry";
	const URL_FUND_TRANSFER 	= "/payments/transfer-payment";

	const TRANSFER_METHOD_RTGS 	= "RT";
	const TRANSFER_METHOD_NEFT 	= "NE";
	const TRANSFER_METHOD_IMPS 	= "PA";
	const TRANSFER_METHOD_FUNDTRANSFER 	= "FT";
	const TRANSFER_METHOD_CORPCHEQUE 	= "CC";
	const TRANSFER_METHOD_DDRAFT 		= "DD";

	public $balance;
	protected $apis = [
		"balance", "benficiary"
	];

	private $http_client;
	public $bankapi_config;

	public function __construct(BankApiConfig $config, HttpClient $http_client)
	{
		$this->bankapi_config = $config;

		$this->http_client = $http_client;
		$this->http_client->setRequestInterceptor(new RequestInterceptor($this->bankapi_config));
		$this->http_client->setResponseInterceptor(new ResponseInterceptor($this->bankapi_config));

		$this->balance = new BankBalance($this->http_client, $this->bankapi_config);
	}

	public function __get($property)
	{
		if (!in_array($property, $this->apis)) {
			throw new \Exception("Undefined property {$property} access");
		}


	}

}