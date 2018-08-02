<?php
/**
 * NewBooks.php contains only the NewBooks class.
 *
 * NewBooks faciliates the retrieval and processing
 * of list of new books from York Library data source.
 *
 * @copyright   Copyright 2013, Vince Chu for Osgoode Hall Law School
 * @license     http://opensource.org/licenses/bsd-license.php
 */

class NewBooks {

    private $startDate;
    private $endDate;
    private $curYear;
    private $bookCount;
    private $newBookCount = 0;
    private $booklist;
    private $isProcessed = false;
    private $googleBooks;

    // Configs
    private $isDebug = false;
    private $errorLogSrc;
    private $runsLogSrc;
    private $yorklibBiblioUri;
    private $googleBooksAPIUri;
    private $googleBooksAPIKey;
    private $yorklibCacheSrc;
    private $googleBooksCacheSrc;
    private $isConfigured = false;

    /**
     * Load the configuration parameters.
     *
     * @param   $config     object of configurations
     */
    private function loadConfig($config) {
        if (!$this->isConfigured) {
            $id = strtotime($this->endDate);
            $this->isDebug             = !!$config->isDebug;
            $this->yorklibBiblioUri    = implode($config->yorklibBiblioUri);
            $this->googleBooksAPIUri   = implode($config->googleBooksAPIUri);
            $this->googleBooksAPIKey   = $config->googleBooksAPIKey;
            $this->errorLogSrc         = $config->errorLogSrc;
            $this->runsLogSrc          = $config->runsLogSrc;
            $this->yorklibCacheSrc     = str_replace('{$id}', $id, $config->yorklibCacheSrc);
            $this->googleBooksCacheSrc = str_replace('{$id}', $id, $config->googleBooksCacheSrc);
            $this->isConfigured        = true;
            if ($this->isDebug) {
                file_put_contents($this->runsLogSrc,
                    "\tRun ID: ".$id."\r\n",
                    FILE_APPEND | LOCK_EX); // Log run ID
            }
        }
    }

    /**
     * Perform client-side URL (cURL) request.
     * Return response data.
     *
     * @param   $uri        of the resource
     * @return              the response data.
     */
    private static function curl($uri) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Retrieve the response data from Google Books API
     * for the given query.
     *
     * @param   $query      the URI to resource requested.
     * @return              the response data or null if error.
     */
    private function getGoogleBook($query) {
        if (isset($this->googleBooks[$query])) { // in memory cache
            return $this->googleBooks[$query];
        }
        $result = json_decode(self::curl($query));
        $this->googleBooks[$query] = $result;
        if (isset($result->error)) {
            $queryMsg = explode('?', $query);
            $queryMsg = $queryMsg[0]."\r\n\t?".implode("\r\n\t&", explode('&', $queryMsg[1]));
            $error = $result->error;
            $message  = date('Y-m-d H:i:s').': ';
            $message .= 'Error: '.$error->message.' ('.$error->code.'): '.$queryMsg."\r\n";
            file_put_contents($this->errorLogSrc, $message, FILE_APPEND | LOCK_EX); // Log error
        }
        return $result;
    }

    /**
     * Retrieve the response data from Google Books API
     * for the given book serial number.
     *
     * @param   $serial     the serial number
     * @param   $format     the serial number format
     * @param   $isbn       search as ISBN-13 or ISBN-10, default: false
     */
    private function getGoogleBookBySerialID($serial, $format, $isbn = false) {
        if ($format === 'isbn' && $isbn) {
            $format .= '_'.strlen($serial);
        }
        $query = str_replace('{$format}', $format, $this->googleBooksAPIUri);
        $query = str_replace('{$apiKey}', $this->googleBooksAPIKey, $query);
        $query = str_replace('{$serial}', $serial, $query);
        $googleBook = $this->getGoogleBook($query);
        if (is_null($googleBook) || !isset($googleBook->items)) {
            if ($format === 'isbn') {
                return $this->getGoogleBookBySerialID($serial, $format, true);
            }
        }
        return $googleBook;
    }

    /**
     * Retrieve the URL to the book cover thumbnail
     * for the given book serial number.
     *
     * @param   $serials    array of possible serial numbers
     * @param   $format     serial number format type
     * @return              the URL of the thumbnail or null if not found.
     */
    private function getBookCover($serials, $format) {
        if (is_array($serials)) {
            foreach($serials as $serial) {
                $serial = explode(' ', $serial);
                $googleBook = $this->getGoogleBookBySerialID($serial[0], $format);
                if (!is_null($googleBook)
                        && isset($googleBook->items)
                        && isset($googleBook->items[0]->volumeInfo->imageLinks)) {
                    $imageLinks = $googleBook->items[0]->volumeInfo->imageLinks;
                    if (!empty($imageLinks)) { // found
                        return str_replace("&edge=curl", "", $imageLinks->thumbnail);
                    }
                }
            } // foreach
        }
        return null;
    } // getBookCover
    
    /**
     * Retrieve a Google Books cover using the callback api
     *
     * @return    the URL of the thumbnail or null if not found.
     */
    function getBookCoverV2($serial, $format)
    {
        $serial = preg_replace('/[^0-9x]/i', '', $serial);
        
        // Construct the request URL:
        $url = 'http://books.google.com/books?jscmd=viewapi&' .
               'bibkeys=ISBN:' . implode(',', $serial) . '&callback=addTheCover';
        
        // make the request to google books
        $result = self::curl($url);

        // extract the useful JSON from the response:
        $count = preg_match('/^[^{]*({.*})[^}]*$/', $result, $matches);
        if ($count < 1) {
            return null;
        }
        $json = $matches[1];

        // convert \x26 or \u0026 to &
        $json = str_replace(array("\\x26", "\\u0026"), "&", $json);

        // decode the object:
        $json = json_decode($json, true);

        // convert a flat object to an array -- probably unnecessary, but
        // retained just in case the response format changes:
        if (isset($json['thumbnail_url'])) {
            $json = array($json);
        }

        // find the first thumbnail URL
        foreach ($json as $current) {
            if (isset($current['thumbnail_url'])) {
                $thumbnail_url = str_replace('zoom=5', 'zoom=1', $current['thumbnail_url']);
                $thumbnail_url = str_replace('&edge=curl', '', $thumbnail_url);
                return $thumbnail_url;
            }
        }

        return null;
    }

    /**
     * Retrieve the publisher for the given book serial number.
     *
     * @param   $serials    array of possible serial numbers
     * @param   $format     serial number format type
     * @return              array of publishers of the book
     */
    private function getBookPublisher($serials, $format) {
        if (is_array($serials)) {
            foreach($serials as $serial) {
                $serial = explode(' ', $serial);
                $googleBook = $this->getGoogleBookBySerialID($serial[0], $format);
                if (!is_null($googleBook)
                        && isset($googleBook->items)
                        && isset($googleBook->items[0]->volumeInfo->publisher)) {
                    $publisher = $googleBook->items[0]->volumeInfo->publisher;
                    if (!empty($publisher)) { // found
                        return array($publisher);
                    }
                }
            } // foreach
        }
        return null;
    } // getBookPublisher

    /**
     * Sets the date range of the query.
     *
     * @param   $startDate  the start date of the data range (default: 7 days ago)
     * @param   $endDate    the end date of the data range (default: now)
     */
    private function setDateRange($startDate, $endDate) {
        date_default_timezone_set('UTC');
        $this->startDate = (is_null($startDate))
            ? date('Y-m-d\TH:i:s\Z', time() - (7 * 24 * 60 * 60)) : $startDate;
        $this->endDate   = (is_null($endDate))
            ? date('Y-m-d\TH:i:s\Z') : $endDate;
        $this->curYear   = date('Y');
    }

    /**
     * Returns the JSON object of the response data for
     * the queried book list given start row and
     * number of subsequent rows.
     *
     * @param   $start      the start row of the result set
     * @param   $rows       the number of rows in the result set
     *                      subsequent to the start row
     * @return              JSON object of the result set
     */
    private function getBookData($start, $rows) {
        $query = str_replace('{$startDate}', $this->startDate, $this->yorklibBiblioUri);
        $query = str_replace('{$endDate}',   $this->endDate,   $query);
        $query = str_replace('{$start}',     $start,           $query);
        $query = str_replace('{$rows}',      $rows,            $query);
        $query = str_replace('{$format}',    'json',           $query);
        if ($this->isDebug) {
            $queryMsg = explode('?', $query);
            $queryMsg = $queryMsg[0]."\r\n\t\t?".implode("\r\n\t\t&", explode('&', $queryMsg[1]));
            file_put_contents($this->runsLogSrc,
                "\tYork Library Query:\r\n\t\t".$queryMsg."\r\n",
                FILE_APPEND | LOCK_EX); // Log query
        }
        return json_decode(self::curl($query));
    }

    /**
     * Returns the start date of the range of last
     * indexed books in the response data. (default: 7 days ago)
     *
     * @return              the start date of data range.
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * Returns the end date of the range of last
     * indexed books in the response data. (default: now)
     *
     * @return              the end date of data range.
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * Returns the number of books returned in
     * the response data.
     *
     * @return              the number of books in response data.
     */
    public function getBookCount() {
        if (is_null($this->bookCount)) {
            $data = $this->getBookData(0, 0);
            $this->bookCount = $data->response->numFound;
        }
        return $this->bookCount;
    }

    /**
     * Returns the raw book list returned in
     * the response data.
     *
     * @return              the raw book list.
     */
    public function getRawBookList() {
        if (is_null($this->booklist)) {
            $data = $this->getBookData(0, $this->getBookCount());
            if ($this->isDebug) {
                file_put_contents($this->yorklibCacheSrc,
                    json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
            }
            $this->booklist = $data->response->docs;
        }
        return $this->booklist;
    }

    /**
     * Returns the processed book list.
     *
     * @return              the processed book list.
     */
    public function getBookList() {
        if (!$this->isProcessed) {
            foreach ($this->getRawBookList() as $book) {
                // Obtain the book cover thumbnail
                $book->thumbnail = null;
                if (isset($book->isbn)) {
                    $book->thumbnail = $this->getBookCoverV2($book->isbn, 'isbn');
                }
                if (is_null($book->thumbnail) && isset($book->issn)) {
                    $book->thumbnail = $this->getBookCover($book->issn, 'issn');
                }

                // Missing publisher
                if (!isset($book->publisher)) {
                    if (isset($book->isbn)) {
                        //$book->publisher = $this->getBookPublisher($book->isbn, 'isbn');
                    }
                    if (is_null($book->publisher) && isset($book->issn)) {
                        //$book->publisher = $this->getBookPublisher($book->issn, 'issn');
                    }
                }

                // Increment newBookCount if published this year
                if (isset($book->publishDate)) {
                    $pubDate = $book->publishDate;
                    if (isset($pubDate) && $pubDate[sizeof($pubDate) - 1] === $this->curYear) {
                        $this->newBookCount += 1;
                    }
                }
            } // foreach
            if ($this->isDebug) {
                file_put_contents($this->googleBooksCacheSrc,
                    json_encode($this->googleBooks, JSON_PRETTY_PRINT), LOCK_EX);
            }
            $this->isProcessed = true;
        }
        return $this->booklist;
    } // getBookList

    /**
     * Returns the number of books published
     * this year in the response data.
     *
     * @return              the number of books published this year.
     */
    public function getNewBookCount() {
        if (!$this->isProcessed) {
            $this->getBookList();
        }
        return $this->newBookCount;
    }

    /**
     * Construct a new NewBooks object with the given
     * configurations and date range.
     *
     * @param   $config     the configuration object
     * @param   $startDate  the start date of the data range (default: 7 days ago)
     * @param   $endDate    the end date of the data range (default: now)
     */
    public function __construct($config, $startDate = null, $endDate = null) {
        $this->googleBooks = array();
        $this->setDateRange($startDate, $endDate);
        self::loadConfig($config);
    }

} // NewBooks
