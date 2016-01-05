<?php
class OURUtils {
    
    static function getLicenseNameFromURL($url)
    {
        if(preg_match('/\/licenses\/(.+)/', $url, $matches)) {
            return $matches[1];
        } 
        return null;
    }
    
    static function getUsageRights($name)
    {
        global $configArray, $logger, $memcache;
        
        $cacheKey = 'OCUL Usage Rights ' . $name;
        if ($memcache) {
            $xmlstr = $memcache->get($cacheKey);
            if ($xmlstr !== false) {
                $logger->log('Cache hit - ' . $cacheKey, PEAR_LOG_DEBUG);
                return self::parseUsageRights($xmlstr);
            }
        }
        $url = $configArray['UsageRightsApi']['url'] . '/' . $name . '/api';
        $xmlstr = file_get_contents($url);
        if ($memcache && $memcache->set($cacheKey, $xmlstr, 0, $configArray['Caching']['memcache_expiry'])) {
            $logger->log('Cache set - ' . $cacheKey, PEAR_LOG_DEBUG);
        }
        return self::parseUsageRights($xmlstr);
    }
    
    static function parseUsageRights($xmlstr) 
    {
        global $logger;
        $map = array('Yes'=>'success', 'Ask'=>'warning', 'No'=>'danger');
        $rights = null;
        try {
            $xml = new SimpleXmlElement($xmlstr);
            $root = $xml->xpath("//license");
            $xml = $root[0];
            $rights = $xml->children();
        } catch (Exception $e) {
            $logger->log('Can not parse usage rights XML: ' . $xmlstr, PEAR_LOG_ERR);
        }
        foreach ($rights as $right) {
            $right->addChild('status', $map[(string)$right->usage]);
        }
        return $rights;
    }
}
?>