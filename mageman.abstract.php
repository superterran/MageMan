<?php

abstract class mageman
{

//	public $staging 	= array();
//	public $production 	= array();
//	public $productiondump 		= null;
//	public $production_filename 	= null;
	public $config;
	public $basedir;
	public $echolog = false;

/*	public function __construct()
	{
	      $this->staging['localxml'] 	= 'app/etc/local.xml';
	      $this->production['localxml'] 	= 'app/etc/local.xml.production';

	      // these are the settings that are retained after the database import.

	      $this->settings = array
			  (
			      'web/unsecure/base_url'	=> null,
			      'web/secure/base_url' 	=> null
			  );
	}
*/	

	abstract public function controller($params);

	public function readlocalxml($which)
	{
		$thisone = $this->$which;
		$this->log('Loading database configuration for '.$which);
		$file = $this->basedir.$thisone['localxml'];
		$local = simplexml_load_file($file) or die('File not found: '.$file.', aborting'); 
		
		$connect = $local->global->resources->default_setup->connection;
		
		$data['host'] 		= (string) $connect->host;
		$data['user'] 		= (string) $connect->username;
		$data['pass'] 		= (string) $connect->password;
		$data['database'] 	= (string) $connect->dbname;
		$data['pref']		= (string) $local->global->resources->db->table_prefix;
 	
		$this->$which = array_merge($this->$which, $data);
		
	}

	public function dumpdb($db)
	{
		$this->log('Generating database backup of '.$db);	
		$which = $this->$db;
		$cmd = 	'mysqldump --lock-tables --extended-insert --dump-date --disable-keys --comments --create-options --quote-names '.  
			'-h '.$which['host'].' -u'.$which['user'].' -p'.$which['pass'].' '.$which['database'];
		$output = shell_exec($cmd); 	

		return $output;

	}


	public function savefile($output, $path)
	{

		$fh = fopen($path, 'w') or die("can't open file");
		fwrite($fh, $output);
		fclose($fh);
	}
	
	
	public function backupdbtofile($which)
	{
		$output = $this->dumpdb($which);
		  
		$db = $this->$which;
		$db['filename'] = './dumps/'.$db['database'].'.'.date('Y-m-d').'.sql';
		$this->savefile($output, $db['filename']);
		$this->$which = array_merge($this->$which, $db);		
		$this->log($which.' saved to '.$db['filename']);
		return $output;
	}
	
	public function executedump($from, $to)
	{
		$this->log('Importing '.$to.' from '.$from);	
		$which = $this->$to;
		$from = $this->$from;
		$cmd = 'mysql -h '.$which['host'].' -u'.$which['user'].' -p'.$which['pass'].' -D '.$which['database'].' < '.$from['filename'];
		$output = shell_exec($cmd); 		

		return $output;

	}
	
	public function loadmagento()
	{
		$this->log('Instantiating Magento');	
		require_once $this->basedir.'app/Mage.php';
		Mage::app();
		Mage::setIsDeveloperMode(true);
	}
	
	
	public function getconfig()
	{
		foreach($this->settings as $key => $val)
		{
		    
		      $this->settings[$key] = Mage::getStoreConfig($key);

		}
	}

	public function setconfig($which)
	{
        $this->log('setconfig hit');
        $db = $this->$which;
		//$saver = new Mage_Core_Model_Config();

        mysql_connect($db['host'], $db['user'], $db['pass']) or die(mysql_error());
        mysql_select_db($db['database']) or die(mysql_error());

        $this->log('connected to database '.$db['database']);

        foreach($this->settings as $key => $val)
		{
             $sql = 'update core_config_data set value = "'.$val.'" where path = "'.$key.'"';
            mysql_query($sql);
            $this->log($sql);
        //    $saver->saveConfig($key, $val, 'default', 0);
		}

        mysql_close();
	}

	public function performmaintence($which)
	{
	    $db = $this->$which;
	    
	    $this->log('Truncating '.$which.' Logs');	
	    // truncate log tables
	    $tables = array(
		  'dataflow_batch_export',
		  'dataflow_batch_import',
		  'log_customer',
		  'log_quote',
		  'log_summary',
		  'log_summary_type',
		  'log_url',
		  'log_url_info',
		  'log_visitor',
		  'log_visitor_info',
		  'log_visitor_online',
		  'report_event'	
	    );

	    mysql_connect($db['host'], $db['user'], $db['pass']) or die(mysql_error());
	    mysql_select_db($db['database']) or die(mysql_error());
   
	    foreach($tables as $v => $k) {
		mysql_query('TRUNCATE `'.$db['pref'].$k.'`') or die(mysql_error());
	    }


	}

	public function clearCache($name)
	{
		
            $which = $this->$name;
   

	    // clear the caches
	    $this->log('Clearing '.$name.' cache');	
	    $dirs = array(
		'downloader/pearlib/cache/*',
		'downloader/pearlib/download/*',
		'var/cache/*',
		// 'var/log/*',
		// 'var/report/*',
		// 'var/session/*',
		'var/tmp/*'
	    );
   
	    foreach($dirs as $v => $k) {
	//	var_dump('rm -rf '.$which->basedir.$k); 
		exec('rm -rf '.$which->basedir.$k, $output);
	    //	$this->log($output[0]);
	     }

	}


	public function log($msg)
	{
	  $fh = fopen($this->config->basedir.'.mageman_log', 'a') or die("can't open file");
	  $log = date("n/j/Y H:i:s")."\t".$msg;
	  fwrite($fh, $log."\n");
	  fclose($fh);
	  
	  if($this->echolog == true) echo $log."\n";
	}

	public function isMageRoot($dir)
	{
		if(file_exists($dir.'/app/Mage.php')) return true; else return false;
	}


}
