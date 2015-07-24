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
require_once 'sys/ISBN.php';

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

// global to hold filename constructed from ISBN
$localFile = '';

// Proxy server settings
if (isset($configArray['Proxy']['host'])) {
    if (isset($configArray['Proxy']['port'])) {
        $proxy_server
            = $configArray['Proxy']['host'].":".$configArray['Proxy']['port'];
    } else {
        $proxy_server = $configArray['Proxy']['host'];
    }
    $proxy = array(
        'http' => array(
            'proxy' => "tcp://$proxy_server", 'request_fulluri' => true
        )
    );
    stream_context_get_default($proxy);
}

// cache cover images for 24 hours
$maxAge = 86400;
header('Pragma:');
header("Cache-Control: max-age=$maxAge, public");
header("Expires: ". gmdate("D, d M Y H:i:s", time() + $maxAge) . " GMT");

// Display a fail image unless our parameters pass inspection and we are able to
// display an ISBN or content-type-based image.
if (!sanitizeParameters()) {
    dieWithFailImage();
} else if (!fetchFromId($_GET['id'], $_GET['size'])
    && !fetchFromISBN($_GET['isn'], $_GET['size'])
    && !fetchFromContentType($_GET['contenttype'], $_GET['size'])
    && !fetchFromTMDB($_GET['id'], $_GET['size'])
    && !generateImage($_GET['id'], $_GET['size'])
) {
    dieWithFailImage();
}

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

    return true;
}

/**
 * Load bookcover fom URL from cache or remote provider and display if possible.
 *
 * @param string $isn  ISBN (10 characters preferred)
 * @param string $size Size of cover (large, medium, small)
 *
 * @return bool        True if image displayed, false on failure.
 */
function fetchFromISBN($isn, $size)
{
    global $configArray;
    global $localFile;

    if (empty($isn)) {
        return false;
    }
    
    // We should check whether we have cached images for the 13- or 10-digit ISBNs.
    // If no file exists, we'll favor the 10-digit number if available for the sake
    // of brevity.
    $isbn = new ISBN($isn);
    if ($isbn->get13()) {
        $localFile = 'images/covers/' . $size . '/' . $isbn->get13();
    } else {
        // Invalid ISBN?  Keep it as-is to avoid a bad file path; the error will
        // be caught later down the line anyway.
        $localFile = 'images/covers/' . $size . '/' . $isn;
    }
    if (!is_readable($localFile) && $isbn->get10()) {
        $localFile = 'images/covers/' . $size . '/' . $isbn->get10();
    }
    if (is_readable($localFile)) {
        header('Location: ' . trim(file_get_contents($localFile)));
        return true;
    } else {
        // Fetch from provider
        if (isset($configArray['Content']['coverimages'])) {
            $providers = explode(',', $configArray['Content']['coverimages']);
            foreach ($providers as $provider) {
                $provider = explode(':', $provider);
                $func = $provider[0];
                $key = isset($provider[1]) ? $provider[1] : null;
                if ($func($key)) {
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * Load content type icon image from URL from theme images and display if possible.
 *
 * @param string $type Content type names, matching filename
 * @param string $size Size of icon (large, medium, small)
 *
 * @return bool        True if image displayed, false on failure.
 */
function fetchFromContentType($type, $size)
{
    global $configArray;

    // Give up if no content type was passed in:
    if (empty($type)) {
        return false;
    }

    // Array of image formats we may want to display:
    $formats = array('png', 'gif', 'jpg');

    // Take theme inheritance into account -- iterate down the list of themes from
    // config.ini and check each one in turn for icon images:
    $themes = explode(',', $configArray['Site']['theme']);
    for ($i = 0; $i < count($themes); $i++) {
        // Check all supported image formats:
        foreach ($formats as $format) {
            // Build the potential filename:
            $iconFile = dirname(__FILE__) . '/interface/themes/' . $themes[$i] .
                '/images/' . $size . '/' . $type . '.' . $format;
            // If the file exists, display it:
            if (is_readable($iconFile)) {
                // Most content-type headers match file extensions... but include a
                // special case for jpg vs. jpeg:
                header(
                    'Content-type: image/' . ($format == 'jpg' ? 'jpeg' : $format)
                );
                echo readfile($iconFile);
                return true;
            }
        }
    }

    // If we got this far, no icon was found:
    return false;
}

/**
 * Display the user-specified "cover unavailable" graphic (or default if none
 * specified) and terminate execution.
 *
 * @return void
 * @author Thomas Schwaerzler <vufind-tech@lists.sourceforge.net>
 */
function dieWithFailImage()
{
    global $configArray, $logger;
    
    // Get "no cover" image from config.ini:
    $noCoverImage = isset($configArray['Content']['noCoverAvailableImage'])
        ? $configArray['Content']['noCoverAvailableImage'] : null;

    // No setting -- use default, and don't log anything:
    if (empty($noCoverImage)) {
        // log?
        dieWithDefaultFailImage();
    }

    // If file defined but does not exist, log error and display default:
    if (!file_exists($noCoverImage) || !is_readable($noCoverImage)) {
        $logger->log(
            "Cannot access file: '$noCoverImage' in directory " . dirname(__FILE__),
            PEAR_LOG_ERR
        );
        dieWithDefaultFailImage();
    }

    // Array containing map of allowed file extensions to mimetypes (to be extended)
    $allowedFileExtensions = array(
        "gif" => "image/gif",
        "jpeg" => "image/jpeg", "jpg" => "image/jpeg",
        "png" => "image/png",
        "tiff" => "image/tiff", "tif" => "image/tiff"
    );

    // Log error and bail out if file lacks a known image extension:
    $fileExtension = strtolower(end(explode('.', $noCoverImage)));
    if (!array_key_exists($fileExtension, $allowedFileExtensions)) {
        $logger->log(
            "Illegal file-extension '$fileExtension' for image '$noCoverImage'",
            PEAR_LOG_ERR
        );
        dieWithDefaultFailImage();
    }

    // Get mime type from file extension:
    $mimeType = $allowedFileExtensions[$fileExtension];

    // Display the image and die:
    header("Content-type: $mimeType");
    echo readfile($noCoverImage);
    exit();
}

/**
 * Display the default "cover unavailable" graphic and terminate execution.
 *
 * @return void
 */
function dieWithDefaultFailImage()
{
    header('Content-type: image/gif');
    echo readfile('images/noCover2.gif');
    exit();
}

/**
 * Cache the image URL, and redirect to it.
 */
function processImageURL($url, $cache = true)
{
    global $localFile;  // this was initialized by fetchFromISBN()

    file_put_contents($localFile, trim($url));
    header('Location: ' . $url);
    exit();
}

/**
 * Retrieve a Syndetics cover.
 *
 * @param string $id Syndetics client ID.
 *
 * @return bool      True if image displayed, false otherwise.
 */
function syndetics($id)
{
    global $configArray;

    switch ($_GET['size']) {
    case 'small':
        $size = 'SC.GIF';
        break;
    case 'medium':
        $size = 'MC.GIF';
        break;
    case 'large':
        $size = 'LC.JPG';
        break;
    }

    $url = isset($configArray['Syndetics']['url']) ?
            $configArray['Syndetics']['url'] : 'http://syndetics.com';
    $url .= "/index.aspx?type=xw12&isbn={$_GET['isn']}/{$size}&client={$id}";
    return processImageURL($url);
}

/**
 * Retrieve a Content Cafe cover.
 *
 * @param string $id Content Cafe client ID.
 *
 * @return bool      True if image displayed, false otherwise.
 */
function contentcafe($id)
{
    global $configArray;

    switch ($_GET['size']) {
    case 'small':
        $size = 'S';
        break;
    case 'medium':
        $size = 'M';
        break;
    case 'large':
        $size = 'L';
        break;
    }
    $pw = $configArray['Contentcafe']['pw'];
    $url = isset($configArray['Contentcafe']['url'])
        ? $configArray['Contentcafe']['url'] : 'http://contentcafe2.btol.com';
    $url .= "/ContentCafe/Jacket.aspx?UserID={$id}&Password={$pw}&Return=1" .
        "&Type={$size}&Value={$_GET['isn']}&erroroverride=1";
    return processImageURL($url);
}

/**
 * Retrieve a LibraryThing cover.
 *
 * @param string $id LibraryThing client ID.
 *
 * @return bool      True if image displayed, false otherwise.
 */
function librarything($id)
{
    $url = 'http://covers.librarything.com/devkey/' . $id . '/' . 'large' .
        '/isbn/' . $_GET['isn'];
    return processImageURL($url);
}

/**
 * Retrieve an OpenLibrary cover.
 *
 * @return bool True if image displayed, false otherwise.
 */
function openlibrary()
{
    // Convert internal size value to openlibrary equivalent:
    switch ($_GET['size']) {
    case 'large':
        $size = 'L';
        break;
    case 'medium':
        $size = 'M';
        break;
    case 'small':
    default:
        $size = 'S';
        break;
    }

    // Retrieve the image; the default=false parameter indicates that we want a 404
    // if the ISBN is not supported.
    $url = 'http://covers.openlibrary.org/b/isbn/' . $_GET['isn'] .
        "-L.jpg?default=false";
    return processImageURL($url);
}

/**
 * Retrieve a Google Books cover.
 *
 * @return bool True if image displayed, false otherwise.
 */
function google()
{
    // Don't bother trying if we can't read JSON:
    if (is_callable('json_decode')) {
        // Construct the request URL:
        $url = 'https://books.google.com/books?jscmd=viewapi&' .
               'bibkeys=ISBN:' . $_GET['isn'] . '&callback=addTheCover';

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
                    return processImageURL($thumbnail_url, false);
                }
            }
        }
    }
    return false;
}

/**
 * Retrieve an Amazon cover.
 *
 * @param string $id Amazon Web Services client ID.
 *
 * @return bool      True if image displayed, false otherwise.
 */
function amazon($id)
{
    include_once 'sys/Amazon.php';

    $params = array('ResponseGroup' => 'Images', 'ItemId' => $_GET['isn']);
    $request = new AWS_Request($id, 'ItemLookup', $params);
    $result = $request->sendRequest();
    if (!PEAR::isError($result)) {
        $data = @simplexml_load_string($result);
        if (!$data) {
            return false;
        }
        if (isset($data->Items->Item[0])) {
            // Where in the XML can we find the URL we need?
            switch ($_GET['size']) {
            case 'small':
                $imageIndex = 'SmallImage';
                break;
            case 'medium':
                $imageIndex = 'MediumImage';
                break;
            case 'large':
                $imageIndex = 'LargeImage';
                break;
            default:
                $imageIndex = false;
                break;
            }

            // Does a URL exist?
            if ($imageIndex && isset($data->Items->Item[0]->$imageIndex->URL)) {
                $imageUrl = (string)$data->Items->Item[0]->$imageIndex->URL;
                return processImageURL($imageUrl, false);
            }
        }
    }

    return false;
}

/**
 * Retrieve a Summon cover.
 *
 * @param string $id Serials Solutions client key.
 *
 * @return bool      True if image displayed, false otherwise.
 */
function summon($id)
{
    global $configArray;

    // convert normalized 10 char isn to 13 digits
    $isn = $_GET['isn'];
    if (strlen($isn) != 13) {
        $ISBN = new ISBN($isn);
        $isn = $ISBN->get13();
    }
    $url = 'http://api.summon.serialssolutions.com/image/isbn/' . $id . '/' . $isn .
        '/' . $_GET['size'];
    return processImageURL($url);
}

function fetchFromId($id, $size)
{
	global $configArray;

	if (empty($id) || empty($size)) {
		return false;
	}
	$jpgFile = 'images/covers/local/' . $size . '/' . $id . '.jpg';
	if (is_readable($jpgFile)) {
		header('Content-type: image/jpeg');
		echo readfile($jpgFile);
		return true;
	} else {
	    // if there is a local original image,
	    // then scale the original to the specified size and save it
	    $original = 'images/covers/local/original/' . $id . '.jpg';
	    if (is_readable($original) && copy($original, $finalFile)) {
	        header('Content-type: image/jpeg');
	        scaleImage($finalFile);
	        echo readfile($finalFile);
	        return true;
	    }
	}
	$urlTextFile = 'images/covers/by-id/' . $id;
	if (is_readable($urlTextFile)) {
	    header('Location: ' . trim(file_get_contents($urlTextFile)));
        return true;
	}
	return false;
}

function scaleImage($file) {
    $size = $_GET['size'];
    $sizes = array('small'=>'128', 'medium'=>'140','large'=>'160');
    if (function_exists('imagecreatefromjpeg') 
        && ($image = @imagecreatefromjpeg($file))) {
        $fullSize = getimagesize($file);
        $ratio = $fullSize[0] / $fullSize[1];
        $newWidth = $sizes[$size];
        $newHeight = $newWidth / $ratio;
        $scaled = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($scaled, $image, 0, 0, 0, 0,
            $newWidth, $newHeight, $fullSize[0], $fullSize[1]);
        imagedestroy($image);
        imagejpeg($scaled, $file);
        imagedestroy($scaled);
    }
}

function generateImage($id, $size) {
    require_once 'sys/ConnectionManager.php';
    require_once 'generator.php';
    
    $record = null;
    $solr = ConnectionManager::connectToIndex();
    if (!($record = $solr->getRecord($id))) {
        return false;
    }

    $title = preg_replace('/\[[A-Za-z \(\)]+\]/', '', $record['title_full']);
    list($title, $junk) = explode('/', $title);
    list($title, $junk) = explode('=', $title);
    $title = trim($title, "\n/* :-");
    if (in_array('Journal/Periodical', $record['format']) || in_array('eJournal', $record['format'])) {
        if (isset($record['issn'][0]) && !empty($record['issn'][0])) {
            $author = preg_replace('/[^0-9x\-]/i', '', $record['issn'][0]);
        }
    } else {
        list($junk, $author) = explode('/', $record['title_full']);
        $author = trim($author);
        if (empty($author)) {
            $author = $record['author'];
        }
        $author = trim($author, "\n/* :");
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
    require_once 'sys/ConnectionManager.php';
    require_once 'vendor/autoload.php';
        
    $record = null;
    $solr = ConnectionManager::connectToIndex();
    if (!($record = $solr->getRecord($id))) {
        return false;
    }

    if (!in_array('Sound Recording', $record['format']) && 
        (in_array('VHS', $record['format']) || in_array('DVD', $record['format']) || in_array('Blu-Ray', $record['format']))
    ) {
        global $configArray;
        global $localFile;

        $localFile = 'images/covers/by-id/' . $id;

        $token  = new \Tmdb\ApiToken($configArray['TMDB']['apikey']);
        $client = new \Tmdb\Client($token);
        $configRepo = new \Tmdb\Repository\ConfigurationRepository($client);        
        $imageHelper = new \Tmdb\Helper\ImageHelper($configRepo->load());
        $searchRepo = new \Tmdb\Repository\SearchRepository($client);     
        $query = new \Tmdb\Model\Search\SearchQuery\MovieSearchQuery();
        $query->page(1);
        //$query->year(1975);
        list($title, $variant) = explode(' = ', $record['title_full']);
        $title = preg_replace('/\[videorecording\]|\(Blu\-ray\)/i', '', $title);
        $movies = $searchRepo->searchMovie($title, $query);
        foreach($movies as $movie) {
            $image = $movie->getPosterPath();
            if ($image) {
                $url = $imageHelper->getUrl($image, 'w185');
                return processImageUrl($url);
            }
        }
    }
    
    return false;
}

?>
