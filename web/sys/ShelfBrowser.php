<?php
/**
 * Shelf Browsing Class
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
 * @package  Support_Classes
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */

/**
 * Shelf Browsing Class
 *
 *
 * @category VuFind
 * @package  Support_Classes
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/system_classes#index_interface Wiki
 */
class ShelfBrowser
{   
    // assuming each bib record may have at most this number 
    // of items (callnumbers) attached to it.
    private $maxItemsPerBib = 2000;
    
    // max number of items on each side of the browse
    private $maxItemsPerSide = 20;
    
    public function __construct()
    {
        global $configArray;
        
        $this->maxItemsPerBib = isset($configArray['ShelfBrowse']['maxItemsPerBib']) 
            ? $configArray['ShelfBrowse']['maxItemsPerBib'] : 100;
        $this->maxItemsPerSide = isset($configArray['ShelfBrowse']['maxItemsPerSide']) 
            ? $configArray['ShelfBrowse']['maxItemsPerSide'] : 20;

        $this->biblio = ConnectionManager::connectToIndex();
        $this->shelf = ConnectionManager::connectToIndex('Solr', 'shelf');
    }
    
    public function guessMinMaxOrder($recordId)
    {
        // find the min/max order numbers of the given record
        $query = "bib_id:$recordId";
        
        $result = $this->shelf->search($query, null, null, 0, 1, null, '', null, 'order asc');
        $min = $result['response']['numFound'] > 0 ? $result['response']['docs'][0]['order'] : -1;
        
        $result = $this->shelf->search($query, null, null, 0, 1, null, '', null, 'order desc');
        $max = $result['response']['numFound'] > 0 ? $result['response']['docs'][0]['order'] : -1;
        
        return array($min, $max);
    }

    public function browseLeft($order, $inclusive=false) {
        if ($order < 0) {
            return array();
        }
        $from = $order - $this->maxItemsPerBib * $this->maxItemsPerSide;
        if ($from < 0) {
            $from = 0;   
        }
        $to = $inclusive ? $order : $order - 1;
        return $this->browse($from, $to, 'desc');
    }
    
    public function browseRight($order, $inclusive=false) {
        if ($order < 0) {
            return array();
        }
        $from = $inclusive ? $order : $order + 1;
        $to = $order + $this->maxItemsPerBib * $this->maxItemsPerSide;
        return $this->browse($from, $to);
    }
    
    public function getHTMLItems($items) 
    {
        global $interface;
        
        $htmlItems = array();
        foreach ($items as $item) {
            $recordDriver = RecordDriverFactory::initRecordDriver($item['record']);
            $recordDriver->getSearchResult();
            $interface->assign('shelfOrder', $item['order']);
            $interface->assign('callnum', $item['callnum']);
            $interface->assign('lazy', true);
            $html = $interface->fetch('RecordDrivers/Index/browse-shelf-item.tpl');
            if (strlen(trim($html)) > 0) {
                $htmlItems[] = $html;    
            }
        }
        return $htmlItems;
    }
    
    private function browse($from, $to, $dir='asc') 
    {
        $query = "order:[$from TO $to]";
        $sort = "order $dir";
        $limit = $this->maxItemsPerSide;
        $fields = '*';
        $method = HTTP_REQUEST_METHOD_POST;
        $returnSolrError = false;
        $options = array(
            'group' => 'true',
            'group.field' => 'bib_id',
            'group.limit' => '1',
            'group.sort' => $sort
        );
        $result = $this->shelf->search(
            $query, null, null, 0, $limit, null, '', null, $sort, $fields,
            $method, $returnSolrError, $options
        );
        
        // extract the docs
        $docs = array();
        foreach ($result['grouped']['bib_id']['groups'] as $g) {
            if (isset($g['doclist']['docs'][0])) {
                $docs[] = $g['doclist']['docs'][0];
            }
        }

        if ($dir == 'desc') {
            // need to resort in ascending order
            usort($docs, function($a, $b) {
                if ($a['order'] == $b['order']) {
                    return 0;
                }
                return ($a['order'] < $b['order']) ? -1 : 1;
            });
        }
        return $this->merge($docs);
    }
    
    /**
     * De-dup and match shelf browsing records with biblio records. 
     */
    private function merge($docs) 
    {
        $seen = array();
        foreach ($docs as $doc) {
            if (!isset($seen[$doc['bib_id']])) {
                $seen[$doc['bib_id']] = array(
                    'order' => $doc['order'],
                    'callnum' => $doc['callnum'],
                    'shelving_key' => $doc['shelving_key'],
                    'bib_id' => $doc['bib_id']
                );
            }
        }
        $ids = array_keys($seen);
        $query = 'id:' . implode(' OR id:', $ids);
        $result = $this->biblio->search($query, null, null, 0, count($ids));
        foreach ($result['response']['docs'] as $doc) {
            if (isset($seen[$doc['id']])) {
                $seen[$doc['id']]['record'] = $doc;
            }
        }
        return array_values($seen);
    }
}
?>
