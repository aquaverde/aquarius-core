<?php 
/** @package Aquarius */

/** Superclass of addon modules, performs default operations expected of a module.
  */
class Module {

    /** short name of the module*/
    var $short = false;
    
    /** name of the module*/
    var $name = false;
    
    /** Reference to the aquarius core */
    var $aquarius = false;
    
    /** List of hooks that should be registered for this module */
    var $register_hooks = array();
    
    /** Absolute path to the module's root dir */
    var $path;

    /** List of modules this module depends on
      * Modules listed here will be added as properties for direct access */
    var $use_modules = array();

    function __construct($path) {
        $this->path = $path;
    }
    
    /** Initialize the module into the system
      * Adds module path to PHP include paths and registers hooks. */
    function initialize($aquarius) {
        $this->aquarius = $aquarius;

        // Add module dependencies as properites for convenient access
        foreach($this->use_modules as $depend_module_short) {
            $depend_module = get($aquarius->modules, $depend_module_short);
            if (!$depend_module instanceof Module) throw new Exception("Module '$this->short' depends on module '$depend_module_short' which is not loaded.");
            $this->{$depend_module_short} = $depend_module;
        }

        // Register the specified hooks
        foreach($this->register_hooks as $hook_name) {
            $this->aquarius->register_hook($hook_name, $this);
        }
    }

    /** Apply module specific configuration for smarty containers, general stuff.
      * Adds module's smarty_plugins path.
      * Registers this object in smarty container under short module name.
      */
    function smarty_config($smarty) {
        // If we have a plugin dir, add it
        $plugins_dir = $this->path."lib/smarty_plugins/";
        if (file_exists($plugins_dir)) {
           array_unshift($smarty->plugins_dir, $this->path.'/lib/smarty_plugins/');
        }
    }
    
    /** Apply module specific configuration for backend smarty containers.
      * Template dir and language config of the module are added for backend templates.
      */
    function smarty_config_backend($smarty, $admin_lg) {
    
        // Maybe we have some additional config for the language
        $config_file = $this->path."lang/".$admin_lg.".lang";
        if (file_exists($config_file)) {
            $smarty->config_load($config_file);
        }

        // If we have a template dir, add it
        $templates_dir = $this->path."templates/";
        if (file_exists($templates_dir)) {
           $smarty->template_dir[] = $templates_dir;
        }
    }
    
    /** Apply module specific configuration for frontend smarty containers.
      * Registers the frontend interface object (if there is one) under the module's short name.
      */
    function smarty_config_frontend($smarty, $lg) {
        $frontend_interface = $this->frontend_interface($lg);
        if ($frontend_interface) $smarty->register_object($this->short, $frontend_interface);
    }

    /** Provide an interface object to be registered into frontend smarty containers
      * Override to return a frontend object, default is none */
    function frontend_interface($lg) {
        return false;
    }
    
    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        //do nothing per default
    }
    
    /** Apply formtype specific conversion prior to saving content
     */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        //do nothing per default
        return $value;
    }
    
    /** Get an module config value
      * Same as aquarius->conf() but with module prefix.
      * @see conf()
      */
    function conf($path, $default = null) {
        global $aquarius;
        return $aquarius->conf($this->short.'/'.$path, $default);
    }
}
?>