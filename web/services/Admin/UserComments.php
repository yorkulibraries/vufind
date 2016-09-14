<?php
/**
 * List user comments.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
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
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */

require_once 'Admin.php';
require_once 'services/MyResearch/lib/Resource.php';
require_once 'services/MyResearch/lib/Comments.php';

/**
 * List user comments.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class UserComments extends Admin
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
        
        if (isset($_REQUEST['delete'])) {
            $comments = new Comments();
            $comments->id = $_REQUEST['delete'];
            if ($comments->find(true)) {
                $comments->delete();
            }
        }
        $commentList = array();
        $db = new DB_DataObject();
        $db->query('select *, c.id as id, c.created as created ' 
                . 'from comments c, user u, resource r '
                . 'where c.user_id=u.id and c.resource_id=r.id '
                . 'order by c.created desc');
        while ($db->fetch()) {
            $commentList[] = clone($db);
        }
        $interface->assign('commentList', $commentList);
        $interface->setPageTitle('User Comments');
        $interface->setTemplate('user-comments.tpl');
        $interface->display('layout-admin.tpl');
    }
}
?>
