<?php
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_PYORK_') !== false) {
            $headers[$key] = $value;
        }
    }
    
    header('Content-type: application/json');
    echo json_encode($headers);
?>
