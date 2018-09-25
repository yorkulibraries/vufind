<?php
require_once 'EZProxy.php';

class CanadianLegislativePulse extends EZProxy
{
    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $config = parse_ini_file('conf/canadianlegislativepulse.ini', true);
        $this->config = array_merge($this->config, $config);
    }

    protected function connect($patron) 
    {
        global $interface;
        $interface->assign('passwords', $this->config['Passwords']);
        $interface->assign('finalUrl', $this->config['General']['finalUrl']);
        $interface->setTemplate('canadianlegislativepulse.tpl');
        $interface->display('layout.tpl');
        exit;
    }
}
?>
