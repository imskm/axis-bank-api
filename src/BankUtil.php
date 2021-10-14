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
		if ($message) {
			echo $message . PHP_EOL;
		}

		switch ($format) {
			case 'json':
				$result = json_encode($data, JSON_PRETTY_PRINT);
				echo $result;
				break;

			case 'pretty':
				print_r($data);
				break;

			case 'dump':
			default:
				var_dump($data);
				break;
		}
		echo "\n====================\n\n";
	}
}