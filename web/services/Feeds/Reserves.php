<?php
/**
 * Push course reserve records out as RSS feed.
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
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';

/**
 * Push course reserve records out as RSS feed.
 *
 * @category VuFind
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Reserves extends Action
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
        
        $tags = array();
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $tags = explode(',', $_GET['tag']);
        }
        $tagsToSearch = array();
        if (!empty($tags)) {
            $tagsToSearch[] = str_replace('_', ' ', $tags[0]);
        }
        $_REQUEST['lookfor'] = implode(' OR ', $tagsToSearch);
        $searchObject = SearchObjectFactory::initSearchObject('SolrReserves');
        $searchObject->init();
        // Process Search
        $result = $searchObject->processSearch(false, false);
        if (empty($tagsToSearch)) {
            $interface->assign('resultTotal', 0);
        } else {
            $interface->assign('resultTotal', $searchObject->getResultTotal());
        }
        $interface->assign('searchQuery', $searchObject->displayQuery());
        $interface->assign('searchUrl', $searchObject->renderSearchUrl());
        echo $interface->fetch('Feeds/reserves_rss.tpl');
    }
}
?>
