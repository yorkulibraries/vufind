<?php
/**
 * Main action for unAPI module
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
 * @package  Controller_unAPI
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';

/**
 * Main action for unAPI module
 *
 * @category VuFind
 * @package  Controller_unAPI
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Server extends Action
{
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        
        // support MODS only for now
        $this->formats = array('mods'=>'application/xml');
        
        // solr index
        $this->index = ConnectionManager::connectToIndex();
        
        $id = isset($_REQUEST['id'])? $_REQUEST['id'] : false;
        $format = isset($_REQUEST['format'])? $_REQUEST['format'] : false;
        
        if (!$format) {
            return $this->listFormats();
        } else if ($id) {
            $record = $this->index->getRecord($id);
            if ($record) {
                switch(strtolower($format)) {
                case 'mods':
                    if ($record['recordtype'] == 'marc') {
                        header('Content-type: application/xml');
                        echo $this->marc2Mods($this->getMARC($record));
                    }
                    break;
                default:
                    return $this->listFormats();
                }
            } else {
                header('HTTP/1.0 404 Not Found', true, 404);
            }
        } else {
            header('HTTP/1.0 400 bad request', true, 400);
        }
    }
    
    private function listFormats($id = false) {
        global $interface;

        $interface->assign('id', $id);
        $interface->assign('formats', $this->formats);
                
        header('Content-type: application/xml');
        $interface->display('unAPI/list-formats.tpl');
    }
    
    private function marc2Mods($record) {
        // Get Record as MARCXML
        $xml = trim($record->toXML());

        // Load Stylesheet
        $style = new DOMDocument;
        $style->load('services/Record/xsl/MARC21slim2MODS3-5.xsl');

        // Setup XSLT
        $xsl = new XSLTProcessor();
        $xsl->importStyleSheet($style);

        // Transform MARCXML
        $doc = new DOMDocument;
        if ($doc->loadXML($xml)) {
            return $xsl->transformToXML($doc);
        }
    }
    
    private function getMARC($record) {
        $marc = trim($record['fullrecord']);

        // check if we are dealing with MARCXML
        $xmlHead = '<?xml version';
        if (strcasecmp(substr($marc, 0, strlen($xmlHead)), $xmlHead) === 0) {
            $marc = new File_MARCXML($marc, File_MARCXML::SOURCE_STRING);
        } else {
            $marc = preg_replace('/#31;/', "\x1F", $marc);
            $marc = preg_replace('/#30;/', "\x1E", $marc);
            $marc = new File_MARC($marc, File_MARC::SOURCE_STRING);
        }

        return $marc->next();
    }
}

?>