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
        
        if(strtolower($_REQUEST['style']) != 'refworks' && strtolower($_REQUEST['style']) != 'refworks_data') {
            PEAR::raiseError(new PEAR_Error('Unsupported Export Format'));
        }
        
        $list = User_list::staticGet($_REQUEST['id']);
        if (!$list) {
            PEAR::raiseError(new PEAR_Error('List not found'));
        }
        
        if (!$list->public) {
            PEAR::raiseError(new PEAR_Error('This list is not public'));
        }
        
        $resources = $list->getResources();
        if (empty($resources)) {
            PEAR::raiseError(new PEAR_Error('This list does not have any resource'));
        }
        
        if (strtolower($_REQUEST['style']) == 'refworks') {
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
            
            // Build the URL to pass data to RefWorks:
            $exportUrl = $configArray['Site']['url'] . '/MyResearch/ExportList/' .
                 $list->id . '?style=refworks_data';

            // Build up the RefWorks URL:
            $url = $configArray['RefWorks']['url'] . '/express/expressimport.asp';
            $url .= '?vendor=' . urlencode($configArray['RefWorks']['vendor']);
            $url .= '&filter=RefWorks%20Tagged%20Format&url=' . urlencode($exportUrl);
            header("Location: {$url}");
            exit;
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
        $result = $searchObject->processSearch(false, true);
        if (PEAR::isError($result)) {
            PEAR::raiseError($result);
        }
        if (isset($result['response']['docs'])) {
            foreach ($result['response']['docs'] as $doc) {
                $record = RecordDriverFactory::initRecordDriver($doc);
                $interface->assign('id', $record->getUniqueId());
                echo $interface->fetch($record->getExport($_REQUEST['style'])) . "\n";
            }
        }
    }
}
