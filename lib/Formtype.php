<?
/** Basic implementation of a formtype.
  * 
  * Does not alter values between DB, Content and Contentedit. */
class Formtype {

    var $code = null;

    /** Create a formfield
      * @param $code unique string name of this form field
      * @param $template_name optional name for the template to be used in contentedit, $code is used if this is not specified */
    function __construct($code, $template_name = false) {
        $this->code          = $code;

        if (strlen($template_name) < 1) $template_name = $code;
        $this->template_name = $template_name;
    }

    function get_code() {
        return $this->code;
    }

    /** Get name of form field */
    function get_title() {
        return new Translation('formtype_'.$this->code);
    }

    /** Translate field value to string */
    function to_string($values) {
        return str($values);
    }

    /** Apply formtype specific conversion prior to editing content */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        // No change to valobject
    }
    
    /** Apply formtype specific conversion prior to saving content */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        return $value;
    }

    /** Get the template name for this formtype
      * Different fields can use the same template. However, for standard fields, the template_name is the same as the field's code (see constructor). */
    function template_name() {
        return $this->template_name;
    }

    /** Process key/value pairs loaded from DB
      * @param $values key/value pair loaded from DB
      * @return String or whatever the overriding method thinks is best
      * This default method assumes that there's just one value loaded from the DB and returns it. If there are multiple values they are concatenated with a space. If you need different processing, override this method.
      * Essentially, db_get() and db_set() should be reversible, so that if you have a content value, then running it through db_set() and db_get() should yield the same value, maybe sanitized:
      * <pre>
      *   $val = $content->somevalue;
      *   $formtype = new Formtype_date();
      *   $val == $formtype->db_get($formtype->db_set($val)); // Should be true for normal values (the meaning of normal depends on the field type). */
    function db_get($values, $form_field, $lg) {
        return implode(" ", $values);
    }

    /** Process field to store it in the DB.
      * @param $value The value from the content object, should have the same format as it was returned by db_get()
      * @return key/value pairs as assoc array */
    function db_set($value, $form_field, $lg) {
        return array($value);
    }

    function db_set_field($vals, $formfield, $lg) {
        if (!$formfield->multi) {
            $vals = array($vals); // Just so we can handle both the same way
        }
        $processed_vals = array();
        foreach ($vals as $fieldvalues) {
            $processed_vals[] = $this->db_set($fieldvalues, $formfield, $lg);
        }
        return $processed_vals;
    }

    /** Digest values from DB load
      * This handles single and multi-value fields. Every value for the field is run through db_get(). For multi-value fields, the list of non-null return values from db_get() is returned, for single-value fields, the last non-null field as returned by db_get() is passed on. (While it is not much use to have multiple values for a single-value field, it happens, for example if fields are changed from multi to single value.)
      *
      * Formtype implementations should not override this method, and change db_get() instead. This makes migrating to a different DB model easier. */
    function db_get_field($vals, $formfield, $lg) {
        $ret = null;
        if ($formfield->multi) {
            $ret = array();
        }
        
        foreach($vals as $fieldvalue) {
            $value = $this->db_get($fieldvalue, $formfield, $lg);
            if ($value !== null) {
                if ($formfield->multi) {
                   $ret[] = $value;
                } else {
                    $ret = $value;
                }
            }
        }
        return $ret;
    }
}
?>