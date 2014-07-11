<?php
class YorkVuFind {
    public static function getYorkSpaceFormat($type) {
        $type = trim($type);
        if (stripos($type, 'jpeg') !== false || stripos($type, 'tif') !== false
        || stripos($type, 'image') !== false || stripos($type, 'photo') !== false
        || stripos($type, '600 dpi') !== false || stripos($type, '1200 dpi') !== false
        || stripos($type, '300 dpi') !== false) {
            return 'Image';
        }
        switch ($type) {
            case 'Book chapter':
                return 'Book Chapter';
            case 'Book':
                return 'eBook';
            case 'Recording, musical':
                return 'Music Recording';
            case 'Musical Score':
                return 'Score';
            case 'Sheet Music':
            case 'Sheet music':
                return 'Digitized Score';
            case 'Technical Report':
                return 'Technical Report';
            case 'Working Paper':
                return 'Working Paper';
            case 'Preprint':
                return 'Preprint';
            case 'Dataset':
                return 'Dataset';
        }
        return $type;
    }

    public static function getYorkSpacePublishDate($date) {
        $year = substr($date, -4, 4);
        if (is_numeric($year)) {
            return $year;
        }
        $year = substr($date, 0, 4);
        if (is_numeric($year)) {
            return $year;
        }
        return '';
    }

    public static function getYorkSpaceLanguage($lang, $file) {
        if ($lang == 'en_US' || $lang == 'En') {
            return 'English';
        }
        return VuFind::mapString($lang, $file);
    }
    
    public static function getYorkSpaceFullText($handle) {
        $fulltext = '';
        $handle = str_replace('-', '/', $handle);
        $extensions = 'pdf|doc|docx|ppt|pptx|odt';
        $urls = YorkVuFind::scrapeDspaceScreenForBitstreamURLs($handle, $extensions);
        foreach ($urls as $url) {
            $fulltext .= VuFind::harvestWithAperture($url) . "\n";
        }
        return $fulltext;
    }

    public static function getFirstIndexed($core, $id) {
        return VuFind::getFirstIndexed($core, $id, date(DATE_ISO8601));
    }

    public static function getLastIndexed($core, $id) {
        return VuFind::getLastIndexed($core, $id, date(DATE_ISO8601));
    }

    public static function getMulerSearchableFields($in) {
        // Ensure that $in is an array:
        if (!is_array($in)) {
            $in = array($in);
        }
        $searchable = array('title', 'other_titles', 'issn', 
        	'public_notes', 'description', 'publisher', 
        	'vendor','public_notes_cascade'
        );
        $text = '';
        foreach ($in as $xml) {
            foreach($xml->childNodes as $node) {
                if (in_array($node->nodeName, $searchable)) {
                    $text .= ' ' . strip_tags($node->nodeValue);
                }
            }
        }
        return $text;
    }

   /**
    * Slighly different from VuFind::mapString function - return empty string if no match.
    */
    public static function mapString($in, $filename)
    {
        global $importPath;
    
        // Load the translation map and send back the appropriate value.  Note
        // that PHP's parse_ini_file() function is not compatible with SolrMarc's
        // style of properties map, so we are parsing this manually.
        $map = array();
        $mapLines = file($importPath . '/translation_maps/' . $filename);
        foreach ($mapLines as $line) {
            $line = str_replace('\\', '', $line);
            $parts = explode('=', $line, 2);
            if (isset($parts[1])) {
                $key = trim($parts[0]);
                $map[$key] = trim($parts[1]);
            }
        }
        return isset($map[$in]) ? $map[$in] : '';
    }
    
    private static function scrapeDspaceScreenForBitstreamURLs($handle, $extensions) {
        $url = 'http://pi.library.yorku.ca/dspace/handle/' . $handle;
        $data = @file_get_contents($url);
        $pattern = '/href="(.*\/bitstream\/handle\/.*(' . $extensions . ')[^"]*)"/i';
        preg_match_all($pattern, $data, $matches);
        $urls = array();
        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                $url = $match;
                $url = trim($url, '"');
                if (strpos($url, '/') === 0) {
                    $urls[] = 'http://pi.library.yorku.ca' . $url;
                } else {
                    $urls[] = $url;
                }
            }
        }
        $urls = array_unique($urls);
        return $urls;
    }
}
?>
