<?php

	$root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

	include $root . 'classes' . DIRECTORY_SEPARATOR . 'core.php';

	EFCore::init($root);

	$parameters = EFCore::getUrlParameters();
	$config = EFCore::getConfig();

	if(is_array($config))
	{
		if(isset($parameters[0]) && in_array($parameters[0], $config['output']))
		{
			EFCore::generate($config, $parameters[0]);
		}
		elseif(empty($parameters))
		{
			EFCore::redirect(EFCore::$baseUrl . $config['output'][0]);
		}
		else
		{
			EFCore::notFound();
		}
	}
	elseif(empty($parameters))
	{
		session_start();

		if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$errors = EFCore::validateFileForm();

			if(empty($errors))
			{
				$info = EFCore::getFileInfo();

				if(is_array($info))
				{
					$info['output'] = EFCore::getOutputFormats();

					$_SESSION['info'] = $info;

					EFCore::redirect(EFCore::$baseUrl . '?preview');
				}
				else
				{
					$errors = array((string) $info);
				}
			}

			$_SESSION['form'] = array('post' => $_POST, 'errors' => $errors);

			EFCore::redirect(EFCore::$baseUrl);
		}

		$readmeUrl = EFCore::getReadmeUrl();

		if(!empty($_GET))
		{
			if(!isset($_SESSION['info']))
			{
				EFCore::redirect(EFCore::$baseUrl);
			}

			if(isset($_GET['preview']))
			{
				$info = $_SESSION['info'];
				$baseFullUrl = EFCore::getBaseFullUrl();

				include EFCore::getFullPath('htm', 'preview.htm');
			}
			elseif(isset($_GET['save']))
			{
				$result = EFCore::saveConfig($_SESSION['info']);

				if($result)
				{
					unset($_SESSION['info']);
				}

				include EFCore::getFullPath('htm', 'save.htm');
			}
			elseif(isset($_GET['cancel']))
			{
				unset($_SESSION['info']);

				EFCore::redirect(EFCore::$baseUrl);
			}
			elseif(isset($_GET['test']) && in_array($_GET['test'], $_SESSION['info']['output']))
			{
				EFCore::generate($_SESSION['info'], $_GET['test']);
			}
			else
			{
				EFCore::notFound();
			}
		}
		else
		{
			if(isset($_SESSION['form']))
			{
				$post = $_SESSION['form']['post'];
				$errors = $_SESSION['form']['errors'];

				unset($_SESSION['form']);
			}

			include EFCore::getFullPath('htm', 'index.htm');
		}
	}
	elseif(isset($parameters[0]) && $parameters[0] === EFCore::README_NAME)
	{
		echo str_replace("\n", '<br>', file_get_contents(EFCore::getFullPath() . $parameters[0]));
	}
	else
	{
		EFCore::redirect(EFCore::$baseUrl);
	}

?>