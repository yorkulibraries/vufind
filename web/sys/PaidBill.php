<?php
/**
 * Table Definition for paid bills
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
 * @package  DB_DataObject
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
require_once 'DB/DataObject.php';

/**
 * Table Definition for paid bills
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class PaidBill extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'paid_bill';           // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $bib_id;                          // int(11)  
    public $user_barcode;                    // string(14)  not_null multiple_key
    public $item_barcode;                    // string(14)  
    public $item_title;                      // string(200)  
    public $bill_key;                        // string(20)  not_null unique_key
    public $bill_date;                       // datetime(19)  not_null binary
    public $bill_reason;                     // string(100)  
    public $bill_library;                    // string(20)  not_null
    public $item_library;                    // string(20)  
    public $balance;                         // real(12)  not_null
    public $payment_amount;                  // real(12)  not_null
    public $payment_date;                    // datetime(19)  not_null binary
    public $payment_auth_code;               // string(100)  not_null
    public $user_key;                        // string(20)  not_null
    public $bill_number;                     // int(11)  not_null


    /* Static get */
    static function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('PaidBill',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd
}
