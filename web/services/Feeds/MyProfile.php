<?php
/**
 * Push patron profile out to authorized applications.
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

require_once 'MyAccount.php';

/**
 * Push patron profile out to authorized applications.
 *
 * @category VuFind
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class MyProfile extends MyAccount
{
    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
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
        
        $profile = null;
        
        // get the user record from the SIRSI database
        $york_id = $this->getPYorkHeader('CYIN');
        if (!$york_id) {
            $york_id = $_REQUEST['alt_id'];
        }
        $patron = $this->getPatronByYorkId($york_id);
        if ($patron) {
            $patron = $this->catalog->patronLogin($patron['barcode'], $patron['pin']);
            $profile = $this->catalog->getMyProfile($patron);
            $patron['address1'] = $profile['address1'];
            $patron['zip'] = $profile['zip'];
            $patron['phone'] = $profile['phone'];
            $patron['email'] = $profile['email'];
            unset($patron['pin']);
            unset($patron['cat_password']);
        }
        header('Content-type: application/json');
        echo json_encode($patron);
    }
}
?>
