<?php
/**
 * List users who are BANNED for abusing proxy.
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

require_once 'Admin.php';

/**
 * List users who are BANNED for abusing proxy.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class ProxyAbusers extends Admin
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
        if (isset($_REQUEST['unlock'])) {
            $user = new User();
            $user->cat_username = $_REQUEST['unlock'];
            if ($user->find(true)) {
                $user->banned = 0;
                $user->update();
                header('Location: ' . $configArray['Site']['url'] . '/Admin/ProxyAbusers');
                exit;
            }
        }
        $lockedOutList = array();
        $bannedUsers = new User();
        $bannedUsers->banned = 1;
        $bannedUsers->orderBy('cat_username');
        $bannedUsers->find();
        while ($bannedUsers->fetch()) {
            $entry = clone($bannedUsers);
            //$entry->last_attempt = $dateFormat->convertToDisplayDate('Y-m-d H:i:s', $entry->last_attempt);
            $lockedOutList[] = $entry;
        }
        $interface->assign('lockedOutList', $lockedOutList);
        $interface->setPageTitle('Proxy Abusers');
        $interface->setTemplate('proxy-abusers.tpl');
        $interface->display('layout-admin.tpl');
    }
}
?>
