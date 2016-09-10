<?php
require_once 'services/Translation/TranslationBase.php';

class Home extends TranslationBase
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
        
        $index = array();
        $translation = new Translation();
        $translation->orderBy('`key`');
        $translation->find();
        while ($translation->fetch()) {
            if (!isset($index[$translation->key])) {
                $index[$translation->key] = array();
            }
            $index[$translation->key][$translation->lang] = clone($translation);
        }

        // default display all keys
        $interface->assign('index', $index);
        $interface->setPageTitle('Translations');
        $interface->setTemplate('home.tpl');
        $interface->display('layout.tpl');
    }
}
?>
