<?php
class action_modules extends AdminAction {

    var $props = array("class", "command");
    
    /** Superadmins only */
    function permit_user($user) {
      return $user->isSuperadmin();
    }
}

class action_modules_list extends action_modules implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
        // Update module list in DB
        try {
            global $loader;

            $aquarius->module_manager->update_list(false, $loader);
        } catch (No_Such_Module_Exception $e) {
            $result->add_message("Failed updating module list: ".$e->getMessage());
        }

        $mods = $aquarius->module_manager->available_modules();
        ksort($mods);
        
        foreach($mods as $name => $mod) {
            $module = get($aquarius->modules, $name);
            if ($module) {
                foreach($module->use_modules as $dep) {
                    if (!isset($aquarius->modules[$dep])) {
                        $result->add_message(AdminMessage::with_html('warn', "Module $name depends on module $dep which is not loaded"));
                    }
                }
            }
        }

        $smarty->assign("modules", $mods);
        $result->use_template('moduleslist.tpl');
    }
}

class action_modules_toggle_active extends action_modules implements ChangeAction {

    var $props = array("class", "command", "id");

    function process($aquarius, $post, $result) {
        $mod =& DB_DataObject::factory('modules');
        $modid = intval($this->id);
        $mod->get($modid);
        $mod->active = !$mod->active;
        $mod->update();

        $result->touch_region('loader');
        $result->add_message(($mod->active?"A":"Dea")."ctivated module $mod->short");
    }
}
