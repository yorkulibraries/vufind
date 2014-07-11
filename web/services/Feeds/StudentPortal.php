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
class StudentPortal extends Action
{
    protected $cacheKey;
    protected $htmlTemplate;
    protected $erisURL;
    
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
        
        if (empty($this->htmlTemplate)) {
            $logger->log('services/Feeds/StudentPortal.php - $htmlTemplate is not set', PEAR_LOG_DEBUG);
        }
        
        if (empty($this->erisURL)) {
            $logger->log('services/Feeds/StudentPortal.php - $erisURL is not set', PEAR_LOG_DEBUG);
        }
        
        if (empty($this->cacheKey)) {
            $logger->log('services/Feeds/StudentPortal.php - $cacheKey is not set', PEAR_LOG_DEBUG);
        }
        
        $courses = explode(',', strtoupper(trim($_SERVER['HTTP_PYORK_COURSES'] . ',' . $_REQUEST['courses'], ',')));
        if (in_array($_SERVER['HTTP_PYORK_USER'], array('tuan', 'dfiguero', 'aronse', 'rtester'))) {
            $courses[] = '2012_ap_huma_y_1970__9_b_en_a_lect_01';
            $courses[] = '2013_GS_EDUC_SU_5860__3_A_EN_A_ONLN_01';
        }
        $courses = array_unique($courses);
        $courses = implode(',', $courses);

        $xml = $this->getCachedXML($courses);

        header('Content-Type: text/xml');
        echo $xml;
        exit;
    }
    
    protected function getCachedXML($courses)
    {
        global $interface;
        global $configArray;
        global $memcache;
        global $logger;
        
        $cacheKey = $this->cacheKey . $courses;;
        if ($cacheKey && $memcache && ($xml = $memcache->get($cacheKey))) {
            $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
            return $xml;
        }

        $xml = $this->getXML($courses);
        
        $expiry = $this->cacheExpiry;
        if ($xml && $cacheKey && $memcache && $memcache->set($cacheKey, $xml, 0, $expiry) !== false) {
            $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
        }
        
        return $xml;
    }
    
    protected function getXML($courses)
    {
        global $interface;
        global $configArray;
        global $translator;
        global $logger;

        if (!empty($this->erisURL)) {
            $url = $this->erisURL . '?courses=' . $courses;
            $logger->log('Fetching XML from ERIS - ' . $url, PEAR_LOG_DEBUG);
            $src_xml = @file_get_contents($url);
            $logger->log('Got XML from ERIS - ' . $src_xml, PEAR_LOG_DEBUG);
         
            $simplexml = null;
            try {
                $simplexml = new SimpleXmlElement($src_xml);
            } catch (Exception $e) {
                $logger->log($e->getMessage(), PEAR_LOG_ERR);
            }
            $contents = array();
            if ($simplexml) {
                $items = $simplexml->xpath("//item");
                foreach ($items as $item) {
                    $group = trim((string) $item->description);
                    if (!empty($group)) {
                        if (!isset($contents[$group])) {
                            $contents[$group] = array();
                        }
                        $contents[$group][] = $item;
                    }
                }
            }
         
            $interface->assign('contents', $contents);
        }
        $html = array();            
        foreach ($configArray['Languages'] as $lang => $name) {
            $translator = new I18N_Translator('lang', $lang, false);
            $interface->setLanguage($lang);
            if (isset($configArray['Locale'][$lang])) {
                setlocale(LC_TIME, $configArray['Locale'][$lang]);
            }
            $html[$lang] = trim($interface->fetch($this->htmlTemplate));
        }
        // switch back to site default language and locale
        $translator = new I18N_Translator('lang', $configArray['Site']['language'], false);
        $interface->setLanguage($configArray['Site']['language']);
        setlocale(LC_TIME, $configArray['Site']['locale']);
         
        // render the result xml
        $interface->assign('html', $html);
        $xml = $interface->fetch('Feeds/student_portal_xml.tpl');
        return $xml;
    }
}
?>
