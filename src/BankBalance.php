<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;

class BankBalance
{
	private $http_client;

	private $channel_id;
	private $bank_corpcode;
	private $bank_corpaccnum;
	private $request_uuid;

	private $bankapi_config;

	public function __construct(HttpClient &$http_client, $channel_id, $corpcode, $corpaccnum, $request_uuid, &$config)
	{
		$this->http_client 			= $http_client;
		$this->channel_id 			= $channel_id;
		$this->bank_corpcode 		= $corpcode;
		$this->bank_corpaccnum 		= $corpaccnum;
		$this->request_uuid 		= $request_uuid;
		$this->bankapi_config 		= $config;
	}

	public function get()
	{
		$request_body = new BankRequestBody(
			BankApi::PROPNAME_GET_BALANCE,
			$this->request_uuid,
			$this->channel_id
		);
		$request_body->setBodyProperties([
			"channelId" 	=> $this->channel_id,
			"corpCode" 		=> $this->bank_corpcode,
			"corpAccNum" 	=> $this->bank_corpaccnum,
		]);

		$url = $this->bankapi_config->api_url_get_balance;
		$response = $this->http_client->request($url, $request_body);

		// @TODO check response status (API request ran successfully or not)
		$response_data = $response->data;
		echo "\n\nBank Balance - Get Balance:\n";
		var_dump($response_data);

		return $response_data->Balance;
	}
}