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
        
        $id = $_REQUEST['id'];
        
        if (isset($_POST['save'])) {
            // TODO: validate input
            
            if ($id) {
                $translation = new Translation();
                $translation->id = $id;
                if ($translation->find(true)) {
                    $translation->value = $_POST['value'];
                    $translation->verified = 1;
                    $translation->last_modified_by = $user->id;
                    $translation->update();
                    $this->redirectToIndex();
                }
            } else {
                $translation = new Translation();
                $translation->lang = $_POST['lang'];
                $translation->key = $_POST['key'];
                $translation->value = $_POST['value'];
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
                }                
            } else {
                $interface->assign('lang', $_REQUEST['lang']);
                $interface->assign('key', $_REQUEST['key']);
            }
            $interface->setPageTitle('Edit Translation');
            $interface->setTemplate('edit.tpl');
            $interface->display('layout.tpl');
        }
    }
}
?>
