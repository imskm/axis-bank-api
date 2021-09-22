<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;
use AxisBankApi\Exceptions\ResponsePayloadFailure;

class BankBalance
{
	private $http_client;
	private $bankapi_config;
	private $to_bank_account;
	private $fund_transfer_method = BankApi::TRANSFER_METHOD_IMPS;
	public $response_body;
	public $response_data;
	public $txn_ref;

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
		$this->response_body = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$this->response_data = $this->response_body->data;
		echo "\n\nBank Balance - Get Balance:\n";
		var_dump($this->response_data);
		if ($this->response_body->status === "F") {
			throw new ResponsePayloadFailure($this->response_body->message);
		}

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
		$this->txn_ref = "" . random_int(1000, 999999);
		$request_body->setBodyProperties([
			"channelId" 	=> $this->bankapi_config->request_channel_id,
			"corpCode" 		=> $this->bankapi_config->bank_corpcode,
			"paymentDetails" => [
				[
					"txnPaymode"	=> $this->fund_transfer_method, // IMPS
					"custUniqRef"	=> $this->txn_ref,
					"corpAccNum" 	=> $this->bankapi_config->bank_corpaccnum,
					"valueDate"		=> date("Y-m-d"),
					"txnAmount" 	=> $txn_amount,
					"beneName"		=> $this->to_bank_account->acct_holder,
					"beneCode"		=> $this->to_bank_account->bene_code,
					"beneAccNum"	=> $this->to_bank_account->acct_number,
					"beneIfscCode"	=> $this->to_bank_account->bank_ifsc,
					"beneBankName"	=> $this->to_bank_account->bank_name,
				],
			],
		]);

		$url = $this->bankapi_config->api_url_fund_transfer;
		$this->response_body = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$this->response_data = $this->response_body->data;
		echo "\n\nBank Balance - Fund Transfer:\n";
		var_dump($this->response_data);
		if ($this->response_body->status === "F") {
			throw new ResponsePayloadFailure($this->response_body->message);
		}

		return $this->response_body->status === "S";
	}

	public function transferRTGS($txn_amount)
	{
		$this->fund_transfer_method = BankApi::TRANSFER_METHOD_RTGS;

		return $this->transfer($txn_amount);
	}

	public function transferNEFT($txn_amount)
	{
		$this->fund_transfer_method = BankApi::TRANSFER_METHOD_NEFT;

		return $this->transfer($txn_amount);
	}

	public function transferIMPS($txn_amount)
	{
		$this->fund_transfer_method = BankApi::TRANSFER_METHOD_IMPS;

		return $this->transfer($txn_amount);
	}

	// @TODO Only Axis to Axis is allowed
	public function transferFT($txn_amount)
	{
		$this->fund_transfer_method = BankApi::TRANSFER_METHOD_FUNDTRANSFER;

		return $this->transfer($txn_amount);
	}

	/**
	 * @param $txn_ref string|int|array Transaction reference number
	 *
	 * @return mixed  If $txn_ref is string then the return value will be single object
	 *                or if $txn_ref is an array then return value will be array of
	 *                objects each representing status of single transaction
	 */
	public function transferStatus($txn_ref)
	{
		$request_body = new BankRequestBody(
			BankApi::PROPNAME_GET_STATUS,
			$this->bankapi_config->request_uuid,
			$this->bankapi_config->request_channel_id
		);
		$request_body->setBodyProperties([
			"channelId" 	=> $this->bankapi_config->request_channel_id,
			"corpCode" 		=> $this->bankapi_config->bank_corpcode,
			"crn" 			=> $txn_ref,
		]);

		$url = $this->bankapi_config->api_url_get_status;
		$this->response_body = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$this->response_data = $this->response_body->data;
		$s = sprintf("\n\nBank Balance - Get Status of '%s':\n", serialize($txn_ref));
		echo $s;
		var_dump($this->response_data);
		if ($this->response_body->status === "F") {
			throw new ResponsePayloadFailure($this->response_body->message);
		}

		return !is_array($txn_ref)
			? array_pop($this->response_data->CUR_TXN_ENQ)
			: $this->response_data->CUR_TXN_ENQ;
	}
}