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
        
        $results = array();
        
        $q = trim($_REQUEST['q']);
        if ($q) {
            $r = Translation::search($q);
            foreach ($r as $translation) {
                if (!isset($results[$translation->key])) {
                    $results[$translation->key] = array();
                }
                foreach (array_keys($configArray['Languages']) as $language) {
                    $t = new Translation();
                    $t->key = $translation->key;
                    $t->lang = $language;
                    if ($t->find(true)) {
                        $results[$translation->key][$language] = clone($t);
                    }
                }
            }
        } else {
            $translation = new Translation();
            $translation->orderBy('`key`');
            $translation->find();
            while ($translation->fetch()) {
                if (!isset($results[$translation->key])) {
                    $results[$translation->key] = array();
                }
                $results[$translation->key][$translation->lang] = clone($translation);
            }
        }
        
        // default display all keys
        $interface->assign('q', $q);
        $interface->assign('results', $results);
        $interface->setPageTitle('Translations');
        $interface->setTemplate('home.tpl');
        $interface->display('layout.tpl');
    }    
}
?>
