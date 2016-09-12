<?php
require_once 'services/Translation/TranslationBase.php';
require_once 'sys/Pager.php';

class Populate extends TranslationBase
{
    /**
     * Process parameters and display the response.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        
        foreach ($configArray['Languages'] as $langCode => $langName) {
            $this->loadFromFile($langCode);
        }
        
        $this->redirectToIndex();   
    }
    
    private function loadFromFile($langCode)
    {
        global $user;
        
        $translator = new I18N_Translator('lang', $langCode, false, false);
        foreach ($translator->words as $key => $value) {
            $translation = new Translation();
            $translation->key = $key;
            $translation->lang = $langCode;
            $exists = $translation->find(true);
            
            $translation->value = $value;
            $translation->verified = 1;
            // if language is not 'en' and key and value are the same the we need to flag it 
            if ($translation->lang != 'en' && $translation->key == $translation->value) {
                $translation->verified = 0;
            }
            $translation->last_modified_by = $user->id;
            
            if ($exists) {
                $translation->insert();
            } else {
                $translation->update();
            }
        }
    }
}
?>
