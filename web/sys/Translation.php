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
    public $lang;                            // string(2)  not_null
    public $key;                             // string(250)  not_null multiple_key
    public $value;                           // string(4000)  
    public $last_modified_by;                // string(14)  
    public $verified;                        // int(6)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public static function search($q)
    {
        $q = strtolower($q);
        $result = array();
        $db = new Translation();
        $db->whereAdd("LOWER(`key`) LIKE '%" . $db->escape($q) . "%'");
        $db->whereAdd("LOWER(`value`) LIKE '%" . $db->escape($q) . "%'", 'OR');
        $db->orderBy('`key`');
        $db->find();
        while ($db->fetch()) {
            $result[] = clone($db);
        }
        return $result;
    }
}
