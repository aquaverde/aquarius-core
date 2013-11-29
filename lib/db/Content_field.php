<?php

/**
 * Table Definition for content_field, UNUSED.
 * This DataObject is not used, the fields are directly loaded from the content. (It'd be a huge overhead to create all the content_field objects)
 */
class db_Content_field extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_field';                   // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $content_id;                      // int(11)  not_null multiple_key group_by
    public $name;                            // varchar(250)  not_null multiple_key
    public $weight;                          // int(11)  not_null multiple_key group_by
    public $value;                           // varchar(1)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_field',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /** Write a content field to the DB */
    static function write($content_id, $name, $weight, $values) {
        $content_field = DB_DataObject::factory("content_field");
        $content_field->content_id = intval($content_id);
        $content_field->name = $name;
        $content_field->weight = $weight;
        $content_field->insert();

        db_Content_field_value::save_fieldvalues($values, $content_field->id);
    }
}
