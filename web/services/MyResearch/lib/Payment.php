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
}
