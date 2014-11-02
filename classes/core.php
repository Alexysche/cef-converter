<?php

/**
 * @copyright Copyright (c) 2014, Ходаковский Алексей
 */

/**
 * Базовый клас генератора новых форматов экспортных файлов,
 * на основе устаревших форматов файлов.
 *
 * В его фукнции входит валидация данных с формы и генерация
 * файлов экспорта.
 *
 * @author Ходаковский Алексей <alexboxmy@list.ru>
 */
class EFCore
{

	/**
	 * Название файла с настройками для новых экспортных файлов.
	 */
	const CONFIG_NAME = 'config.json';

	/**
	 * Название файла с информацией о том, как пользоваться этим генератором.
	 */
	const README_NAME = 'readme.md';

	/**
	 * @var string адрес размещения каталога генератора, на сайте.
	 */
	public static $baseUrl;

	/**
	 * @var array список псевдонимов путей к каталогам.
	 */
	private static $_paths = array
	(
		'root' => '',
		'htm' => 'htm/',
		'classess' => 'classes/',
		'formats' => 'classes/formats/'
	);

	/**
	 * @var array список известных форматов файлов и их класов-обработчиков.
	 */
	private static $_formats = array
	(
		'short' => 'EFShort',
		'named' => 'EFNamed',
		'estandards' => 'EFEStandards',
		'xml' => 'EFXml',
		'json' => 'EFJson'
	);

	/**
	 * @var string путь к файлу с настройками.
	 */
	private static $_configPath;

	/**
	 * Инциализация пареметров, необходимых для генератора файлов.
	 * Создает абсолютные пути к файлам и определяет адрес размещения
	 * папки генератора файлов.
	 * @param string $root абсолютный путь к папке генератора файлов.
	 */
	public static function init($root)
	{
		foreach(self::$_paths as &$path)
		{
			$path = $root . strtr($path, '/', DIRECTORY_SEPARATOR);
		}

		self::$_configPath = self::getFullPath() . self::CONFIG_NAME;

		self::$baseUrl = str_replace(array('\\', '/'), '/', dirname($_SERVER['PHP_SELF'])) . '/';
	}

	/**
	 * Возвращает абсолютный путь согласно указанного псевдонима.
	 * Если указан еще и относительный путь то он будет добавлен к бсолютному.
	 * Если метод вызван без параметров то, будет возвращен абсолютный путь
	 * к какталогу генератора файлов.
	 * @param string $alias псевдоним пути к каталогу.
	 * @param string $path путь относительно указанного каталога.
	 * @return string абсолютный путь согласно указанного псевдонима.
	 */
	public static function getFullPath($alias = null, $path = null)
	{
		$aliasPath = isset($alias) && isset(self::$_paths[$alias]) ? self::$_paths[$alias] : self::$_paths['root'];

		return isset($path) ? $aliasPath . $path : $aliasPath;
	}

	/**
	 * Опеределяет параметры, переданные в строке адреса.
	 * @return array параметры, переданные в строке адреса.
	 */
	public static function getUrlParameters()
	{
		$url = parse_url($_SERVER['REQUEST_URI']);

		if(isset($url['path']))
		{
			$url = str_replace(self::$baseUrl, '', $url['path']);

			if($url !== '')
			{
				return explode('/', $url);
			}
		}

		return array();
	}

	/**
	 * Возвращает настройки экспортных файлов, если они есть.
	 * @return array|boolean настройки экспортных файлов или
	 * false если нет настроек.
	 */
	public static function getConfig()
	{
		if(file_exists(self::$_configPath))
		{
			return json_decode(file_get_contents(self::$_configPath), true);
		}

		return false;
	}

	/**
	 * Сохраняет настройки экспортных файлов в файл.
	 * @return boolean успешно ли сохранены настройки.
	 */
	public static function saveConfig($config)
	{
		return file_put_contents(self::$_configPath, json_encode($config));
	}

	/**
	 * Проверяет данные указанные в форме добавления файла с курсами валют.
	 * @return array ошибки, выявленные во время проверки файла.
	 */
	public static function validateFileForm()
	{
		$errors = array();

		if(!isset($_POST['file']))
		{
			$errors[] = 'Не указан файл с курсами валют';
		}
		elseif(!preg_match('{^http(|s)://[-a-zA-Z0-9.]+(\.[-a-zA-Z0-9]+)*/.*}', $_POST['file']))
		{
			$errors[] = 'Ссылка на файл с курсами валют не правильная';
		}
		else
		{
			$url = parse_url($_POST['file']);

			if($url === false || strpos($url['host'], $_SERVER['HTTP_HOST']) === false)
			{
				$errors[] = 'Ссылка на файл с курсами валют должна быть правильной и принадлежать сайту ' . $_SERVER['HTTP_HOST'];
			}
		}

		if(!isset($_POST['json']) && !isset($_POST['xml']))
		{
			$errors[] = 'Не выбран формат файла, который необходимо сформировать';
		}

		return $errors;
	}

	/**
	 * Возвращает формат указанного файла и адрес его расположения.
	 * @return array|string информация о файле или строка, содержащая ошибку.
	 */
	public static function getFileInfo()
	{
		$info = self::getFileAndContent($_POST['file']);

		if(is_string($info))
		{
			return $info;
		}

		list($file, $content) = $info;

		$path = self::getFullPath('formats');

		include $path . 'export.file.php';
		include $path . 'files.parser.php';
		include $path . 'files.generator.php';

		$formats = self::$_formats;

		unset($formats['json']);

		foreach($formats as $format => $class)
		{
			include $path . $format . '.php';

			if($class::isThisFormat($content))
			{
				return array
				(
					'file' => $file,
					'format' => $format
				);
			}
		}

		return 'Не удалось определить формат файла; файл не принадлежит ни одному из известных форматов';
	}

	/**
	 * Проверяет, указан ли в адресе статический файл или нет, если да то,
	 * возвращает путь к нему, иначе ссылку на него, а также его содержимое.
	 * @return array|string путь к файлу и его содержимое или текст ошибки.
	 */
	private static function getFileAndContent($file)
	{
		$content = @file_get_contents($file);

		if($content === false)
		{
			return 'Не удается прочитать файл по указанному адресу';
		}

		$url = parse_url($file);

		if(isset($url['path']))
		{
			$path = strtr($url['path'], '/', DIRECTORY_SEPARATOR);
			$extension = pathinfo($path, PATHINFO_EXTENSION);

			// Проверяем а вдруг этот файл статический
			if($extension === 'txt' || $extension === 'xml')
			{
				$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);

				// Возможно файл лежит в том же каталоге, куда указывает ссылка и его содержимое такое же как и по ссылке
				if(file_exists($path) && ($pathContent = @file_get_contents($path)) !== false && md5($content) === md5($pathContent))
				{
					$file = $path;
				}
			}
		}

		// убираяем всякий мусор вначале и конце файла, в том числе и BOM для файлов в УТФ-8
		$content = trim($content, " \t\n\r\xEF\xBB\xBF");

		if(!strlen($content))
		{
			return 'Не удалось определить формат файла; файл пустой';
		}

		return array($file, $content);
	}

	/**
	 * Возвращает форматы файлов, указанные в форме добавления файла
	 * с курсами валют, которые необходимо генерировать.
	 * @return array форматы файлов для генерации.
	 */
	public static function getOutputFormats()
	{
		return isset($_POST['xml']) ? array('json', 'xml') : array('json');
	}

	/**
	 * Формирует экспортный файл на основе настроек и заданого формата,
	 * а потом отдает его в браузер.
	 * @param array $config настройки экспортных файлов.
	 * @param string $format формат нового файла с курсами валют.
	 */
	public static function generate($config, $format)
	{
		$content = @file_get_contents($config['file']);

		if($content === false)
		{
			exit();
		}

		$path = self::getFullPath('formats');

		include $path . 'export.file.php';
		include $path . 'files.parser.php';
		include $path . 'files.generator.php';
		include $path . $config['format'] . '.php';

		$parserClass = self::$_formats[$config['format']];
		$generatorClass = self::$_formats[$format];

		if(!class_exists($generatorClass))
		{
			include $path . $format . '.php';
		}

		$rates = $parserClass::parse($content);
		echo $generatorClass::generate($rates);
	}

	/**
	 * Возвращает абсолютный адрес размещения каталога генератора.
	 * @return string абсолютный адрес размещения каталога генератора.
	 */
	public static function getBaseFullUrl()
	{
		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . self::$baseUrl;
	}

	/**
	 * Перенаправляет браузер пользователя на указанный адрес.
	 * @param string $url адрес, на который необходимо перенаправить
	 * пользователя.
	 */
	public static function redirect($url)
	{
		header('Location: ' . $url);
		exit();
	}

	/**
	 * Формирует 404 ошибку сервера.
	 */
	public static function notFound()
	{
		Header('HTTP/1.1 404 Not Found');
		exit();
	}

	/**
	 * Возвращает адрес файла с информацией о том, как пользоваться
	 * этим генератором.
	 * @return string адрес файла с информацией о том, как пользоваться
	 * этим генератором.
	 */
	public static function getReadmeUrl()
	{
		return self::getBaseFullUrl() . self::README_NAME;
	}

}

?>