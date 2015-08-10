<?php
/**
 * BookBag Save action.
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
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/User.php';
require_once 'services/MyResearch/lib/User_list.php';

/**
 * BookBag Save action.
 *
 * @category VuFind
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Save extends Action
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        
        // Check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
            $interface->assign('followup', true);
            $interface->assign('followupModule', 'Search');
            $interface->assign('followupAction', 'Email');                    
            $interface->setPageTitle('Login');
            $interface->assign('message', 'You must be logged in first');
            $interface->assign('subTemplate', '../MyResearch/login.tpl');
            if ($_REQUEST['modal']) {
                $interface->assign('followupURL', $_SERVER['PHP_SELF']);
                $interface->assign('followupQueryString', $_SERVER['QUERY_STRING']);
                $interface->assign('modal', $_REQUEST['modal']);
                $interface->display('modal.tpl');
            } else {
                $interface->setTemplate('view-alt.tpl');
                $interface->display('layout.tpl');
            }
            exit();
        }

        if (isset($_POST['submit'])) {
            if (strlen(trim($_REQUEST['listname'])) > 0) {
                $this->saveBookBag();
                header('Location: ' . $configArray['Site']['url'] . '/BookBag/Home');
                exit;
            }
        }
        
        // fetch lists
        $lists = $user->getLists();
        $interface->assign('mylists', $lists);
        
        // find last used list
        if (!empty($lists)) {
            // select to the first list as lastUsedList unless one was used previously
            $interface->assign('lastUsedList', $lists[0]->id);
            
            $lastUsedListId = User_list::getLastUsed();
            if ($lastUsedListId) {            
                $lastUsedList = new User_list();
                $lastUsedList->id = $lastUsedListId;
                if ($lastUsedList->find(true)) {
                    $interface->assign('lastUsedList', $lastUsedList->id);
                }
            }
        }
        
        // Display Page
        $interface->setPageTitle('Save Marked Items');
        $interface->setTemplate('save.tpl');
        if ($_REQUEST['modal']) {
            $interface->assign('modal', $_REQUEST['modal']);
            $interface->display('modal.tpl');
        } else {
            $interface->display('layout.tpl');
        }
    }
    
    public static function saveBookBag()
    {
        global $user;
        $cart = Cart_Model::getInstance();
        $items = $cart->getItems();
        if (!empty($items)) {
            $list = null;
            
            if (isset($_REQUEST['selected_list']) && !empty($_REQUEST['selected_list'])) {
                $list = new User_list();
                $list->id = $_REQUEST['selected_list'];
                if (!$list->find(true)) {
                    return false;
                }
            } else {
                $list = new User_list();
                $list->user_id = $user->id;
                $list->title = trim($_REQUEST['listname']);
                $list->description = trim($_REQUEST['desc']);
                $list->public = $_REQUEST['public'];
                if (!$list->insert()) {
                    return false;
                }
            }
            
            if (!$list) {
                return false;
            }
            
            $list->rememberLastUsed();
            
            foreach ($items as $item) {
                $resource = new Resource();
                $resource->record_id = $item;
                $resource->service = 'VuFind';
                if (!$resource->find(true)) {
                    $resource->insert();
                }
                if (!$user->addResource($resource, $list, null, null)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
