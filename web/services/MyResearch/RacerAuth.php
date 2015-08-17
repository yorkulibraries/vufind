<?php
require_once 'XLogin.php';

class RacerAuth extends XLogin
{
    private $config;

    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $this->config = parse_ini_file('conf/racer-auth.ini', true);
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
        if ($patron['profile'] == 'EXTERNAL') {
            // Redirect to the RACER policy form
            header('Location: ' . $this->config['policy_form_url']);
        	return;
        }
        
        // Redirect to the RACER registration form
        header('Location: ' . $this->config['racer_registration_form']);
    }
}
?>
