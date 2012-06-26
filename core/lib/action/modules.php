<?
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
            $aquarius->module_manager->update_list();
        } catch (No_Such_Module_Exception $e) {
            $result->add_message("Failed updating module list: ".$e->getMessage());
        }

        $mods = $aquarius->module_manager->available_modules();
        ksort($mods);
        
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

        $result->add_message(($mod->active?"A":"Dea")."ctivated module $mod->short");
    }
}
