<?php
/**
* Manages Page Requisites for using before the template gets rendered
*/
class Page_Requisites
{
    private $js_files = array();
    

    function add_js_lib($file) {
        $this->js_files[] = $file;
    } 
    
    function load_js_lib() {       
        return array_unique($this->js_files);
    }
    
    /** List of required JS files from a managed source in the templates folder */
    var $managed_js = array();
    
    /** List of required CSS files from a managed source in the templates folder */
    var $managed_css = array();
    
    /** Add a managed JS file to the requisites
      * @param $file filename of the JS-file to add
      * @param $is_lib look for the file in the library path */
    function add_managed_js($file, $is_lib) {
        $this->managed_js []= array('file' => $file, 'lib' => $is_lib);
    }
    
    /** Add a managed JS file to the requisites
      * @param $file filename of the CSS-file to add */
    function add_managed_css($file) {
        $this->managed_css []= array('file' => $file);
    }
}