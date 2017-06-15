<?php
require_once 'EZProxy.php';

class Preqin extends EZProxy
{
    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $config = parse_ini_file('conf/preqin.ini', true);
        $this->config = array_merge($this->config, $config);
    }

    protected function connect($patron) 
    {
        global $interface;
        $logins = $this->config['Passwords']['logins'];
        $passwords = array();
        foreach ($logins as $l) {
            $passwords[] = explode('|', $l);
        }
        $interface->assign('passwords', $passwords);
        $interface->assign('finalUrl', $this->config['General']['finalUrl']);
        $interface->setTemplate('preqin.tpl');
        $interface->display('layout.tpl');
        exit;
    }
}
?>
