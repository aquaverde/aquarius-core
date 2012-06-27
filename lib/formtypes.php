<?php
/** @package Aquarius */

/** Configuration of available form field types. */
class Formtypes {
    private $class_path;
    private $formtypes = array();

    public function __construct($aquarius) {
        $this->class_path = $aquarius->core_path.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'formtypes'.DIRECTORY_SEPARATOR;
        $this->load_fields();
        $this->load_modules($aquarius);
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

    private function load_modules($aquarius) {
        $aquarius->execute_hooks('init_form', $this);
    }

    private function load_fields() {
        // Some basic fields do not yet require their own class
        $this->add_formtype(new Formtype('ef'));
        $this->add_formtype(new Formtype('sf'));
        $this->add_formtype(new Formtype('radiogroup'));
        $this->add_formtype(new Formtype('radiobool'));
        $this->add_formtype(new Formtype('checkbox'));

        // Load extended fields
        if($dh = opendir($this->class_path)) {
            while (($file = readdir($dh)) !== false) {
                $filepath = $this->class_path.$file;
                if(is_file($filepath) && substr($file, -4) == '.php') {
                    require_once($filepath);
                    $code = basename($file, '.php');
                    $classname = 'Formtype_'.$code;
                    $this->add_formtype(new $classname($code));
                }
            }
            closedir($dh);
        }
    }
}
?>