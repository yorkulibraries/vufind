<?php
/**
 * Worker for Gearman
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2009.
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
 * @package  Utilities
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki Wiki
 */
ini_set('memory_limit', '50M');
ini_set('max_execution_time', '0');

/**
 * Set up util environment
 */
require_once 'util.inc.php';
require_once 'sys/ConnectionManager.php';
require_once 'services/MyResearch/lib/Paid_bill.php';
require_once 'services/MyResearch/lib/Payment.php';
require_once 'sys/Logger.php';

// Read Config file
$configArray = readConfig();
date_default_timezone_set($configArray['Site']['timezone']);

// use VuFind logger for the worker general logging
$workerLogger = new Logger();
$workerLogger->log('VuFind gearman worker starting', PEAR_LOG_NOTICE);

$worker = new GearmanWorker();
$worker->addServer();

$worker->addFunction('sendPayBillsToSymphony', 'sendPayBillsToSymphony');

while ($worker->work());


function sendPayBillsToSymphony($job)
{
    global $configArray;
    global $logger;
    
    // get the workload and decode it
    $workload = json_decode($job->workload());
    
    // for each job, use a logger with file name based on date/userid
    $logFile = $configArray['Fines']['payment_log_dir'] . '/' . date('Ymd') . '/' . $workload->userBarcode . '.log';
    $logger = Log::singleton('file', $logFile);
    $logger->setMask(Log::UPTO($logger->stringToPriority($configArray['Fines']['log_level'])));
        
    $logger->log('Begin processing job ID: ' . $job->unique());
    
    // Setup Local Database Connection
    ConnectionManager::connectToDatabase();

    // Connect to ILS
    $catalog = ConnectionManager::connectToCatalog();
    
    // find the payment record
    $logger->log('Looking for payment ID: ' . $workload->paymentId . ' in VuFind DB');
    $payment = new Payment();
    $payment->id = $workload->paymentId;
    if ($payment->find(true)) {
        $logger->log('Found a payment record with ID: ' . $payment->id);   
    } else {
        $logger->log('No payment record found with ID: ' . $workload->paymentId, PEAR_LOG_EMERG);
        return;
    }
    
    // verify that the tokenId matches the job ID and the saved tokenId 
    if ($workload->tokenId != $job->unique() || $payment->tokenid != $workload->tokenId) {
        $logger->log('Job ID != workload/saved tokenId - something is very wrong, abort.', PEAR_LOG_EMERG);
        return;
    }
    
    // get the bills associated with this payment
    $logger->log('Fetching bills associated with payment ID:' . $payment->id);
    $paidBills = $payment->getPaidBills();
    $billCount = count($paidBills);
    $logger->log('There are ' . $billCount . ' bills associated with this payment record.');
    
    // only process the ones that have not been sent to Symphony
    $billsToSend = array();
    $alreadySuccessfulCount = 0;
    foreach ($paidBills as $b) {
        if ($b->api_successful) {
            $alreadySuccessfulCount++;
        } else {
            $billsToSend[] = $b;
        }
    }
    $sendingCount = count($billsToSend);
    $logger->log('There are ' . $sendingCount . ' pay bills transaction to be sent to Symphony.');
    
    if ($sendingCount > 0) {
        // extract required configuration values
        $apiUser = $apiStation = $apiLibrary = null;
        foreach ($configArray['Fines']['api_user'] as $s) {
            list($group, $values) = explode(':', $s);
            if ($group == $payment->fines_group) {
                list($apiUser, $apiStation, $apiLibrary) = explode(',', $values);
                break;
            }
        }
        $paymentType = $configArray['Fines']['payment_type'];
    
        $logger->log('Sending pay bill requests to Symphony...');
        $logger->log("Using configured apiUser: $apiUser, apiStation: $apiStation, apiLibrary: $apiLibrary, paymentType: $paymentType");
        $results = $catalog->payBills($billsToSend, $payment->tokenid, $apiUser, $apiStation, $apiLibrary, $paymentType);
        $logger->log($results);
    
        // deal with the results
        $successCount = 0;
        foreach ($results as $key => $result) {
            $api_response = $result['api_response'];
            if ($result['api_successful']) {
                $successCount++;
                $logger->log("Pay bill API successful for bill $key");
            } else {
                $logger->log("Pay bill API NOT successful for bill $key");
            }
            $logger->log($api_response);
            $pb = new Paid_bill();
            $pb->bill_key = $key;
            if($pb->find(true)) {
                $pb->api_request = $result['api_request'];
                $pb->api_response = $result['api_response'];
                $pb->api_successful = $result['api_successful'];
                if ($result['api_successful']) {
                    $pb->payment_status = Payment::STATUS_COMPLETE;
                }
                $pb->update();
            } else {
                $logger->log("paid bill $key not found in db");
            }
        }
    
        if ($successCount == $sendingCount) {
            $logger->log("All $sendingCount transactions successfully sent to Symphony.");
        
            // update payment status to COMPLETE
            $payment->payment_status = Payment::STATUS_COMPLETE;
            $payment->notified_user = 0;
            $payment->update();
        
            $logger->log('Payment ID: ' . $payment->id . ' status updated to: ' . $payment->payment_status);
        } else {
            $logger->log('There were errors while sending pay bill transactions to Symphony.');
            $logger->log("$successCount out of $sendingCount transactions were successful");
        
            // update payment status to PARTIALLY COMPLETED
            $payment->payment_status = Payment::STATUS_PARTIALLY_COMPLETED;
            $payment->notified_user = 0;
            $payment->update();
        
            $logger->log('Payment ID: ' . $payment->id . ' status updated to: ' . $payment->payment_status);
        }
    } else {
        if ($alreadySuccessfulCount == $billCount) {
            $logger->log("All $billCount transactions were already successfully sent to Symphony.");
            
            // update payment status to COMPLETE
            $payment->payment_status = Payment::STATUS_COMPLETE;
            $payment->notified_user = 0;
            $payment->update();
        
            $logger->log('Payment ID: ' . $payment->id . ' status updated to: ' . $payment->payment_status);
        }
    }
    $logger->log('Finished processing job ID: ' . $job->unique());
}


?>
