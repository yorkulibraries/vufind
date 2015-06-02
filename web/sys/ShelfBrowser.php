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
    private $maxItemsPerBib = 100;
        
    public function __construct()
    {
        global $configArray;

        $this->biblio = ConnectionManager::connectToIndex();
        $this->shelf = ConnectionManager::connectToIndex('Solr', 'shelf');
    }
    
    public function guessMinMaxOrder($recordId)
    {
        // find the order numbers of the given record
        $query = "bib_id:$recordId";
        $sort = 'order asc';
        $limit = $this->maxItemsPerBib;
        $result = $this->shelf->search($query, null, null, 0, $limit, null, '', null, $sort);
        
        $min = $max = -1;
        if ($result['response']['numFound'] > 0) {
            $min = $result['response']['docs'][0]['order'];
            $max = $result['response']['docs'][count($result['response']['docs'])-1]['order'];
        }
        return array($min, $max);
    }

    public function browseLeft($order) {
        $from = ($order > $this->maxItemsPerBib) ? $order - $this->maxItemsPerBib : $order;
        $to = ($order > 1) ? $order - 1 : $order;
        return $this->browse($from, $to);
    }
    
    public function browseRight($order) {
        $from = $order + 1;
        $to = $order + $this->maxItemsPerBib;
        return $this->browse($from, $to);
    }
    
    private function browse($from, $to) 
    {
        $query = "order:[$from TO $to]";
        $sort = 'order asc';
        $limit = $this->maxItemsPerBib;
        $result = $this->shelf->search($query, null, null, 0, $limit, null, '', null, $sort);
        
        return $this->merge($result['response']['docs']);
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
