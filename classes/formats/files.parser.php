<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Интерфейс обработки экспортных фалйов. Этот интерфейс должны реализовать
 * класы экспортных файлов, которые поддерживают обработку своего формата
 * файла (например парсинг).
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
interface FilesParser
{

	/**
	 * Проверяет, принадлежит ли файл текущему формату файлов.
	 * @param string $content содержимое проверяемого файла.
	 * @return boolean принадлежит ли файл текущему формату файлов.
	 */
	public static function isThisFormat($content);

	/**
	 * Осуществялет разбор файла и возвращает находящиеся в нем данные
	 * о курсах обмена.
	 * @param string $content содержимое файла, который необходимо разобрать.
	 * @return array данные находящиеся в файле. Каждый елемент массива содержит
	 * такой базовый набор елементов:
	 * - from: сигнатура валюты, которую пользователь переводит обменнику
	 * - to: сигнатура валюты, которую обменник переводит пользователю
	 * - in: сумма средств, которые пользователь переводит обменнику
	 * - out: сумма средств, которые обменник переводит пользователю
	 * - amount: доступный резерв валюты в обменнике
	 */
	public static function parse($content);

}

?>