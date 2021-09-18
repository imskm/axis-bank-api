<?php

namespace AxisBankApi\Interfaces;

interface RequestAdapter
{
	public function __construct(array $body);

	public function body(): array;

	public function bodyToJson(): array;
}