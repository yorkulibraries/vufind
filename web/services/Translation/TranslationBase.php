<?php
require_once 'Action.php';
require_once 'sys/Translation.php';

class TranslationBase extends Action
{
    public function __construct()
    {
        global $configArray;
        global $interface;
        
        $interface->assign('enabledLanguages', $configArray['Languages']);
        $interface->assign('enabledLanguageCodes', array_keys($configArray['Languages']));
    }
    
    protected function redirectToIndex()
    {
        global $configArray;
        header('Location: ' . $configArray['Site']['path'] . '/Translation/Home');
        exit;
    }
}

?>