<?php
/**
 * Table Definition for content_field_value
 */

class db_Content_field_value extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_field_value';             // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $content_field_id;                // int(11)  not_null multiple_key group_by
    public $name;                            // varchar(750)  multiple_key
    public $value;                           // blob(50331645)  not_null multiple_key blob

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    
    /** saves the field values of a content field.
     * @param $values an hash of field values (fieldname => value)
     * @param $cf_id the content-field id
     */
    static function save_fieldvalues($values, $cf_id) {
        foreach($values as $fieldname => $value) {
            if (strlen($value) > 0) {
                $proto = DB_DataObject::factory("content_field_value");
                $proto->content_field_id = $cf_id;
                $proto->name = $fieldname;
                $proto->value = $value;
                $proto->insert();
            }
        }
    }
}
