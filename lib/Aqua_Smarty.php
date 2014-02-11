<?php
/** Smarty template engine with Aquarius extensions */
class Aqua_Smarty extends SmartyBC {
    var $require_active;
    var $uri; /* link factory */
    

    public function __construct(array $options=array()) {
        
        parent::__construct($options);
        
        // Override handler for the {php} tag
        $this->unregisterPlugin('block', 'php');
        $this->registerPlugin('block', 'php', array($this, 'eval_php'));
    }

    /** Insert template dir in front of other template dirs
      * Otherwise same as addTemplateDir()
      */
    function insertTemplateDir($dir) {
        $dirs = $this->getTemplateDir();
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
        // Override handler for the {php} tag
        $this->unregisterPlugin('block', 'php');
        $this->registerPlugin('block', 'php', array($this, 'eval_php'));
        
        set_error_handler(array($this, 'receive_error'), error_reporting());
        $result = parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
        restore_error_handler();
        return $result;
    }
    
    function receive_error($errno, $errstr, $errfile, $errline, $errcontext) {
        Log::debug(compact('errno', 'errstr', 'errfile', 'errline'));
        return true;
    }

    /** Custom error logging over the use of the {php} tag */
    function eval_php($params, $content, $template, &$repeat) {
        $result = eval($content);
        if ($result === false) {
            $error = error_get_last();
            $error['template'] = $template->template_resource;
            $error['code'] = $content;
            Log::debug($error);
        }
        return '';
    }
}
