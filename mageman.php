#!/usr/bin/php -q
<?php

//var_dump($_SERVER); 

if (PHP_SAPI !== 'cli') { echo 'cli only please'; die; }

//load config
$config = loadConfig();
$pathToMageman = $config->basedir;

require_once($pathToMageman.'mageman.abstract.php'); 

if(isset($_SERVER['argv'])) 
{
	$param = $_SERVER['argv'];

	try{

		require_once($pathToMageman.'functions/'.$param[1].'.class.php');

		$function = new $param[1](); 
		$param[0] = $_SERVER['PWD'];


		$function->config = $config; 	
		$function->echolog = true;	
		$function->controller($param);
		
		
	} catch(Exception $e)
	{

		die('function not found');

	}	

}

function loadConfig()
{

	$file = $_SERVER['HOME'].'/.mageman';	

	if(!file_exists($file)) die('please configure mageman. Expecting config file '.$_SERVER['HOME'].'/.mageman'."\n"); 	

	$handle = fopen($file, "r");

	$configRaw = fread($handle, filesize($file));
	fclose($handle);

	$config = new SimpleXMLElement($configRaw); 
	
	return $config;		

}
