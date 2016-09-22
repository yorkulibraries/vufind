<?php
require_once 'RecordDrivers/MarcRecord.php';

/**
 * York's MARC record driver.
 */
class YorkMarcRecord extends MarcRecord
{
    private $researchGuides;

    public function __construct($record)
    {
        parent::__construct($record);

        // process marc 856 fields to separate fulltext from supplemental URLs
        $this->process856Fields();

        global $interface;
        $interface->assign('isJournal', $this->isJournal());
        $interface->assign('isFond', $this->isFond());
        $interface->assign('recordDataSource', $this->fields['data_source_str']);
    }

    public function isJournal()
    {
        $formats = $this->getFormats();
        foreach ($formats as $format) {
            if (stripos($format, 'Journal') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Override so that we can translate VuFind formats to proper Refworks RT values.
     * @param unknown_type $format
     */
    public function getExport($exportFormat)
    {
        global $interface;
        global $configArray;
        if (strtolower($exportFormat) == 'endnoteweb') {           
            $this->driver = $this;
            $this->recordURL = $configArray['Site']['url'] . '/Record/' . $this->getUniqueId();
            
            // multiple formats causes Zotero to create blank records, so pick the best match
            $formats = $this->getFormats();
            if (is_array($formats) && count($formats) > 1) {
                $risFormats = array();
                foreach ($formats as $format) {
                    switch (strtolower($format)) {
                        case "book";
                            $risFormats[] = "BOOK";
                            break;
                        case "journal":
                        case "article":
                            $risFormats[] = "JOUR";
                            break;
                        case "thesis":
                            $risFormats[] = "THES";
                            break;
                        default:
                            $risFormats[] = "GEN";
                            break;
                    }
                }
                $risFormats = array_unique($risFormats);
                $this->overrideFormats = array($risFormats[0]);
            }
            
            include('export-endnoteweb.phtml');
            return null;
        }
        return parent::getExport($exportFormat);
    }
    
    /**
     * support method for the export-endnoteweb.phtml template
     */
    private function tryMethod($method) 
    {
        return method_exists($this, $method) ? $this->$method() : null;
    }

    public function getEdition()
    {
        return $this->getFirstFieldValue('250', 'ab');
    }
    
    public function getEditions()
    {
        return null;
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
        global $interface;

        // call parent to setup common template variables
        $template = parent::getSearchResult();

        // assign the full_title (with 245|n and 245|p) to result title
        $interface->assign('summTitle', $this->fields['title_full']);

        // assign the secondary authors
        $interface->assign('summSecondaryAuthors', $this->getSecondaryAuthors());

        // assign the Uniform Title
        $interface->assign('summUniformTitles', $this->getUniformTitles());
        
        // NEW: The following is to support simplified search result
        $this->assignBriefDetails();
        
        // assign openurls for Journals 
        $interface->assign('summJournalOpenURLs', $this->getJournalOpenURLs());

        return $template;
    }
    
    private function assignBriefDetails() {
        global $interface;
        
        // assign highlighted title info
        $highlightedTitleInfo = $this->getHighlightedTitle();
        $interface->assign('yorkHighlightedTitleInfo', $highlightedTitleInfo);
        
        // assign normal title info
        $titleInfo = $this->fields['title_full'];
        $interface->assign('yorkTitleInfo', $titleInfo);
        
        // assign publication info
        // display 264 if present, fallback to 260 if not
        // per cataloguing committee request sept. 20, 2016
        $pubinfo = $this->getFirstFieldValue('264');
        if (!$pubinfo) {
            $pubinfo = $this->getFirstFieldValue('260');
        }
        $interface->assign('yorkPublicationInfo', $pubinfo);
        
        // assign author info
        $authorInfo = trim($this->getFirstFieldValue('245', 'c'));
        if (empty($authorInfo)) {
            $authorInfo = trim($this->getFirstFieldValue('100', 'abcd'));
        }
        $interface->assign('yorkAuthorInfo', $authorInfo);
        
        $interface->assign('yorkTitleWithoutMedium', $this->getTitleWithoutMedium());
        
        $interface->assign('yorkSummary', $this->getSummary());
    }
    
    private function getTitleWithoutMedium()
    {
        $title = '';
        $f245 = $this->marcRecord->getField('245');
        if ($f245) {
            $subFields = $f245->getSubfields();
            foreach ($subFields as $sf) {
                if ($sf->getCode() == 'h') {
                    if (strpos($sf->getData(), ':') !== false ) {
                        $title .= ': ';
                    }
                } else {
                    $title .= $sf->getData() . ' ';
                }
            }
        }
        return $title;
    }
    
    public function getListEntry($user, $listId = null, $allowEdit = true)
    {
        global $interface;
        
        // call parent to setup common template variables
        $template = parent::getListEntry($user, $listId, $allowEdit);
        
        // assign normal title info
        $listTitleInfo = $this->fields['title_full'];
        if (!empty($listTitleInfo)) {
            list($listTitleInfo, $responsibility) = explode('/', $listTitleInfo);
            $interface->assign('listTitleInfo', $listTitleInfo);
        }
        
        // assign publication info
        $interface->assign('listPublicationInfo', $this->getFirstFieldValue('260'));
        
        // assign author info
        $listAuthorInfo = trim($this->getFirstFieldValue('245', 'c'));
        if (empty($listAuthorInfo)) {
            $listAuthorInfo = trim($this->getFirstFieldValue('100', 'abcd'));
        }
        $interface->assign('listAuthorInfo', $listAuthorInfo);
        
        // asssign openurls for Journals 
        $interface->assign('listJournalOpenURLs', $this->getJournalOpenURLs());
        
        $interface->assign('listCOinS', $this->getOpenURL());
        
        $interface->assign('yorkSummary', $this->getSummary());
        
        return $template;
    }
    
    public function getHoldings($patron = false)
    {
        global $interface;

        $interface->assign('holdingsJournalOpenURLs', $this->getJournalOpenURLs());
        
        return parent::getHoldings($patron);
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

        // override coreContributors and coreCorporateAuthor
        $interface->assign('coreMainAuthorFuller', $this->getFirstFieldValue('100', 'q'));
        $interface->assign('coreContributors', $this->getOtherAuthors());
        $interface->assign('coreCorporateAuthor', $this->getCorporateAuthors());

        // assign a whole whack of additional metadata to display as "core" metadata
        // we use the prefix "york" so we can tell these variables from the ones
        // assigned by the generic VuFind record drivers
        $interface->assign('fullTitle', $this->fields['title_full']);
        $interface->assign('yorkFullTitle', $this->getFirstFieldValue('245'));
        $interface->assign('yorkMediumDesignation', $this->getFirstFieldValue('245', 'h'));
        $interface->assign('yorkVaryingFormsOfTitle', $this->getVaryingFormsOfTitle());
        $interface->assign('yorkUniformTitles', $this->getUniformTitles());


        $interface->assign('yorkPublisherNumbers', $this->getFieldValues('028'));
        $interface->assign('yorkMeetingNames', $this->getMeetingNames());
        $interface->assign('yorkRelatedURLs', $this->getRelatedURLs());
        $interface->assign('yorkPhysicalDescription', $this->getFieldValues('300'));
        $interface->assign('yorkWithNote', $this->getFieldValues('501'));
        $interface->assign('yorkDissertationNote', $this->getFieldValues('502'));
        $interface->assign('yorkPerformerNote', $this->getFieldValues('511'));
        $interface->assign('yorkNumberingNote', $this->getFieldValues('515'));
        $interface->assign('yorkFileNote', $this->getFieldValues('516'));
        $interface->assign('yorkDatePlaceEventNote', $this->getFieldValues('518'));
        $interface->assign('yorkSupplementNote', $this->getFieldValues('525'));
        $interface->assign('yorkAlternativeNote', $this->getFieldValues('530'));
        $interface->assign('yorkReproductionNote', $this->getFieldValues('533'));
        $interface->assign('yorkOriginalVersionNote', $this->getFieldValues('534'));
        $interface->assign('yorkTermsGoverningUseReproductionNote', $this->getFieldValues('540'));
        $interface->assign('yorkBiographicalSketch', $this->getFieldValues('545'));
        $interface->assign('yorkLanguageNotes', $this->getFieldValues('546'));
        $interface->assign('yorkFormerTitleNote', $this->getFieldValues('547'));
        $interface->assign('yorkIssuingBodyNote', $this->getFieldValues('550'));
        $interface->assign('yorkOwnershipHistoryNote', $this->getFieldValues('561'));
        $interface->assign('yorkAccumulation', $this->getFieldValues('584'));
        $interface->assign('yorkSourceDescription', $this->getFieldValues('588'));
        $interface->assign('yorkLocal590Note', $this->getFieldValues('590'));
        $interface->assign('yorkLocal593Note', $this->getFieldValues('593'));
        $interface->assign('yorkLocal599Note', $this->getFieldValues('599', 'bcdefgh'));

        $interface->assign('yorkAddedTitles', $this->getFieldValues('740'));
        $interface->assign('yorkHoldingInstitution', $this->getFieldValues('850'));

        //April 2012, Includes RDA Fields
        $interface->assign('yorkCountryOfProducingEntity', $this->getFieldValues('257', 'a268'));
        $interface->assign('yorkProdPubDistManufactureCopyrightNotice', $this->getProdPubDistManufactureCopyrightNotices());
        // do NOT display 336,337,338 as per cataloguing commmittee request Sept. 20, 2016
        //$interface->assign('yorkContentType', $this->getFieldValues('336', 'ab368'));
        //$interface->assign('yorkMediaType', $this->getFieldValues('337', 'ab368'));
        //$interface->assign('yorkCarrierType', $this->getFieldValues('338', 'ab368'));
        $interface->assign('yorkPhysicalMedium', $this->getFieldValues('340', 'abcdefhijkmno02368'));
        $interface->assign('yorkSoundCharacteristics', $this->getFieldValues('344', 'abcdefgh02368'));
        $interface->assign('yorkProjectionCharacteristicsOfMovingImage', $this->getFieldValues('345', 'ab02368'));
        $interface->assign('yorkVideoCharacteristics', $this->getFieldValues('346', 'ab02368'));
        $interface->assign('yorkDigitalFileCharacteristics', $this->getFieldValues('347', 'abcdef02368'));
        $interface->assign('yorkAssociatedLanguage', $this->getFieldValues('377', 'al268'));
        $interface->assign('yorkFormOfWork', $this->getFieldValues('380', 'a0268'));
        $interface->assign('yorkOtherDistinguishingCharacteristics', $this->getFieldValues('381', 'auv0268'));
        $interface->assign('yorkMediumOfPerformance', $this->getFieldValues('382', 'a0268'));
        $interface->assign('yorkNumbericalDesignationOfMusicalWork', $this->getFieldValues('383', 'abcde268'));
        $interface->assign('yorkKey384', $this->getFieldValues('384', 'a8'));
        $interface->assign('yorkAudienceNote', $this->getFieldValues('521', 'ab368'));

        $interface->assign('yorkManufacturedDetails', $this->getFieldValues('260', 'efg'));

        // assign additional variables if this record is a Fond
        if ($this->isFond()) {
            $interface->assign('yorkOutsideDates', $this->getFieldValues('260', 'c'));
            $interface->assign('yorkAssociatedMaterials', $this->getFieldValues('544', 'e'));
        }

        // Look in 710 tags for "xxxx - York University."
        // then extract the "xxxx" part and check to see
        // if there is a mobile ebooks research guide
        $f710 = $this->getFieldValues('710', 'a');
        foreach ($f710 as $f) {
            $index = strpos($f, ' - York University.');
            if ($index !== false) {
                $collection = substr($f, 0, $index);
                if (isset($this->researchGuides['MobileEBooks'][$collection])) {
                    $guide = array("collection"=>$collection, "url"=>$this->researchGuides['MobileEBooks'][$collection]);
                    $interface->assign('yorkMobileEBookGuide', $guide);
                }
            }
        }
        
        $this->assignBriefDetails();
        $interface->assign('coreCOinS', $this->getOpenURL());
        return $template;
    }

    /**
     * Get the "Uniform Title".
     * @access protected
     * @return array
     */
    protected function getUniformTitles() {
        $results = array();
         
        // get the primary author
        $author = $this->getPrimaryAuthor();
         
        // process the 240
        $fields = $this->marcRecord->getFields('240');
        foreach($fields as $field) {
            $s = $this->getAllSubFields($field);
            if ($s) {
                $results[] = trim($author . ' ' . $s);
            }
        }

        // process the 730
        $fields = $this->marcRecord->getFields('730');
        foreach($fields as $field) {
            $s = $this->getAllSubFields($field);
            if ($s) {
                $results[] = trim($author . ' ' . $s);
            }
        }

        // process the 130
        $fields = $this->marcRecord->getFields('130');
        foreach($fields as $field) {
            $s = $this->getAllSubFields($field);
            if ($s) {
                $results[] = trim($author . ' ' . $s);
            }
        }
         
        return $results;
    }

    /**
     * Return an associative array of URLs associated with this record (key = URL,
     * value = description).
     *
     * @return array
     * @access protected
     */
    protected function getURLs()
    {
        global $configArray;
        $uselessNotes = array(
                'Available on the Internet. MODE OF ACCESS via web browser by entering the following URL',
                'An electronic book accessible through the World Wide Web; click for information',
                'An electronic book accessible through the World Wide Web; click to view',
                'Click here for full text options',
                'Click to view',
                'Electronic music accessible through the World Wide Web; click for information',
                'Full text online',
                'Access through the membership directory in the faculty directory section',
                'Restricted to Springer LINK subscribers',
                'Connect to this resource online',
                'Access restricted to subscribers',
                'Connect to free full text at publisher site.',
                'Connect to Internet resource',
                'Inhaltsverzeichnis',
                'Bibliographic record display'
        );
        $urls = array();
        $mulerHost = $configArray['MULER']['host'];
        foreach ($this->fulltextURLFields as $field) {
            $subu = $field->getSubfield('u');
            if (!$subu) {
                $subu = $field->getSubfield('a');
            }
            if ($subu) {
                $u = $subu->getData();
                if ($mulerHost) {
                    $u = str_replace('www.library.yorku.ca', $mulerHost, $u);
                }
                $notes = trim($this->getAllSubFields($field, '3z'), ' :()[]"\'\.');
                // if the notes contain "useless" information, we should remove it
                foreach ($uselessNotes as $useless) {
                    $notes = str_replace($useless, '', $notes);
                }
                $urls[$u] = trim($notes);
            }
        }
        return $urls;
    }

    /**
     * Get all related URLs. eg: Table of Contents etc...
     *
     *
     * @access  protected
     * @return  array
     */
    protected function getRelatedURLs()
    {
        $urls = array();
        foreach ($this->relatedURLFields as $field) {
            if ($subu = $field->getSubfield('u')) {
                $u = $subu->getData();
                $urls[$u] = trim($this->getAllSubFields($field, '3yz'), ' :()[]"\'');
            }
        }
        return $urls;
    }

    /**
     * Separate all the 856 fields into full text and supplemental URLs.
     * @access  private
     */
    private function process856Fields()
    {
        $this->relatedURLFields = array();
        $this->fulltextURLFields = array();
        $fields = $this->marcRecord->getFields('856');
        foreach ($fields as $field) {
            $subu = $field->getSubfield('u');
            if ($subu) {
                $u = $subu->getData();
                if (stripos($u, 'http://www.library.yorku.ca/images/erc/') !== false) {
                    // ERC manipulative photo, take it an move on
                    $this->relatedURLFields[] = $field;
                    continue;
                } else if (stripos($u, 'loc.gov') !== false) {
                    // Library of Congress URL, definitely NOT full text
                    $this->relatedURLFields[] = $field;
                    continue;
                }
            }

            // check second indicator for full text
            $ind = trim($field->getIndicator(2));
            if ($ind == '0') {
                $this->fulltextURLFields[] = $field;
                continue;
            }

            // check second indicator for "Related Resource"
            if ($ind == '2') {
                $this->relatedURLFields[] = $field;
                continue;
            }

            // check content of |3 |y and |z for presence of "table of content", etc..
            $s = $this->getAllSubFields($field, '3yz');
            if (stripos($s, 'table of contents') !== false
                    || stripos($s, 'abstract') !== false
                    || stripos($s, 'description') !== false
                    || stripos($s, 'sample text') !== false
                    || stripos($s, 'View cover art') !== false
            ) {
                $this->relatedURLFields[] = $field;
                continue;
            }

            // none of the above, assume full text
            $this->fulltextURLFields[] = $field;
        }
    }

    /**
     * Get the "Varying Forms of Title"
     * @access protected
     * @return array of array("form"=>"Form of title", "title"=>"title string")
     */
    protected function getVaryingFormsOfTitle()
    {
        $forms = array(
                ' ' => 'Variant Title', '0' => 'Portion of Title', '1' => 'Parallel Title',
                '2' => 'Distinctive Title', '4' => 'Cover Title', '5' => 'Added Title Page',
                '6'=> 'Caption Title', '7' => 'Running Title', '8' => 'Spine Title'
        );
        $titles = array();
        $fields = $this->marcRecord->getFields('246');
        foreach($fields as $field) {
            $form = '';
            $title = $this->getAllSubFields($field);
            $ind2 = $field->getIndicator(2);
            $ind2 == ($ind2 == null) ? ' ' : $ind2;
            $form = isset($forms[$ind2]) ? $forms[$ind2] : 'Other Title';
            $titles[] = array('form'=>$form, 'title'=>$title);
        }
        return $titles;
    }

    /**
     * Get the corporate authors (110ab4 and 710abcd4)
     *
     * @access protected
     * @return array of associative array (search=>display)
     */
    protected function getCorporateAuthors()
    {
        $values = array();
        $fields = $this->marcRecord->getFields('110');
        foreach ($fields as $field) {
            $display = $this->getAllSubFields($field, 'ab4');
            $search = $this->getAllSubFields($field, 'ab');
            $values[$search] = $display;
        }
        $fields = $this->marcRecord->getFields('710');
        foreach ($fields as $field) {
            $display = $this->getAllSubFields($field, 'abcde4');
            $search = $this->getAllSubFields($field, 'abcd');
            $values[$search] = $display;
        }
        return $values;
    }

    /**
     * Get the other authors (700)
     *
     * @access  protected
     * @return  array of associative array(search=>display)
     */
    protected function getOtherAuthors()
    {
        $values = array();
        $fields = $this->marcRecord->getFields('700');
        foreach ($fields as $field) {
            $display = $this->getAllSubFields($field);
            $search = $this->getAllSubFields($field, 'abcd');
            $values[$search] = $display;
        }
        return $values;
    }

    /**
     * Get the Meeting Name (111ab and 711ab)
     *
     * @access protected
     * @return array of associative array (search=>display)
     */
    protected function getMeetingNames()
    {
        $values = array();
        $fields = $this->marcRecord->getFields('111');
        foreach ($fields as $field) {
            $display = $this->getAllSubFields($field, 'ab');
            $search = $this->getAllSubFields($field, 'ab');
            $values[$search] = $display;
        }
        $fields = $this->marcRecord->getFields('711');
        foreach ($fields as $field) {
            $display = $this->getAllSubFields($field, 'ab');
            $search = $this->getAllSubFields($field, 'ab');
            $values[$search] = $display;
        }
        return $values;
    }

    /**
     * Return all subfield data concatenated into 1 string.
     * @param $marcField The data field to look at
     * @param $codes      The subfield code to look at, empty means any.
     * @param $separator The separator used to concatenate the data, default is SPACE.
     * @access  protected
     * @return String
     */
    protected function getAllSubFields($marcField, $codes='', $separator=' ') {
        $value = '';
        if ($marcField) {
            $data = array();
            $subfields = $marcField->getSubfields();
            foreach ($subfields as $subfield) {
                if (empty($codes) || strpos($codes, $subfield->getCode()) !== FALSE) {
                    $data[] = $subfield->getData();
                }
            }
            $value = trim(join($separator, $data));
        }
        return $value;
    }

    /**
     * Get all values of matching MARC field.
     * @param $number The marc field number to get
     * @param $codes      The subfield code to look at, empty means any.
     * @param $separator The separator used to concatenate the data, default is SPACE.
     * @return array of strings
     */
    protected function getFieldValues($number, $codes='', $separator=' ')
    {
        $values = array();
        $fields = $this->marcRecord->getFields($number);
        foreach ($fields as $field) {
            $values[] = $this->getAllSubFields($field, $codes, $separator);
        }
        return $values;
    }

    /**
     * Get first value of the matching MARC field.
     * @param $number The marc field number to get
     * @param $codes      The subfield code to look at, empty means any.
     * @param $separator The separator used to concatenate the data, default is SPACE.
     * @return array of strings
     */
    protected function getFirstFieldValue($number, $codes='', $separator=' ')
    {
        $field = $this->marcRecord->getField($number);
        return $this->getAllSubFields($field, $codes, $separator);
    }

    /**
     * Get a highlighted title string, if available.
     *
     * @return string
     * @access protected
     */
    protected function getHighlightedTitle()
    {
        // Don't check for highlighted values if highlighting is disabled:
        if (!$this->highlight) {
            return '';
        }
        $title = (isset($this->fields['_highlighting']['title_full'][0]))
        ? $this->fields['_highlighting']['title_full'][0] : '';
        if (strlen($title) < strlen($this->fields['title_full'])) {
            return '';
        }
        return $title;
    }

    /**
     * Get the "RDA 264 Prod Pub Dist Manufacture Copyright-Notice"
     * @access protected
     * @return array of array("form"=>"Function of entity", "value"=>"value string")
     */
    protected function getProdPubDistManufactureCopyrightNotices()
    {
        $forms = array(
                '0' => 'Production', '1' => 'Publication',
                '2' => 'Distribution', '3' => 'Manufacture', '4' => 'Copyright Notice Date'
        );
        $values = array();
        $fields = $this->marcRecord->getFields('264');
        foreach($fields as $field) {
            $form = '';
            $value = $this->getAllSubFields($field, 'abc368');
            $ind2 = $field->getIndicator(2);
            $ind2 == ($ind2 == null) ? ' ' : $ind2;
            $form = isset($forms[$ind2]) ? $forms[$ind2] : '264';
            $values[] = array('form'=>$form, 'value'=>$value);
        }
        return $values;
    }
    
    protected function getThumbnail($size = 'small')
    {
        global $configArray;
        
        // check if there is a link to cover art in 856
        $coverArtURL = null;
        foreach ($this->relatedURLFields as $field) {
            // check content of |3 |y and |z for presence of "View cover art", etc..
            $s = $this->getAllSubFields($field, '3yz');
            if (stripos($s, 'View cover art') !== false) {
               $subu = $field->getSubfield('u');
               if ($subu) {
                   $coverArtURL = $subu->getData();
                   break;
               }
            }
        }
        
        $url = parent::getThumbnail($size);
        if (!$url) {
            $url = $configArray['Site']['url'] . '/bookcover.php?size=' . urlencode($size);
        }
    	$url .= '&id=' . urlencode($this->getUniqueID());
    	if ($coverArtURL) {
    	    $url .= '&url=' . urlencode($coverArtURL);
    	}
    	return $url;
    }
    
    public function isFond() 
    {
        $fields = $this->marcRecord->getFields('856');
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $sf = $field->getSubField('u');
                if ($sf && stripos($sf->getData(), '.library.yorku.ca/fonds/') !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function getJournalOpenURLs()
    {
        $results = array();
        $issns = $this->getISSNs();
        foreach ($issns as $issn) {
            $issn = substr(preg_replace('/[^0-9x]/i', '', $issn), 0, 8);
            if (!empty($issn)) {
                $results[$issn] = $this->getJournalOpenURL($issn);
            }
        }
        return $results;
    }
    
    private function getJournalOpenURL($issn)
    {
        global $configArray;

        // Start an array of OpenURL parameters:
        $params = array(
            'ctx_ver' => 'Z39.88-2004',
            'rft.issn' => $issn,
            'rft.format' => 'Journal',
            'sfx.ignore_date_threshold' => 1
        );

        // Assemble the URL:
        $parts = array();
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . urlencode($value);
        }
        return implode('&', $parts);
    }
    
    /**
     * Get all subject headings associated with this record.  Each heading is
     * returned as an array of chunks, increasing from least specific to most
     * specific.
     *
     * @return array
     * @access protected
     */
    protected function getAllSubjectHeadings()
    {
        // These are the fields that may contain subject headings:
        $fields = array('600', '610', '630', '650', '651', '655');

        // This is all the collected data:
        $retval = array();

        // Try each MARC field one at a time:
        foreach ($fields as $field) {
            // Do we have any results for the current field?  If not, try the next.
            $results = $this->marcRecord->getFields($field);
            if (!$results) {
                continue;
            }

            // If we got here, we found results -- let's loop through them.
            foreach ($results as $result) {
                // skip 650/651 with subfield 2 == fast
                // per cataloguing committee request sept. 20, 2016
                if ($field == '650' || $field == '651') {
                    $sf = $result->getSubfield('2');
                    if ($sf) {
                        $data = $sf->getData();
                        if (stripos($data, 'fast') !== false) {
                            continue;
                        }
                    }
                }
                
                // Start an array for holding the chunks of the current heading:
                $current = array();

                // Get all the chunks and collect them together:
                $subfields = $result->getSubfields();
                if ($subfields) {
                    foreach ($subfields as $subfield) {
                        // Numeric subfields are for control purposes and should not
                        // be displayed:
                        if (!is_numeric($subfield->getCode())) {
                            $current[] = $subfield->getData();
                        }
                    }
                    // If we found at least one chunk, add a heading to our result:
                    if (!empty($current)) {
                        $fieldName = 'marc' . $field;
                        $retval[] = array($fieldName, $current);
                    }
                }
            }
        }

        // Send back everything we collected:
        return $retval;
    }
}
?>