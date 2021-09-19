<?php

namespace AxisBankApi\Interfaces;

use AxisBankApi\BankApiConfig;

interface ResponseInterceptable
{
	public function __construct(BankApiConfig &$bankapi_config);

	public function processResponseBody(string $res_payload, ResponseBodyStruct &$res_body): object;
}