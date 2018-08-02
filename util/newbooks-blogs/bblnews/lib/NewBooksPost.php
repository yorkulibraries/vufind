<?php
/**
 * NewBooksPost.php contains only the NewBooksPost class.
 *
 * NewBooksPost faciliates the generation of a WordPress post
 * from York library and Google Books API data sources. Posts to
 * WordPress blog.
 *
 * @copyright   Copyright 2013, Vince Chu for Osgoode Hall Law School
 * @license     http://opensource.org/licenses/bsd-license.php
 */

include('NewBooks.php');
include('IXR.php');

class NewBooksPost {
    private $newbooks;
    private $curYear;
    private $bookList;
    private $bookCount;
    private $newBookCount;
    private $post;

    // Configs
    private $isDebug = false;
    private $clientURI;
    private $wpUser;
    private $wpPasswd;
    private $runsLogSrc;
    private $errorLogSrc;
    private $postTmplSrc;
    private $tn1TmplSrc;
    private $tn2TmplSrc;
    private $bitemTmplSrc;
    private $catalogURI;
    private $postCategory;
    private $isConfigured = false;

    /**
     * Load the configuration parameters.
     *
     * @param   $config     object of configurations
     */
    private function loadConfig($config) {
        if (!$this->isConfigured) {
            $this->isDebug      = !!$config->isDebug;
            $this->clientURI    = $config->clientURI;
            $this->wpUser       = $config->wpUser;
            $this->wpPasswd     = $config->wpPasswd;
            $this->runsLogSrc   = $config->runsLogSrc;
            $this->errorLogSrc  = $config->errorLogSrc;
            $this->postTmplSrc  = $config->postTmplSrc;
            $this->tn1TmplSrc   = $config->thumbnail1TmplSrc;
            $this->tn2TmplSrc   = $config->thumbnail2TmplSrc;
            $this->bitemTmplSrc = $config->bookItemTmplSrc;
            $this->catalogURI   = $config->catalogURI;
            $this->postCategory = $config->postCategory;
            $this->isConfigured = true;
        }
    }

    /**
     * Shorten/Trim the given string to the specified
     * maximum length. Append ellipse to the end of string
     * if shortened.
     *
     * @param   $maxlen     the maximum length of the string
     * @param   $str        string to shorten
     * @return              the shortened string
     */
    private static function shortenStr($maxlen, $str) {
        if (strlen($str) > $maxlen) {
            $strArray = explode(' ', $str);
            for ($idx = 0, $retStr = '', $dlm = ''; strlen($retStr) < 50; $idx++) {
                $retStr .= $dlm . $strArray[$idx];
                $dlm = ' ';
            }
            if ($idx < sizeof($strArray)) {
                $retStr .= '...';
            }
            return $retStr;
        }
        return $str;
    }

    /**
     * Generates the HTML code for the given book at the given
     * index in the list of books.
     *
     * @return              the generated HTML for the book item.
     */
    private function getBookItem($book, $i) {

        $title = preg_replace('| /.*|', '', $book->title);
        $catalogURI = str_replace('{$id}', $book->id, $this->catalogURI);
        $background = ($i % 2 === 1) ? 'ffffff' : 'efefef';
        $pubDate    = !isset($book->publishDate) ? array('') : $book->publishDate;
        $publisher  = !isset($book->publisher)   ? '' : $book->publisher[0].' ';
        $physical   = !isset($book->physical)    ? '' : $book->physical[0];
        $publisher .= $pubDate[sizeof($pubDate) - 1];

        // Get HTML for the thumbnail
        if (!is_null($book->thumbnail)) {
            $thumbnail = file_get_contents($this->tn1TmplSrc);
            $thumbnail = str_replace('{$thumbnail}', $book->thumbnail, $thumbnail);
        } else {
            $thumbTitle = self::shortenStr(50, $title);

            // Compute random background color
            $coverBG = '';
            mt_srand((double)microtime() * 1000000);
            while (strlen($coverBG) < 6) {
                $coverBG .= sprintf("%02X", mt_rand(0, 150));
            }

            // Get author
            $author = '';
            if (isset($book->author)) {
                $author = $book->author;
            } else if (isset($book->author2)) {
                $author = implode(', ', $book->author2);
            }
            $author = self::shortenStr(50, $author);

            // Supplant values in thumbnail template
            $thumbnail = file_get_contents($this->tn2TmplSrc);
            $thumbnail = str_replace('{$coverBG}', $coverBG,                 $thumbnail);
            $thumbnail = str_replace('{$title}',   htmlentities($thumbTitle, ENT_QUOTES, "UTF-8"), $thumbnail);
            $thumbnail = str_replace('{$author}',  htmlentities($author,     ENT_QUOTES, "UTF-8"), $thumbnail);
        } // if-else thumbnail

        // Supplant values in book item template
        $bookHTML = file_get_contents($this->bitemTmplSrc);
        $bookHTML = str_replace('{$background}',   $background,              $bookHTML);
        $bookHTML = str_replace('{$catalogURI}',   $catalogURI,              $bookHTML);
        $bookHTML = str_replace('{$thumbnail}',    $thumbnail,               $bookHTML);
        $bookHTML = str_replace('{$callNumber}',   $book->callnumber,        $bookHTML);
        $bookHTML = str_replace('{$title}',        htmlentities($title,      ENT_QUOTES, "UTF-8"), $bookHTML);
        $bookHTML = str_replace('{$publisher}',    htmlentities($publisher,  ENT_QUOTES, "UTF-8"), $bookHTML);
        $bookHTML = str_replace('{$physicalDesc}', $physical,                $bookHTML);
        return $bookHTML;
    } // getBookItem

    /**
     * Generates the HTML code for the new books post.
     *
     * @return              the generated HTML for the post.
     */
    public function getPost() {
        if (is_null($this->post)) {
            // generate the HTML for each book item
            // and populate them into an array
            $books = array();
            foreach ($this->bookList as $i => $book) {
                array_push($books, $this->getBookItem($book, $i));
            }

            // HTML for number of books published this year
            $newBookCount = '';
            if ($this->newBookCount > 0) {
                $newBookCount = ', including '.$this->newBookCount.' from '.$this->curYear;
            }

            // Supplant values in post template
            $post = file_get_contents($this->postTmplSrc);
            $post = str_replace('{$bookList}',     implode($books),  $post);
            $post = str_replace('{$bookCount}',    $this->bookCount, $post);
            $post = str_replace('{$newBookCount}', $newBookCount,    $post);
            if ($this->bookCount == 1) {
                $post = str_replace('acquisitions', 'acquisition', $post);
            }
            $this->post = $post;
        }
        return $this->post;
    } // getPost

    /**
     * Pushes the generated Post to the WordPress blog.
     */
    public function pushPost() {
        $client = new IXR_Client($this->clientURI);
        //$client->debug = true;

        // Post title
        $title = 'Recent Acquisitions, {$start} - {$end}';
        $title = str_replace('{$start}', date('F j',    strtotime($this->startDate)), $title);
        $title = str_replace('{$end}',   date('F j, Y', strtotime($this->endDate)),   $title);

        // Post content object
        $content['title'] = $title;
        $content['categories'] = array($this->postCategory);
        $content['mt_allow_comments'] = 0;
        $content['blog_charset'] = 'UTF-8';
        $content['description'] = utf8_encode($this->getPost());

        // Attempt posting to WordPress
        if (!$client->query('metaWeblog.newPost', '', $this->wpUser, $this->wpPasswd, $content, true)) {
            $errmsg  = date('Y-m-d H:i:s').': ';
            $errmsg .= 'Error while creating a new post'.$client->getErrorCode()." : ".$client->getErrorMessage()."\r\n";
            file_put_contents($this->errorLogSrc, $errmsg, FILE_APPEND | LOCK_EX); // Log errors
        } else {
            $id = $client->getResponse();
            if ($id) {
                $message = "\t".date('Y-m-d H:i:s').': Post published with ID:#'.$id."\r\n";
                file_put_contents($this->runsLogSrc, $message, FILE_APPEND | LOCK_EX); // Log run
            }
        }
    } // pushPost

    /**
     * Construct a new NewBooksPost object with the given
     * configurations.
     *
     * @param   $config     the configuration object
     */
    public function __construct($config) {
        $nb = new NewBooks($config);
        self::loadConfig($config);
        $this->newbooks     = $nb;
        $this->bookList     = $nb->getBookList();
        $this->bookCount    = $nb->getBookCount();
        $this->newBookCount = $nb->getNewBookCount();
        $this->startDate    = $nb->getStartDate();
        $this->endDate      = $nb->getEndDate();
        $this->curYear      = date('Y');
    }
} // NewBooksPost
