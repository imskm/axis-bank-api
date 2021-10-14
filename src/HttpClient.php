<?php

namespace AxisBankApi;

use AxisBankApi\BankApi;
use AxisBankApi\BankApiConfig;
use AxisBankApi\BankResponseBody;
use AxisBankApi\Interfaces\RequestBodyStruct;
use AxisBankApi\Interfaces\RequestInterceptable;
use AxisBankApi\Interfaces\ResponseInterceptable;

class HttpClient
{
	private $client_id;
	private $client_secret;

	private $cert_filepath;
	private $privkey_filepath;
	private $privkey_password;

	private $curl;

	private $req_interceptor;
	private $res_interceptor;

	public $verbosity;

	public function __construct(
		$privkey_filepath,
		$privkey_password,
		$cert_filepath,
		$client_id,
		$client_secret,
		$verbosity = 0
	)
	{
		$this->privkey_filepath = $privkey_filepath;
		$this->privkey_password = $privkey_password;
		$this->cert_filepath 	= $cert_filepath;
		$this->client_id 		= $client_id;
		$this->client_secret 	= $client_secret;
		$this->verbosity 		= $verbosity;


		$this->curl 				= curl_init();
		if ($this->curl === false) {
			throw new \Exception("curl_init: Failed to create curl handler.");
		}

		$this->setupCurl();
		$this->configure2WaySSL();
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
		curl_setopt($this->curl, CURLOPT_POST, true);
		if (is_int($this->verbosity) && $this->verbosity >= 3) {
			curl_setopt($this->curl, CURLOPT_VERBOSE, true);
		}
	}

	private function configure2WaySSL()
	{
		curl_setopt($this->curl, CURLOPT_SSLCERT, $this->cert_filepath);
		curl_setopt($this->curl, CURLOPT_SSLKEY, $this->privkey_filepath);
		curl_setopt($this->curl, CURLOPT_KEYPASSWD, $this->privkey_password);
	}

	public function setRequestInterceptor(RequestInterceptable $req_interceptor)
	{
		$this->req_interceptor = $req_interceptor;
	}

	public function setResponseInterceptor(ResponseInterceptable $res_interceptor)
	{
		$this->res_interceptor = $res_interceptor;
	}

	public function request(string $url, RequestBodyStruct $req_body)
	{
		// 1. Get the JSON request body from request interceptor
		if ($this->req_interceptor) {
			$req_payload = $this->req_interceptor->processRequestBody($req_body);
		} else {
			// @TODO Test this case when request interceptor is not set then
			// $req_payload is generated correctly for curl
			$req_payload = $req_body->getBodyProperties();
		}

		// 2. Setup the API request URL
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $req_payload);

		// 3. Fire off the request
		// 4. Get the response from Bank server
		$res_payload = curl_exec($this->curl);
		if ($res_payload === false) {
			throw new \Exception("curl_exec: " . curl_error($this->curl));
		}

		// 5. Get the PHP object version of response body from response interceptor
		$res_body = new BankResponseBody($req_body->root_propname);
		if ($this->res_interceptor) {
			// @NOTE $response is response body coming from bank server in PHP Array
			// you can do additional processing here with this data if you want
			$response = $this->res_interceptor->processResponseBody($res_payload, $res_body);
		} else {
			// @TODO Test this case when response interceptor is not set then
			// then call the default interceptor.
			$response = $res_body;
		}
		
		// 6. Return the result to caller
		return $res_body;
	}
}