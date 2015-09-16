<?php
/**
 * SFX Link Resolver Driver
 *
 * PHP version 5
 *
 * Copyright (C) Royal Holloway, University of London
 *
 * last update: 2010-10-11
 * tested with X-Server SFX 3.2
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
 * @package  Resolver_Drivers
 * @author   Graham Seaman <Graham.Seaman@rhul.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_link_resolver_driver Wiki
 */
require_once 'Interface.php';

/**
 * SFX Link Resolver Driver
 *
 * @category VuFind
 * @package  Resolver_Drivers
 * @author   Graham Seaman <Graham.Seaman@rhul.ac.uk>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_link_resolver_driver Wiki
 */
class Resolver_Sfx implements ResolverInterface
{
    private $_baseUrl;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        // Load Configuration for this Module
        global $configArray;
        $this->_baseUrl = $configArray['OpenURL']['url'];
    }

    /**
     * Fetch Links
     *
     * Fetches a set of links corresponding to an OpenURL
     *
     * @param string $openURL openURL (url-encoded)
     *
     * @return string         raw XML returned by resolver
     * @access public
     */
    public function fetchLinks($openURL)
    {
        global $logger;
        
        // Make the call to SFX and load results
        $url = $this->_baseUrl . 
            '?sfx.response_type=multi_obj_detailed_xml&svc.fulltext=yes&' . $openURL;
        $logger->log('Making SFX request: ' . $url, PEAR_LOG_DEBUG);
        $feed = file_get_contents($url);
        return $feed;
    }

    /**
     * Parse Links
     *
     * Parses an XML file returned by a link resolver
     * and converts it to a standardised format for display
     *
     * @param string $xmlstr Raw XML returned by resolver
     *
     * @return array         Array of values
     * @access public
     */
    public function parseLinks($xmlstr)
    {
        global $configArray;
        global $logger;

        $records = array(); // array to return
        try {
            $xml = new SimpleXmlElement($xmlstr);
        } catch (Exception $e) {
            return $records;
        }
         
        $root = $xml->xpath("//ctx_obj_targets");
        $xml = $root[0];
        foreach ($xml->children() as $target) {
            $record = array();
            $record['title'] = (string)$target->target_public_name;
            $record['href'] = (string)$target->target_url;
            $record['service_type'] = (string)$target->service_type;
            if (isset($target->coverage)) {
                $record['coverage'] = (string)$target->coverage->coverage_text->threshold_text->coverage_statement;
            }
            $record['service_id'] = (string)$target->target_service_id;
            $record['note'] = (string)$target->note;
            $record['proxy'] = (string)$target->proxy;
            $record['target_name'] = (string)$target->target_name;
            
            // temporary workaround for https://github.com/yorkulibraries/vufind/issues/3
            $doNotProxy = $configArray['EZproxy']['do_not_proxy'];
            if (!in_array($record['target_name'], $doNotProxy) && $record['proxy'] == 'no') {
                $record['proxy'] = 'yes';
                $record['href'] = $configArray['EZproxy']['host'] . '/login?url=' . $record['href'];
            }
            
            // temporary workaround for https://github.com/yorkulibraries/vufind/issues/59
            if (strpos($record['target_name'], 'CHINA_ONLINE_JOURNALS') !== false && stripos($record['href'], 'http://www.wanfangdata.com/') !== false) {
                if(preg_match('/server_loc=&jkey=(.+)$/', $target->parse_param, $matches)) {
                    $logger->log('Journal key=' . $matches[1], PEAR_LOG_DEBUG);
                    $record['proxy'] = 'yes';
                    $record['href'] = $configArray['EZproxy']['host'] . '/login?url='  
                        . 'http://c.wanfangdata.com.cn/Periodical-' . $matches[1] . '.aspx';
                    $logger->log('Corrected Journal URL=' . $record['href'], PEAR_LOG_DEBUG);
                }
            }
            
            if(preg_match('/\/licenses\/(.+)\/sfx/', $record['note'], $matches)) {
                $rights = $this->getUsageRights($matches[1]);
                $record['usage_rights'] = $rights;
                $record['license_name'] = $matches[1];
            } else if (preg_match('/src=[\'\"]?http:\/\/york.scholarsportal.info\/licenses\/([^\'\" ]+)[\'\" ]? /', $record['note'], $matches)) {
                $rights = $this->getUsageRights($matches[1]);
                $record['usage_rights'] = $rights;
                $record['license_name'] = $matches[1];
            }
            array_push($records, $record);
        }
        return $records;
    }
    
    private function getUsageRights($name)
    {
        global $configArray, $logger, $memcache;
        
        $cacheKey = 'OCUL Usage Rights ' . $name;
        if ($memcache) {
            $xmlstr = $memcache->get($cacheKey);
            if ($xmlstr !== false) {
                $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
                return $this->parseUsageRights($xmlstr);
            }
        }
        $url = $configArray['UsageRightsApi']['url'] . '/' . $name . '/api';
        $xmlstr = file_get_contents($url);
        if ($memcache && $memcache->set($cacheKey, $xmlstr, 0, $configArray['Caching']['memcache_expiry'])) {
            $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
        }
        return $this->parseUsageRights($xmlstr);
    }
    
    private function parseUsageRights($xmlstr) 
    {
        global $logger;
        $map = array('Yes'=>'success', 'Ask'=>'warning', 'No'=>'danger');
        $rights = null;
        try {
            $xml = new SimpleXmlElement($xmlstr);
            $root = $xml->xpath("//license");
            $xml = $root[0];
            $rights = $xml->children();
        } catch (Exception $e) {
            $logger->log('Can not parse usage rights XML: ' . $xmlstr, PEAR_LOG_ERR);
        }
        foreach ($rights as $right) {
            $right->addChild('status', $map[(string)$right->usage]);
        }
        return $rights;
    }
}

?>
