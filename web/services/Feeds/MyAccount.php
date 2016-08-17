<?php
/**
 * Push data out to the my.yorku.ca students portal.
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
 * Push data out to the my.yorku.ca students portal.
 *
 * @category VuFind
 * @package  Controller_Feeds
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class MyAccount extends Action
{
    // define some status constants
    const STATUS_OK = 'OK';                  // good
    const STATUS_ERROR = 'ERROR';            // bad
    const STATUS_NEED_AUTH = 'NEED_AUTH';    // must login first

    protected $index;
    protected $catalog;

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        global $configArray;

        // Setup Search Engine Connection
        $class = $configArray['Index']['engine'];
        $this->index = new $class($configArray['Index']['url']);
        if ($configArray['System']['debug']) {
            $this->index->debug = true;
        }

        // Connect to catalog driver
        $this->catalog = $this->getCatalogConnection($configArray['Catalog']['driver']);
    }

    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        global $memcache;
        global $logger;
        
        $york_id = $this->getPYorkHeader('CYIN');
        
        $cacheKey = '/myaccount/' . $york_id;
        if (!empty($york_id) && $memcache && ($xml = $memcache->get($cacheKey))) {
            $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
            header('Content-Type: text/xml');
            echo $xml;
            exit;
        }

        // get the user record from the SIRSI database
        if (in_array($_SERVER['HTTP_PYORK_USER'], array('rtester'))) {
            $patron = $this->getPatronByYorkId($configArray['StudentPortal']['test_user_alt_id']);
        } else {
            $patron = !empty($york_id) ? $this->getPatronByYorkId($york_id) : null;
        }
        $xml = $this->getXML($patron);
        $expiry = isset($configArray['Caching']['myaccount_expiry']) 
            ? $configArray['Caching']['myaccount_expiry']
            : $configArray['Caching']['memcache_expiry'];
        if ($xml && $memcache && $memcache->set($cacheKey, $xml, 0, $expiry) !== false) {
            $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
        }
        header('Content-Type: text/xml');
        echo $xml;
        exit;
    }

    /**
     * Return library account information in predefined XML format.
     *
     * @return void
     * @access public
     */
    public function getXML($patron)
    {
        global $interface;
        global $configArray;
        global $translator;
        
        if ($patron) {
            $loans = $this->getMyTransactions($patron);
            $fines = $this->getMyFines($patron);
            $holds = $this->getMyHolds($patron);
            $interface->assign('status', 'OK');
            $interface->assign('patron', $patron);
            $interface->assign('loans', $loans);
            $interface->assign('fines', $fines);
            $interface->assign('holds', $holds);
        } else {
            $interface->assign('status', 'ERROR: user account not found.');
            $interface->assign('patron', null);
            $interface->assign('loans', null);
            $interface->assign('fines', null);
            $interface->assign('holds', null);
        }
        $html = array();            
        foreach ($configArray['Languages'] as $lang => $name) {
            $translator = new I18N_Translator('lang', $lang, false);
            $interface->setLanguage($lang);
            if (isset($configArray['Locale'][$lang])) {
                setlocale(LC_TIME, $configArray['Locale'][$lang]);
            }
            $html[$lang] = trim($interface->fetch('Feeds/myaccount_html.tpl'));
        }
        // switch back to site default language and locale
        $translator = new I18N_Translator('lang', $configArray['Site']['language'], false);
        $interface->setLanguage($configArray['Site']['language']);
        setlocale(LC_TIME, $configArray['Site']['locale']);
        
        // render the result xml
        $interface->assign('html', $html);
        $xml = $interface->fetch('Feeds/myaccount_xml.tpl');
        return $xml;
    }

    /**
     * Get the library patron record from the ILS driver.
     *
     * @access  protected
     * @return  mixed               $patron array (on success) or false (on failure)
     */
    protected function getPatronByYorkId($york_id)
    {
        $patron = $this->catalog->getPatronByAltId($york_id, '');
        if (empty($patron) || PEAR::isError($patron)) {
            return false;
        } else {
            $patron['cat_username'] = $patron['barcode'];
            $patron['cat_password'] = $patron['pin'];
            return $patron;
        }
        return false;
    }

    protected function getPYorkHeader($key)
    {
        $value = null;
        if (array_key_exists('HTTP_PYORK_' . $key, $_SERVER)) {
            $value = $_SERVER['HTTP_PYORK_' . $key];
        } else if (array_key_exists('HTTP_MAYAAA_' . $key, $_SERVER)) {
            $value = $_SERVER['HTTP_MAYAAA_' . $key];
        } else if (array_key_exists('HTTP_MAYA_' . $key, $_SERVER)) {
            $value = $_SERVER['HTTP_MAYA_' . $key];
        }
         
        return $value;
    }

    protected function getCatalogConnection($driver)
    {
        global $configArray;

        $path = "{$configArray['Site']['local']}/Drivers/{$driver}.php";
        if (is_readable($path)) {
            require_once $path;
            return new $driver;
        }

        return false;
    }

    private function getMyTransactions($patron)
    {
        $result = $this->catalog->getMyTransactions($patron);
        if (!PEAR::isError($result)) {
            $transList = array();
            foreach ($result as $data) {
                $record = $this->index->getRecord($data['id']);
                $trans = array('isbn'    => $record['isbn'],
                                           'author'  => $record['author'],
                                           'title'   => $record['title'],
                                           'format'  => $record['format']);
                foreach($data as $key => $val) {
                    $trans[$key] = $val;
                }
                $transList[] = $trans;
            }
            return $transList;
        }
        return $result;
    }

    private function getMyFines($patron)
    {
        $result = $this->catalog->getMyFines($patron);
        if (!PEAR::isError($result)) {
            $filtered = array();
            foreach ($result as $group => $data) {
                if (!isset($filtered[$group])) {
                    $filtered[$group] = array(
                        'items' => array(),
                        'groupTotal' => 0.00
                    );
                }
                $items = $data['items'];
                for ($i = 0, $j = 0; $i < count($items); $i++) {
                    $filtered[$group]['items'][] = $items[$i];
                    $filtered[$group]['groupTotal'] += $items[$i]['balance'];
                
                    // get the title from the solr index
                    $record = $this->index->getRecord($filtered[$group]['items'][$j]['id']);
                    $filtered[$group]['items'][$j]['title'] = $record ? $record['title'] : null;
                
                    $j++;
                }
            }
            return $filtered;
        }
        return $result;
    }

    private function getMyHolds($patron)
    {
        $holds = array();
        $result = $this->catalog->getMyHolds($patron);
        if (!PEAR::isError($result)) {
            foreach ($result as $row) {
                $record = $this->index->getRecord($row['id']);
                $record['ils_details'] = $row;
                $holds[] = $record;
            }
        }
        return $holds;
    }
}
?>
