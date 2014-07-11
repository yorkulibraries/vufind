<?php
require_once 'JSON.php';
require_once 'sys/RequestLogic.php';

class MaterialsRequest extends JSON
{
    private $catalog;
    private $requestLogic;
    
	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct()
	{
		parent::__construct();
		$this->catalog = ConnectionManager::connectToCatalog();
		$this->requestLogic = new RequestLogic($this->catalog);
	}

	public function getPickupLocations()
	{
	    global $interface;
	    $homeLocation = $_REQUEST['home_location_code'];
	    $currentLocation = $_REQUEST['current_location_code'];
	    $library = $_REQUEST['library_code'];
	    $requestType = $_REQUEST['request_type'];
	    $itemType = $_REQUEST['item_type'];
	    $selected = $_REQUEST['selected'];
	    $pickup = $this->requestLogic->getPickupLocations($requestType, $itemType, $library, $homeLocation, $currentLocation);
	    $interface->assign('pickup', $pickup);
	    $interface->assign('pickUpLocation', $selected);
	    $html = $interface->fetch('Record/pickupLocations.tpl');
	    $data = array('raw' => $pickup, 'html' => $html);
	    return $this->output($data, JSON::STATUS_OK);
	}
}
?>