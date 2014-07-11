<?php
/**
 * Email action for Search module
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
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'Action.php';
require_once 'sys/Mailer.php';

/**
 * Email action for Search module
 *
 * @category VuFind
 * @package  Controller_Search
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Email extends Action
{
    /**
     * Process incoming parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;
        
        // Check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
            // Needed for login followup:
            if (isset($_REQUEST['lightbox'])) {
                $interface->assign('title', $_REQUEST['message']);
                $interface->assign('message', 'You must be logged in first');
                $interface->assign('followup', true);
                $interface->assign('followupModule', 'Search');
                $interface->assign('followupAction', 'Email');
                return $interface->fetch('AJAX/login.tpl');
            } else {
                $interface->assign('followup', true);
                $interface->assign('followupModule', 'Search');
                $interface->assign('followupAction', 'Email');                    
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
        
        $from = $configArray['Site']['email'];
        if($patron = UserAccount::catalogLogin()) {
            $this->catalog = ConnectionManager::connectToCatalog();
            $profile = $this->catalog->getMyProfile($patron);
            if (!PEAR::isError($profile)) {
                if (strlen(trim($profile['email'])) > 0 ) {
                    $from = trim($profile['email']);
                    $interface->assign('to', $from);
                }
            }
        }

        if (isset($_POST['submit'])) {
            if (strlen(trim($_POST['to'])) == 0) {
                $interface->assign('errorMsg', 'Recipient is required');
            } else {
                $result = $this->sendEmail(
                    $_POST['url'], $_POST['to'], $from, $_POST['message']
                );
                if (!PEAR::isError($result)) {
                    header('Location: ' . $_POST['url']);
                    exit();
                } else {
                    $interface->assign('to', $_POST['to']);
                    $interface->assign('errorMsg', $result->getMessage());
                }
            }
        }
        
        // Display Page
        $interface->assign('from', $from);
        if (isset($_GET['lightbox'])) {
            $interface->assign('title', $_GET['message']);
            return $interface->fetch('Search/email.tpl');
        } else {
            // If the user has disabled HTTP referer, we can't email their search
            // link without Javascript.
            if (!isset($_POST['url']) && !isset($_SERVER['HTTP_REFERER'])) {
                PEAR::raiseError(new PEAR_Error('HTTP Referer missing.'));
                exit();
            }
            // If the user resubmits the form after an error, the $_POST url
            // variable will be set and we should use that.  If this is the first
            // time through, we need to rely on the referer to find out the target.
            $searchURL = isset($_POST['url']) ? 
                $_POST['url'] : $_SERVER['HTTP_REFERER'];
            $interface->setPageTitle('Email This Search');
            $interface->assign('subTemplate', 'email.tpl');
            // For form POST:
            $interface->assign('searchURL', $searchURL);
            // For "back to search" link:
            $interface->assign('lastsearch', $searchURL);
            if ($_REQUEST['modal']) {
                $interface->assign('modal', $_REQUEST['modal']);
                $interface->display('modal.tpl');
            } else {
                $interface->setTemplate('view-alt.tpl');
                $interface->display('layout.tpl');
            }
        }
    }
    
    /**
     * Send a record email.
     *
     * @param string $url     URL to include in message
     * @param string $to      Message recipient address
     * @param string $from    Message sender address
     * @param string $message User-provided message to send
     *
     * @return mixed          Boolean true on success, PEAR_Error on failure.
     * @access public
     */
    public function sendEmail($url, $to, $from, $message)
    {
        global $interface;

        $subject = translate('Library Catalog Search Result');
        $interface->assign('from', $from);
        $interface->assign('message', $message);
        $interface->assign('msgUrl', $url);
        $body = $interface->fetch('Emails/share-link.tpl');

        $mail = new VuFindMailer();
        return $mail->send($to, $from, $subject, $body);
    }
}
?>