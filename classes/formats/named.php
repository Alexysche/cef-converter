<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Формат структурированного текстового файла, с курсами эквивалентными 1.
 *
 * Например:
 *
 * ```
 * WMZ -> WMR: rate=0.0230445, reserve=32092.23
 * WMR -> WMZ: rate=45.12, reserve=1255.70
 * ```
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFNamed extends EFExportFile implements FilesParser
{

	public static function isThisFormat($content)
	{
		return preg_match('/^.+ -> .+: rate=.+, reserve=.+$/mU', $content);
	}

	public static function parse($content)
	{
		$rates = array();

		preg_match_all('/^(.+) -> (.+): rate=(.+), reserve=(.+)$/mU', $content, $rows);

		foreach($rows[1] as $i => $from)
		{
			if($rows[3][$i] > 0)
			{
				$rates[] = array
				(
					'from' => self::getSCS($from),
					'to' => self::getSCS($rows[2][$i]),
					'in' => (float) $rows[3][$i],
					'out' => 1,
					'amount' => $rows[4][$i] < 0 ? 0 : (float) $rows[4][$i]
				);
			}
		}

		return $rates;
	}

}

?>