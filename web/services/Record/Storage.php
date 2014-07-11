<?php
require_once 'Hold.php';

class Storage extends Hold
{
   /**
    * Constructor
    *
    * @access public
    */
    public function __construct()
    {
        global $interface;
    	parent::__construct();
    	
    	$interface->setPageTitle('Storage Request');
    }
    
    public function getRequestType() {
        return RequestLogic::STORAGE;
    }
}

?>