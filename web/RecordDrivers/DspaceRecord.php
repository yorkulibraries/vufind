<?php
/**
 * Dspace Record Driver
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
 * @package  RecordDrivers
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
require_once 'RecordDrivers/IndexRecord.php';

/**
 * Dspace Record Driver
 *
 * This class is designed to handle Dspace records.  Much of its functionality
 * is inherited from the default index-based driver.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
class DspaceRecord extends IndexRecord
{
    // simplexml dom object holding the full Dublin Core metadata record
    protected $fullRecord = null;
    
    public function __construct($indexFields)
    {
        parent::__construct($indexFields);
        
        $this->preferredSnippetFields = array(
        	'fulltext_unstemmed', 'description'
        );
        
        $this->fullRecord = simplexml_load_string($this->fields['fullrecord']);
        $this->fullRecord->registerXPathNamespace('dc', 
        	'http://purl.org/dc/elements/1.1/');
        $this->fullRecord->registerXPathNamespace('oai_dc', 
        	'http://www.openarchives.org/OAI/2.0/oai_dc/');
    }
    
    /**
     * Return a URL to a thumbnail preview of the record, if available; false
     * otherwise.
     *
     * @param array $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return mixed
     * @access protected
     */
    protected function getThumbnail($size = 'small')
    {
        global $configArray;

        $formats = $this->getFormats();
        $format = empty($formats) ? '' : $formats[0];

        // default content type icon
        $icon = false;
        switch ($format) {
            case 'Book Chapter':
                //$type = 'BookChapter';
                break;
            case 'Article':
                $type = 'JournalArticle';
                break;
        }
        
        $handle = $this->getUniqueID();
        $url = $configArray['Site']['url'] . '/bookcover.php?handle='
            . $handle . '&size=' . urlencode($size);
        if (!empty($type)) {
            $url .= '&contenttype=' . urlencode($type);
        }
        return $url;
    }
    
   /**
    * Assign necessary Smarty variables and return a template name to
    * load in order to display the full record information on the Staff
    * View tab of the record view page.
    *
    * @return string Name of Smarty template file to display.
    * @access public
    */
    public function getStaffView()
    {
        return false;
    }
    
   /**
    * Assign necessary Smarty variables and return a template name to
    * load in order to display core metadata (the details shown in the
    * top portion of the record view pages, above the tabs).
    *
    * @access  public
    * @return  string              Name of Smarty template file to display.
    */
    public function getCoreMetadata()
    {
        global $interface;
    
        // call parent to setup core metadata template variables
        $template = parent::getCoreMetadata();
    
        // call parent to setup extended metadata template variables
        parent::getExtendedMetadata();
    
        return $template;
    }
}

?>
