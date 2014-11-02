<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Базовый клас файлов экспорта.
 *
 * Содержит список устаревших и не соответвующих стандарту номеров
 * и названий валют, а также их новые и правильные названия.
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFExportFile
{

	/**
	 * @var array список устаревших и не соответвующих стандарту номеров
	 * и сигнатур валют, а также их новые и правильные сигнатуры.
	 */
	private static $_wrongSignatures = array
	(
		// стандарт
		1 => 'WMZ',
		2 => 'WMR',
		3 => 'WME',

		// не стандарт
		6 => 'YAMRUB',
		18 => 'WMB',
		20 => 'WMU',
		21 => 'WMG',
		55 => 'P24USD',
		56 => 'P24UAH',
		70 => 'QWRUB',

		'w1 usd' => 'WOUSD',
		'w1 uah' => 'WOUAH',
		'w1 rur' => 'WORUB',

		'btce' => 'BTCEUSD',
		'tcs' => 'TCSBRUB',
		'tcsrub' => 'TCSBRUB',
		'opb' => 'OPNBRUB',
		'opnrub' => 'OPNBRUB',
		'paxumusd' => 'PXMUSD',
		'kazkomkzt' => 'KKBKZT',
		'halykkzt' => 'HLKBKZT'
	);

	/**
	 * Проверяет, является ли сигнатура устаревшей или не соответвующей стандарту,
	 * и возвращает правильную, иначе возвращает сигнатуру без изменений.
	 * Обратите внимание, все сигнатуры приводятся к строковому типу данных.
	 * @param string $signature сигнатура валюты.
	 * @return string таже или новая сигнатура.
	 */
	protected static function getSCS($signature)
	{
		$signature = (string) $signature;

		$trueSignature = is_numeric($signature) ? $signature : strtolower($signature);

		if(isset(self::$_wrongSignatures[$trueSignature]))
		{
			$signature = self::$_wrongSignatures[$trueSignature];
		}

		return $signature;
	}

}

?>