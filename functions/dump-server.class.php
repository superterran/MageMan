<?php
class dumpserver extends mageman
{

	public $active;

   	public function controller($params)
	{

		$this->active->basedir = $params[0].'/';
	 	
		try{
			
			if($this->isMageRoot($params[0])) 
			{
				$this->clearCache('active');
		
			} else {
			
				throw new Exception('Please run this command from a the root of the magento installation');
			}	
			
		}catch(Exception $e) {

			$this->log($e->getMessage());		

		}

		return;
	}


}
