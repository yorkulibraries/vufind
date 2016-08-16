<?php
/**
 * Table Definition for paid_bill
 */
require_once 'DB/DataObject.php';

class Paid_bill extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'paid_bill';           // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $bib_id;                          // int(11)  
    public $user_key;                        // string(20)  not_null
    public $user_barcode;                    // string(14)  not_null
    public $item_barcode;                    // string(14)  
    public $item_title;                      // string(200)  
    public $item_library;                    // string(20)  
    public $bill_key;                        // string(20)  not_null unique_key
    public $bill_number;                     // int(11)  not_null
    public $bill_date;                       // datetime(19)  not_null binary
    public $bill_reason;                     // string(100)  
    public $bill_library;                    // string(20)  not_null
    public $bill_balance;                    // real(12)  not_null
    public $payment_id;                      // int(11)  not_null multiple_key
    public $payment_status;                  // string(20)  not_null
    public $api_response;                    // string(1000)  
    public $api_request;                     // string(1000)  
    public $api_successful;                  // int(6)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public static function getConfirmedPaidBills($userKey)
    {
        $paidBills = array();
        $p = new Paid_bill();
        $p->user_key = $userKey;
        $p->find();
        while ($p->fetch()) {
            if ($p->payment_status != Payment::STATUS_CANCELLED && $p->payment_status != Payment::STATUS_INITIATED) {
                $paidBills[] = clone($p);
            }
        }
        
        return $paidBills;
    }
}
