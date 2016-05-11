<?php
/**
 * PayFines action for MyResearch module
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
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'services/MyResearch/MyResearch.php';

/**
 * PayFines action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class PayFines extends MyResearch
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        $group = $_REQUEST['g'];
        if (empty($group)) {
            header('Location: ' . $configArray['Site']['url'] . '/MyResearch/Fines');
            exit;
        }
        
        // Get My Fines
        if ($patron = UserAccount::catalogLogin()) {
            if (PEAR::isError($patron)) {
                PEAR::raiseError($patron);
            }
            if (empty($patron['cat_username'])) {
                PEAR::raiseError('Received invalid patron information from catalogue.');
            }
            
            // library patron/barcode
            $this->patron = $patron;
            $username = $this->patron['cat_username'];
            
            // start logger using barcode as file name
            $logFile = $configArray['Fines']['payment_log_dir'] . '/' . date("Ymd") . '/' . $username . '.log';
            $this->logger = Log::singleton('file', $logFile);
            
            // get the bills
            $result = $this->catalog->getMyFines($this->patron);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }
            $this->bills = $result;
            
            if (isset($_POST['pay']) && !empty($_POST['pay'])) {
                $this->doPayAction();
                exit;
            }

            // default
            $this->displayItemsToPay();
            exit;
        }
    }
    
    private function doPayAction()
    {
        global $interface;
        global $configArray;
        
        $this->logger->log('Fetching list of items to pay');
        $itemsToPay = $this->getItemsToPay();
        $this->logger->log($itemsToPay);
        
        
    }
    
    private function getItemsToPay()
    {
        $group = $_REQUEST['g'];
        $items = $this->bills[$group]['items'];
        
        // if an array of selected items is available, then only show those
        $selected = $_POST['selected'];
        if (is_array($selected) && !empty($selected)) {
            $selectedItems = array();
            foreach ($items as $item) {
                if (in_array($item['bill_key'], $selected)) {
                    $selectedItems[] = $item;
                }
            }
            $items = $selectedItems;
        }
        
        $total = 0.00;
        for ($i = 0; $i < count($items); $i++) {
            $record = $this->db->getRecord($items[$i]['id']);
            $items[$i]['title'] = $record ? $record['title'] : null;
            $total += $items[$i]['balance'];
        }
        
        return array('group' => $group, 'total' => $total, 'items' => $items);
    }
    
    private function displayItemsToPay()
    {
        global $interface;
        global $configArray;

        $itemsToPay = $this->getItemsToPay();
        
        $interface->assign('confirming', (isset($_POST['confirm']) && !empty($_POST['confirm'])));
        $interface->assign('items', $itemsToPay['items']);
        $interface->assign('total', $itemsToPay['total']);
        $interface->assign('group', $itemsToPay['group']);
        $interface->setTemplate('pay-fines.tpl');
        $interface->setPageTitle('Pay Fines');
        $interface->display('layout.tpl');
    }
}

?>