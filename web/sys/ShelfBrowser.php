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

require_once 'sys/Proxy_Request.php';

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
    
    // max number of bib records
    private $maxRecords = 10;
    
    public function __construct()
    {
        global $configArray;

        $this->host = $configArray['Index']['url'] . '/shelf';

        $this->client = new Proxy_Request(null, array('useBrackets' => false));
        
        $this->biblio = ConnectionManager::connectToIndex();
    }
    
    public function guessMinMaxOrder($recordId)
    {
        // find the order numbers of the given record
        $options = array('q' => "bib_id:$recordId", 'sort' => 'order asc', 'rows' => $this->maxItemsPerBib);
        $result = $this->_select('GET', $options);
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
        
        $options = array('q' => "order:[$from TO $to]", 'sort' => 'order asc', 'rows' => $this->maxItemsPerBib);
        $result = $this->_select('GET', $options);
        
        $records = array();
        $count = 0;
        foreach ($result['response']['docs'] as $doc) {
            $record = $this->biblio->getRecord($doc['bib_id']);
            if ($record) {
                $records[] = array('order' => $doc['order'], 'record' => $record);
                if ($count++ >= $this->maxRecords) {
                    break;
                }
            }
        }

        return $records;
    }
    
    public function browseRight($order) {
        $from = $order + 1;
        $to = $order + $this->maxItemsPerBib;
        $options = array('q' => "order:[$from TO $to]", 'sort' => 'order asc', 'rows' => $this->maxItemsPerBib);
        $result = $this->_select('GET', $options);
        
        $records = array();
        $count = 0;
        foreach ($result['response']['docs'] as $doc) {
            $record = $this->biblio->getRecord($doc['bib_id']);
            if ($record) {
                $records[] = array('order' => $doc['order'], 'record' => $record);
                if ($count++ >= $this->maxRecords) {
                    break;
                }
            }
        }

        return $records;
    }
    
    /**
     * Submit REST Request to read data
     *
     * @param string $method          HTTP Method to use: GET, POST,
     * @param array  $params          Array of parameters for the request
     * @param bool   $returnSolrError Should we fail outright on syntax error
     * (false) or treat it as an empty result set with an error key set (true)?
     *
     * @return array                  The Solr response (or a PEAR error)
     * @access private
     */
    private function _select($method = HTTP_REQUEST_METHOD_GET, $params = array(),
        $returnSolrError = false
    ) {
        global $configArray, $memcache, $logger;
        
        $this->client->setMethod($method);
        $this->client->setURL($this->host . "/select/");

        $params['wt'] = 'json';
        $params['json.nl'] = 'arrarr';        
        $params['fl'] = '*,score';

        // Build query string for use with GET or POST:
        $query = array();
        if ($params) {
            foreach ($params as $function => $value) {
                if ($function != '') {
                    if (is_array($value)) {
                        foreach ($value as $additional) {
                            $additional = urlencode($additional);
                            $query[] = "$function=$additional";
                        }
                    } else {
                        $value = urlencode($value);
                        $query[] = "$function=$value";
                    }
                }
            }
        }

        $queryString = implode('&', $query);
        
        $logger->log("SolrShelf queryString: $queryString", PEAR_LOG_ERROR);
        
        $cacheKey = false;
        if ($memcache) {
            $cacheKey = md5(trim($queryString));
            $result = $memcache->get($cacheKey);
            if ($result !== false) {
                $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
                return $result;
            }
        }

        if ($method == 'GET') {
            $this->client->addRawQueryString($queryString);
        } elseif ($method == 'POST') {
            $this->client->setBody($queryString);
        }

        // Send Request
        $result = $this->client->sendRequest();
        $this->client->clearPostData();

        if (!PEAR::isError($result)) {
            $result = $this->_process(
                $this->client->getResponseBody(), $returnSolrError
            );
            if ($cacheKey !== false && !isset($result['error']) && isset($result['response']['docs'])
                    && $memcache->set($cacheKey, $result, 0, $configArray['Caching']['memcache_expiry']) !== false) {
                $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
            }
            return $result;
        } else {
            return $result;
        }
    }
    
    /**
     * Perform normalization and analysis of Solr return value.
     *
     * @param array $result          The raw response from Solr
     * @param bool  $returnSolrError Should we fail outright on syntax error
     * (false) or treat it as an empty result set with an error key set (true)?
     *
     * @return array                 The processed response from Solr
     * @access private
     */
    private function _process($result, $returnSolrError = false)
    {
        // Catch errors from SOLR
        if (substr(trim($result), 0, 2) == '<h') {
            $errorMsg = substr($result, strpos($result, '<pre>'));
            $errorMsg = substr(
                $errorMsg, strlen('<pre>'), strpos($result, "</pre>")
            );
            if ($returnSolrError) {
                return array(
                    'response' => array('numfound' => 0, 'docs' => array()),
                    'error' => $errorMsg
                );
            } else {
                $msg = 'Unable to process query<br />Solr Returned: ' . $errorMsg;
                PEAR::raiseError(new PEAR_Error($msg));
            }
        }
        $result = json_decode($result, true);

        return $result;
    }
}
?>
