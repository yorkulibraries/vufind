<?php
/**
 * Table Definition for role
 */
require_once 'DB/DataObject.php';

class Role extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'role';                // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $user_id;                         // int(11)  not_null multiple_key
    public $role;                            // string(100)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
