<?php

namespace AxisBankApi;

use AxisBankApi\Interfaces\RequestBodyStruct;

class BankRequestBody implements RequestBodyStruct
{
	public $root_propname;
	private $request_body = [];
	private $request_body_final = "";
	private $propname_suffix = "Request";
	private $propname_suffix_body = "RequestBody";

	private $request_uuid;
	private $channel_id;

	public function __construct(string $root_propname, string $request_uuid, string $channel_id)
	{
		$this->root_propname = $root_propname;
		$this->request_uuid = $request_uuid;
		$this->channel_id = $channel_id;
	}

	public function getRootPropName()
	{
		return $this->root_propname . $this->propname_suffix;
	}

	public function getRequestBodyPropName(): string
	{
		return $this->root_propname . $this->propname_suffix_body;
	}

	public function getEncryptedRequestBodyPropName(): string
	{
		return $this->getRequestBodyPropName() . "Encrypted";
	}

	public function setBodyProperties(array $body): RequestBodyStruct
	{
		$this->request_body = $body;

		return $this;
	}

	public function getBodyProperties(): array
	{
		return $this->request_body;
	}

	public function getNonEncryptedRequestPayload(): array
	{
		$sub_header = $this->getRequestPayloadHeader();
		$body_propname = $this->getRequestBodyPropName();

		$full_payload[$this->getRootPropName()]["SubHeader"] = $sub_header;
		$full_payload[$this->getRootPropName()][$body_propname] = $this->getBodyProperties();

		return $full_payload;
	}

	public function getEncryptedRequestPayload(): array
	{
		if (!$this->request_body_final) {
			throw new \Exception("Encoded encrypted request body is not set.");
		}
		$sub_header = $this->getRequestPayloadHeader();
		$body_propname = $this->getEncryptedRequestBodyPropName();

		$full_payload[$this->getRootPropName()]["SubHeader"] = $sub_header;
		$full_payload[$this->getRootPropName()][$body_propname] = $this->request_body_final;

		return $full_payload;
	}

	public function getRequestPayloadHeader(): array
	{
		return [
			"requestUUID" 			=> $this->request_uuid,
			"serviceRequestId" 		=> "OpenAPI",
			"serviceRequestVersion" => "1.0",
			"channelId" 			=> $this->channel_id,
		];
	}


	public function getEncryptedRequestPayloadAsJsonString(): string
	{
		$request_payload = $this->getEncryptedRequestPayload();

		$request_payload_json = json_encode($request_payload);
		if ($request_payload_json === false) {
			throw new \Exception("Failed to encode request payload in JSON format.");
		}

		return $request_payload_json;
	}

	public function appendBodyProperty(string $prop_name, string $prop_value): void
	{
		if (!$prop_name) {
			throw new \Exception("Invalid prop name");
		}
		$this->request_body[$prop_name] = $prop_value;
	}

	public function setEncryptedRequestBody(string $encoded_req_body): void
	{
		$this->request_body_final = $encoded_req_body;
	}
}