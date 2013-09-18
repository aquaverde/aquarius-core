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
}
