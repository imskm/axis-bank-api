<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;
use AxisBankApi\BankApiConfig;
use AxisBankApi\BankResponseBody;
use AxisBankApi\Traits\RequestInterceptor;
use AxisBankApi\Traits\ResponseInterceptor;
use AxisBankApi\Interfaces\RequestBodyStruct;
use AxisBankApi\Interfaces\RequestInterceptable;
use AxisBankApi\Interfaces\ResponseInterceptable;

class HttpClient implements RequestInterceptable, ResponseInterceptable
{
	private $key;
	private $client_id;
	private $client_secret;

	private $cert_filepath;
	private $privkey_filepath;
	private $privkey_password;

	private $curl;

	use RequestInterceptor, ResponseInterceptor;

	public function __construct(
		$privkey_filepath,
		$privkey_password,
		$cert_filepath,
		$client_id,
		$client_secret
	)
	{
		$this->privkey_filepath = $privkey_filepath;
		$this->privkey_password = $privkey_password;
		$this->cert_filepath 	= $cert_filepath;
		$this->client_id 		= $client_id;
		$this->client_secret 	= $client_secret;


		$this->curl 				= curl_init();
		if ($this->curl === false) {
			throw new \Exception("curl_init: Failed to create curl handler.");
		}

		$this->setupCurl();
		$this->configure2WaySSL();
	}

	public function tempSetUpOldConfig(BankApiConfig &$config)
	{
		$this->key 					= $config->key;
	}

	private function setupCurl()
	{
		$headers = [
			"Content-Type: application/json",
			"X-IBM-Client-Id: {$this->client_id}",
			"X-IBM-Client-Secret: {$this->client_secret}",
		];

		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, '2');
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, '1');
		curl_setopt($this->curl, CURLOPT_VERBOSE, true);
		curl_setopt($this->curl, CURLOPT_POST, true);
	}

	private function configure2WaySSL()
	{
		curl_setopt($this->curl, CURLOPT_SSLCERT, $this->cert_filepath);
		curl_setopt($this->curl, CURLOPT_SSLKEY, $this->privkey_filepath);
		curl_setopt($this->curl, CURLOPT_KEYPASSWD, $this->privkey_password);
	}

	public function request(string $url, RequestBodyStruct $req_body)
	{
		// 1. Get the JSON request body from request interceptor
		$req_payload = $this->processRequestBody($req_body);

		// 2. Setup the API request URL
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $req_payload);

		// 3. Fire off the request
		// 4. Get the response from Bank server
		$res_payload = curl_exec($this->curl);
		if ($res_payload === false) {
			// @TEMP
			print_r(curl_getinfo($this->curl));
			throw new \Exception("curl_exec: " . curl_error($this->curl));
		}

		// 5. Get the PHP object version of response body from response interceptor
		$res_body = new BankResponseBody($req_body->root_propname);
		$response = $this->processResponseBody($res_payload, $res_body);
		
		// 6. Return the result to caller
		return $response;
	}
}