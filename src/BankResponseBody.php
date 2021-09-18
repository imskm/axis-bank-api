<?php

namespace AxisBankApi;

use AxisBankApi\Interfaces\ResponseBodyStruct;

class BankResponseBody implements ResponseBodyStruct
{
	public $root_propname;
	private $response_body = "";
	private $response_body_final = [];
	private $response_payload = [];
	private $propname_suffix = "Response";
	private $propname_suffix_body = "ResponseBody";

	public function __construct(string $root_propname)
	{
		$this->root_propname = $root_propname;
	}

	public function getRootPropName()
	{
		return $this->root_propname . $this->propname_suffix;
	}

	public function getResponseBodyPropName(): string
	{
		return $this->root_propname . $this->propname_suffix_body;
	}

	public function getEncryptedResponseBodyPropName(): string
	{
		return $this->getResponseBodyPropName() . "Encrypted";
	}

	public function getBodyProperties(): object
	{
		return $this->response_body;
	}

	public function setBodyProperties(object $body): ResponseBodyStruct
	{
		$this->response_body = $body;

		return $this;
	}

	public function getNonEncryptedResponsePayload(): object
	{
		$result = [
			$this->getRootPropName() => [
				"SubHeader" => $this->getResponsePayloadHeader(),
				$this->getResponseBodyPropName() => $this->getBodyProperties()
			]
		];
		// $response_body_as_obj = new \stdClass();
		// $response_body_as_obj->{$this->getRootPropName()} = new \stdClass();
		// $response_body_as_obj->{$this->getRootPropName()}->SubHeader = $this->getResponsePayloadHeader();
		return (object) $result;
	}
	
	public function getEncryptedResponsePayload()
	{
		return $this->response_payload;
	}

	public function getResponsePayloadHeader(): object
	{
		if (!isset($this->response_payload->{$this->getRootPropName()})) {
			throw new \Exception("Invalid response structure, missing {$this->getRootPropName()} object property");
		}

		if (!isset($this->response_payload->{$this->getRootPropName()}->SubHeader)) {
			throw new \Exception("Invalid response structure, missing SubHeader object property");
		}

		return $this->response_payload->{$this->getRootPropName()}->SubHeader;
	}

	public function setEncryptedResponsePayload(object $response_payload): void
	{
		$this->response_payload = $response_payload;
	}
}