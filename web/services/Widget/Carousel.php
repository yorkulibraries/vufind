<?php
/**
 * Generate the JSONP to include carousels in web pages.
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
 * @package  Controller_Widget
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';
require_once 'services/MyResearch/lib/FavoriteHandler.php';

/**
 * Generate the JSONP to include carousels in webpages.
 *
 * @category VuFind
 * @package  Controller_Widget
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Carousel extends Action
{
    protected $searchObject;
    
    public function __construct() 
    {
        $this->searchObject = SearchObjectFactory::initSearchObject();
        $this->searchObject->init();
    }
    
    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        global $user;
        $interface->assign('time', time());
        if (isset($_GET['preview'])) {
            if (isset($_GET['list']) && is_numeric($_GET['list'])) {
                $list = User_list::staticGet($_GET['list']);
                if ($list) {
                	if ($list->public || ($user && $user->id == $list->user_id)) {
                        $interface->assign('carouselTitle', $list->title);
                	}
                }
                $interface->assign('searchUrlParams', 'list=' . $_GET['list']);
            } else {
                $this->searchObject->processSearch(false, true);
                $interface->assign('carouselTitle', translate("Search Results"));
                $interface->assign('searchUrlParams', $this->searchObject->renderSearchUrlParams());
                $interface->assign('lookfor', $this->searchObject->displayQuery());
                $interface->assign('searchType', $this->searchObject->getSearchType());
                $interface->assign('searchIndex', $this->searchObject->getSearchIndex());
            }
            $interface->setPageTitle(translate('Carousel Preview'));
            $interface->setTemplate('carousel-preview.tpl');
            $interface->display('layout.tpl');
            exit;
        }
                
        header('Content-Type: text/javascript');
        echo $this->getWidgetJS();
        exit;
    }
    
    private function getWidgetJS()
    {
        global $interface;
        global $configArray;
        global $user;
        
        // how long to keep the cache (in seconds), default 1 hour
        $cacheExpiry = isset($configArray['Widget']['cache_expiry']) 
            ? $configArray['Widget']['cacheTime'] : 3600;
        
        // cache generated in system temporary directory
        $cacheDir = sys_get_temp_dir();
        
        // cache file is the md5 hash of the query string
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . md5($_SERVER['QUERY_STRING']);
        
        $js = '';
        if (!file_exists($cacheFile) || (time() - filemtime($cacheFile) > $cacheExpiry)) {
            if (isset($_GET['list']) && is_numeric($_GET['list'])) {
                $list = User_list::staticGet($_GET['list']);
                if ($list) {
                    if ($list->public || ($user && $user->id == $list->user_id)) {
                	    $interface->assign('items', $this->getItemsFromUserList($list));
                    }
                }
            } else {
                $interface->assign('items', $this->getItemsFromSearchParams());
            }
            
            $interface->assign('id', $_GET['id']);
            $carousel = $interface->fetch('Widget/carousel.tpl');
            $interface->assign('count', $_GET['count']);
            $interface->assign('offset', 0);
            $interface->assign('carousel', $carousel);
            $js = $interface->fetch('Widget/carousel-js.tpl');
            
            // save cache
            file_put_contents($cacheFile, $js);
        } else {
            $js = file_get_contents($cacheFile);
        }
        return $js;   
    }

    private function getItemsFromUserList($list)
    {
        global $configArray;
        
        $resources = $list->getResources();
        $recordIds = array();
        foreach ($resources as $current) {
        	if (!empty($current->record_id) && $current->source == 'VuFind') {
            	$recordIds[] = $current->record_id;
        	}
        }
        $this->searchObject->setQueryIDs($recordIds);
        $result = $this->searchObject->processSearch(false, false);
        return $result['response']['docs'];
    }
    
    private function getItemsFromSearchParams()
    {
        $result = $this->searchObject->processSearch(false, false);
        return $result['response']['docs'];
    }
}
?>
