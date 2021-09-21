<?php

namespace AxisBankApi\Interceptors;

use AxisBankApi\BankApiConfig;
use AxisBankApi\Interfaces\ResponseBodyStruct;
use AxisBankApi\Interfaces\ResponseInterceptable;

class ResponseInterceptor implements ResponseInterceptable
{
	private $bankapi_config;

	private $key;
	private $cipher;

	public function __construct(BankApiConfig &$bankapi_config)
	{
		$this->bankapi_config = $bankapi_config;

		$this->key = $bankapi_config->key;
		$this->cipher = $bankapi_config->cipher;
	}

	public function processResponseBody(string $res_payload, ResponseBodyStruct &$res_body): object
	{
		// JSON decode response payload.
		$response = json_decode($res_payload, false);
		if ($response === false) {
			throw new \Exception("Failed to decode response payload");
		}

		$root_propname = $res_body->getRootPropName();
		$body_propname = $res_body->getEncryptedResponseBodyPropName();

		if (!isset($response->{$root_propname}->{$body_propname})) {
			throw new \Exception("Invalid response structure, missing expected properties.");
		}

		$response_encoded_body = $response->{$root_propname}->{$body_propname};
		$ciphertext = base64_decode($response_encoded_body);

		// @NOTE @CLEANUP $this->cipher is coming from different tait which is not good at all
		$key = hex2bin($this->key);
		if ($key === false) {
			throw new \Exception("hex2bin: failed to covert hex key to bin");
		}
		$plaintext = $this->decryptResponse($ciphertext, $key, $this->cipher);
		$response_decrypted_body = json_decode($plaintext, false);
		if ($response_decrypted_body === false) {
			throw new \Exception("Failed to parse decrypted JSON response");
		}

		// Populate $res_body (ResponseBodyStruct)
		$res_body->setEncryptedResponsePayload($response);
		$res_body->setBodyProperties($response_decrypted_body);

		// @TODO Verify checksum

		echo "Non encrypted Response body\n";
		var_dump($res_body->getNonEncryptedResponsePayload());
		echo "\nNon encrypted response body (JSON)\n";
		echo $res_body->getNonEncryptedResponsePayloadAsJsonString();
		echo "\n";

		// Check the status of the response
		return $res_body->getBodyProperties();
	}

	private function decryptResponse($ciphertext, $key, $cipher)
	{
		// 1. Extract IV first
		$iv = substr($ciphertext, 0, 16);
		$actual_response_body_payload = substr($ciphertext, 16);

		// 2. Set the flags for openssl_decrypt
		$options = OPENSSL_RAW_DATA;

		// 3. Call openssl_decrypt()
		$plaintext = openssl_decrypt(
			$actual_response_body_payload,
			$cipher,
			$key,
			$options,
			$iv
		);

		if ($plaintext === false) {
			throw new \Exception("Failed to decrypt response: " . openssl_error_string());
		}

		// 4. Done
		return $plaintext;
	}
}