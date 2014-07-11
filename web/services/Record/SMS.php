<?php
/**
 * SMS (text messaging) action for Record module
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2007.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Record.php';

require_once 'sys/Mailer.php';

/**
 * SMS (text messaging) action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class SMS extends Record
{
    private $_sms;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        parent::__construct();
        $this->_sms = new SMSMailer();
    }

    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $configArray;
        global $interface;
        
        // Check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
            // Needed for "back to record" link in view-alt.tpl:
            $interface->assign('id', $_REQUEST['id']);
            // Needed for login followup:
            $interface->assign('recordId', $_REQUEST['id']);
            if (isset($_REQUEST['lightbox'])) {
                $interface->assign('title', $_REQUEST['message']);
                $interface->assign('message', 'You must be logged in first');
                $interface->assign('followup', true);
                $interface->assign('followupModule', 'Record');
                $interface->assign('followupAction', 'SMS');
                return $interface->fetch('AJAX/login.tpl');
            } else {
                $interface->assign('followup', true);
                $interface->assign('followupModule', 'Record');
                $interface->assign('followupAction', 'SMS');                    
                $interface->setPageTitle('Login');
                $interface->assign('message', 'You must be logged in first');
                $interface->assign('subTemplate', '../MyResearch/login.tpl');
                if ($_REQUEST['modal']) {
                    $interface->assign('followupURL', $_SERVER['PHP_SELF']);
                    $interface->assign('followupQueryString', $_SERVER['QUERY_STRING']);
                    $interface->assign('modal', $_REQUEST['modal']);
                    $interface->display('modal.tpl');
                } else {
                    $interface->setTemplate('view-alt.tpl');
                    $interface->display('layout.tpl');
                }
            }
            exit();
        }
        
        if($patron = UserAccount::catalogLogin()) {
            $profile = $this->catalog->getMyProfile($patron);
            if (!PEAR::isError($profile)) {
                if (strlen(trim($profile['phone'])) > 0 ) {
                    $interface->assign('to', trim($profile['phone']));
                }
            }
        }

        if (isset($_POST['submit'])) {
            $result = $this->sendSMS();
            if (PEAR::isError($result)) {
                $interface->assign('error', $result->getMessage());
            }
            $interface->assign('subTemplate', 'sms-status.tpl');
            $interface->setTemplate('view-alt.tpl');
            $interface->display('layout.tpl');
        } else {
            return $this->_displayForm();
        }
    }

    /**
     * Display the blank SMS form.
     *
     * @return void
     * @access private
     */
    private function _displayForm()
    {
        global $interface;

        $interface->assign('carriers', $this->_sms->getCarriers());
        $interface->assign(
            'formTargetPath', '/Record/' . urlencode($_REQUEST['id']) . '/SMS'
        );

        if (isset($_GET['lightbox'])) {
            // Use for lightbox
            $interface->assign('title', $_REQUEST['message']);
            return $interface->fetch('Record/sms.tpl');
        } else {
            // Display Page
            $interface->setPageTitle('SMS');
            $interface->assign('subTemplate', 'sms.tpl');
            if ($_REQUEST['modal']) {
                $interface->assign('modal', $_REQUEST['modal']);
                $interface->display('modal.tpl');
            } else {
                $interface->setTemplate('view-alt.tpl');
                $interface->display('layout.tpl', 'RecordSMS' . $_REQUEST['id']);
            }
        }
    }

    /**
     * Send the SMS message by email.
     *
     * @return mixed Boolean true on success, PEAR_Error on failure.
     * @access public
     */
    public function sendSMS()
    {
        global $configArray;
        global $interface;

        // Get Holdings
        $holdings = $this->catalog->getStatus($_REQUEST['id']);
        if (PEAR::isError($holdings)) {
            return $holdings;
        }

        $interface->assign('callnumber', $holdings[0]['callnumber']);
        $interface->assign('location', $holdings[0]['location']);
        $interface->assign('title', $this->recordDriver->getBreadcrumb());
        $interface->assign('recordID', $_REQUEST['id']);
        $message = $interface->fetch('Emails/catalog-sms.tpl');

        return $this->_sms->text(
            $_REQUEST['provider'], $_REQUEST['to'], $configArray['Site']['email'],
            $message
        );
    }
}
?>