<?php
/**
 * List material requests.
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

require_once 'Action.php';
require_once 'services/MyResearch/lib/Requests.php';
require_once 'sys/VuFindDate.php';
require_once 'sys/Pager.php';

/**
 * List material requests.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class MaterialRequests extends Action
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
        
        $dateFormat = new VuFindDate();
        $request_type = '';
        $from = date('Y-m-d');
        $to = date('Y-m-d');
        $page = 1;
        $limit = 200;
        if (isset($_REQUEST['from']) && !empty($_REQUEST['from'])) {
            $from = date('Y-m-d', strtotime($_REQUEST['from']));
        }
        if (isset($_REQUEST['to']) && !empty($_REQUEST['to'])) {
        	$to = date('Y-m-d', strtotime($_REQUEST['to']));
        }
        if (isset($_REQUEST['request_type']) && !empty($_REQUEST['request_type'])) {
            $request_type = $_REQUEST['request_type'];
        }
        if (isset($_REQUEST['page']) && !empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
        	$page = $_REQUEST['page'];
        }
        $recordStart = ($page - 1) * $limit;
        $requests = array();
        $db = new Requests();
        if (!empty($request_type)) {
            $db->request_type = $request_type;
        }
        $db->whereAdd("DATE(created) >= '$from'");
        $db->whereAdd("DATE(created) <= '$to'");
        $count = $db->count();
        $db->orderBy('created');
        $db->limit($recordStart, $limit);
        $db->find();
        while ($db->fetch()) {
            $request = clone($db);
            $request->created = $dateFormat->convertToDisplayDate(
            		'Y-m-d H:i:s', $request->created
            );
            $request->expiry = $dateFormat->convertToDisplayDate(
            		'Y-m-d H:i:s', $request->expiry
            );
            $requests[] = $request;
        }
        // build url for the pager
        $params = array(
            'from' => $from,
            'to' => $to,
            'request_type' => $request_type
        );
        $link = $configArray['Site']['url'] . '/Admin/MaterialRequests?'
            . http_build_query($params) . '&page=%d';
        $options = array(
        		'totalItems' => $count,
        		'perPage' => $limit,
        		'fileName' => $link
        );
        $pager = new VuFindPager($options);
        $interface->assign('pageLinks', $pager->getLinks());
        $interface->assign('count', $count);
        $interface->assign('request_type', $request_type);
        $interface->assign('from', date('m/d/Y', strtotime($from)));
        $interface->assign('to', date('m/d/Y', strtotime($to)));
        $interface->assign('fromDateDisplay', $dateFormat->convertToDisplayDate('Y-m-d', $from));
        $interface->assign('toDateDisplay', $dateFormat->convertToDisplayDate('Y-m-d', $to));
        $interface->assign('requests', $requests);
        $interface->setPageTitle('Material Requests');
        $interface->setTemplate('material-requests.tpl');
        $interface->display('layout-admin.tpl');
    }
}
?>
