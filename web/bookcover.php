<?php
/**
 * Book Cover Generator
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
 * @package  Cover_Generator
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/use_of_external_content Wiki
 */
require_once 'sys/ConfigArray.php';
require_once 'sys/Proxy_Request.php';
require_once 'sys/Logger.php';
require_once 'sys/ConnectionManager.php';
require_once 'generator.php';
require_once 'vendor/autoload.php';

// Retrieve values from configuration file
$configArray = readConfig();
$logger = new Logger();

// Try to set the locale to UTF-8, but fail back to the exact string from the config
// file if this doesn't work -- different systems may vary in their behavior here.
setlocale(
    LC_MONETARY, array($configArray['Site']['locale'] . ".UTF-8",
    $configArray['Site']['locale'])
);
date_default_timezone_set($configArray['Site']['timezone']);

// Setup memcached interface if configured
global $memcache;
if (isset($configArray['Caching'])) {
    $host = isset($configArray['Caching']['memcache_host']) ? $configArray['Caching']['memcache_host'] : 'localhost';
    $port = isset($configArray['Caching']['memcache_port']) ? $configArray['Caching']['memcache_port'] : 11211;
    $timeout = isset($configArray['Caching']['memcache_connection_timeout']) ? $configArray['Caching']['memcache_connection_timeout'] : 1;
    $memcache = new Memcache();
    if (!@$memcache->pconnect($host, $port, $timeout)) {
        $logger->log("Could not connect to Memcache (host = {$host}, port = {$port}).", PEAR_LOG_ERR);
        $memcache = false;
    }
}

// cache cover images for 24 hours
$maxAge = 86400;
header('Pragma:');
header("Cache-Control: max-age=$maxAge, public");
header("Expires: ". gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");

sanitizeParameters() || dieWithDefaultImage();

if(fetchFromId($_GET['id'], $_GET['size'])
|| fetchFromGoogle($_GET['id'], $_GET['size'], $_GET['isn'])
|| fetchFromTMDB($_GET['id'], $_GET['size'])
|| fetchFromURL($_GET['id'], $_GET['size'], $_GET['url'])) {
    exit();
}

$generate = true;
if ($generate && generateImage($_GET['id'], $_GET['size'])) {
    exit();
}

dieWithDefaultImage();

/* END OF INLINE CODE */

/**
 * Sanitize incoming parameters to avoid filesystem attacks.  We'll make sure the
 * provided size matches a whitelist, and we'll strip illegal characters from the
 * ISBN and/or contentType
 *
 * @return  bool       True if parameters ok, false on failure.
 */
function sanitizeParameters()
{
    $validSizes = array('small', 'medium', 'large');
    if (!count($_GET) || !in_array($_GET['size'], $validSizes)) {
        return false;
    }
    // sanitize ISBN
    $_GET['isn'] = isset($_GET['isn'])
        ? preg_replace('/[^0-9xX,]/', '', $_GET['isn']) : '';

    // sanitize contenttype
    // file names correspond to Summon Content Types with spaces
    // removed, eg. VideoRecording.png
    $_GET['contenttype'] = isset($_GET['contenttype'])
        ? preg_replace("/[^a-zA-Z]/", "", $_GET['contenttype']) : '';

    // id
    $_GET['id'] = isset($_GET['id'])
        ? preg_replace('/[^0-9a-z\-]/', '', $_GET['id']) : '';
    
    return true;
}

/**
 * Cache the image URL (or actual content), then redirect (or serve).
 */
function processImageURL($url, $id, $cache = 'url')
{
    global $configArray, $logger;

    if (empty($url)) {
        dieWithDefaultImage();
    }
    
    if (!$cache || empty($id)) {
        header('Location: ' . $url);
        exit();
    }
    
    $file = $configArray['Site']['local'] . '/images/covers/by-id/' . $id;
    
    if (!file_exists($file)) {
        $url = trim($url);
        if ($cache == 'url') {
            $logger->log('Caching ' . $url . ' to ' . $file, PEAR_LOG_DEBUG);
            file_put_contents($file, $url);
            header('Location: ' . $url);
            exit();
        } 
        if (isSecure()) {
            // if in HTTPS, then proxy the URL if it is NOT HTTPS
            if (stripos($url, 'https://') === false) {
                $logger->log('Not a secure URL, proxying', PEAR_LOG_DEBUG);
                file_put_contents($file, file_get_contents($url));
                $sent = sendLocalFile($file);
                unlink($file);
                if ($sent) {
                    exit();
                }
            }
        }
        header('Location: ' . $url);
        exit();
    }
    sendLocalFile($file);
    exit();
}

/**
 * Retrieve a Google Books cover.
 *
 * @return bool True if image displayed, false otherwise.
 */
function fetchFromGoogle($id, $size, $isn)
{
    global $logger;
    
    // Don't bother trying if we can't read JSON:
    if (is_callable('json_decode') && !empty($isn)) {        
        // Construct the request URL:
        $url = 'https://books.google.com/books?jscmd=viewapi&' .
               'bibkeys=ISBN:' . $isn . '&callback=addTheCover';
               
        $logger->log('Trying Google API: ' . $url, PEAR_LOG_DEBUG);

        // Make the HTTP request:
        $client = new Proxy_Request();
        $client->setMethod(HTTP_REQUEST_METHOD_GET);
        $client->setURL($url);
        $result = $client->sendRequest();

        // Was the request successful?
        if (!PEAR::isError($result)) {
            // grab the response:
            $json = $client->getResponseBody();

            // extract the useful JSON from the response:
            $count = preg_match('/^[^{]*({.*})[^}]*$/', $json, $matches);
            if ($count < 1) {
                return false;
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

            // find the first thumbnail URL and process it:
            foreach ($json as $current) {
                if (isset($current['thumbnail_url'])) {
                    $thumbnail_url = str_replace('zoom=5', 'zoom=1', $current['thumbnail_url']);
                    $thumbnail_url = str_replace('&edge=curl', '', $thumbnail_url);
                    $logger->log('Got ' . $thumbnail_url, PEAR_LOG_DEBUG);
                    processImageURL($thumbnail_url, $id);
                }
            }
        }
    }
    return false;
}

function fetchFromId($id, $size='small')
{
	global $configArray, $logger;

	if (empty($id)) {
		return false;
	}
	
	$file = $configArray['Site']['local'] . '/images/covers/by-id/' . $id;
	if (file_exists($file) && is_readable($file)) {
	    return sendLocalFile($file);
    }
    return false;
}

function generateImage($id, $size) {
    global $configArray, $logger;
    
    $record = null;
    $solr = ConnectionManager::connectToIndex();
    if (!($record = $solr->getRecord($id))) {
        return false;
    }

    $logger->log('Trying to generate cover image for record: ' . $id, PEAR_LOG_DEBUG);

    // clean title
    $title = preg_replace('/\[[A-Za-z \(\)]+\]/', '', $record['title_full']);
    list($title, $junk) = explode('/', $title);
    list($title, $junk) = explode(' = ', $title);
    $title = trim($title, "\n/* :-");

    // truncate title to 100 chars
    if (strlen($title) > 100) {
        $title = substr($title , 0, strrpos(substr($title, 0, 100), ' ' ));
    }
    
    if (isset($record['issn'][0]) && !empty($record['issn'][0])) {
        $author = preg_replace('/[^0-9x\-]/i', '', $record['issn'][0]);
    } else {
        list($junk, $author) = explode('/', $record['title_full']);
        $author = trim($author);
        if (empty($author)) {
            $author = $record['author'];
        }
        $author = trim($author, "\n/* :");
        // truncate author to 40 chars
        if (strlen($author) > 40) {
            $author = substr($author , 0, strrpos(substr($author, 0, 40), ' ' ));
        }
    }
    
    // don't draw author for Video records
    if (in_array('Video', $record['format'])) {
        $author = null;
    }
    
    $settings = array(
	    'mode'         => 'solid',
	    'saturation'   => 100,
	    'size'         => 128,
	    'height'       => 190,
	    'titleFont'    => 'LiberationSans-Bold.ttf',
        'authorFont'   => 'LiberationSans-Bold.ttf'
    );
	$generator = new Generator(null, $settings);
	header('Content-type: image/png');
	echo $generator->generate($title, $author);
	return true;
}

function fetchFromTMDB($id, $size) {
    global $configArray, $logger;
    
    $record = null;
    $solr = ConnectionManager::connectToIndex();
    if (!($record = $solr->getRecord($id))) {
        return false;
    }
    
    if (!in_array('Sound Recording', $record['format']) && 
        (in_array('Video', $record['format']))
    ) {
        $logger->log('Trying TMDB for record: ' . $id, PEAR_LOG_DEBUG);
        
        try {
            $token  = new \Tmdb\ApiToken($configArray['TMDB']['apikey']);
            $client = new \Tmdb\Client($token);
            $configRepo = new \Tmdb\Repository\ConfigurationRepository($client);
            $config = $configRepo->load();     
            $searchRepo = new \Tmdb\Repository\SearchRepository($client);
            $movieRepo = new \Tmdb\Repository\MovieRepository($client);  
        
            $query = new \Tmdb\Model\Search\SearchQuery\MovieSearchQuery();
            $query->page(1);
            $publishDate = (strlen($record['publishDate'][0]) >= 4) ? $record['publishDate'][0] : null;
            $originalReleaseDate = (strlen($record['video_release_date_str']) >= 4) ? $record['video_release_date_str'] : null;
            if ($originalReleaseDate) {
                $query->year($originalReleaseDate);
            } else if ($publishDate){
                $query->year($publishDate);
            }

            list($title, $variant) = explode(' = ', $record['title_full']);
            $title = preg_replace('/\[videorecording\]|\(Blu\-ray\)|\[videorecording \(BLU\-RAY\)\]/i', '', $title);
            $title = trim($title, ' /');

            // search movie with title and year
            $movies = $searchRepo->searchMovie($title, $query);
        
            $directors = $record['video_director_str_mv'];
        
            // if nothing found, then try without the year
            if (!processMovieMatches($title, $movies, $directors, $movieRepo, $config, $id)) {
                $query->year(null);
                $movies = $searchRepo->searchMovie($title, $query);
                if (!processMovieMatches($title, $movies, $directors, $movieRepo, $config, $id)) {
                    // if nothing found, then try shorter title
                    $movies = $searchRepo->searchMovie($record['title'], $query);
                    if (!processMovieMatches($record['title'], $movies, $directors, $movieRepo, $config, $id)) {
                        $movies = $searchRepo->searchMovie($record['title_short'], $query);
                        return processMovieMatches($record['title_short'], $movies, $directors, $movieRepo, $config, $id);
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    return false;
}

function processMovieMatches($title, $movies, $directors, $movieRepo, $config, $id) {
    global $configArray, $logger;
    
    $match = null;
    
    foreach($movies as $movie) {
        $title = preg_replace('/[^\da-z]/i', '', strtolower($title));
        $otherTitle = preg_replace('/[^\da-z]/i', '', strtolower($movie->getTitle()));
        $similarity = 0;
        similar_text($title, $otherTitle, $similarity);

        // if only 1 match, then check the title similarity, if more than 70%, then take it.
        if ($movies->getTotalResults() == 1 && $similarity > 70) {
            $match = $movie;
            break;
        }
        
        // don't bother checking directors if the title doesn't quite match
        if ($similarity > 80 && !empty($directors) && $movies->getTotalResults() > 1) {
            $movie = $movieRepo->load($movie->getId());
            $crew = $movie->getCredits()->getCrew();
            foreach ($crew as $person) {
                foreach ($directors as $director) {
                    $name1 = preg_replace('/[^\da-z]/i', '', strtolower($director));
                    $name2 = preg_replace('/[^\da-z]/i', '', strtolower($person->getName()));
                    $diff = levenshtein($name1, $name2);
                    if ($diff < 2) {
                        $match = $movie;
                        break;
                    }
                }
            }
        }
    }
    
    if ($match) {
        $image = $match->getPosterPath();
        if ($image) {
            $imageConfig = $config->getImages();
            $size = 'w500';
            $base_url = isSecure()
                ? $imageConfig['secure_base_url']
                : $imageConfig['base_url'];
            $url = $base_url . $size . $image;
            $logger->log('Got ' . $url, PEAR_LOG_DEBUG);
            processImageURL($url, $id);
        }
    }
    
    return false;
}

function fetchFromURL($id, $size, $url) {
    global $configArray, $logger;
    
    $url = trim($url);
    if (!empty($url)) {
        $logger->log('Trying URL embeded in record: ' . $url, PEAR_LOG_DEBUG);
        processImageURL($url, $id, false);
    }
    return false;
}

function isSecure() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
}

function sendDefaultImage() {
    global $configArray;
    $file = $configArray['Site']['local'] . '/images/covers/1x1.gif';
    header("Content-type: image/gif");
    readfile($file);
}

function dieWithDefaultImage() {
    sendDefaultImage();
    exit();
}

function sendLocalFile($file) {
    global $logger;
    if (file_exists($file) && is_readable($file)) {
	    $info = getimagesize($file);
	    if ($info === false) {
	        $url = trim(file_get_contents($file));
	        if (stripos($url, 'http') === 0) {
	            // is a HTTP URL cache file - redirect
	            $logger->log($file . ' is ' . $url, PEAR_LOG_DEBUG);
	            header('Location: ' . trim($url));
                return true;
            }
            // does not look like an HTTP URL
            $logger->log($file . ' does not contain a URL, sending default image instead', PEAR_LOG_DEBUG);
            sendDefaultImage();
            return true;
        }
        // some kind of image file, send it off
        $logger->log($file . ' is ' . $info['mime'], PEAR_LOG_DEBUG);
        header("Content-type: {$info['mime']}");
        readfile($file);
        return true;
	}
	$logger->log($file . ' not exist or not readable', PEAR_LOG_DEBUG);
	return false;
}
?>
