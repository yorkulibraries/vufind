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
require_once 'services/MyResearch/MyResearch.php';

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
class Fines extends MyResearch
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
        global $finesIndexEngine;

        // Get My Fines
        if ($patron = UserAccount::catalogLogin()) {
            if (PEAR::isError($patron)) {
                PEAR::raiseError($patron);
            }
            $result = $this->catalog->getMyFines($patron);
            $totalBalance = 0.00;
            if (!PEAR::isError($result)) {
                for ($i = 0; $i < count($result); $i++) {
                    $record = $this->db->getRecord($result[$i]['id']);
                    $result[$i]['title'] = $record ? $record['title'] : null;
                    $totalBalance += $result[$i]['balance'];
                }
                $interface->assign('rawFinesData', $result);
                $interface->assign('totalBalance', $totalBalance);
            }
        }

        $interface->setTemplate('fines.tpl');
        $interface->setPageTitle('Your Fines');
        $interface->display('layout.tpl');
    }
    
}

?>