<?php
/**
 * BookBag Export action.
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
require_once 'services/MyResearch/lib/User_list.php';

/**
 * BookBag Export action.
 *
 * @category VuFind
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Export extends Action
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
        
        if(strtolower($_REQUEST['style']) != 'refworks') {
            PEAR::raiseError(new PEAR_Error('Unsupported Export Format'));
        }
        
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

        $cart = Cart_Model::getInstance();
        $items = $cart->getItems();
        
        if (!empty($items)) {
            // Create a temporary list for the items in the book bag
            $id = $this->saveBookBag($items);
            
            if ($id) {
                // Build the URL to pass data to RefWorks:
                $exportUrl = $configArray['Site']['url'] . '/MyResearch/ExportList/' .
                     $id . '?style=refworks_data';

                // Build up the RefWorks URL:
                $url = $configArray['RefWorks']['url'] . '/express/expressimport.asp';
                $url .= '?vendor=' . urlencode($configArray['RefWorks']['vendor']);
                $url .= '&filter=RefWorks%20Tagged%20Format&url=' . urlencode($exportUrl);
                header("Location: {$url}");
                exit;
            }
        }
        PEAR::raiseError(new PEAR_Error('Error exporting book bag'));
    }
    
    private function saveBookBag($items)
    {
        global $user;
        
        $list = new User_list();
        $list->user_id = $user->id;
        $list->title = 'RefWorks';
        $list->public = 1;
        $id = $list->insert();
        if (!$id) {
            return false;
        }
    
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

        return $id;
    }
}
