<?php

class mageman {


	const APPNAME			= 'mageman';
	const MODULE_PATH 		= 'modules';
	const CLASS_SUFFIX 		= '.class.php'; 
	const ABS_FILE  		= '.abstract.php';
    const ABOUT_APP         = 'MageMan - A Mageneto helper tool http://github.com/superterran/MageMan';
 

	public $config;
	public $path;	
	
	public function __construct($config = null)
	{

		try {
			$this->config = $this->loadConfig();
            var_dump($this->config);


		} catch(Exception $e) {

			$this->log($e);
			return false;
		}
	
		return $this; 

	}

	function loadConfig()
	{

		$file = $_SERVER['HOME'].'/.'.self::APPNAME;	
		if(!file_exists($file)) log('please configure mageman. Expecting config file '.$file."\n"); 	
		return new SimpleXMLElement(file_get_contents($file)); 
						
	}

	public function getModule($moduleName = null, $params = null)
	{

        var_dump($moduleName); die();

		if($moduleName) 
		{
			try
            {


                var_dump($this->config->basedir);

                $include = ($this->config->basedir.'/mageman.class.php');

				require_once($include);
				require_once($this->config->basedir.self::APPNAME.$moduleName.self::CLASS_SUFFIX);

				$function = new $moduleName($params); 
				$param[0] = $_SERVER['PWD'];


				$function->config = $config; 	
				$function->echolog = true;	
				$function->controller($param);
				
			}
            catch (Exception $e)
            {

				$this->log($e);	
				return false;
			}
			
			return $function;
			

		} else {

			return $this;

		}


	}


	public function initCli($param = null)
	{

		if(isset($_SERVER['argv'])) 
		{
			$param = $_SERVER['argv'];

            if(!isset($param[1])) $this->log(self::ABOUT_APP); return false;

			try{

				$module = $this->getModule($param[1], $param);

	
			} catch(Exception $e) {

				die('function not found');

				return false;
			}	
		
		}

		return $this;

	}

	public function log($message)
	{

		echo $message."\n";
		return true;	

	}

	
}
