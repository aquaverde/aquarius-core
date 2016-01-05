<?php 
/* Field type that ensures URL-save strings when saving to the DB */
class Formtype_Urltitle extends Formtype {

    function db_set($value, $form_field, $lg) {
        return array(convert_chars_url($value));
    }
}
