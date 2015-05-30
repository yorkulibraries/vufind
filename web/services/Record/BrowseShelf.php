<?php
/**
 * BrowseShelf action for Record module
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
 * BrowseShelf action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class BrowseShelf extends Record
{
    public function __construct() 
    {
        parent::__construct();
        $this->browser = new ShelfBrowser();
    }
    
    public function launch()
    {
        global $interface;
        
        list($min, $max) = $this->browser->guessMinMaxOrder($_REQUEST['id']);
        
        $left = $this->browser->browseLeft($min);
        $right = $this->browser->browseRight($max);
        
        $recordsOnEachSide = 2;
        
        $recordsToTheLeft = array();
        $countLeft = count($left);
        for ($i = $countLeft - $recordsOnEachSide; $i < $countLeft; $i++) {
            $item = $left[$i];
            $recordDriver = RecordDriverFactory::initRecordDriver($item['record']);
            $recordDriver->getSearchResult();
            $interface->assign('shelfOrder', $item['order']);
            $recordsToTheLeft[] = $interface->fetch('RecordDrivers/Index/shelf-browse-item.tpl');
        }
        $interface->assign('recordsToTheLeft', $recordsToTheLeft);
        
        $recordsToTheRight = array();
        for ($i = 0; $i < $recordsOnEachSide; $i++) {
            $item = $right[$i];
            $recordDriver = RecordDriverFactory::initRecordDriver($item['record']);
            $recordDriver->getSearchResult();
            $interface->assign('shelfOrder', $item['order']);
            $recordsToTheRight[] = $interface->fetch('RecordDrivers/Index/shelf-browse-item.tpl');
        }
        $interface->assign('recordsToTheRight', $recordsToTheRight);
        
        $this->recordDriver->getSearchResult();
        $interface->assign('shelfOrder', $min);
        $interface->assign('thisRecord', $interface->fetch('RecordDrivers/Index/shelf-browse-item.tpl'));
        
        $interface->assign('tab', 'BrowseShelf');
        $interface->setPageTitle(translate('Browse Shelf'));
        $interface->assign('subTemplate', 'view-browse-shelf.tpl');
        $interface->setTemplate('view.tpl');

        // Display Page
        $interface->display('layout.tpl');
    }
}
?>
