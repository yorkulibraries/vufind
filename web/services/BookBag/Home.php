<?php
/**
 * Home action for BookBag module.
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
require_once 'sys/Pager.php';

/**
 * Home action for BookBag module.
 *
 * @category VuFind
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Home extends Action
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
        
        // Initialise from the current search globals
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();
        if (!isset($_REQUEST['sort'])) {
        	$searchObject->setSort('title');
        }
        $interface->assign('sortList', $searchObject->getSortList());
        
        $interface->setPageTitle('View Marked Items');
        $interface->setTemplate('bookbag-list.tpl');
        $currentView  = $searchObject->getView();
        $interface->assign('subpage', 'Search/list-' . $currentView .'.tpl');
        $interface->assign('viewList',   $searchObject->getViewList());
        
        $cart = Cart_Model::getInstance();
        $items = $cart->getItems();
        if (!empty($items)) {
            $searchObject->setQueryIDs($items);

            // Process Search
            $result = $searchObject->processSearch(false, true);
            if (PEAR::isError($result)) {
                PEAR::raiseError($result->getMessage());
            }
        
            $interface->assign('viewBookBag', true);
            $summary = $searchObject->getResultSummary();
            $interface->assign('recordCount', $summary['resultTotal']);
            $interface->assign('recordStart', $summary['startRecord']);
            $interface->assign('recordEnd',   $summary['endRecord']);
            $interface->assign('recordSet', $searchObject->getResultRecordHTML());

            $link = $searchObject->renderLinkPageTemplate();
            $total = isset($result['response']['numFound']) ?
                $result['response']['numFound'] : 0;
            $options = array('totalItems' => $total,
                             'perPage' => $summary['perPage'],
                             'fileName' => $link);
            $pager = new VuFindPager($options);
            $interface->assign('pageLinks', $pager->getLinks());
        } else {
            $interface->assign('recordCount', 0);
            $interface->assign('recordStart', 0);
            $interface->assign('recordEnd',   0);
        }
        $interface->display('layout.tpl');
    }
}
