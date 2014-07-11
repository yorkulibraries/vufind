<?php
require_once 'EZProxy.php';

class Bates extends EZProxy
{
    public function __construct()
    {
        parent::__construct();

        // Load Configuration for this Module
        $config = parse_ini_file('conf/bates.ini', true);
        $this->config = array_merge($this->config, $config);
    }

    protected function connect($patron) 
    {
        global $interface;
        
        header('Location: ' . $this->config['index']);
    }
}
?>