<?php
/**
 * OJS (NLM metadata format) Record Driver
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
 * OJS (NLM metadata format) Record Driver
 *
 * This class is designed to handle OJS records harvested with NLM metadata format.  
 * Much of its functionality is inherited from the default index-based driver.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/other_than_marc Wiki
 */
class NLMOJSRecord extends IndexRecord
{    
    // simplexml dom object holding the full NLM metadata record
    protected $fullRecord = null;
    
    public function __construct($indexFields)
    {
        parent::__construct($indexFields);
        
        $this->preferredSnippetFields = array(
        	'fulltext_unstemmed', 'description'
        );
        
        $this->fullRecord = simplexml_load_string($this->fields['fullrecord']);
        $this->fullRecord->registerXPathNamespace(
        	'nlm', 'http://dtd.nlm.nih.gov/publishing/2.3'
        );
    }
    
   /**
    * Assign necessary Smarty variables and return a template name for the current
    * view to load in order to display a summary of the item suitable for use in
    * search results.
    *
    * @param string $view The current view.
    *
    * @return string      Name of Smarty template file to display.
    * @access public
    */
    public function getSearchResult($view = 'list')
    {
        global $configArray;
        global $interface;
                
        $template = parent::getSearchResult($view);
        
        $interface->assign('summJournalTitle', $this->getJournalTitle());
        $interface->assign('summJournalVolume', $this->getJournalVolume());
        $interface->assign('summJournalIssue', $this->getJournalIssue());
        $interface->assign('summJournalISSNs', $this->getISSNs());
        $interface->assign('summJournalIssueDate', $this->getJournalIssueDate());
        $interface->assign('summJournalIssueTitle', $this->getJournalIssueTitle());
        
        return 'RecordDrivers/NLMOJS/result-' . $view . '.tpl';
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

        $url = $configArray['Site']['url'] . '/bookcover.php?&size=' 
            . urlencode($size) . '&contenttype=JournalArticle';
        return $url;
    }
    
    /**
     * Get the title of the journal this article is published in.
     * 
     * @return string
     * @access protected
     */
    protected function getJournalTitle()
    {
        $results = $this->fullRecord->xpath('//nlm:journal-title');
        return empty($results) ? '' : (string) $results[0];
    }
    
   /**
    * Get the journal volume this article is published in.
    *
    * @return string
    * @access protected
    */
    protected function getJournalVolume()
    {
        $results = $this->fullRecord->xpath('//nlm:volume');
        return empty($results) ? '' : (string) $results[0];
    }
    
   /**
    * Get the journal issue this article is published in.
    *
    * @return string
    * @access protected
    */
    protected function getJournalIssue()
    {
        $results = $this->fullRecord->xpath('//nlm:issue');
        return empty($results) ? '' : (string) $results[0];
    }
    
   /**
    * Get the journal issue title.
    *
    * @return string
    * @access protected
    */
    protected function getJournalIssueTitle()
    {
        $results = $this->fullRecord->xpath('//nlm:issue-title');
        return empty($results) ? '' : (string) $results[0];
    }
    
   /**
    * Get the journal issue date.
    *
    * @return string
    * @access protected
    */
    protected function getJournalIssueDate()
    {
        $pubdate = $this->fullRecord->xpath('//nlm:pub-date');
        if (empty($pubdate)) {
            return '';
        }
        $pubdate[0]->registerXPathNamespace('nlm', 'http://dtd.nlm.nih.gov/publishing/2.3');
        $month = $pubdate[0]->xpath('nlm:month');
        $date = empty($month) ? '' : (string) $month[0];
        $year = $pubdate[0]->xpath('nlm:year');
        $date .= empty($year) ? '' : '/' . (string) $year[0];
        return $date;
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
