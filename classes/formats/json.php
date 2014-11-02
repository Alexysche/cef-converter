<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Формат файла, описанного на сайте [kurses.com.ua](http://kurses.com.ua/for-exchangers/formats#json).
 *
 * Например:
 *
 * ```
 * {
 *   "rates":
 *   [
 *     {
 *       "from": "WMZ",
 *       "to": "WMR",
 *       "in": 1,
 *       "out": 44.62,
 *       "amount": 32092.23
 *     },
 *     {
 *       "from": "WMR",
 *       "to": "WMZ",
 *       "in": 45.12,
 *       "out": 1,
 *       "amount": 1255.70,
 *       "in_min_fee": 5,
 *       "in_min_amount": 100,
 *       "out_min_fee": "1%",
 *       "options":
 *       [
 *         "manual"
 *       ]
 *     }
 *   ]
 * }
 * ```
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFJson extends EFExportFile implements FilesGenerator
{

	public static function generate($rates)
	{
		return json_encode(array('rates' => $rates));
	}

}

?>