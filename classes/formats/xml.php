<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Формат файла, описанного на сайте [bestchange.ru](http://www.bestchange.ru/wiki/rates.html).
 *
 * Например:
 *
 * ```
 * <?xml version="1.0" encoding="utf-8"?>
 * <rates>
 *  <item>
 *    <from>WMZ</from>
 *    <to>WMR</to>
 *    <in>1</in>
 *    <out>44.62</out>
 *    <amount>32092.23</amount>
 *    <param>manual</param>
 *  </item>
 *  <item>
 *    <from>WMR</from>
 *    <to>WMZ</to>
 *    <in>45.12</in>
 *    <out>1</out>
 *    <amount>1255.70</amount>
 *    <fromfee>0.8 %</fromfee>
 *  </item>
 * </rates>
 * ```
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFXml extends EFExportFile implements FilesParser, FilesGenerator
{

	public static function isThisFormat($content)
	{
		if(strpos($content, '<') === 0)
		{
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($content);

			if($xml && isset($xml->item))
			{
				return true;
			}
			elseif(!$xml)
			{
				libxml_clear_errors();
			}
		}

		return false;
	}

	public static function parse($content)
	{
		$rates = array();

		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($content);

		if(!$xml || !isset($xml->item))
		{
			if(!$xml)
			{
				libxml_clear_errors();
			}

			return $rates;
		}

		foreach($xml->item as $item)
		{
			$row = array
			(
				'from' => self::getSCS($item->from),
				'to' => self::getSCS($item->to),
				'in' => (float) $item->in,
				'out' => (float) $item->out,
				'amount' => $item->amount < 0 ? 0 : (float) $item->amount
			);

			if($item->minfee && $item->minfee > 0)
			{
				$row['in_min_fee'] = (float) $item->minfee;
			}

			if($item->fromfee && (float) $item->fromfee > 0)
			{
				$row['in_fee'] = ((float) $item->fromfee) . (strpos($item->fromfee, '%') !== false ? '%' : '');
			}

			if($item->tofee && (float) $item->tofee > 0)
			{
				$row['out_fee'] = ((float) $item->tofee) . (strpos($item->tofee, '%') !== false ? '%' : '');
			}

			if($item->param)
			{
				if(strpos($item->param, ',') !== false)
				{
					$row['options'] = preg_split('/\s*,\s*/', $item->param, -1, PREG_SPLIT_NO_EMPTY);
				}
				else
				{
					$row['options'] = (array) $item->param;
				}
			}

			$rates[] = $row;
		}

		return $rates;
	}

	public static function generate($rates)
	{
		header('Content-type: text/xml; charset=utf-8');

		$xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$xml .= '<rates>' . "\n";

		foreach($rates as $row)
		{
			$xml .= "\t" . '<item>' . "\n";
			$xml .= "\t\t" . '<from>' . $row['from'] . '</from>' . "\n";
			$xml .= "\t\t" . '<to>' . $row['to'] . '</to>' . "\n";
			$xml .= "\t\t" . '<in>' . $row['in'] . '</in>' . "\n";
			$xml .= "\t\t" . '<out>' . $row['out'] . '</out>' . "\n";
			$xml .= "\t\t" . '<amount>' . $row['amount'] . '</amount>' . "\n";
			$xml .= "\t" . '</item>' . "\n";
		}

		$xml .= '</rates>';

		return $xml;
	}

}

?>