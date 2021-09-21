<?php

namespace AxisBankApi\Interfaces;

interface ResponseBodyStruct
{
	public function __construct(string $root_propname);

	public function getResponseBodyPropName(): string;

	public function getEncryptedResponseBodyPropName(): string;

	public function getBodyProperties(): object;
	
	public function setBodyProperties(object $body): ResponseBodyStruct;

	public function getNonEncryptedResponsePayload(): object;

	public function getNonEncryptedResponsePayloadAsJsonString(): string;
	
	public function getEncryptedResponsePayload();
	
	public function getResponsePayloadHeader(): object;

	public function setEncryptedResponsePayload(object $response_payload): void;

}