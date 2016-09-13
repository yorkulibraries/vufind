<?php
require_once 'services/Translation/TranslationBase.php';

class Edit extends TranslationBase
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
        global $user;
        
        $id = trim($_REQUEST['id']);
        $lang = trim($_REQUEST['lang']);
        $key = trim($_REQUEST['key']);
        $value = trim($_REQUEST['value']);
        
        if (isset($_POST['save'])) {
            $interface->assign('lang', $lang);
            $interface->assign('key', $key);
            
            if (empty($lang)) {
                $interface->assign('errorMsg', 'Language is required.');
                $this->displayForm();
            }
            
            if (empty($key)) {
                $interface->assign('errorMsg', 'Key is required.');
                $this->displayForm();
            }
            
            if ($id) {
                $translation = new Translation();
                $translation->id = $id;
                if ($translation->find(true)) {
                    $translation->value = $value;
                    $translation->verified = 1;
                    $translation->last_modified_by = $user->id;
                    $translation->update();
                    $this->redirectToIndex();
                }
            } else {
                $translation = new Translation();
                $translation->lang = $lang;
                $translation->key = $key;
                $translation->value = $value;
                $translation->verified = 1;
                $translation->last_modified_by = $user->id;
                $translation->insert();
                $this->redirectToIndex();
            }
        } else if (isset($_POST['cancel'])) {
            $this->redirectToIndex();
        } else {
            if ($id) {
                $translation = new Translation();
                $translation->id = $id;
                if ($translation->find(true)) {
                    $interface->assign('lang', $translation->lang);
                    $interface->assign('key', $translation->key);
                    $interface->assign('value', $translation->value);
                    $interface->assign('id', $translation->id);
                    $this->displayForm(); 
                } else {
                    $this->redirectToIndex();
                }
            } else {
                if (!empty($lang) && !empty($key)) {
                    $interface->assign('lang', $lang);
                    $interface->assign('key', $key);
                    $this->displayForm();
                }
            }
            $this->redirectToIndex();
        }
    }
    
    private function displayForm() 
    {
        global $interface;
        
        $interface->setPageTitle('Edit Translation');
        $interface->setTemplate('edit.tpl');
        $interface->display('layout.tpl');
        exit;
    }
}
?>
