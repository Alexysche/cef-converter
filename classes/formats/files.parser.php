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
	 * @return array данные находящиеся в файле.
	 */
	public static function parse($content);

}

?>