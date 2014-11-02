<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Формат файла, описанного на сайте [estandards.ru](http://estandards.ru/formirovanie-eksportnogo-fajla-s-kursami/).
 *
 * Например:
 *
 * ```
 * WMZ;WMR;1;44.62;32092.23
 * WMR;WMZ;45.12;1;1255.70;
 * ```
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFEStandards extends EFExportFile implements FilesParser
{

	public static function isThisFormat($content)
	{
		return preg_match('/^(.+;){4}.+$/mU', $content);
	}

	public static function parse($content)
	{
		$rates = array();

		$rows = explode("\n", str_replace("\r", '', $content));

		foreach($rows as $row)
		{
			$row = explode(';', $row);

			if(isset($row[4]) && $row[2] > 0 && $row[3] > 0)
			{
				$rates[] = array
				(
					'from' => self::getSCS($row[0]),
					'to' => self::getSCS($row[1]),
					'in' => (float) $row[2],
					'out' => (float) $row[3],
					'amount' => $row[4] < 0 ? 0 : (float) $row[4]
				);
			}
		}

		return $rates;
	}

}

?>