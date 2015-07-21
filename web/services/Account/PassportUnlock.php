<?php
/**
 * Unlock user account if authenticated successfully with Passport York.
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
 * @package  Controller_Account
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'services/MyResearch/lib/FailedLogins.php';

/**
 * Unlock user account if authenticated successfully with Passport York.
 *
 * @category VuFind
 * @package  Controller_Account
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class PassportUnlock extends Action
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
        
        if (isset($_SERVER['HTTP_PYORK_USERNAME']) && !empty($_SERVER['HTTP_PYORK_USERNAME'])) {
            $failedLogins = new FailedLogins();
            $failedLogins->username = $_SERVER['HTTP_PYORK_USERNAME'];
            if ($failedLogins->find(true)) {
                $failedLogins->delete();
            }
        } else {
            PEAR::raiseError(new PEAR_Error('Passport York Information Not Available'));
        }
        
        // Display Page
        $interface->setPageTitle('User Account Unlocked');
        $interface->setTemplate('unlocked.tpl');
        $interface->display('layout.tpl');
    }
}
?>
