<?php
require_once 'Hold.php';

class InProcess extends Hold
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
    	
    	$interface->setPageTitle('InProcess Request');
    }
    
    public function getRequestType() {
        return RequestLogic::IN_PROCESS;
    }
}

?>