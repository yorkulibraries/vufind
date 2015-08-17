<?php
require_once 'HTTP/Request.php';
require_once 'XLogin.php';

class GradReadingRoom extends XLogin
{
    private $config;
    private $allowedProfiles;

    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $this->config = parse_ini_file('conf/grad-reading-room.ini', true);

        // process comma delimited array parameters
        $this->allowedProfiles = explode(',', $this->config['allowed_profiles']);
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

        // check profile
        if (!in_array($patron['profile'], $this->allowedProfiles)) {
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
        $interface->setPageTitle('Graduate Reading Room Door Code');
        $interface->setTemplate('grad-reading-room.tpl');
        $interface->display('layout.tpl');
    }
}
?>