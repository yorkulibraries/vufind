<?php
/**
 * CheckedOut action for MyResearch module
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
 * CheckedOut action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class CheckedOut extends MyResearch
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

        // Get My Transactions
        if ($patron = UserAccount::catalogLogin()) {
            if (PEAR::isError($patron)) {
                PEAR::raiseError($patron);
            }

            // Renew Items
            if (isset($_POST['renewAll']) || isset($_POST['renewSelected'])) {
                $renewResult = $this->_renewItems($patron);
            }

            $result = $this->catalog->getMyTransactions($patron);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            $recordList = array();
            foreach ($result as $row) {
                $record = $this->db->getRecord($row['id']);
                $record['ils_details'] = $row;
                $recordList[] = $record;
            }
            
            if ($this->checkRenew) {
                $recordList = $this->_addRenewDetails($recordList);
            }
                        
            $html = array();
            $interface->assign('recordStart', 1);
            for ($x = 0; $x < count($recordList); $x++) {
                // send the record to RecordDriver to generate bib info HTML
                $record = RecordDriverFactory::initRecordDriver($recordList[$x]);
                $interface->assign('listItemIndex', $x);
                $interface->assign('ilsDetails', $recordList[$x]['ils_details']);
                $interface->assign('renewKey', $recordList[$x]['ils_details']['renew_details']);
                $record->getSearchResult();
                $html[] = $interface->fetch('MyResearch/checkedout-list-entry.tpl');
            }
            $interface->assign('recordListHTML', $html);
            $interface->assign('recordList', $recordList);
            $interface->assign('recordCount', count($recordList));
        }
        
        $interface->setTemplate('checkedout.tpl');
        $interface->setPageTitle('Your Checkouts');
        $interface->display('layout.tpl');
    }

    /**
     * Adds a link or form details to existing checkout details
     *
     * @param array $transList An array of patron items
     *
     * @return array An array of patron items with links / form details
     * @access private
     */
    private function _addRenewDetails($transList)
    {
        global $interface;
        $session_details = array();

        foreach ($transList as $key => $item) {
            if ($this->checkRenew['function'] == "renewMyItemsLink") {
                // Build OPAC URL
                $transList[$key]['ils_details']['renew_link']
                    = $this->catalog->renewMyItemsLink($item['ils_details']);
            } else {
                // Form Details
                if ($transList[$key]['ils_details']['renewable'] 
                && $transList[$key]['ils_details']['overdue'] == 'N' 
                && empty($transList[$key]['ils_details']['recall_duedate'])) {
                    $interface->assign('renewForm', true);
                }
                $renew_details
                    = $this->catalog->getRenewDetails($item['ils_details']);
                $transList[$key]['ils_details']['renew_details']
                    = $session_details[] = $renew_details;
            }
        }

        // Save all valid options in the session so user input can be validated later
        $_SESSION['renewValidData'] = $session_details;
        return $transList;
    }

    /**
     * Private method for renewing items
     *
     * @param array $patron An array of patron information
     *
     * @return boolean true on success, false on failure
     * @access private
     */
    private function _renewItems($patron)
    {
        global $interface;

        $gatheredDetails['details'] = isset($_POST['renewAll'])
            ? $_POST['renewAllIDS'] : $_POST['renewSelectedIDS'];

        if (is_array($gatheredDetails['details'])) {
            $session_details = $_SESSION['renewValidData'];

            foreach ($gatheredDetails['details'] as $info) {
                // If the user input contains a value not found in the session
                // whitelist, something has been tampered with -- abort the process.
                if (!in_array($info, $session_details)) {
                    $interface->assign('errorMsg', 'error_inconsistent_parameters');
                    return false;
                }
            }

            // Add Patron Data to Submitted Data
            $gatheredDetails['patron'] = $patron;
            $renewResult = $this->catalog->renewMyItems($gatheredDetails);

            if ($renewResult !== false) {
                // Assign Blocks to the Template
                $interface->assign('blocks', $renewResult['block']);

                // Assign Results to the Template
                $interface->assign('renewResult', $renewResult['details']);

                return true;

            } else {
                 $interface->assign('errorMsg', 'renew_system_error');
            }
        } else {
            $interface->assign('errorMsg', 'renew_empty_selection');
        }
        return false;
    }
}

?>