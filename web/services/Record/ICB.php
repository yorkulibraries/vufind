<?php
require_once 'Hold.php';

class ICB extends Hold
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
    	
    	$interface->setPageTitle('ICB Request');
    }
    
    public function getRequestType() {
        return RequestLogic::ICB;
    }
    
    public function validate()
    {
    	// ensure the pickup location is consitent with the items requested
    	$pickup = $this->gatheredDetails['pickUpLocation'];
    	if($this->requestLogic->isGlendonLocation($pickup)) {
    	    // pickup campus is GLENDON
    	    $this->validateTransferLocation($this->eligibleItems['Keele']);
    	} else {
    	    // pickup campus is KEELE 
    	    $this->validateTransferLocation($this->eligibleItems['Glendon']);
    	}
    	
    	return parent::validate();
    }
    
    private function validateTransferLocation($items)
    {
        foreach ($this->gatheredDetails['copies'] as $barcode) {
        	if ($this->findItem($barcode, $items) === false) {
        	    $this->validationErrors['pickUpLocation']
        	        = 'Items can not be transfered to the location you selected.';
        	    break;
        	}
        }
    }
}

?>