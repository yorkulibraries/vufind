<?php
require_once 'Action.php';

/**
 * Feedback Submit action.
 *
 * @category VuFind
 * @package  Controller_Feedback
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Submit extends Action
{
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        
        if (isset($_POST['submit'])) {
            self::doSubmit();
            exit;
        }
        
        self::displayForm();
    }
    
    public static function doSubmit() 
    {
        global $configArray;        
        global $interface;

        $subject = translate('Feedback');
        $interface->assign('pageURL', $_SERVER['HTTP_REFERER']);
        $interface->assign('from', $_REQUEST['from']);
        $interface->assign('like', $_REQUEST['like']);
        $interface->assign('improvement', $_REQUEST['improvement']);
        $body = $interface->fetch('Emails/catalog-feedback.tpl');

        $mail = new VuFindMailer();
        return $mail->send('tuan@yorku.ca', $_REQUEST['from'], $subject, $body);
    }
    
    public static function displayForm()
    {
        global $configArray;
        global $interface;
        
        $interface->setPageTitle('Feedback');
        $interface->setTemplate('submit.tpl');
        if ($_REQUEST['modal']) {
            $interface->assign('modal', $_REQUEST['modal']);
            $interface->display('modal.tpl');
        } else {
            $interface->display('layout.tpl');
        }
    }
}
