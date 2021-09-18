<?php

namespace AxisBankApi\Interfaces;

interface ResponseInterceptable
{
	public function processResponseBody(string $res_payload, ResponseBodyStruct &$res_body): object;
}