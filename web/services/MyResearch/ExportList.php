<?php
/**
 * MyResearch ExportList action.
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
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/User_list.php';

/**
 * MyResearch ExportList action.
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class ExportList extends Action
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
        
        $list = User_list::staticGet($_REQUEST['id']);
        if (!$list) {
            PEAR::raiseError(new PEAR_Error('List not found'));
        }
        
        $resources = $list->getResources();
        if (empty($resources)) {
            PEAR::raiseError(new PEAR_Error('This list does not have any resource'));
        }

        $items = array();
        foreach($resources as $resource) {
            $items[] = $resource->record_id;
        }
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
        
        header('Content-Disposition: attachment; filename="YUL_List_' . $list->id . '.ris"');
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
}
