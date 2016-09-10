<?php
require_once 'Action.php';
require_once 'sys/Translation.php';

class TranslationBase extends Action
{
    public function __construct()
    {
        global $configArray;
        global $interface;
        
        if (!UserAccount::isLoggedIn()) {
            $this->redirectToLogin();
        }
        
        $interface->assign('enabledLanguages', $configArray['Languages']);
        $interface->assign('enabledLanguageCodes', array_keys($configArray['Languages']));
    }
    
    protected function redirectToIndex()
    {
        global $configArray;
        header('Location: ' . $configArray['Site']['path'] . '/Translation/Home');
        exit;
    }
    
    protected function redirectToLogin()
    {
        global $configArray;
        global $action;
        header('Location: ' . $configArray['Site']['path'] . '/MyResearch/Home?followup=1&followupModule=Translation&followupAction=' . $action);
        exit;
    }
}

?>