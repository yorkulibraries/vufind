<?php
/**
 * Table Definition for requests
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
 * Table Definition for requests
 *
 * @category VuFind
 * @package  DB_DataObject
 * @author   Tuan Nguyen <tuan@yorku.ca>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://pear.php.net/package/DB_DataObject/ PEAR Documentation
 */
class Requests extends DB_DataObject
{
    // @codingStandardsIgnoreStart
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'requests';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $item_id;                         // int(11)  not_null
    public $user_barcode;                    // varchar(14)  not_null
    public $item_barcode;                    // varchar(14)  not_null
    public $item_callnum;                    // varchar(100)  not_null
    public $request_type;                    // varchar(100)  not_null
    public $pickup_location;                 // varchar(100)  not_null
    public $comment;                         // blob(65535)  blob
    public $expiry;                          // datetime(19)  not_null binary
    public $created;                         // datetime(19)  not_null binary
    public $ils_hold_created;                // smallint

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Requests',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    // @codingStandardsIgnoreEnd
}
