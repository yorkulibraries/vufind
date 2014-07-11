<?php
require_once 'EZProxy.php';

class Video extends EZProxy
{
    public function __construct()
    {
        global $interface;

        parent::__construct();

        // Load Configuration for this Module
        $config = parse_ini_file('conf/video.ini', true);
        $this->config = array_merge($this->config, $config);
        
        // assign terms of use
        $interface->assign('tou', 'videos-terms-of-use');
        
        $interface->setPageTitle('Video');
    }
    
    protected function connect($patron) 
    {
        global $interface;
        
        $asxFile = $_GET['num'] . '.asx';
        $asxFileFull = $this->config['asx_directory'] . '/' . $asxFile;
        // create the asx file if it does not exist yet
        if (!is_file($asxFileFull)) {
            $handle = fopen($asxFileFull, 'w+');
            if ($handle) {
                fwrite($handle, $this->generateXML($_GET['num']));
                fclose($handle);
            } else {
                $interface->assign(
                    'message', 
                    'This resource is temporarily unavailable, please try again later'
                );
                fclose($handle);
                $this->display();
                return;
            }
        }
        $url = 'http://' . $this->config['server_name'] . "/asx/$asxFile";
        header('Location: ' . $url);
    }
    
    protected function generateXML($id) 
    {
        $url = $this->config['mms_base_url'] . '/' . $id . '.wmv';
        $asx = '<asx version="3.0">'
        . '<entry>'
        . '<ref href="' . $url . '" />'
        . '</entry>'
        . '</asx>';    
        return $asx;
    }
}
?>