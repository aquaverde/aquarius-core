<?php
/**
 * Table Definition for cron
 */

class db_Cron extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'cron';                            // table name
    public $type;                            // varchar(750)  not_null primary_key
    public $start_run;                       // bigint(20)  not_null group_by
    public $end_run;                         // bigint(20)  not_null group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Cron',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
