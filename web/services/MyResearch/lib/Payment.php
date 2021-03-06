<?php
/**
 * Table Definition for payment
 */
require_once 'DB/DataObject.php';

require_once 'services/MyResearch/lib/Paid_bill.php';

class Payment extends DB_DataObject 
{
    const STATUS_INITIATED = 'INITIATED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_PARTIALLY_COMPLETED = 'PARTIALLY COMPLETED';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_CANCELLED = 'CANCELLED';
    
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'payment';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $amount;                          // real(12)  not_null
    public $tokenid;                         // string(50)  not_null unique_key multiple_key
    public $authcode;                        // string(8)  
    public $refnum;                          // string(18)  unique_key
    public $payment_date;                    // datetime(19)  not_null binary
    public $payment_hash;                    // string(64)  not_null unique_key
    public $payment_status;                  // string(20)  not_null
    public $user_barcode;                    // string(14)  not_null
    public $fines_group;                     // string(40)  not_null
    public $ypborderid;                      // string(50)
    public $notified_user;                   // int(6)  not_null
    public $status;                          // string(50)  
    public $message;                         // string(100)

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public function getPaidBills()
    {
        $paidBills = array();
        $pb = new Paid_bill();
        $pb->payment_id = $this->id;
        $pb->find();
        while ($pb->fetch()) {
            $paidBills[] = clone($pb);
        }
        return $paidBills;
    }
    
    public static function getApprovedPayments($userId, $order=null)
    {   
        return self::getPayments($userId, $order, Payment::STATUS_APPROVED);
    }
    
    public static function getPartiallyCompletedPayments($userId, $order=null)
    {   
        return self::getPayments($userId, $order, Payment::STATUS_PARTIALLY_COMPLETED);
    }
    
    public static function getInitiatedPayments($userId, $order=null)
    {   
        return self::getPayments($userId, $order, Payment::STATUS_INITIATED);
    }
    
    public static function getPayments($userId, $order=null, $status=null, $notified=null)
    {
        $payment = new Payment();
        $payment->user_barcode = $userId;
        if (!empty($status)) {
            $payment->payment_status = $status;
        }
        if (!empty($order)) {
            $payment->orderBy($order);
        }
        if (!is_null($notified)) {
            $payment->notified_user = $notified ? 1 : 0;
        }
        $payment->find();
        $payments = array();
        while ($payment->fetch()) {
            $payments[] = clone($payment);
        }
        return $payments;
    }
}
