<?php
/**
 * Fines action for MyResearch module
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
require_once 'services/MyResearch/PayFines.php';

/**
 * Fines action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Fines extends PayFines
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
            
        // verify initiated payments and set appropriate status eg: APPROVED or CANCELLED
        $this->verifyInitiatedPayments();
        
        // complete the approved payments
        $this->completeApprovedPayments($this->getApprovedPayments());
        
        // get the bills from catalog
        $result = $this->catalog->getMyFines($this->patron);
        
        if (!PEAR::isError($result)) {
            foreach ($result as $group => $data) {
                $items = $data['items'];
                for ($i = 0; $i < count($items); $i++) {
                    $record = $this->db->getRecord($items[$i]['id']);
                    $result[$group]['items'][$i]['title'] = $record ? $record['title'] : null;
                }
            }
            $interface->assign('finesData', $result);
        }
        
        // get recently completed payments that we have not notified user
        $paymentNotifications = Payment::getPayments($this->patron['cat_username'], 'payment_date DESC', null, 0);
        foreach ($paymentNotifications as $p) {
            $p->notified_user = 1;
            $p->update();
        }
        
        $interface->assign('paymentNotifications', $paymentNotifications);
        $interface->assign('payments', $this->getPayments());
        $interface->assign('receiptBaseURL', PayFines::getReceiptBaseURL());
        $interface->setTemplate('fines.tpl');
        $interface->setPageTitle('Your Fines');
        $interface->display('layout.tpl');
    }
}

?>