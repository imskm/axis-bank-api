<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;

class BankBalance
{
	private $http_client;
	private $bankapi_config;
	private $to_bank_account;
	public $response_full;
	public $response_data;

	public function __construct(HttpClient &$http_client, &$bankapi_config)
	{
		$this->http_client 			= $http_client;
		$this->bankapi_config 		= $bankapi_config;
	}

	public function get()
	{
		$request_body = new BankRequestBody(
			BankApi::PROPNAME_GET_BALANCE,
			$this->bankapi_config->request_uuid,
			$this->bankapi_config->request_channel_id
		);
		$request_body->setBodyProperties([
			"channelId" 	=> $this->bankapi_config->request_channel_id,
			"corpCode" 		=> $this->bankapi_config->bank_corpcode,
			"corpAccNum" 	=> $this->bankapi_config->bank_corpaccnum,
		]);

		$url = $this->bankapi_config->api_url_get_balance;
		$this->response_full = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$this->response_data = $this->response_full->data;
		echo "\n\nBank Balance - Get Balance:\n";
		var_dump($this->response_data);

		return $this->response_data->Balance;
	}

	public function to(object $bank_account)
	{
		// @TODO Validate $bank_account
		// Check for required fields are present and in correct format
		$this->to_bank_account = $bank_account;

		return $this;
	}

	public function transfer($txn_amount)
	{
		if (!is_numeric($txn_amount)) {
			throw new \Exception("Invalid transaction amount, expected number");
		}

		// If Beneficiary Bank Account is not set then bell out
		if (!$this->to_bank_account) {
			throw new \Exception("Beneficiary is not set, please set it before calling transfer");
		}

		$request_body = new BankRequestBody(
			BankApi::PROPNAME_FUND_TRANSFER,
			$this->bankapi_config->request_uuid,
			$this->bankapi_config->request_channel_id
		);

		// Setup the Transfer Payment data body
		// @TODO Write a better unique txn ref generator
		$this->txn_ref = random_int(1000, 999999);
		$request_body->setBodyProperties([
			"channelId" 	=> $this->bankapi_config->request_channel_id,
			"corpCode" 		=> $this->bankapi_config->bank_corpcode,
			"paymentDetails" => [
				[
					"txnPaymode"	=> "PA", // IMPS
					"custUniqRef"	=> $this->txn_ref,
					"corpAccNum" 	=> $this->bankapi_config->bank_corpaccnum,
					"valueDate"		=> date("Y-m-d"),
					"txnAmount" 	=> $txn_amount,
					"beneName"		=> $this->to_bank_account->acct_holder,
					"beneAccNum"	=> $this->to_bank_account->acct_number,
					"beneIfscCode"	=> $this->to_bank_account->bank_ifsc,
					"beneBankName"	=> $this->to_bank_account->bank_name,
				],
			],
		]);

		$url = $this->bankapi_config->api_url_fund_transfer;
		$this->response_full = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$this->response_data = $this->response_full->data;
		echo "\n\nBank Balance - Fund Transfer:\n";
		var_dump($this->response_data);

		return $this->response_data->status === "S";
	}
}