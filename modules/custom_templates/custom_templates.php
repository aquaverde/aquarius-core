<?php 
/** Replace backend templates with custom version
  * If this module is activated, its template dir is searched for aquarius templates before the main aquarius template dir is searched. This allows customizing the aquarius look without touching files in the aquarius dir.
  *
  * Usage:
  * 1. Copy template to be customized from aquarius/templates to modules/custom_templates/templates.
  * 2. Make changes in modules/custom_templates/templates
  * 3. Activate this module
  *
  * Warning: Every file that is customized must be updated manually when the main copy changes. It is recommended that this module be used sparingly, if at all.
*/
class Custom_Templates extends Module {

    var $register_hooks = array('smarty_config_backend');
    
    var $short = "custom_templates";
    var $name  = "Custom templates in backend";

    /** Put our template dir in front of the aquarius template dir so that we can override aquarius templates.
      */
    function smarty_config_backend($smarty, $admin_lg) {
        $templates_dir = $this->path."templates/";
        $smarty->insertTemplateDir($templates_dir);
        Log::debug("NOTE: Using cutom templates in ".$templates_dir);
    }
}
