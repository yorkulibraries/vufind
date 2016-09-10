<?php
/**
 * Table Definition for translation
 */
require_once 'DB/DataObject.php';

class Translation extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'translation';         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $lang;                            // string(2)  unique_key
    public $key;                             // string(250)  unique_key
    public $value;                           // string(4000)  
    public $last_modified_by;                // string(14)  

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
