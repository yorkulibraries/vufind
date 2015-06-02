<?php
/**
 * Related action for Record module
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
 * @package  Controller_Record
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Record.php';
require_once 'sys/ShelfBrowser.php';

/**
 * Related action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Related extends Record
{
    public function __construct() 
    {
        parent::__construct();
    }
    
    public function launch()
    {
        global $interface;
    
        $interface->assign('similarItems', $this->similarItems());
        $interface->assign('browseShelf', $this->browseShelf());
        $interface->assign('tab', 'Related');
        $interface->setPageTitle(translate('Related'));
        $interface->assign('subTemplate', 'view-related.tpl');
        $interface->setTemplate('view.tpl');

        // Display Page
        $interface->display('layout.tpl');
    }
    
    private function browseShelf() 
    {
        global $interface;
        
        $browser = new ShelfBrowser();
        
        list($min, $max) = $browser->guessMinMaxOrder($_REQUEST['id']);
        
        $left = $browser->browseLeft($min);
        $right = $browser->browseRight($max);
        
        $recordsToTheLeft = array();
        foreach($left as $item) {
            $recordDriver = RecordDriverFactory::initRecordDriver($item['record']);
            $recordDriver->getSearchResult();
            $interface->assign('shelfOrder', $item['order']);
            $html = $interface->fetch('RecordDrivers/Index/browse-shelf-item.tpl');
            if (strlen(trim($html)) > 0) {
                $recordsToTheLeft[] = $html;    
            }
        }
        $interface->assign('recordsToTheLeft', $recordsToTheLeft);
        
        $recordsToTheRight = array();
        foreach ($right as $item) {
            $recordDriver = RecordDriverFactory::initRecordDriver($item['record']);
            $recordDriver->getSearchResult();
            $interface->assign('shelfOrder', $item['order']);
            $html = $interface->fetch('RecordDrivers/Index/browse-shelf-item.tpl');
            if (strlen(trim($html)) > 0) {
                $recordsToTheRight[] = $html;    
            }
        }
        $interface->assign('recordsToTheRight', $recordsToTheRight);
        
        $this->recordDriver->getSearchResult();
        $interface->assign('shelfOrder', $min);
        $interface->assign('startIndex', count($recordsToTheLeft));
        $interface->assign('thisRecord', $interface->fetch('RecordDrivers/Index/browse-shelf-item.tpl'));
        
        return $interface->fetch('RecordDrivers/Index/browse-shelf-list.tpl');
    }
    
    private function similarItems() 
    {
        global $interface;
        
        // yuck!!!
        $similarRecords = $interface->get_template_vars('similarRecords');
        
        $similarItems = array();
        foreach($similarRecords as $item) {
            $recordDriver = RecordDriverFactory::initRecordDriver($item);
            $recordDriver->getSearchResult();
            $html = $interface->fetch('RecordDrivers/Index/carousel-item.tpl');
            if (strlen(trim($html)) > 0) {
                $similarItems[] = $html;    
            }
        }
        return $similarItems;
    }
}
?>
