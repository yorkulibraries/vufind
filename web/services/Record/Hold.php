<?php
/**
 * Hold action for Record module
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
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Record.php';
require_once 'sys/RequestLogic.php';

/**
 * Hold action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Hold extends Record
{
    protected $gatheredDetails;
    protected $requestLogic;
    protected $requestType;
    protected $eligibleItems;
    protected $validationErrors;
    protected $patron;
    protected $holdings;
    protected $requestAllowed;
    
   /**
    * Constructor
    *
    * @access public
    */
    public function __construct()
    {
        global $configArray;
        global $interface;
        global $user;
        global $action;

        parent::__construct();
        
        // go back to the record page if "Cancel" button is pressed.
        if (isset($_POST['cancel'])) {
            header('Location: '
                . $configArray['Site']['url']
                . '/Record/' . $this->recordDriver->getUniqueID()
            );
            exit;
        }
        
        $this->requestLogic = new RequestLogic($this->catalog);
        $this->requestType = $this->getRequestType();
        $this->gatheredDetails = array();
        $this->eligibleItems = array();
        $this->validationErrors = array();
        $interface->setPageTitle('Hold Request');
        
        // Assign FollowUp Details required for login and catalog login
        $interface->assign('followup', true);
        $interface->assign('recordId', $this->recordDriver->getUniqueID());
        $interface->assign('followupModule', 'Record');
        $interface->assign('followupAction', $action);
        
        // User Must be logged In to Place Holds
        if ($user = UserAccount::isLoggedIn() && $patron = UserAccount::catalogLogin()) {
            $this->patron = $patron;
            $interface->assign(
                'holdingsMetadata', $this->recordDriver->getHoldings($patron)
            );
            $this->holdings = $interface->get_template_vars('holdings');
            $result = $this->checkRequest($patron);
            $this->requestAllowed = $result['allow'];
            if (!$this->requestAllowed) {
                $interface->assign('results', array('status'=>'This request is not allowed.'));
                $interface->assign('requestNotAllowed', true);
            }
            $this->eligibleItems = $result['items'];
            $interface->assign('eligibleItems', $this->eligibleItems);
            $automaticHoldPossible = $this->requestLogic->isAutomaticHoldPossible($this->holdings);
            $interface->assign('automaticHoldPossible', $automaticHoldPossible);
        } else {
            $interface->setTemplate('../MyResearch/login.tpl');
            $interface->assign('message', 'You must be logged in first');
            $interface->setPageTitle('Login');
            if ($_REQUEST['modal']) {
                $interface->assign('modal', $_REQUEST['modal']);
                $interface->assign('followupURL', $_SERVER['PHP_SELF']);
                $interface->assign('followupQueryString', $_SERVER['QUERY_STRING']);
                $interface->display('modal.tpl');
            } else {
                $interface->display('layout.tpl');
            }
            exit;
        }
    }
    
    public function getRequestType() {
        return RequestLogic::HOLD;
    }
    
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;
        global $action;

        if (!$this->requestAllowed) {
            $this->_displayForm();
            exit;
        }

        if (isset($_POST['placeHold'])) {
             $this->gatherDetails();
             
            // Validate the POST'ed values
            $this->validate();
            
            if (!empty($this->validationErrors)) {
                $interface->assign('validationErrors', $this->validationErrors);
                $this->_displayForm();
                exit;
            }
                    
            // if we got here, then there were no validation error
            // we can now process the request
            $result = $this->process();
            if ($result === true) {
                // if successful, redirect to MyResearch/Holds
                header('Location: ' . $configArray['Site']['url'] 
                    . '/MyResearch/Holds?success=true');
                exit;
            }
            
            // there were errors, assign them to the templates
            $interface->assign('errors', $result);            
        }
        
        // display the form
        $this->_displayForm();
    }
    
    public function isRequestAllowed() {
        return $this->requestAllowed;
    }
    
    public function gatherDetails() 
    {
        // gather POST'ed values
        $this->gatheredDetails['id'] = trim($_REQUEST['id']);
        $this->gatheredDetails['comment'] = trim($_REQUEST['comment']);
        $this->gatheredDetails['requiredBy'] = trim($_REQUEST['requiredBy']);
        $this->gatheredDetails['pickUpLocation'] = trim($_REQUEST['pickUpLocation']);
        $this->gatheredDetails['copies'] = $_REQUEST['copies']; // NEVER trim() this
        $this->gatheredDetails['requestedItems'] = $this->getRequestedItems();
    }

   /**
    * Process (validated) form input and place the request.
    *
    * @return boolean True if success, false otherwise.
    */
    public function process()
    {
        return $this->requestLogic->placeRequest($this->patron,
            $this->gatheredDetails, $this->requestType);
    }

    /**
     * Validate form input values and populate validationErrors array
     * if there were invalid/illegal input data.
     */
    public function validate()
    {
        if (empty($this->gatheredDetails['copies'])) {
            $this->validationErrors['copies'] = 'Please select a copy/volume.';
        }
        if (empty($this->gatheredDetails['requiredBy'])) {
            $this->validationErrors['requiredBy'] 
                = 'Please specify a date this item is no longer required.';
        } else {
            $requiredBy = DateTime::createFromFormat(
                'm/d/Y', $this->gatheredDetails['requiredBy']
            );
            if ($requiredBy->getTimestamp() <= time()) {
                $this->validationErrors['requiredBy'] 
                    = 'Please specify a date this item is no longer required.';
            }
        }
        
        if (empty($this->gatheredDetails['pickUpLocation'])) {
            $this->validationErrors['pickupLocation']
                = 'Please choose a pick up location.';
        }
        // validate each requested item to make sure they are all allowed
        foreach ($this->gatheredDetails['requestedItems'] as $item) {
            $result = $this->requestLogic->checkPatron(
                $this->patron, $this->requestType, $item
            );
            if (!$result['allow']) {
                $this->validationErrors['requestedItems'] = $result['message'];
            }
            
            // if any item is an ERC item, additional conditions need to be checked
            if ($item['library_code'] == 'YORK-EDUC') {
                // only Hold is allowed for ERC
                if ($this->requestType != RequestLogic::HOLD) {
                    $this->validationErrors['requestType'] 
                        = $item['location'] . ' does not allow this request.';
                }
                // only the following patron profiles are allowed to place Holds
                $allowedProfiles = array(
                    'EDUCATION'
                );
                if (!in_array($this->patron['profile'], $allowedProfiles)) {
                    $this->validationErrors['patronProfile']
                        = 'Your library account does not allow this request.';
                }
                // the only pickup location allowed is ERC
                if ($this->gatheredDetails['pickUpLocation'] != 'ERC') {
                    $this->validationErrors['pickupLocation']
                        = 'Please choose a pick up location.';
                }
            }
        }
        return $this->validationErrors;
    }
    
    /**
     * Get the items being requested.
     * 
     * @return Array   Array of item records requested.
     */
    protected function getRequestedItems() 
    {
        $eligibleItems = array_merge(
            $this->eligibleItems['Keele'],
            $this->eligibleItems['Glendon']
        );
        $requestedItems = array();
        foreach ($this->gatheredDetails['copies'] as $barcode) {
            $item = $this->findItem($barcode, $eligibleItems);
            if ($item !== false) {
                $requestedItems[] = $item;
            }
        }
        return $requestedItems;
    }
    
   /**
    * Find an item matching a given barcode and call number from the list of items.
    * @param String $barcode     Barcode to search
    * @param Array  $haystack       List of items to search
    */
    protected function findItem($barcode, $haystack)
    {
        foreach ($haystack as $item) {
            if ($item['barcode'] == $barcode) {
                return $item;
            }
        }
        // not found
        return false;
    }

    private function _displayForm() 
    {
        global $interface;
        $institution = $interface->get_template_vars('institution');
        $library = $institution[0];
        $interface->assign('userInstructions', 
            $this->requestLogic->getUserInstructions($this->requestType, $this->eligibleItems));
        $libs = $this->requestLogic->getAllPickupLocations($library);
        $interface->assign('pickup', $libs);
        $interface->assign('requestType', $this->requestType);
        foreach ($this->gatheredDetails as $key => $value) {
            $interface->assign($key, $value);
        }
        $interface->assign('tab', 'Hold');
        $interface->assign('subTemplate', 'hold.tpl');
        $interface->setTemplate('view.tpl');
        if ($_REQUEST['modal']) {
            $interface->assign('modal', $_REQUEST['modal']);
            $interface->display('modal.tpl');
        } else {
            $interface->display('layout.tpl');
        }
    }

   /**
    * Check if this request is allowed.
    *
    * @param Array $patron
    */
    public function checkRequest($patron)
    {
        global $interface;
        $items = array();
        foreach ($this->holdings as $location => $holdings) {
            foreach ($holdings as $item) {
                $items[] = $item;
            }
        }
        $result = $this->requestLogic->checkRequest($items, $patron, $this->requestType);
        return $result;
    }
}

?>
