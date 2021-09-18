<?php

namespace AxisBankApi\Interfaces;

interface RequestBodyStruct
{
	public function __construct(string $root_propname, string $request_uuid, string $request_channel_id);

	public function getRequestBodyPropName(): string;

	public function setBodyProperties(array $body): RequestBodyStruct;

	public function getBodyProperties(): array;

	public function getNonEncryptedRequestPayload(): array;
	
	public function getEncryptedRequestPayload(): array;

	public function getRequestPayloadHeader(): array;

	public function getEncryptedRequestPayloadAsJsonString(): string;

	public function appendBodyProperty(string $prop_name, string $prop_value): void;

	public function setEncryptedRequestBody(string $encoded_req_body): void;
}