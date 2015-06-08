<?php
/**
 * Holdings action for Record module
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

/**
 * Holdings action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Holdings extends Record
{
    public function __construct() 
    {
        global $interface;
        $interface->assign('browseShelf', $this->browseShelf());
        
        parent::__construct();
    }
    
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        // Do not cache holdings page
        $interface->caching = 0;
        
        // See if patron is logged in to pass details onto get holdings for 
        // holds / recalls
        $patron = UserAccount::isLoggedIn() ? UserAccount::catalogLogin() : false;
        $interface->assign(
            'allItemRecords', $this->catalog->getHolding($_REQUEST['id'], $patron)
        );
        $interface->setPageTitle(
            translate('Holdings') . ': ' . $this->recordDriver->getBreadcrumb()
        );
        $interface->assign(
            'holdingsMetadata', $this->recordDriver->getHoldings($patron)
        );
        $this->_assignRequestStatus($patron);
        $interface->assign('subTemplate', 'view-holdings.tpl');
        $interface->setTemplate('view.tpl');

        // Set Messages
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('errorMsg', $this->errorMsg);

        // Display Page
        $interface->display('layout.tpl');
    }

   /**
    * Determine what kind of request is allowed and assign appropriate variables to smarty.
    *
    * @param Array $patron
    */
    private function _assignRequestStatus($patron = false)
    {
    	global $interface;

    	$items = $this->catalog->getHolding($_REQUEST['id'], $patron);
    	
    	include_once 'sys/RequestLogic.php';
    	$requestLogic = new RequestLogic($this->catalog);
    	$result = $requestLogic->checkHold($items, $patron);
    	if ($result['allow']) {
    		$interface->assign('allowHold', true);
    		$interface->assign('holdItems', $result['items']);
    	}
    	$result = $requestLogic->checkICB($items, $patron);
    	if ($result['allow']) {
    		$interface->assign('allowICB', true);
    		$interface->assign('icbItems', $result['items']);
    	}
    	$result = $requestLogic->checkInProcess($items, $patron);
    	if ($result['allow']) {
    		$interface->assign('allowInProcess', true);
    		$interface->assign('inProcessItems', $result['items']);
    	}
    	$result = $requestLogic->checkStorage($items, $patron);
    	if ($result['allow']) {
    		$interface->assign('allowStorage', true);
    		$interface->assign('storageItems', $result['items']);
    	}    
    }
    
    private function browseShelf() 
    {
        require_once 'sys/ShelfBrowser.php';
        
        global $interface;
        
        $browser = new ShelfBrowser();
        
        list($min, $max) = $browser->guessMinMaxOrder($_REQUEST['id']);
        
        $recordsToTheLeft = $browser->getHTMLItems($browser->browseLeft($min, false));
        $interface->assign('recordsToTheLeft', $recordsToTheLeft);
        
        $recordsToTheRight = $browser->getHTMLItems($browser->browseRight($max, true));
        $interface->assign('recordsToTheRight', $recordsToTheRight);
        
        $interface->assign('startIndex', count($recordsToTheLeft));
        return $interface->fetch('RecordDrivers/Index/browse-shelf-list.tpl');
    }
}

?>
