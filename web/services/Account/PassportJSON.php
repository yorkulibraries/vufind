<?php
require_once 'Action.php';

class PassportJSON extends Action
{
    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_PYORK_') !== false) {
                $headers[$key] = $value;
            }
        }
        
        header('Content-type: application/json');
        echo json_encode($headers);
    }
}
?>
