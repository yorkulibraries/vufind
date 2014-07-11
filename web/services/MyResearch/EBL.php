<?php
require_once 'EZProxy.php';

class EBL extends EZProxy
{
    protected function connect($patron) 
    {
        global $interface;

        // parameters
        $url = $_GET['url'];
        $extendedid = $_GET['extendedid'];
        $target = $_GET['target'];

        // generate an md5 hash of the user's ID
        // so we can pass it on to EBL, this way
        // they don't know the actual user id
        $userid = md5($patron['barcode']);
        $secret_key = $this->config['EBL']['secret_key'];
        $time_stamp = time();
        $id = $userid . $time_stamp . $secret_key;
        $id_hash = md5($id);

        // check to see if the url parameter is encoded, if so decode it
        if (strpos($url, '%3fp=') !== false) {
            $url = urldecode($url);
        }

        // decide whether to add ? or & to the final url to send user
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $ebl = $url . "target=$target" . "&extendedid=$extendedid" 
            . "&userid=$userid" . "&tstamp=$time_stamp" . "&id=$id_hash";

        header('Location: ' . $ebl);
    }
}
?>