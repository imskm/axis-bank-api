<?php

namespace AxisBankApi;

/**
 * BankUtil
 */
class BankUtil
{
	/**
	 * @param $format string  json|dump|pretty
	 *  					  dump -> var_dump(), pretty -> print_r()
	 * 
	 * 
	 */
	public static function printDebugLines(array $data, $message = '', $format = 'json'): void
	{
		echo "\n\n====================\n";
		switch ($format) {
			case 'json':
				if ($message) echo $message . ' (json):' . PHP_EOL;
				$result = json_encode($data, JSON_PRETTY_PRINT);
				echo $result;
				break;

			case 'pretty':
				if ($message) echo $message . ' (pretty):' . PHP_EOL;
				print_r($data);
				break;

			case 'dump':
			default:
				if ($message) echo $message . ' (dump):' . PHP_EOL;
				var_dump($data);
				break;
		}
		echo "\n====================\n\n";
	}
}