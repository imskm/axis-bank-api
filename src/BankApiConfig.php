<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;

class BankApiConfig
{
	public $key;
	public $request_uuid;
	public $request_channel_id;
	public $bank_corpcode;
	public $bank_corpaccnum;
	public $base_api_url;
	public $cipher = "aes-128-cbc";

	public $api_url_get_balance;
	public $api_url_fund_transfer;
	public $api_url_get_status;

	public function __construct(
		$key,
		$request_uuid,
		$request_channel_id,
		$bank_corpcode,
		$bank_corpaccnum,
		$base_api_url
	)
	{
		$this->key 					= $key;
		$this->request_uuid 		= $request_uuid;
		$this->request_channel_id 	= $request_channel_id;
		$this->bank_corpcode 		= $bank_corpcode;
		$this->bank_corpaccnum 		= $bank_corpaccnum;
		$this->base_api_url 		= $base_api_url;

		$this->api_url_get_balance = $this->base_api_url . BankApi::URL_GET_BALANCE;
		$this->api_url_fund_transfer = $this->base_api_url . BankApi::URL_FUND_TRANSFER;
		$this->api_url_get_status = $this->base_api_url . BankApi::URL_GET_STATUS;
	}
}