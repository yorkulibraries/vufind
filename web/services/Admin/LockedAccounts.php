<?php
/**
 * List users who are locked out because of too many failed login attempts.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'services/MyResearch/lib/FailedLogins.php';
require_once 'sys/VuFindDate.php';

/**
 * List users who are locked out because of too many failed login attempts.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class LockedAccounts extends Action
{
    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        $failedLimit = $configArray['Authentication']['max_failed_login_attempts'];
        $dateFormat = new VuFindDate();
        if (isset($_REQUEST['delete'])) {
            $failedLogins = new FailedLogins();
            $failedLogins->id = $_REQUEST['delete'];
            if ($failedLogins->find(true)) {
                $failedLogins->delete();
            }
        }
        $lockedOutList = array();
        $failedLogins = new FailedLogins();
        $failedLogins->whereAdd("attempts >= $failedLimit");
        $failedLogins->whereAdd("last_attempt >= timestampadd(hour, -24, now())");
        $failedLogins->orderBy('last_attempt DESC');
        $failedLogins->find();
        while ($failedLogins->fetch()) {
            $entry = clone($failedLogins);
            //$entry->last_attempt = $dateFormat->convertToDisplayDate('Y-m-d H:i:s', $entry->last_attempt);
            $lockedOutList[] = $entry;
        }
        $interface->assign('lockedOutList', $lockedOutList);
        $interface->setPageTitle('Locked Accounts');
        $interface->setTemplate('locked-accounts.tpl');
        $interface->display('layout-admin.tpl');
    }
}
?>
