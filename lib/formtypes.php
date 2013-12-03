<?php
/** @package Aquarius */

/** Configuration of available form field types. */
class Formtypes {
    private $class_path;
    private $formtypes = array();

    public function __construct($path) {
        $this->class_path = $path.DIRECTORY_SEPARATOR;
    }

    public function add_formtype($type) {
        $this->formtypes[$type->get_code()] = $type;
    }

    public function get_formtypes() {
        return $this->formtypes;
    }

    public function get_formtype($type) {
        return get($this->formtypes, $type);
    }

    function load_internal() {
        require_once 'Formtype.php';
        
        foreach(array(
            'ef' => 'Formtype',
            'sf' => 'Formtype',
            'radiogroup' => 'Formtype',
            'radiobool' => 'Formtype',
            'checkbox' => 'Formtype',
            'code' => 'Formtype_Code',
            'date' => 'Formtype_Date',
            'file' => 'Formtype_File',
            'int' => 'Formtype_Int',
            'link' => 'Formtype_Link',
            'mle' => 'Formtype_Mle',
            'nodelist' => 'Formtype_Nodelist',
            'pointing' => 'Formtype_Pointing',
            'pointing_legend' => 'Formtype_Pointing_Legend',
            'rte' => 'Formtype_Rte',
            'urltitle' => 'Formtype_Urltitle',
            'xref' => 'Formtype_Xref',
            'xref_selection' => 'Formtype_Xref_Selection'
        ) as $code => $class) { $this->load($code, $class); }
    }
    
    function load($code, $class) {
        if (!class_exists($class, false)) {
            require $this->class_path.$code.'.php';
        }
        $this->add_formtype(new $class($code));
    }
}
