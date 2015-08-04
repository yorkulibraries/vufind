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
        
        if(strtolower($_REQUEST['style']) != 'endnoteweb') {
            PEAR::raiseError(new PEAR_Error('Unsupported Export Format'));
        }

        $cart = Cart_Model::getInstance();
        $items = $cart->getItems();
        
        if (!empty($items)) {
            // Initialise from the current search globals
            $searchObject = SearchObjectFactory::initSearchObject();
            $searchObject->init();
            $searchObject->setSort('title');
            $searchObject->setQueryIDs($items);
            $searchObject->setLimit(count($items));
            $result = $searchObject->processSearch(false, false);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result);
            }

            header('Content-Disposition: attachment; filename="YUL_Export.ris"');
            header('Content-Type: application/x-research-info-systems;charset=utf-8');
            header('Pragma: private');

            if (isset($result['response']['docs'])) {
                foreach ($result['response']['docs'] as $doc) {
                    $record = RecordDriverFactory::initRecordDriver($doc);
                    $record->getExport($_REQUEST['style']);
                    echo "\n";
                }
            }
        }
        PEAR::raiseError(new PEAR_Error('Error exporting items'));
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
