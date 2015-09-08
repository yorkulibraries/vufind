<?php
require_once 'HTTP/Request.php';
require_once 'XLogin.php';

class EZProxy extends XLogin
{
    protected $config;
    protected $allowedProfiles;
    protected $allowedCat1;
    protected $lawResources;
    protected $blacklistConfig;

    public function __construct()
    {
        global $interface, $logger;
        
        if (isset($_GET['confirmed'])) {
            $interface->setPageTitle('EZProxy');
            $interface->setTemplate('ezproxy-confirmed.' . $interface->lang . '.tpl');
            $interface->display('layout.tpl');
            exit();
        }
        
        // Load Configuration for this Module
        $this->config = parse_ini_file('conf/ezproxy.ini', true);
        
        // Load blacklist configuration 
        $this->blacklistConfig = parse_ini_file('conf/blacklist.ini', true);
        
        // load ip black list
        $this->ipBlacklist = $this->blacklistConfig['ip_blacklist'];
        
        // process comma delimited array parameters
        $this->allowedProfiles = explode(',', $this->config['allowed_profiles']);
        $this->allowedCat1 = explode(',', $this->config['allowed_cat1']);
        $this->lawResources = explode(',', $this->config['law_resources']);
        
        // assign default terms of use
        $interface->assign('tou', 'eresources-terms-of-use');
        
        // assign default page title
        $interface->setPageTitle('EZProxy');

        // check REMOTE_ADDR for blacklisted IPs
        foreach ($this->blacklistConfig['ip_blacklist'] as $blacklist) {
        	$match = strpos($_SERVER['REMOTE_ADDR'], $blacklist) ;
        	if ($match !== false && $match == 0) {
        		$interface->assign('message', 'access_not_allowed_ip_blacklisted');
        		$interface->assign('displayTermsOfUse', true);
        		$this->display();
        		$logger->log('IP address is blacklisted. ' . $_SERVER['REQUEST_URI'], PEAR_LOG_ALERT);
        		exit;
        	}
        }
        
        parent::__construct();
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

        // if this is an OSGOODE restricted resource, then
        // only OSGOODE patrons are allowed access
        if ($this->isLawResource($_GET['qurl']) && !$this->isLawPatron($patron)) {
            $interface->assign('message', 'access_not_allowed_law_only');
            $this->display();
            return;
        }

        // check profile and affiliation
        if (!in_array($patron['profile'], $this->allowedProfiles)
            && !in_array($patron['cat1'], $this->allowedCat1)) {
            $interface->assign('message', 'access_not_allowed_not_eligible_profile');
            $this->display();
            return;
        }
        
        // if user pressed "I agree" button, then make the ezproxy connection
        // otherwise, display the terms of use
        if (isset($_POST['agree'])) {
            $this->connect($patron);
        } else {
            $this->display();
        }
    }
    
    protected function isLawResource($url)
    {
        foreach ($this->lawResources as $regex) {
            if (preg_match($regex, $url)) {
                return true;
            }
        }
        return false;
    }
    
    protected function display()
    {
        global $interface;
        
        $interface->assign('queryString', $this->getQueryString());
        $interface->setTemplate('ezproxy.tpl');
        $interface->display('layout.tpl');
    }

    protected function connect($patron) 
    {
        global $interface, $logger;

        $url = (isset($_GET['qurl']) && !empty($_GET['qurl'])) 
                ? $_GET['qurl'] : $this->config['default_url'];
        $logger->log($patron['barcode'] . ' uses EZProxy to access: ' . $url, PEAR_LOG_NOTICE);
        $connectURL = ($this->config['use_ssl'] ? 'https' : 'http') 
            . '://'
            . $this->config['host'] . ':' . $this->config['port']
            . '/login?user=' . $patron['barcode']
            . '&pass=' . $this->config['connect_password']
            . '&qurl=' . urlencode($url);

        // attempt to connect to ezproxy server
        $request = new HTTP_Request($connectURL);
        $response = $request->sendRequest();
        if (PEAR::isError($response)) {
            $interface->assign('message', 'error_connecting_to_ezproxy');
            $this->display();
            return;
        }

        // check the response headers from ezproxy for sanity
        $ezproxyHeaders = array_change_key_case($request->getResponseHeader(), CASE_LOWER);
        if (!isset($ezproxyHeaders['location'])) {
            $interface->assign('message', 'error_connecting_to_ezproxy');
            $this->display();
            return;
        }

        // pass the headers back to the client
        foreach ($ezproxyHeaders as $key => $value) {
            header("$key: $value");
        }
        
        // gtfo
        exit();
    }
}
?>