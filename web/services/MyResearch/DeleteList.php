<?php
/**
 * DeleteList action for MyResearch module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2011.
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
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once "Action.php";

require_once 'Home.php';

/**
 * DeleteList action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class DeleteList extends Action
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

        if (!($user = UserAccount::isLoggedIn())) {
            include_once 'Login.php';
            Login::launch();
            exit();
        }

        // Fetch List object
        $list = User_list::staticGet($_GET['id']);

        // Ensure user have privs to view the list
        if ($list->user_id != $user->id) {
            PEAR::raiseError(new PEAR_Error(translate('list_access_denied')));
        }
        
        if ($list->emptyList()) {
            header('Location: ' . $configArray['Site']['url'] . '/MyResearch/MyList');
            exit;
        }
        
        PEAR::raiseError(new PEAR_Error(translate('fav_list_delete_fail')));
    }
}

?>
