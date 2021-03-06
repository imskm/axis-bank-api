<?php

namespace AxisBankApi\Interceptors;

use AxisBankApi\BankUtil;
use AxisBankApi\BankApiConfig;
use AxisBankApi\Interfaces\RequestBodyStruct;
use AxisBankApi\Interfaces\RequestInterceptable;

class RequestInterceptor implements RequestInterceptable
{
	private $bankapi_config;
	private $cipher = "aes-128-cbc";
	private $key;

	public function __construct(BankApiConfig &$bankapi_config)
	{
		$this->bankapi_config = $bankapi_config;
	
		$this->cipher = $bankapi_config->cipher;
		$this->key = $bankapi_config->key;
	}

	public function processRequestBody(RequestBodyStruct &$req_body): string
	{
		// Convert Hex version of $key to it's binary form
		// to be used by openssl_encrypt()
		$key = hex2bin($this->key);
		if ($key === false) {
			throw new \Exception("hex2bin: failed to covert hex key to bin");
		}

		$checksum = $this->generateChecksum($req_body->getBodyProperties());
		$req_body->appendBodyProperty("checksum", $checksum);
		$plaintext = json_encode($req_body->getBodyProperties());
		if ($plaintext === false) {
			throw new \Exception("Failed to encode into JSON");
		}

		if ($this->bankapi_config->verbosityLevel() >= 2) {
			BankUtil::printDebugLines(
				$req_body->getNonEncryptedRequestPayload(),
				"Non Encrypted Request Body:",
				"pretty"
			);
		}

		// Generate random IV (Initialisation Vector)
		// Check if PHP has $cipher algo available
		$cipher = $this->cipher;
		if (!in_array($cipher, openssl_get_cipher_methods())) {
			throw new \Exception("{$cipher} is not available");
		}

		$ivlen 	= openssl_cipher_iv_length($cipher);
		$iv  	= openssl_random_pseudo_bytes($ivlen);

		// Encrypt the request body
		$options =  OPENSSL_RAW_DATA;
		$ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options, $iv);
		if ($ciphertext === false) {
			$error_msg = openssl_error_string();
			throw new \Exception("openssl_encrypt: {$error_msg}");
		}

		// Prepending $iv in $ciphertext for reciever to use iv for decryption
		$iv_ciphertext = $iv . $ciphertext;

		// base64 encode the final cipher text
		$request_body_encrypted = base64_encode($iv_ciphertext);
		$req_body->setEncryptedRequestBody($request_body_encrypted);

		// Final Request Body Payload
		$request_body = $req_body->getEncryptedRequestPayloadAsJsonString();

		return $request_body;
	}

	private function generateChecksum(array $data)
	{
		$data_str = $this->stringifyArrayData($data);

		return md5($data_str);
	}

	private function stringifyArrayData(array $data)
	{
		$data_str = "";
		// Base case of recursive function
		if (!$data) {
			return $data_str;
		}
		foreach ($data as $d) {
			if (is_array($d) || is_iterable($d)) {
				$data_str .= $this->stringifyArrayData($d);
			} else {
				$data_str .= $d;
			}
		}

		return $data_str;
	}
}