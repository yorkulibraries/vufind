<?php
/**
 * Online Payments Report.
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
require_once 'services/MyResearch/lib/Payment.php';
require_once 'services/MyResearch/PayFines.php';
require_once 'sys/VuFindDate.php';
require_once 'sys/Pager.php';

/**
 * List online payments.
 *
 * @category VuFind
 * @package  Controller_Admin
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class OnlinePayments extends Action
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
        $payment_status = '';
        $fines_group = '';
        $from = date('Y-m-d');
        $to = date('Y-m-d');
        $page = 1;
        $limit = 1000;
        if (isset($_REQUEST['from']) && !empty($_REQUEST['from'])) {
            $from = date('Y-m-d', strtotime($_REQUEST['from']));
        }
        if (isset($_REQUEST['to']) && !empty($_REQUEST['to'])) {
        	$to = date('Y-m-d', strtotime($_REQUEST['to']));
        }
        if (isset($_REQUEST['payment_status']) && !empty($_REQUEST['payment_status'])) {
            $payment_status = $_REQUEST['payment_status'];
        }
        if (isset($_REQUEST['fines_group']) && !empty($_REQUEST['fines_group'])) {
            $fines_group = $_REQUEST['fines_group'];
        }
        if (isset($_REQUEST['page']) && !empty($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
        	$page = $_REQUEST['page'];
        }
        $recordStart = ($page - 1) * $limit;
        $payments = array();
        $db = new Payment();
        if (!empty($payment_status)) {
            $db->payment_status = $payment_status;
        }
        if (!empty($fines_group)) {
            $db->fines_group = $fines_group;
        }
        $db->whereAdd("DATE(payment_date) >= '$from'");
        $db->whereAdd("DATE(payment_date) <= '$to'");
        $count = $db->count();
        $db->orderBy('payment_date');
        $db->limit($recordStart, $limit);
        $db->find();
        while ($db->fetch()) {
            $payment = clone($db);
            $request->payment_date = $dateFormat->convertToDisplayDate(
            		'Y-m-d H:i:s', $payment->payment_date
            );
            $payments[] = $payment;
        }
        
        $sumQuery = new Payment();
        $sumSQL = "SELECT SUM(amount) AS total FROM {$sumQuery->__table}";
        if (!empty($from)) {
            $sumSQL .= " WHERE DATE(payment_date) >= '" . $sumQuery->escape($from) . "'";
        }
        if (!empty($to)) {
            $sumSQL .= " AND DATE(payment_date) <= '" . $sumQuery->escape($to) . "'";
        }
        if (!empty($fines_group)) {
            $sumSQL .=  " AND fines_group='" . $sumQuery->escape($fines_group) . "'";
        }
        $sumQuery->query($sumSQL . " AND payment_status='" . Payment::STATUS_INITIATED . "'");
        if ($sumQuery->fetch()) {
            $interface->assign('totalInitiated', $sumQuery->total);
        }
        $sumQuery->query($sumSQL . " AND payment_status='" . Payment::STATUS_APPROVED . "'");
        if ($sumQuery->fetch()) {
            $interface->assign('totalApproved', $sumQuery->total);
        }
        $sumQuery->query($sumSQL . " AND payment_status='" . Payment::STATUS_COMPLETE . "'");
        if ($sumQuery->fetch()) {
            $interface->assign('totalComplete', $sumQuery->total);
        }
        $sumQuery->query($sumSQL . " AND payment_status='" . Payment::STATUS_CANCELLED . "'");
        if ($sumQuery->fetch()) {
            $interface->assign('totalCancelled', $sumQuery->total);
        }
        
        // build url for the pager
        $params = array(
            'from' => $from,
            'to' => $to,
            'payment_status' => $payment_status,
            'fines_group' => $fines_group,
        );
        $link = $configArray['Site']['url'] . '/Admin/OnlinePayments?'
            . http_build_query($params) . '&page=%d';
        $options = array(
        		'totalItems' => $count,
        		'perPage' => $limit,
        		'fileName' => $link
        );
        $pager = new VuFindPager($options);
        $interface->assign('pageLinks', $pager->getLinks());
        $interface->assign('count', $count);
        $interface->assign('from', date('m/d/Y', strtotime($from)));
        $interface->assign('to', date('m/d/Y', strtotime($to)));
        $interface->assign('fromDateDisplay', $dateFormat->convertToDisplayDate('Y-m-d', $from));
        $interface->assign('toDateDisplay', $dateFormat->convertToDisplayDate('Y-m-d', $to));
        $interface->assign('payments', $payments);
        $interface->assign('payment_status', $payment_status);
        $interface->assign('fines_group', $fines_group);
        $interface->assign('receiptBaseURL', PayFines::getReceiptBaseURL());
        $interface->setPageTitle('Online Payments');
        $interface->setTemplate('online-payments.tpl');
        $interface->display('layout-admin.tpl');
    }
}
?>
