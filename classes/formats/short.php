<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Формат сокращенного текстового файла, с курсами эквивалентными 1.
 *
 * Например:
 *
 * ```
 * WMZ,WMR,0.0230445,32092.23;
 * WMR,WMZ,45.12,1255.70;
 * ```
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFShort extends EFExportFile implements FilesParser
{

	public static function isThisFormat($content)
	{
		return preg_match('/^(.+,){3}.+;$/mU', $content);
	}

	public static function parse($content)
	{
		$rates = array();

		$rows = explode("\n", str_replace(array("\r", ';'), '', $content));

		foreach($rows as $row)
		{
			$row = explode(',', $row);

			if(isset($row[3]) && $rows[2] > 0)
			{
				$rates[] = array
				(
					'from' => self::getSCS($row[0]),
					'to' => self::getSCS($row[1]),
					'in' => (float) $row[2],
					'out' => 1,
					'amount' => $row[3] < 0 ? 0 : (float) $row[3]
				);
			}
		}

		return $rates;
	}

}

?>