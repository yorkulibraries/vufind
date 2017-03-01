<?php
require_once 'EZProxy.php';

class Vividata extends EZProxy
{
    protected function connect($patron) 
    {
        global $interface;
        
        $config = parse_ini_file('conf/vividata.ini', true);
        $links = array();
        foreach ($config['Vividata']['links'] as $link) {
            list($label, $url) = explode('\\', $link, 2);
            $links[$url] = $label;
        }
        $interface->assign('links', $links);
        $interface->setTemplate('vividata.tpl');
        $interface->display('layout.tpl');
        exit;
    }
}
?>
