<?php
/** Smarty template engine with Aquarius extensions */
class Aqua_Smarty extends SmartyBC {
    var $require_active;
    var $uri; /* link factory */
    
    /** Insert template dir in front of other template dirs
      * Otherwise same as addTemplateDir()
      */
    function insertTemplateDir($dir) {
        $dirs = $this->getTemplateDirs();
        array_unshift($dirs, $dir);
        $this->setTemplateDir($dirs);
    }
    
    /** Insert plugins dir in front of other plugin dirs
      * Otherwise same as addPluginsDir()
      */
    function insertPluginsDir($dir) {
        $dirs = $this->getPluginsDir();
        array_unshift($dirs, $dir);
        $this->setPluginsDir($dirs);
    }
    
    function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
        set_error_handler(array($this, 'receive_error'), error_reporting());
        $result = parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
        restore_error_handler();
        return $result;
    }
    
    function receive_error($errno, $errstr, $errfile, $errline, $errcontext) {
        Log::debug(compact('errno', 'errstr', 'errfile', 'errline'));
        return true;
    }
}