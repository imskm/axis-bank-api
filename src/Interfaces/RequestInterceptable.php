<?php

namespace AxisBankApi\Interfaces;

use AxisBankApi\Interfaces\RequestBodyStruct;

interface RequestInterceptable
{
	public function processRequestBody(RequestBodyStruct &$req_body): string;
}