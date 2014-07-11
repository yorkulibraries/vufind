<?php
/**
 * UserComments action for Record module
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

require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/Comments.php';

/**
 * UserComments action for Record module
 *
 * @category VuFind
 * @package  Controller_Record
 * @author   Andrew S. Nagy <vufind-tech@lists.sourceforge.net>
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class UserComments extends Record
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
        global $user;
        global $configArray;

        // Process Delete Comment
        if ((isset($_REQUEST['delete'])) && (is_object($user))) {
            $this->deleteComment($_REQUEST['delete'], $user);
        }

        $interface->setPageTitle(translate('Comments'));
        
        if ($_REQUEST['add'] || $_REQUEST['submit']) {
            if (!$user) {
                $interface->assign('recordId', $_REQUEST['id']);
                $interface->assign('followup', true);
                $interface->assign('followupModule', 'Record');
                $interface->assign('followupAction', 'UserComments');
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
            if ($_REQUEST['submit']) {
               $result = $this->saveComment($user);
            }
            $interface->assign('add', $_REQUEST['add']);
            $interface->setPageTitle(translate('Add your comment'));
        }

        // Set Messages
        $interface->assign('infoMsg', $this->infoMsg);
        $interface->assign('errorMsg', $this->errorMsg);

        $this->assignComments();
        $interface->assign('user', $user);
        $interface->assign('subTemplate', 'view-comments.tpl');
        $interface->setTemplate('view.tpl');

        // Display Page
        if ($_REQUEST['modal']) {
            $interface->assign('modal', $_REQUEST['modal']);
            $interface->display('modal.tpl');
        } else {
            $interface->display('layout.tpl'/*, $cacheId */);
        }
    }

    /**
     * Delete a comment
     *
     * @param int    $id   ID of comment to delete
     * @param object $user User whose comment is being deleted.
     *
     * @return bool        True for success, false for failure.
     * @access public
     */
    public static function deleteComment($id, $user)
    {
        $comment = new Comments();
        $comment->id = $id;
        if ($comment->find(true)) {
            if ($user->id == $comment->user_id) {
                $comment->delete();
                return true;
            }
        }
        return false;
    }

    /**
     * Assign comments for the current resource to the interface.
     *
     * @return void
     * @access public
     */
    public static function assignComments($recordId=false)
    {
        global $interface;

        $resource = new Resource();
        $resource->record_id = $recordId ? $recordId : $_REQUEST['id'];
        if ($resource->find(true)) {
            $commentList = $resource->getComments();
            $interface->assign('commentList', $commentList);
        }
    }

    /**
     * Save a user's comment to the database.
     *
     * @param object $user User whose comment is being saved.
     *
     * @return bool        True for success, false for failure.
     * @access public
     */
    public static function saveComment($user)
    {
        // What record are we operating on?
        if (!isset($_REQUEST['id'])) {
            return false;
        }
        
        if (strlen(trim($_REQUEST['comment'])) == 0) {
            return false;
        }

        // record already saved as resource?
        $resource = new Resource();
        $resource->record_id = $_REQUEST['id'];
        if (!$resource->find(true)) {
            $resource->insert();
        }

        $resource->addComment($_REQUEST['comment'], $user);
        self::sendEmail();
        return true;
    }

    public static function sendEmail()
    {
        global $configArray;
        global $interface;
        if ($patron = UserAccount::catalogLogin()) {
            if (!PEAR::isError($patron)) {    
                $catalog = ConnectionManager::connectToCatalog();
                $profile = $catalog->getMyProfile($patron);
                if (!PEAR::isError($profile)) {
                    if (strlen(trim($profile['email'])) > 0 ) {
                        $from = trim($profile['email']);
                    }
                    $interface->assign('profile', $profile);
                }
            }
            if (!$from) {
                $from = $configArray['Site']['email'];
            }
            $subject = 'User Comment';
            $interface->assign('patron', $patron);
            $interface->assign('from', $from);
            $interface->assign('recordID', $_REQUEST['id']);
            $interface->assign('comment', $_REQUEST['comment']);
            $body = $interface->fetch('Emails/catalog-record-comment.tpl');

            $mail = new VuFindMailer();
            return $mail->send($configArray['Site']['email'], $from, $subject, $body);
        }        
    }
}

?>
