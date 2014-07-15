<?php
require_once "lib/db/Fe_address.php";

/** Address object with validation functions */
class Usermanagement_Address extends db_Fe_address {
    static private $fields = array('title', 'firstname', 'lastname', 'capacity', 'firma', 'address', 'zipcity', 'phone', 'email', 'website');
    static private $mandatory = array('title', 'firstname', 'lastname', 'capacity', 'firma', 'address', 'zipcity', 'phone', 'email');

    /** Create address object and populate it with values from $fields
      * @param $values dict of address values to read
      */
    function __construct($values=false) {
        if ($values) $this->read($values);
    }

    /** Replace values from dict if they are set */
    function read($values) {
        foreach(self::$fields as $field) {
            $this->$field = trim(get($values, $field, $this->$field));
        }
    }

    /** Trim values and check whether mandatory fields are present
      * @return dict with missing fields and associated error string */
    function validate() {
        $errors = array();
        foreach(self::$fields as $field) {
            $this->$field = trim($this->$field);
            if (empty($this->$field) && in_array($field, self::$mandatory)) {
                $errors[$field] = 'missing_'.$field;
            }
        }
        if (!empty($this->email) && !self::valid_mail_address($this->email)) {
            $errors['email'] = 'invalid_email';
        }
        return $errors;
    }
    /** Ensure the 'email' field looks remotely like a plain mail address
      * This method does not fully comply with the RFC, but it should be liberal enough to accept the worst addresses still in use. Quoted local parts are not accepted. */
    static function valid_mail_address($address) {
        // Yes, even braces '{}' are allowed in local parts of mail addresses
        return eregi('^[-a-z0-9!#$%&*+/=?^_`{|}~.]+@([-a-z0-9]+\.)+[-a-z0-9]+$', $address);
    }
    
    /** Address in a form suitable for display. Empty lines are removed */
    function address_lines() {
        $lines = array(
            array($this->title, $this->firstname, $this->lastname),
            array($this->capacity),
            array($this->firma),
            array($this->address),
            array($this->zipcity),
            array($this->phone),
            array($this->email),
            array($this->website)
        );
        $alines = array();
        foreach($lines as $line) {
            $line = array_filter($line);
            if (!empty($line)) {
                $alines []= join(' ', $line);
            }
        }
        return $alines;
    }
}
