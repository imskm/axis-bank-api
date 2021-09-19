<?php

namespace AxisBankApi\Interfaces;

use AxisBankApi\BankApiConfig;
use AxisBankApi\Interfaces\RequestBodyStruct;

interface RequestInterceptable
{
	public function __construct(BankApiConfig &$bankapi_config);

	public function processRequestBody(RequestBodyStruct &$req_body): string;
}