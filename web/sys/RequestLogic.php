<?php
/**
 * Request Logic Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */

require_once 'CatalogConnection.php';
require_once 'services/MyResearch/lib/Requests.php';

/**
 * Request Logic Class
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */
class RequestLogic
{    
    protected $config;
    protected $catalog;
    
    const HOLD = 'Hold';
    const ICB = 'ICB';
    const IN_PROCESS = 'InProcess';
    const STORAGE = 'Storage';
    
    /**
     * Constructor
     *
     * @param object $catalog Catalog connection object
     *
     * @access public
     */
    public function __construct($catalog)
    {
    	// Load Configuration
        $this->config = parse_ini_file('conf/requests.ini', true);
        $this->catalog = $catalog;
    }
    
    public function getUserInstructions($request, $eligibleItems)
    {
        global $interface;
        $items = array_merge($eligibleItems['Keele'], $eligibleItems['Glendon']);
        if ($request == self::HOLD) {
        	// TODO:
        } else if ($request == self::ICB) {
        	// TODO:
        } else if ($request == self::IN_PROCESS) {
        	// TODO:
        } else if ($request == self::STORAGE) {
        	foreach ($items as $item) {
        	    if (in_array('LAW-STOR', array($item['home_location_code'], $item['current_location_code']))) {
        	        $interface->assign('isLAWStorageRequest', true);
        	    }
        	    if (in_array('LAW-SPEC', array($item['home_location_code'], $item['current_location_code']))
        	    || in_array('LAW-SPEC2', array($item['home_location_code'], $item['current_location_code']))) {
        	    	$interface->assign('isLAWSpecialCollectionRequest', true);
        	    }
        	}
        }
        return $interface->fetch('Record/request-user-instructions.tpl');
    }
    
    /**
     * Given the holdings, determine if any item is eligible for the given type of request.
     * @param Array $items    Item records
     * @param Array $patron   Patron record
     * @param String $request The request type (HOLD, ICB, IN-PROCESS, STORAGE)
     * @return Array         Associative array 
     * ('allow'=>true/false, 'items'=>array)
     */
    public function checkRequest($items, $patron, $request)
    {
        if ($request == self::HOLD) {
            return $this->checkHold($items, $patron);
        } else if ($request == self::ICB) {
            return $this->checkICB($items, $patron);
        } else if ($request == self::IN_PROCESS) {
            return $this->checkInProcess($items, $patron);
        } else if ($request == self::STORAGE) {
            return $this->checkStorage($items, $patron);
        } else {
            return array('allow'=>false, 'Invalid request.');
        }
    }

    /**
     * Given the holdings, determine if any item is eligible for HOLD.
     * @param Array $items   Item records
     * @param Array $patron  Patron record
     * @return Array         Associative array 
     * ('allow'=>true/false, 'items'=>array)
     */
    public function checkHold($items, $patron)
    {
        $requestableItems = array();
        foreach ($items as $item) {
            if (in_array($item['current_location_code'], $this->config['no_request_locn'])) {
            	continue;
            }
            if (in_array($item['home_location_code'], $this->config['no_request_locn'])) {
            	continue;
            }
            if (in_array($item['item_type'], $this->config['no_request_ityp'])) {
            	continue;
            }
            $requestableItems[] = $item;
        }
        $eligibleItems = array();
        $availability = $this->countAvailableCopiesPerCallNumber($requestableItems);
        foreach ($requestableItems as $item) {
            if (isset($availability[$item['callnumber']])
                    && $availability[$item['callnumber']] == 0) {
                $eligibleItems[] = $item;
            }
        }
        $itemsByCampus = $this->splitItemsByCampus($eligibleItems, self::HOLD);
        $allow = !empty($itemsByCampus['Keele']) || !empty($itemsByCampus['Glendon']);
        return array('allow' => $allow, 'items' => $itemsByCampus);
    }
    
   /**
    * Given the holdings, determine if any item is eligible for ICB.
    * @param Array $items   Item records
    * @param Array $patron  Patron record
    * @return Array         Associative array 
    * ('allow'=>true/false, 'items'=>array)
    */
    public function checkICB($items, $patron)
    {        
        // split the items into KEELE items and GLENDON items
        $itemsByCampus = $this->splitItemsByCampus($items, self::ICB);
        
        // map callnumber=>number of available copies
        $keeleAvailability = $this->countAvailableCopiesPerCallNumber($itemsByCampus['Keele'], true);
        $glendonAvailability = $this->countAvailableCopiesPerCallNumber($itemsByCampus['Glendon'], true);
        
        // determine which item can be transfered to KEELE
        $toKeele = array();
        foreach ($itemsByCampus['Glendon'] as $item) {
            // an item can be transfered to KEELE if there is one or more
            // copies available at GLENDON and NONE at KEELE
            if ($glendonAvailability[$item['callnumber']] > 0
            &&  $keeleAvailability[$item['callnumber']] == 0) {
                $toKeele[] = $item;
            }
        }
        
        // determine which item can be transfered to GLENDON
        $toGlendon = array();
        foreach ($itemsByCampus['Keele'] as $item) {
        	// an item can be transfered to GLENDON if there is one or more
        	// copies available at KEELE and NONE at GLENDON
        	if ($keeleAvailability[$item['callnumber']] > 0
        	&&  $glendonAvailability[$item['callnumber']] == 0) {
        		$toGlendon[] = $item;
        	}
        }
        
        // if no item can be transfered then we're done 
        if (empty($toGlendon) && empty($toKeele)) {
            return array('allow'=>false, 'items'=>array());
        }

        $eligibleItems = array('Glendon'=>$toKeele, 'Keele'=>$toGlendon);

        // If we got here, then the item is requestable
        return array('allow' => true, 'items' => $eligibleItems);
    }
    
   /**
    * Given the holdings, determine if any item is eligible for Storage.
    * @param Array $items   Item records
    * @param Array $patron  Patron record
    * @return Array         Associative array 
    * ('allow'=>true/false, 'items'=>array)
    */
    public function checkStorage($items, $patron)
    {
        // split the items into KEELE items and GLENDON items
        $itemsByCampus = $this->splitItemsByCampus($items, self::STORAGE);
        
        $keele = $itemsByCampus['Keele'];
        $glendon = $itemsByCampus['Glendon'];
        $allow = !empty($keele) || !empty($glendon);
        $eligibleItems = array('Glendon'=>$glendon, 'Keele'=>$keele);
        
        return array('allow' => $allow, 'items' => $eligibleItems);
    }
    
   /**
    * Given the holdings, determine if any item is eligible for In-Process.
    * @param Array $items   Item records
    * @param Array $patron  Patron record
    * @return Array         Associative array 
    * ('allow'=>true/false, 'items'=>array)
    */
    public function checkInProcess($items, $patron)
    {
        // split the items into KEELE items and GLENDON items
        $itemsByCampus = $this->splitItemsByCampus($items, self::IN_PROCESS);
        
        $keele = $itemsByCampus['Keele'];
        $glendon = $itemsByCampus['Glendon'];
        $allow = !empty($keele) || !empty($glendon);
        $eligibleItems = array('Glendon'=>$glendon, 'Keele'=>$keele);
        return array('allow' => $allow, 'items' => $eligibleItems);
    }
    
    /**
     * Check patron record to see if he is allowed to place a request.
     * @param Array $patron
     * @param String $type    Type in ('HOLD', 'ICB', 'STORAGE', 'IN-PROCESS')
     * @return Array          Associative array 
     * ('allow'=>true/false, 'message'=>string)
     */
    public function checkPatron($patron, $type, $item)
    {
        if (!$patron) return array('allow'=>true, 'message'=>'');
        
        // make sure user's status is OK
        if ($patron['expired']) {
            return array('allow'=>false, 'message'=>'User privileges expired.');
        }
        if ($patron['status'] == 'BLOCKED' || $patron['status'] == 'BARRED') {
        	return array('allow'=>false, 'message'=>'User status is ' . $patron['status']);
        }
        // make sure patron # of holds is not at the limit
        if ($patron['number_of_holds'] >= $this->config['MaxNumberOfHolds'][$patron['library']]
        || ($patron['profile'] == 'EXTERNAL' && $patron['number_of_holds'] >= 5)) {
        	return array('allow'=>false, 'message'=>'Hold limit exceeded.');
        }
        
        // make sure user did not already check out the item
        $charges = $this->catalog->getMyTransactions($patron);
        foreach ($charges as $charge) {
            if (in_array($charge['callnum'], array('DVD', 'VIDEO'))) {
                if ($charge['item_id'] == $item['item_key']) {
                    return array('allow'=>false, 'message'=>'This item is already checked out by you.');
                }
            } else if ($charge['callnum'] == $item['callnumber']) {
                return array('allow'=>false, 'message'=>'This item is already checked out by you.');
            }
        }
        
        // make sure user did not already has a hold on the item
        $holds = $this->catalog->getMyHolds($patron);
        foreach ($holds as $hold) {
        	if (in_array($hold['callnum'], array('DVD', 'VIDEO'))) {
        	    if ($hold['item_id'] == $item['item_key']) {
        	    	return array('allow'=>false, 'message'=>'This item is already on hold for you.');
        	    }
            } else if ($hold['callnum'] == $item['callnumber']) {
        		return array('allow'=>false, 'message'=>'This item is already on hold for you.');
        	}
        }
        
        // make sure the number of requests in the last 24hrs is not at the limit
        $requests = new Requests();
        $requests->whereAdd("user_barcode='" .$patron['barcode'] . "'");
        $requests->whereAdd('created > (CURDATE() - INTERVAL 1 DAY)');
        $count = $requests->count('id');
        if ($count > $this->config['max_requests_per_day']) {
        	return array('allow'=>false, 'message'=>'Number of requests exceeded daily limit.');
        }
        
        // make sure the same item has not been requested already in the last 24hrs
        $requests = new Requests();
        $requests->whereAdd("user_barcode='" . $patron['barcode'] . "'");
        $requests->whereAdd("item_key='" . $item['item_key'] . "'");
        $requests->whereAdd('created > (CURDATE() - INTERVAL 1 DAY)');
        $count = $requests->count('id');
        if ($count) {
        	return array('allow'=>false, 'message'=>'You already requested this item.');
        }
       
        // If we got here, then the patron is allowed
        return array('allow'=>true, 'message'=>'');
    }
    
   /**
    * Check an item record to see if it is "requestable".
    * @param Array  $item
    * @param String $type    Type in ('HOLD', 'ICB', 'STORAGE', 'IN-PROCESS')
    * @return Array          Associative array
    * ('allow'=>true/false, 'message'=>string)
    */
    public function checkItem($item, $type)
    {
        // SMIL-STOR items are eligible for storage requests
        if ($type == self::STORAGE && in_array('SMIL-STOR', array($item['current_location_code'], $item['home_location_code']))) {
        	return array('allow'=>true, 'message'=>'');
        }
        // SMIL-DESK DVD-3DAY and DVD-7DAY items are eligible to ICB requests
        if ($type == self::ICB 
                && in_array('SMIL-DESK', array($item['current_location_code'], $item['home_location_code'])) 
                && in_array($item['item_type'], array('DVD-3DAY', 'DVD-7DAY', 'AUDIO-CD'))) {
        	return array('allow'=>true, 'message'=>'');
        }
        if ($item['recirculate_flag'] == 'N' && !in_array($type, array(self::STORAGE, self::ICB))) {
            return array('allow'=>false, 'message'=>'This item is not circulating.');
        }
        if ($item['reserve'] == 'Y') {
        	return array('allow'=>false, 'message'=>'This item is on reserve.');
        }
        if (in_array($item['current_location_code'], $this->config['no_request_locn']) 
                && !in_array($type, array(self::IN_PROCESS))) {
            return array('allow'=>false, 
            	'message'=>'Items in location ' 
                . $item['current_location_code'] . ' are not requestable.');
        }
        if (in_array($item['home_location_code'], $this->config['no_request_locn']) 
                && !in_array($type, array(self::IN_PROCESS))) {
        	return array('allow'=>false,
            	'message'=>'Items in location ' 
        	    . $item['home_location_code'] . ' are not requestable.');
        }
        if (in_array($item['item_type'], $this->config['no_request_ityp']) 
                && !in_array($type, array(self::IN_PROCESS))) {
        	return array('allow'=>false,
            	'message'=>'Item type ' 
        	    . $item['item_type'] . ' is not requestable.');
        }
        // Additional rules apply depending on the type of request
        if ($type == self::HOLD || $type == self::ICB) {
        	// item with is_holdable flag == 0 are NOT allowed
        	if ($item['is_holdable'] == 0) {
        		return array('allow'=>false, 'message'=>'This item is not holdable.');
        	}
            // In process item types are NOT allowed
            if (in_array($item['item_type'], $this->config['in_process_ityp'])) {
            	return array('allow'=>false,
                	'message'=>'Item type ' 
            	    . $item['item_type'] . ' is not requestable.');
            }
            // In storage items are NOT allowed
            if (in_array($item['current_location_code'], $this->config['storage_locn'])) {
            	return array('allow'=>false,
            		'message'=>'Items in location ' 
        	        . $item['current_location_code'] . ' are not requestable.');
            }
            if (in_array($item['home_location_code'], $this->config['storage_locn'])) {
            	return array('allow'=>false,
                	'message'=>'Items in location ' 
            	    . $item['home_location_code'] . ' are not requestable.');
            }
            // for ICB, YORK-EDUC items are not allowed
            if ($type == self::ICB) {
                if ($item['library_code'] == 'YORK-EDUC') {
                	return array('allow'=>false,
                    	'message'=>'Items in library ' 
                	    . $item['library_code'] . ' are not requestable.');
                }
            }
        } else if ($type == self::IN_PROCESS) {
            if ($item['library_code'] == 'YORK-EDUC') {
            	return array('allow'=>false,
                	'message'=>'Items in library ' 
            	    . $item['library_code'] . ' are not requestable.');
            }
            if (!in_array($item['item_type'], $this->config['in_process_ityp'])) {
            	return array('allow'=>false,
                	'message'=>'Item type ' 
            	    . $item['item_type'] . ' is not requestable.');
            }
        } else if ($type == self::STORAGE) {
            if ($item['library_code'] == 'YORK-EDUC') {
            	return array('allow'=>false,
                	'message'=>'Items in library ' 
            	    . $item['library_code'] . ' are not requestable.');
            }
            if (!in_array($item['home_location_code'], $this->config['storage_locn'])
            &&  !in_array($item['current_location_code'], $this->config['storage_locn'])) {
            	return array('allow'=>false,
                	'message'=>'This item is not in storage.');
            }
        }
        
        // If we got here, then the item is requestable
        return array('allow'=>true, 'message'=>'');
    }
    
    public function getAllPickupLocations($institution) {
        $allLocations = $this->config['PickupLocations'];
        $pickup = array();
        if ($institution == 'YORK-EDUC' || $institution == 'Education Resource Centre') {
        	return array(array('locationID'=>'ERC', 'locationDisplay'=>$allLocations['ERC']));
        }
        foreach($allLocations as $id=>$display) {
            if ($id != 'ERC') {
                $pickup[] = array('locationID'=>$id, 'locationDisplay'=>$display);
            }
        }
        return $pickup;
    }
    
    public function getPickupLocations($requestType, $itemType, $library, $homeLocation, $currentLocation)
    { 
    	$allLocations = $this->config['PickupLocations'];
    	$isSMILStorage = ($requestType == RequestLogic::STORAGE
    			&& in_array('SMIL-STOR', array($currentLocation, $homeLocation))
    	);
    	if ($isSMILStorage) {
    		$pickup = array(array('locationID'=>'SMIL', 'locationDisplay'=>$allLocations['SMIL']));
    		return $pickup;
    	}
    	$isSMILICB = ($requestType == RequestLogic::ICB
    			&& in_array('SMIL-DESK', array($currentLocation, $homeLocation))
    			&& in_array($itemType, array('DVD-3DAY', 'DVD-7DAY', 'AUDIO-CD'))
    	);
    	if ($isSMILICB) {
    		$pickup = array(array('locationID'=>'FROST', 'locationDisplay'=>$allLocations['FROST']));
    		return $pickup;
    	}
    	$isLAWStorage = ($requestType == RequestLogic::STORAGE && $library == 'YORK-LAW');
    	if ($isLAWStorage) {
    	    $pickup = array(array('locationID'=>'LAW', 'locationDisplay'=>$allLocations['LAW']));
    	    return $pickup;
    	}
    	return $this->getAllPickupLocations($library);
    }
    
    /**
     * Determine if a location is on Glendon campus.
     * @param String     $location
     * @return boolean   True/False
     */
    public function isGlendonLocation($location)
    {
        return in_array($location, $this->config['glendon_locn']);
    }
    
    /**
     * Split the items array into 2 arrays one for Keele and one for Glendon.
     * 
     * @param Array     $items
     * @param String    $requestType
     * @return Array    ('Keele'=>items on Keele campus, 'Glendon'=>items on Glendon campus)
     */
    public function splitItemsByCampus($items, $requestType)
    {
        $keele = array();
        $glendon = array();
        foreach ($items as $item) {
        	// ignore items that are not eligible for the given request type
        	$result = $this->checkItem($item, $requestType);
        	if (!$result['allow']) continue;
        
        	if (in_array($item['current_location_code'], $this->config['glendon_locn'])
        	||  in_array($item['home_location_code'], $this->config['glendon_locn'])) {
        		$glendon[] = $item;
        	} else {
        		$keele[] = $item;
        	}
        }
        return array('Keele'=>$keele, 'Glendon'=>$glendon);
    }
    
    /**
     * Count number of items per callnumber available.
     * 
     * @param  Array    $items
     * @return Array    ('call number' => Number of items with "Available" status).
     */
    public function countAvailableCopiesPerCallNumber($items, $includeNonCircItems = false) 
    {
        $availability = array();
        
        // count number of items per callnumber available 
        foreach ($items as $item) {
        	// count available copies per call number
        	if (!isset($availability[$item['callnumber']])) {
        		$availability[$item['callnumber']] = 0;
        	}
        	if ($item['recirculate_flag'] != 'N' && $item['status'] == 'Available'  && $item['reserve'] == 'N') {
        		$availability[$item['callnumber']]++;
        	} else if ($includeNonCircItems && $item['recirculate_flag'] == 'N') {
        	    $availability[$item['callnumber']]++;
        	}
        }
        
        return $availability;
    }

    /**
     * Send the request to the appropriate email address and/or place a request into Symphony.
     * 
     * @param Array   $patron
     * @param Array   $requestDetails
     * @param String  $requestType
     * @return Mixed  True if successful, or array of Pear errors otherwise.
     */
    public function placeRequest($patron, $requestDetails, $requestType)
    {
        $errors = array();
        foreach ($requestDetails['requestedItems'] as $item) {
            $result = $this->processItemRequest(
                $patron, $requestDetails, $requestType, $item
            );
            if (PEAR::isError($result)) {
                $errors[] = $result;
            } else if (is_array($result) && isset($result['success']) && !$result['success']) {
                $errors[] = $result['sysMessage'];
            }
        }
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Determine if automatic hold is possible given the realtime holdings data.
     * @param array $realtimeHoldings
     * @return boolean 
     */
    public function isAutomaticHoldPossible($realtimeHoldings)
    {
        foreach ($realtimeHoldings as $location => $holdings) {
            if (isset($this->config['Automatic_Holds'][$location])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Process a single item request.
     * 
     * @param Array   $patron
     * @param Array   $requestDetails
     * @param String  $requestType
     * @param Array   $item
     * 
     * @return Mixed  Associative array('success'=>true/false, 'sysMessage'=>string) 
     *                or Pear error.
     */
    protected function processItemRequest($patron, $requestDetails, $requestType, $item) 
    {
        global $configArray;
        
        $isAutomaticHold = (
            $requestType == self::HOLD 
            && isset($this->config['Automatic_Holds'][$item['location']])
        );
        if ($isAutomaticHold) {
            $override = $this->config['Automatic_Holds'][$item['location']];
            // we use jQuery UI datepicker, which defaults to MM/DD/YYYY format
            // we need to convert it to VuFind "display" format before passing
            // it to the driver which will assume date format is "display" format
            $requiredBy = DateTime::createFromFormat(
                'm/d/Y', $requestDetails['requiredBy']
            );
            $requiredBy = $requiredBy->format($configArray['Site']['displayDateFormat']);
            $pickupLocations = $this->getAllPickupLocations();
            $pickupLocationDisplay = $requestDetails['pickUpLocation'];
            foreach ($pickupLocations as $pickup) {
                if ($pickup['locationID'] == $requestDetails['pickUpLocation']) {
                    $pickupLocationDisplay = $pickup['locationDisplay'];
                    break;
                }
            }
            $comment = 'VuFind - Pickup: ' . $pickupLocationDisplay;
            $holdDetails = array(
                'patron' => $patron,
                'item_id' => $item['barcode'],
                'pickUpLocation' => $requestDetails['pickUpLocation'],
                'requiredBy' => $requiredBy,
                'comment' => $comment,
                'level' => 'CALL',
                'callnumber' => $item['callnumber'],
                'override' => ($override == 'NONE' ? '' : $override),
                'recall' => 'NO'
            );
            // Attempt to place the hold directly into Symphony
            $result = $this->catalog->placeHold($holdDetails);
            if ($result['success'] == true) {
                $this->saveRequestToDB($patron, $requestDetails, $requestType, $item, true);
            }
            return $result;
        }
        // default to saving the request to db and send email to staff
        $this->saveRequestToDB($patron, $requestDetails, $requestType, $item, false);
        return $this->sendRequestEmail($patron, $requestDetails, $requestType, $item); 
    }
    
    /**
     * Send the request to the appropriate email address.
     * 
     * @param Array $patron
     * @param Array $requestDetails
     * @param Array $requestType
     * 
     * @return Mixed  True if successful, or Pear error otherwise.
     */
    protected function sendRequestEmail($patron, $requestDetails, $requestType, $item)
    {
        global $interface;
        
        foreach ($requestDetails as $key => $value) {
        	$interface->assign($key, $value);
        }
        $db = ConnectionManager::ConnectToIndex();
        $record = $db->getRecord($item['id']);
        $interface->assign('record', $record);
        $interface->assign('patron', $patron);
        $interface->assign('profile', $this->catalog->getMyProfile($patron));
        $interface->assign('requestedItem', $item);
        $interface->assign('requestType', $requestType);
        $body = $interface->fetch('Emails/catalog-item-request.tpl');
        $subject = translate($requestType . ' Request');
        $from = $this->config['from_address'];
        $to = $this->getRequestRecipient($item, $requestType);
        $mail = new VuFindMailer();
        return $mail->send($to, $from, $subject, $body);
    }

    protected function getRequestRecipient($item, $requestType) 
    {
        $section = $requestType.'_Recipients';
    	$recipient = $this->config['default_recipient'];
    	switch ($requestType) {
    		case self::ICB:
    		    if (in_array('SMIL-DESK', array($item['current_location_code'], $item['home_location_code'])) 
                && in_array($item['item_type'], array('DVD-3DAY', 'DVD-7DAY', 'AUDIO-CD'))) {
                    $recipient = $this->config[$section]['SMIL'];
                } else if (ereg('^SC.*', $item['home_location_code']) 
    			||  ereg('^SMIL.*', $item['home_location_code'])) {
    				$recipient = $this->config[$section]['SCOTT'];
    			} else if (ereg('^ST.*', $item['home_location_code'])) {
    				$recipient = $this->config[$section]['STEACIE'];
    			} else if (ereg('^BRONF.*', $item['home_location_code'])) {
    				$recipient = $this->config[$section]['BRONFMAN'];
    			} else if (ereg('^LAW.*', $item['home_location_code'])) {
    				$recipient = $this->config[$section]['LAW'];
    			} else if (ereg('^FR.*', $item['home_location_code'])) {
    				$recipient = $this->config[$section]['FROST'];
    			}
    			break;
    		case self::HOLD:
    		case self::IN_PROCESS:
    		    $recipient = $this->config[$section][$item['library_code']];
    		    break;
    		case self::STORAGE:
    		    if (ereg('^BRONF.*', $item['home_location_code'])) {
    		    	$recipient = $this->config[$section]['BRONFMAN'];
    		    } else if (in_array('SMIL-STOR', array($item['current_location_code'], $item['home_location_code']))) {
    		        $recipient = $this->config[$section]['SMIL'];
    		    } else {
    			    $recipient = $this->config[$section][$item['library_code']];
    		    }
    			break;
    		default:
    			break;
    	}
    	return $recipient;
    }
    
    private function saveRequestToDB($patron, $requestDetails, $requestType, $item, $ilsHoldCreated)
    {
        $expiry = DateTime::createFromFormat(
        	'm/d/Y', $requestDetails['requiredBy']
        );
        
        $request = new Requests();
        $request->item_id = $item['id'];
        $request->user_barcode = $patron['barcode'];
        $request->item_barcode = $item['barcode'];
        $request->item_callnum = $item['callnumber'];
        $request->request_type = $requestType;
        $request->pickup_location = $requestDetails['pickUpLocation'];
        $request->comment = $requestDetails['comment'];
        $request->expiry = $expiry->format('Y-m-d');
        $request->created = date('Y-m-d H:i:s');
        $request->ils_hold_created = ($ilsHoldCreated ? 1 : 0);
        $request->item_key = $item['item_key'];
        $request->insert();
    }
}
?>
