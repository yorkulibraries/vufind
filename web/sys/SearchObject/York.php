<?php
require_once 'sys/SearchObject/Solr.php';

class SearchObject_York extends SearchObject_Solr {
    static $ONLINE_ACCESS_FILTER = 'location_str_mv:"Online Access"';
    
    protected $hasLocationFilter = false;
    protected $hasOnlineAccessFilter = false;
    protected $alphaSortFacets = array();
    
    public function __construct() {
        parent::__construct();
        $this->alphaSortFacets = explode(',', $this->getFacetSetting(
            'Results_Settings', 'alpha_sort_facets'
        ));
    }
    
    /**
     * Override to sort some facets by label.
     */
    public function getFacetList($filter = null, $expandingLinks = false)
    {        
        $facetList = parent::getFacetList($filter, $expandingLinks);
        $cmp = create_function(
            '$a,$b',
            'return strtoupper($a["value"]) <= strtoupper($b["value"]) ? -1 : 1;'
        );
        foreach ($this->alphaSortFacets as $field) {
            if (isset($facetList[$field]['list'])) {
                usort($facetList[$field]['list'], $cmp);
            }
        }
        // make the Online Access item the FIRST one on the Location facet list
        if (isset($facetList['location_str_mv']['list'])) {
            $list = array();
            foreach ($facetList['location_str_mv']['list'] as $item) {
            	if ($item['untranslated'] == 'Online Access') {			
            		array_unshift($list, $item);
            	} else {
            		$list[] = $item;
            	}
            }
            $facetList['location_str_mv']['list'] = $list;
        }
        
        return $facetList;
    }
    
   /**
    * Get a user-friendly string to describe the provided facet field.
    *
    * @param string $field Facet field name.
    *
    * @return string       Human-readable description of field.
    * @access public
    */
    public function getFacetLabel($field)
    {
        return ($field == 'broad_format_str_mv') 
            ? $this->allFacetSettings['Advanced']['broad_format_str_mv']
            : parent::getFacetLabel($field);
    }
    
   /**
    * Add filters to the object based on values found in the $_REQUEST superglobal.
    *
    * @return void
    * @access protected
    */
    protected function initFilters()
    {
        if (isset($_REQUEST['filter'])) {
            $filter = $_REQUEST['filter'];
            if (is_array($_REQUEST['filter'])) {
                $filter = array();
                foreach($_REQUEST['filter'] as $f) {
                    // keep only non-empty filters
                    // otherwise we may end up filtering everything out
                    if (!empty($f)) {
                        // some filter names have changed due to the 
                        // use of Dynamic fields
                        // we need to map old names to new names so 
                        // bookmark'ed searches continue to work
                        $filterNameMap = array(
                            'location'=>'location_str_mv',
                            'author_facet'=>'author_facet_txtF_mv',
                            'broad-format'=>'broad_format_str_mv'
                        );
                        list($filterName, $filterValue) = explode(':', $f);
                        if (isset($filterNameMap[$filterName])) {
                            $filterName = $filterNameMap[$filterName];
                            $f = $filterName . ':' . $filterValue;
                        }
                        $this->hasLocationFilter = (
                            $this->hasLocationFilter
                            || ($filterName == 'location_str_mv')
                        );
                        $this->hasOnlineAccessFilter = (
                            $this->hasOnlineAccessFilter
                            || ($f == self::$ONLINE_ACCESS_FILTER)
                        );
                        $filter[] = $f;
                    }
                }
            }
            if (empty($filter)) {
                unset($_REQUEST['filter']);
            } else {
                $_REQUEST['filter'] = $filter;
            }
        }
        
        // Use the default behavior of the parent class
        parent::initFilters();
        
        // force narrow to "Catalogue" if no Source facet is selected
        if (!isset($this->filterList['source_str'])) {
            $this->addFilter('source_str:"Catalogue"');
        }
    }
    
    /**
     * Get an array of strings to attach to a base URL in order to reproduce the
     * current search.
     *
     * @access  protected
     * @return  array    Array of URL parameters (key=url_encoded_value format)
     */
    protected function getSearchParams()
    {
        global $interface;

        $params = array();
        if ($this->searchType == $this->advancedSearchType) {
            // Advanced search
            for ($i = 0; $i < count($this->searchTerms); $i++) {
                if ($i > 0) {
                    $params[] = "join".$i."=" . urlencode($this->searchTerms[$i]['join']);
                }
                for ($j = 0; $j < count($this->searchTerms[$i]['group']); $j++) {
                    $params[] = "lookfor".$i."[]=" . urlencode($this->searchTerms[$i]['group'][$j]['lookfor']);
                }
                $params[] = "type".$i."[]="    . urlencode($this->searchTerms[$i]['group'][0]['field']);
            }
        } else {
            // Basic search - let the parent take care of it
            $params = parent::getSearchParams();
        }
        return $params;
    }

    /**
     * Initialise the object from the global
     *  search parameters in $_REQUEST.
     *
     * @access  public
     * @return  boolean
     */
    public function init()
    {
        global $module;
        global $action;
        global $configArray;
        global $interface;
        
        // fix lookfor that contains " - "
        if (isset($_REQUEST['lookfor'])) {
            $_REQUEST['lookfor'] = preg_replace('/\s+-\s+/', ' ', $_REQUEST['lookfor']);
        }

        // if we're NOT already in the Search/Reserves action
        // then we need to see if this is a "reserves" search
        // if so, redirect to the Search/Reserves action
        if (!($module == 'Search' && $action == 'Reserves')) {
            if ($_REQUEST['type'] == 'reserve' || $_REQUEST['type'] == 'Reserves') {
                // all we need to do here is redirect to the Search/Reserves action
                $url = $configArray['Site']['url'] . '/Search/Reserves';
                $params = array();
                if (isset($_REQUEST['lookfor']) && !empty($_REQUEST['lookfor'])) {
                    $params[] = 'lookfor=' . urlencode($_REQUEST['lookfor']);
                }
                if (isset($_REQUEST['reserve']) && !empty($_REQUEST['reserve'])) {
                    $params[] = 'reserve=' . urlencode($_REQUEST['reserve']);
                }
                if (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {
                    $params[] = 'page=' . urlencode($_REQUEST['page']);
                }
                if (!empty($params)) {
                    $url = $url . '?' . implode('&', $params);
                }
                header('Location: ' . $url, true, 301);
                // and we're done
                exit;
            }
        }
        
        // assign list of "multiselect" facets to the templates
        $interface->assign('multiSelectFacets', $this->multiSelectFacets);
        
        // Call the standard initialization routine in the parent:
        return parent::init();
    } // End init()

    /**
     * Initialize the object's search settings for an advanced search found in the
     * $_REQUEST superglobal.  Advanced searches have numeric subscripts on the
     * lookfor and type parameters -- this is how they are distinguished from basic
     * searches.
     *
     * @access  protected
     */
    protected function initAdvancedSearch()
    {
        global $interface;

        //********************
        // York Advanced Search logic
        //            'lookfor0[]' 'type0[]'
        //  'join1  'lookfor1[]' 'type1[]'
        //  'join2  'lookfor2[]' 'type2[]'
        $this->searchType = $this->advancedSearchType;
        $groupCount = 0;
        // Loop through each search group
        while (isset($_REQUEST['lookfor'.$groupCount])) {
            $group = array();
            // Loop through each term inside the group
            for ($i = 0; $i < count($_REQUEST['lookfor'.$groupCount]); $i++) {
                // Ignore advanced search fields with no lookup
                if (trim($_REQUEST['lookfor'.$groupCount][$i]) != '') {
                    // Use default fields if not set
                    if (isset($_REQUEST['type'.$groupCount][0]) && $_REQUEST['type'.$groupCount][0] != '') {
                        $type = $_REQUEST['type'.$groupCount][0];
                    } else {
                        $type = $this->defaultIndex;
                    }

                    // Add term to this group
                    $group[] = array(
                        'field'   => $type,
                        'lookfor' => $_REQUEST['lookfor'.$groupCount][$i],
                        'bool'    => 'OR' // all terms/boxes in a group are OR'ed together
                    );
                }
            }

            // default joining operator (for joining groups together) is AND
            $join = null;
            // groups are joined together by boolean operators
            if ($groupCount > 0 && isset($_REQUEST['join'.$groupCount])) {
                // use default operator if bad operator is set
                if (isset($_REQUEST['join'.$groupCount]) && in_array($_REQUEST['join'.$groupCount], array('AND','OR','NOT'))) {
                    $join = $_REQUEST['join'.$groupCount];
                } else {
                    $join = 'AND';
                }
            }

            // Make sure we aren't adding groups that had no terms
            if (count($group) > 0) {
                // Add the completed group to the list
                $this->searchTerms[] = array(
                    'group' => $group,
                    'join'  => $join
                );
            }

            // Increment
            $groupCount++;
        }

        // Finally, if every advanced row was empty, we still treat this as an
        // Advanced Search - vanilla VuFind treats this as a Basic Search
        if (count($this->searchTerms) == 0) {
            // make a empty group
            $group[] = array(
                'field'   => $this->defaultIndex,
                'lookfor' => '',
                'bool'    => 'OR' // all terms/boxes in a group are OR'ed together
            );
            $this->searchTerms[] = array(
                'group'   => $group,
                'join' => 'AND'
            );
        }
    }

    /**
     * Get a human-readable presentation version of the advanced search query
     * stored in the object.  This will not work if $this->searchType is not
     * 'advanced.'
     *
     * @access  protected
     * @return  string
     */
    protected function buildAdvancedDisplayQuery()
    {
        global $interface;

        // Groups and exclusions. This mirrors some logic in Solr.php
        $groups   = array();
        $excludes = array();

        foreach ($this->searchTerms as $search) {
            $thisGroup = array();
            // Process each search group
            foreach ($search['group'] as $group) {
                // Build this group individually as a basic search
                $thisGroup[] = $this->getHumanReadableFieldName($group['field']) .
                    ":{$group['lookfor']}";
            }
            $groups[] = join(" ".$group['bool']." ", $thisGroup);
        }

        $i = 0;
        foreach ($groups as $group) {
            if ($i == 0) {
                $output = '(' . $group . ')';
            } else {
                $output .= ' ' . $this->searchTerms[$i]['join'] . ' (' . $group . ')';
            }
            $i++;
        }
        return $output;
    }
    
    /**
     * Override to indicate whether a filter is multi select (ie: OR'ed).
     */
    public function getFilterList($excludeCheckboxFilters = false)
    {
        $list = parent::getFilterList($excludeCheckboxFilters);
        foreach ($list as $facetLabel => &$filters) {
            foreach ($filters as &$filter) {
                $filter['isMultiSelect'] = in_array($filter['field'], $this->multiSelectFacets);
            }
        }
        return $list;
    }
    
    /**
     * Override to assign more data to the interface.
     */
    public function getResultRecordHTML()
    {
    	global $interface;
    	$ids = array();
    	for ($x = 0; $x < count($this->indexResult['response']['docs']); $x++) {
    		$current = & $this->indexResult['response']['docs'][$x];
    		$ids[] = $current['id'];
    	}
    	$interface->assign('recordIds', $ids);
    	$_SESSION['lastSearchQuery'] = $this->displayQuery();
    	$_SESSION['lastFilterList'] = $this->getFilterList(true);
    	$_SESSION['lastSearchType'] = $this->getSearchType();
    	$_SESSION['lastSearchId'] = $this->getSearchId();
    	return parent::getResultRecordHTML();
    }
    
    /**
     * Override to collect all record IDs into an array and assign it to the interface.
     */
    public function getResultListHTML($user, $listId = null, $allowEdit = true)
    {
    	global $interface;
    	$ids = array();
    	for ($x = 0; $x < count($this->indexResult['response']['docs']); $x++) {
    		$current = & $this->indexResult['response']['docs'][$x];
    		$ids[] = $current['id'];
    	}
    	$interface->assign('recordIds', $ids);
    	return parent::getResultListHTML($user, $listId, $allowEdit);
    }
}
?>
