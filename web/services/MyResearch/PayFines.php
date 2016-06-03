<?php
/**
 * PayFines action for MyResearch module
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
 * @package  Controller_MyResearch
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
require_once 'services/MyResearch/MyResearch.php';
require_once 'services/MyResearch/lib/Paid_bill.php';
require_once 'services/MyResearch/lib/Payment.php';
require_once 'services/MyResearch/lib/Broker.php';

/**
 * PayFines action for MyResearch module
 *
 * @category VuFind
 * @package  Controller_MyResearch
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/building_a_module Wiki
 */
class PayFines extends MyResearch
{
    public function __construct()
    {
        parent::__construct();
        
        $patron = UserAccount::catalogLogin();
        
        if (!$patron) {
            PEAR::raiseError('Catalogue cannot authenticate user.');
            exit;
        }
        if (PEAR::isError($patron)) {
            PEAR::raiseError('Catalogue cannot authenticate user.');
            exit;
        }
        if (empty($patron['cat_username'])) {
            PEAR::raiseError('Catalogue cannot authenticate user.');
            exit;
        }
        
        $this->patron = $patron;
        $this->logger = $this->getLogger();
    }
    
    /**
     * Process parameters and display the page.
     *
     * @return void
     * @access public
     */
    public function launch()
    {
        global $interface;
        global $configArray;

        if (isset($_POST['pay']) && !empty($_POST['pay'])) {
            $this->doPayAction();
            exit;
        }
        
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            if ($_POST['status'] == 'APPROVED') {
                $this->doCompleteAction();
                exit;
            } else {
                $this->logger->log('Payment not approved. Status is ' . $_POST['status']);
                $this->logger->log('Redirecting to display fines page.');
                $this->redirectToDisplayFines();
                exit;
            }
        }

        // default
        $this->displayItemsToPay();
        exit;
    }
    
    private function doPayAction()
    {
        global $interface;
        global $configArray;
        
        // make sure the fines group is present, if not, send user back to display fines page
        $finesGroup = $this->getFinesGroup();
        if (empty($finesGroup)) {
            $this->redirectToDisplayFines();
            exit;
        }
        
        $this->logger->log('Fetching list of items to pay');
        $itemsToPay = $this->getItemsToPay($finesGroup);
        $this->logger->log($itemsToPay);
        
        // generate a hash to make sure items being paid for are not tempered with
        $hash = $this->generatePaymentHashFromBills($finesGroup, $itemsToPay['items']);
        $this->logger->log('generated payment hash: ' . $hash);
        
        // check if there is a payment transaction already for this hash
        $payment = new Payment();
        $payment->payment_hash = $hash;
        if ($payment->find(true)) {
            $this->logger->log("Found payment record for this hash, id: $payment->id, status: $payment->payment_status");
            $this->logger->log('Do nothing, redirect to display fines.');
            $this->redirectToDisplayFines();
            exit;
        }
        
        // get the application ID and password to the YPB store
        $paymentAppInfo = $this->getPaymentApplicationInfo($finesGroup);
        
        // get the YPB language code corresponding to the current user's selected language
        $ypbLanguage = $configArray['YorkPaymentBroker']['lang_' . $interface->getLanguage()];
        $this->logger->log("Selecting language code: $ypbLanguage for YPB payment page.");
        
        // create payment broker instance
        $broker = new Broker($configArray['YorkPaymentBroker']['wsdl']);
        
        // initialize the order
        $order = $broker->OrderInitialize();
        $order->RequiredInformation->ApplicationId = $paymentAppInfo['ApplicationId'];
        $order->RequiredInformation->ApplicationPassword = $paymentAppInfo['ApplicationPassword'];
        $order->RequiredInformation->TransPaymentType = 'CC-PURCHASE';
        $order->RequiredInformation->Total = $itemsToPay['total'];
        $order->ConfigSettings->UrlSuccess = $this->getPayFinesURL();
        $order->ConfigSettings->UrlFail = $this->getPayFinesURL();
        $order->ConfigSettings->Language = $ypbLanguage;
        $order->ConfigSettings->ShowOrderDetails = true;

        // list all the selected bills in this order
        $countItems = count($itemsToPay['items']);
        $order->Items = $broker->ItemsInitialize($countItems);
        for ($i = 0; $i < $countItems; $i++) {
            $item = $itemsToPay['items'][$i];
            $order->Items[$i]->ItemId = $item['bill_key'];
            $order->Items[$i]->Price = number_format($item['balance'], 2);
            $order->Items[$i]->Quantity = 1;
            $order->Items[$i]->Description = $item['bill_reason'] . ' ' . $item['title'];
        }

        // get the transaction token
        $tokenId = $broker->GetToken($order);
        $this->logger->log('Got token ID: ' . $tokenId);

        // create this payment-in-progress record
        $paymentId = $this->initPaymentRecord($tokenId, $hash, $itemsToPay['total']);
        
        // create the paid bill records
        $this->initPaidBillRecords($paymentId, $itemsToPay);

        // send the user to the YPB payment page 
        $this->logger->log('Redirecting to YPB payment page');
        header('Location: ' . $configArray['YorkPaymentBroker']['payment_url'] . $tokenId);
        exit;
    }
    
    private function doCompleteAction()
    {
        global $interface;
        global $configArray;
                
        $this->logger->log('Got POST back from YPB payment page');

        $tokenId = $_POST['tokenid'];
        
        // find the payment record for this tokenId
        $userBarcode = $this->patron['cat_username'];
        $payment = new Payment();
        $payment->tokenid = $tokenId;
        $payment->user_barcode = $userBarcode;
        if (!$payment->find(true)) { 
            $this->logger->log("No valid payment record found for token: $tokenId, user barcode: $userBarcode", PEAR_LOG_EMERG);
            $this->logger->log('Redirecting to display fines page...');
            $this->redirectToDisplayFines();
            exit;
        }

        // verify that the payment has been processed successfully, otherwise abort/cancel the payment
        $verified = $this->verifyPayment($payment->tokenid);
        if ($verified === false || !is_array($verified)) {
            $this->logger->log('Payment cannot be verified for token: ' . $payment->tokenid, PEAR_LOG_EMERG);
            
            $this->abortPayment($payment);
            
            $this->logger->log('Redirecting to display fines page...');
            $this->redirectToDisplayFines();
            exit;
        }
        
        // payment has been approved, update status
        $this->paymentApproved($payment, $verified);
        
        // complete the approved payment
        $this->completeApprovedPayment($payment);

        // and we're done
        $this->logger->log('All done. Redirecting to MyResearch/Fines page');
        $this->redirectToDisplayFines();
        exit;
    }
    
    private function getFinesGroup()
    {
        $group = $_REQUEST['g'];
        return $group;
    }
    
    private function getItemsToPay($group)
    {
        $allBills = $this->getAllBills($group);
        $unpaidBills = $this->getUnpaidBills($allBills);        
        $selectedBills = $this->getSelectedBills($unpaidBills);

        $count = count($selectedBills);
        $total = 0.00;
        for ($i = 0; $i < $count; $i++) {
            $record = $this->db->getRecord($selectedBills[$i]['id']);
            $selectedBills[$i]['title'] = $record ? $record['title'] : null;
            $total += $selectedBills[$i]['balance'];
        }
        
        return array('group' => $group, 'total' => $total, 'items' => $selectedBills);
    }
    
    private function displayItemsToPay()
    {
        global $interface;
        global $configArray;
        
        // make sure the fines group is present, if not, send user back to display fines page
        $finesGroup = $this->getFinesGroup();
        if (empty($finesGroup)) {
            $this->redirectToDisplayFines();
            exit;
        }
        
        // get items to pay
        $itemsToPay = $this->getItemsToPay($finesGroup);
        
        if (empty($itemsToPay['items'])) {
            $this->redirectToDisplayFines();
            exit;
        }
        
        $interface->assign('confirming', (isset($_POST['confirm']) && !empty($_POST['confirm'])));
        $interface->assign('items', $itemsToPay['items']);
        $interface->assign('total', $itemsToPay['total']);
        $interface->assign('group', $itemsToPay['group']);
        $interface->setTemplate('pay-fines.tpl');
        $interface->setPageTitle('Pay Fines');
        $interface->display('layout.tpl');
        exit;
    }
    
    private function getPayFinesURL() 
    {
        global $configArray;
        return $configArray['Site']['url'] . '/MyResearch/PayFines';
    }
    
    private function redirectToDisplayFines()
    {
        global $configArray;
        
        header('Location: ' . $configArray['Site']['url'] . '/MyResearch/Fines');
        exit;
    }
    
    private function initPaymentRecord($tokenId, $hash, $total)
    {
        $this->logger->log('Inserting payment-in-progress into VuFind db');
        $payment = new Payment();
        $payment->tokenid = $tokenId;
        $payment->payment_hash = $hash;
        $payment->payment_date = date('Y-m-d H:i:s');
        $payment->amount = $total;
        $payment->user_barcode = $this->patron['cat_username'];
        $payment->payment_status = Payment::STATUS_INITIATED;
        $payment->fines_group = $this->getFinesGroup();
        $paymentId = $payment->insert();
        if (!$paymentId) {
            $this->logger->log("Got error while inserting into payment table", PEAR_LOG_EMERG);
            PEAR::raiseError('Unable to save payment to database.');
        }
        return $paymentId;
    }
    
    private function initPaidBillRecords($paymentId, $itemsToPay)
    {
        $this->logger->log('Inserting the bills payment-in-progress into VuFind db');
        foreach ($itemsToPay['items'] as $item) {
            $this->logger->log($item);
            $paid = new Paid_bill();
            $paid->bib_id = $item['id'];
            $paid->user_key = $item['user_key'];
            $paid->user_barcode = $item['user_barcode'];
            $paid->item_barcode = $item['item_barcode'];
            $paid->item_title = $item['title'];
            $paid->item_library = $item['item_library'];
            $paid->bill_key = $item['bill_key'];
            $paid->bill_number = $item['bill_number'];
            $paid->bill_date = date('Y-m-d H:i:s', $item['date_billed_raw']);
            $paid->bill_reason = $item['fine'];
            $paid->bill_library = $item['library'];
            $paid->bill_balance = $item['balance'];
            $paid->payment_id = $paymentId;
            $paid->payment_status = Payment::STATUS_INITIATED;
            if (!$paid->insert()) {
                $this->logger->log("Got error while inserting into paid_bill table", PEAR_LOG_EMERG);
                PEAR::raiseError('Unable to save paid bill record to database.');
            }
        }
    }
    
    private function getAllBills($group)
    {
        $bills = $this->catalog->getMyFines($this->patron);
        if (PEAR::isError($bills)) {
            PEAR::raiseError($bills);
        }
        return $bills[$group]['items'];
    }
    
    private function getUnpaidBills($bills)
    {
        $paidBills = array();
        $p = new Paid_bill();
        $p->user_key = $this->patron['user_key'];
        $p->find();
        while ($p->fetch()) {
            if ($p->payment_status != Payment::STATUS_CANCELLED) {
                $paidBills[] = $p->bill_key;
            }
        }
        
        $unpaid = array();
        foreach ($bills as $b) {
            if (!in_array($b['bill_key'], $paidBills)) {
                $unpaid[] = $b;
            }
        }
        
        return $unpaid;
    }
    
    private function getSelectedBills($bills)
    {
        // if an array of selected items is available, then only show those
        $items = $bills;
        $selected = $_POST['selected'];
        if (is_array($selected) && !empty($selected)) {
            $selectedItems = array();
            foreach ($bills as $item) {
                if (in_array($item['bill_key'], $selected)) {
                    $selectedItems[] = $item;
                }
            }
            $items = $selectedItems;
        }
        return $items;
    }

    protected function paymentApproved($payment, $verified)
    {
        $this->logger->log('Payment APPROVED. Updating payment record for token: ' . $payment->tokenid);
        $payment->payment_status = Payment::STATUS_APPROVED;
        if (isset($verified['authcode'])) {
            $payment->authcode = $verified['authcode'];
        }
        if (isset($verified['refnum'])) {
            $payment->refnum = $verified['refnum'];
        }
        if (isset($verified['amount'])) {
            $payment->amount = $verified['amount'];
        }
        if (isset($verified['ypborderid'])) {
            $payment->ypborderid = $verified['ypborderid'];
        }
        $payment->update();
    }
    
    protected function abortPayment($payment)
    {
        $this->logger->log('Aborting/cancelling payment token: ' . $payment->tokenid);
        $pb = $payment->getPaidBills();
        foreach ($pb as $b) {
            $b->payment_status = Payment::STATUS_CANCELLED;
            // because bill_key must be unique, we append the payment ID to the bill_key
            // this will ensure future payments for this bill will be possible
            $b->bill_key = $b->bill_key . '|' . $payment->id;
            $b->update();
        }
        $payment->payment_status = Payment::STATUS_CANCELLED;
        // because payment hash must be unique, we append the payment ID to the payment hash
        // this will ensure future payments for the same bills will be possible
        $payment->payment_hash = $payment->payment_hash . '|' . $payment->id;
        $payment->update();
    }
    
    protected function getLogger()
    {
        global $configArray;
        
        // start logger using username as file name
        $logFile = $configArray['Fines']['payment_log_dir'] . '/' . date('Ymd') . '/' . $this->patron['cat_username'] . '.log';
        $logger = Log::singleton('file', $logFile);
        
        return $logger;
    }
    
    protected function completeApprovedPayment($payment)
    {
        $this->logger->log('Begin completion process for payment ID: ' . $payment->id);
        
        if ($payment->payment_status != Payment::STATUS_APPROVED) {
            $this->logger->log('Cannot complete a payment with status ' . $payment->payment_status);
            return;
        }
        
        // get the bills associated with this payment
        $this->logger->log('Fetching bills associated with payment ID:' . $payment->id);
        $paidBills = $payment->getPaidBills();
        $this->logger->log('There are ' . count($paidBills) . ' bills associated with this payment record.');
        
        // get the bills keys 
        $billKeys = array();
        foreach ($paidBills as $b) {
            $billKeys[] = $b->bill_key;
        }
        $this->logger->log($billKeys);
        
        // regenerate payment hash using the above bill keys and the approved payment amount
        $hash = $this->generatePaymentHash($payment->fines_group, $billKeys, $payment->amount);
        $this->logger->log("Generated payment hash: $hash");
        $this->logger->log("Saved payment hash: " . $payment->payment_hash);
        
        // verify the saved hash and the generated hash are identical
        if ($hash !== $payment->payment_hash) {
            $this->logger->log('Saved payment hash !== generated hash. Something went very wrong.', PEAR_LOG_EMERG);
            $this->logger->log('Cannot complete payment ID: ' . $payment->id);
        } else {            
            // send pay bills transaction to Symphony
            $this->sendPayBillsToSymphony($payment->fines_group, $paidBills, $payment->tokenid);
            
            // update payment status to COMPLETE
            $payment->payment_status = Payment::STATUS_COMPLETE;
            $payment->update();
            
            // tell YPB we got it
            $this->acknowledgeComplete($payment->fines_group, $payment->ypborderid, $payment->tokenid);
            
            $this->logger->log('Payment ID: ' . $payment->id . ' is COMPLETE.');
        }
    }
    
    protected function generatePaymentHashFromBills($finesGroup, $bills) 
    {
        $billKeys = array();
        $total = 0.00;
        foreach ($bills as $b) {
            $billKeys[] = $b['bill_key'];
            $total += $b['balance'];
        }
        
        return $this->generatePaymentHash($finesGroup, $billKeys, $total);
    }
    
    protected function generatePaymentHash($finesGroup, $billKeys, $total)
    {
        sort($billKeys);
        $hashData = $finesGroup . ',' . $this->patron['cat_username'] 
            . ',' . implode($billKeys) . ',' . number_format($total, 2);
        
        $this->logger->log("Generating hash from input string: $hashData");
        return hash('sha256', $hashData);
    }
    
    protected function getPaymentApplicationInfo($finesGroup)
    {
        global $configArray;
        
        $sAccounts = $configArray['Fines']['ypb_accounts'];
        foreach ($sAccounts as $s) {
            list($key, $val) = explode(':', $s);
            list($id, $pass) = explode(',', $val);
            if ($key == $finesGroup) {
                return array('ApplicationId' => $id, 'ApplicationPassword' => $pass);
            }
        }
        return false;
    }
    
    protected function acknowledgeComplete($finesGroup, $ypbOrderId, $tokenId)
    {
        global $configArray;
        
        // get the application ID and password to the YPB store
        $paymentAppInfo = $this->getPaymentApplicationInfo($finesGroup);
        
        // create payment broker instance
        $broker = new Broker($configArray['YorkPaymentBroker']['wsdl']);
        
        // tell YPB we got it
        if (!empty($ypbOrderId)) {
            $this->logger->log('Sending Acknowlege Complete to YPB for ypborderid: ' . $ypbOrderId);
            $result = $broker->AcknowledgeComplete($paymentAppInfo['ApplicationId'], $paymentAppInfo['ApplicationPassword'], $ypbOrderId);
        } else {
            $this->logger->log('Sending Acknowlege Complete to YPB for tokenid: ' . $tokenId);
            $result = $broker->AcknowledgeComplete($paymentAppInfo['ApplicationId'], $paymentAppInfo['ApplicationPassword'], $tokenId);
        }
        
        $this->logger->log($result);
    }
    
    protected function verifyPayment($tokenId)
    {
        global $interface;
        global $configArray;
        
        // there is no documented SOAP API to verify that a transaction has been approved
        // so we have to resort to screen scraping the YPB receipt page for this, at least for now.
        $receiptURL = $configArray['YorkPaymentBroker']['receipt_url'] . $tokenId;
        $approvedMessage = $configArray['YorkPaymentBroker']['approved_message_' . $interface->getLanguage()];
        $refNumRegex = $configArray['YorkPaymentBroker']['ref_num_regex_' . $interface->getLanguage()];
        $authCodeRegex = $configArray['YorkPaymentBroker']['auth_code_regex_' . $interface->getLanguage()];
        $amountRegex = $configArray['YorkPaymentBroker']['amount_regex_' . $interface->getLanguage()];
        $ypbOrderIdRegex = $configArray['YorkPaymentBroker']['ypborderid_regex_' . $interface->getLanguage()];

        $this->logger->log("Verifying token $tokenId with YPB receipt page.");
        $this->logger->log("Requesting YPB receipt page at: $receiptURL");
        $html = file_get_contents($receiptURL);
        $this->logger->log($html, PEAR_LOG_DEBUG);
        
        $approved = (stripos($html, $approvedMessage) !== false);
        
        if ($approved) {
            $this->logger->log("$tokenId status is APPROVED");
            preg_match($refNumRegex, $html, $refNumMatches);
            preg_match($authCodeRegex, $html, $authCodeMatches);
            preg_match($amountRegex, $html, $amountMatches);
            $refnum = $refNumMatches[1];
            $authcode = $authCodeMatches[1];
            $amount = $amountMatches[1];

            // get the ypborderid from Payment page
            $this->logger->log('Trying to get the ypborderid from YPB payment page');
            $paymentURL = $configArray['YorkPaymentBroker']['payment_url'] . $tokenId;
            $this->logger->log("Requesting YPB payment page at: $paymentURL");
            $html = file_get_contents($paymentURL);
            $this->logger->log($html, PEAR_LOG_DEBUG);
            preg_match($ypbOrderIdRegex, $html, $ypbOrderIdMatches);
            $ypborderid = $ypbOrderIdMatches[1];
            
            $result = array(
                'refnum' => $refnum,
                'authcode' => $authcode,
                'amount' => $amount,
                'ypborderid' => $ypborderid,
            );
            
            $this->logger->log($result);
            return $result;
        }
        $this->logger->log("Cannot verify token: $tokenId");
        return false;
    }
    
    protected function sendPayBillsToSymphony($finesGroup, $paidBills, $tokenId)
    {
        global $configArray;
        
        $this->logger->log('Sending pay bill requests to Symphony...');
        
        // extract required configuration values
        $apiUser = $apiStation = $apiLibrary = null;
        foreach ($configArray['Fines']['api_user'] as $s) {
            list($group, $values) = explode(':', $s);
            if ($group == $finesGroup) {
                list($apiUser, $apiStation, $apiLibrary) = explode(',', $values);
                break;
            }
        }
        $paymentType = $configArray['Fines']['payment_type'];
        
        $this->logger->log("Using configured apiUser: $apiUser, apiStation: $apiStation, apiLibrary: $apiLibrary, paymentType: $paymentType");
        
        $results = $this->catalog->payBills($paidBills, $tokenId, $apiUser, $apiStation, $apiLibrary, $paymentType);
        
        $this->logger->log($results);
        
        // deal with the results
        foreach ($results as $key => $result) {            
            if (!$result['api_successful']) {
                $api_response = $result['api_response'];
                $this->logger->log("Got error while paying bill $key $api_response", PEAR_LOG_EMERG);
            }
            $pb = new Paid_bill();
            $pb->bill_key = $key;
            if($pb->find(true)) {
                $pb->api_request = $result['api_request'];
                $pb->api_response = $result['api_response'];
                $pb->api_successful = $result['api_successful'];
                $pb->payment_status = Payment::STATUS_COMPLETE;
                $pb->update();
            } else {
                $this->logger->log("paid bill $key not found in db", PEAR_LOG_EMERG);
            }
        }
    }
    
    protected function verifyInitiatedPayments()
    {
        $payment = new Payment();
        $payment->user_barcode = $this->patron['cat_username'];
        $payment->payment_status = Payment::STATUS_INITIATED;
        $payment->find();
        while ($payment->fetch()) {
            $tokenId = $payment->tokenid;
            $verified = $this->verifyPayment($tokenId);
            if ($verified !== false && is_array($verified)) {
                $this->paymentApproved($payment, $verified);
            } else {
                $this->abortPayment($payment);
            }
        }
    }
        
    protected function getApprovedPayments()
    {
        $payments = array();
        $payment = new Payment();
        $payment->user_barcode = $this->patron['cat_username'];
        $payment->payment_status = Payment::STATUS_APPROVED;
        $payment->find();
        while ($payment->fetch()) {
            $payments[] = clone($payment);
        }
        return $payments;
    }
    
    protected function getPayments()
    {
        $payment = new Payment();
        $payment->user_barcode = $this->patron['cat_username'];
        $payment->orderBy('payment_date DESC');
        $payment->find();
        $payments = array();
        while ($payment->fetch()) {
            $payments[] = clone($payment);
        }
        return $payments;
    }
    
    protected function completeApprovedPayments($payments)
    {
        foreach ($payments as $payment) {
            $this->completeApprovedPayment($payment);
        }
    }
    
    static function getReceiptBaseURL()
    {
        global $configArray;
        return $configArray['YorkPaymentBroker']['receipt_url'];
    }
}

?>