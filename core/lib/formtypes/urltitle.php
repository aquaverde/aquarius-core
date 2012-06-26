<?
/* Field type that ensures URL-save strings when saving to the DB */
class Formtype_Urltitle extends Formtype {

    function db_set($value) {
        return array(convert_chars_url($value));
    }
}
?>