<?php
/**
 * Location action for Record module
 *
 * PHP version 5
 *
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

/**
 * Location action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Location extends Record
{
    private $maps;
    private $buildings;
    
    public function __construct() 
    {
        parent::__construct();
        
        $this->maps = parse_ini_file('conf/maps.ini', true);
        
        // parse the Unicorn.ini for location to Library/Building mappings
        $conf = parse_ini_file('conf/Unicorn.ini', true);
        $this->buildings = $conf['Libraries'];
    }
    
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        
        $interface->setPageTitle('Item Location');
        $interface->assign('map', $this->findMap($_REQUEST['callnumber'], $_REQUEST['location_code']));
        $interface->assign('location', $_REQUEST['location']);
        $interface->assign('callnumber', $_REQUEST['callnumber']);
        $interface->setTemplate('view-location.tpl');

        $interface->display('layout.tpl', $this->cacheId);
    }
    
    private function findMap($callnumber, $location)
    {
        if (isset($this->buildings[$location])) {
            $building = $this->buildings[$location];
            foreach ($this->maps as $area) {
                if ($area['building'] == $building) {
                    // check designated locations
                    $locations = array_map('trim', explode(',', $area['locations']));
                    if (in_array($location, $locations)) {
                        return $this->pickMap($area);
                    }
                    
                    // check call number range
                    list($alpha, $rest) = explode(' ', $callnumber);
                    if ($alpha >= $area['start'] && $alpha <= $area['end']) {
                        return $this->pickMap($area);
                    }
                }
            }
        }
        return false;
    }
    
    private function pickMap($area) 
    {
        if (isset($area['map'])) {
            return $area['map'];
        }
        if (isset($area['googleMap'])) {
            return $area['googleMap'];
        }
        return null;    
    }
}

?>
