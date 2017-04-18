<?php
require_once 'HTTP/Request.php';
require_once 'XLogin.php';

class LASRoom extends XLogin
{
    private $config;

    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $this->config = parse_ini_file('conf/las-room.ini', true);
    }
    
    public function launch()
    {
        global $interface;
        
        // get patron from ILS
        $patron = UserAccount::catalogLogin();
        if (!$patron || PEAR::isError($patron)) {
            $interface->assign('message', 'access_not_allowed_no_active_library_account');
            $this->display();
            return;
        }

        // check expiry
        if ($patron['expired']) {
            $interface->assign('message', 'access_not_allowed_privileges_expired');
            $this->display();
            return;
        }

        $papyrus_user = $this->get_papyrus_user($patron['alt_id']);
        if(!$papyrus_user) {
            $interface->assign('message', 'access_not_allowed_not_eligible_profile');
            $this->display();
            return;
        }
        
        
        $catalog = ConnectionManager::connectToCatalog();
        if ($catalog && $catalog->status) {
            $door = $catalog->getPatron($this->config['barcode']);
            if (!$door || PEAR::isError($door)) {
                $interface->assign('message', 'Access code currently not available');
                $this->display();
                return;
            }
            $interface->assign('code', $door['pin']);
        }
        
        $this->display();
    }

    private function display()
    {
        global $interface;
        $interface->setPageTitle('LAS Room Door Code');
        $interface->setTemplate('las-room.tpl');
        $interface->display('layout.tpl');
    }
    
    private function get_papyrus_user($cyin) {
        global $memcache;
        global $configArray;
        
        $papyrus_user = null;
        if (isset($this->config['papyrus_user_api_url']) && !empty($this->config['papyrus_user_api_url'])) {
            if (strlen($cyin) == 9) {
                $user_api_url = $this->config['papyrus_user_api_url'] . $cyin;
                $cache_key = 'lasroom_' . md5($user_api_url);
                if ($memcache) {
                    $result = $memcache->get($cache_key);
                    if ($result !== false) {
                        return $result;
                    }
                }
                $papyrus_user = @file_get_contents($user_api_url);
                if ($papyrus_user && strlen($papyrus_user) == 9) {
                    if ($memcache) {
                        $memcache->set($cache_key, $papyrus_user, 0, $configArray['Caching']['myaccount_expiry']);
                    }
                    return $papyrus_user;
                } else {
                    // not a valid papyrus user object
                    return null;
                }
            }
        }
        return $papyrus_user;
    }
    
}
?>