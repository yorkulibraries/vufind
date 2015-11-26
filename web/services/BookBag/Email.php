<?php
/**
 * BookBag Email action.
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
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Action.php';

/**
 * BookBag Email action.
 *
 * @category VuFind
 * @package  Controller_BookBag
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class Email extends Action
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
        
        // Check if user is logged in
        $user = UserAccount::isLoggedIn();
        if (!$user) {
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
                    $_POST['to'], $from, $_POST['message']
                );
                if (!PEAR::isError($result)) {
                    header('Location: ' . $configArray['Site']['url'] . '/BookBag/Home');
                    exit();
                } else {
                    $interface->assign('to', $_POST['to']);
                    $interface->assign('errorMsg', $result->getMessage());
                }
            }
        }
        
        // Display Page
        $interface->assign('from', $from);
        $interface->setPageTitle('Email Marked Items');
        $interface->setTemplate('email.tpl');
        if ($_REQUEST['modal']) {
            $interface->assign('modal', $_REQUEST['modal']);
            $interface->display('modal.tpl');
        } else {
            $interface->display('layout.tpl');
        }
    }
    
    public function sendEmail($to, $from, $message) 
    {
        global $interface;
        
        // Initialise from the current search globals
        $searchObject = SearchObjectFactory::initSearchObject();
        $searchObject->init();
        $searchObject->setSort('title');
        $cart = Cart_Model::getInstance();
        $items = $cart->getItems();
        $records = array();
        if (!empty($items)) {
            $searchObject->setQueryIDs($items);
            $searchObject->setLimit(count($items));
            $result = $searchObject->processSearch(false, true);
            if (PEAR::isError($result)) {
                return $result;
            }
            if (isset($result['response']['docs'])) {
                foreach ($result['response']['docs'] as $doc) {
                    $record = RecordDriverFactory::initRecordDriver($doc);
                    $records[$record->getUniqueId()] = trim($record->getEmail());
                }
            }
        }
        
        $interface->assign('from', $from);
        $interface->assign('records', $records);
        $interface->assign('message', $message);        
        $body = $interface->fetch('Emails/catalog-records.tpl');
        
        require_once 'sys/Mailer.php';
        $subject = translate('Library Catalogue') . ': ' . translate('Marked Items');
        $mail = new VuFindMailer();
        return $mail->send($to, $from, $subject, $body);
    }
}
