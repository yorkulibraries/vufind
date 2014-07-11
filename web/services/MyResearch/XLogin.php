<?php
require_once 'MyResearch.php';

class XLogin extends MyResearch
{
    public function __construct()
    {
        // preserve $_GET parameters
        global $interface;
        $interface->assign('extraParams', $this->getExtraParameters());

        parent::__construct();
    }

    protected function getExtraParameters()
    {
        $extraParams = array();
        foreach ($_GET as $name=>$value) {
            if ($name != 'module' && $name != 'action') {
                $extraParams[] = array('name' => $name, 'value' => $value);
            }
        }
        return $extraParams;
    }
    
    protected function getQueryString()
    {
        $extraParams = $this->getExtraParameters();
        $queryString = '';
        foreach ($extraParams as $param) {
        	$queryString .=  '&' . $param['name'] . '=' . urlencode($param['value']);
        }
        return trim($queryString, '& ');      
    }

    protected function isLawPatron($patron) 
    {
        return (preg_match("/.*LAW.*/", $patron['library'])
            || preg_match("/.*LAW.*/", $patron['cat1'])
            || preg_match("/.*LAW.*/", $patron['profile']));
    }
}

?>