<?

class action_languageadmin extends AdminAction {

    var $props = array("class", "command", "lg");
    
    
    /** Whether these actions may be used by admins depends on the
      * admin/allow_languageadmin setting */
    function permit_user($user) {
        global $aquarius;
        if ($aquarius->conf('admin/allow_languageadmin')) {
            return   $user->isSiteadmin();
        }
        return $user->isSuperadmin();
    }
}

class action_languageadmin_delete extends action_languageadmin implements ChangeAction {
    function process($aquarius, $post, $result) {
        $lang = DB_DataObject::factory('languages');
        $lang->lg = $this->lg;
        $lang->delete();
        $result->add_message("$lang->name deleted");
    }
}

class action_languageadmin_save extends action_languageadmin implements ChangeAction {
    function process($aquarius, $post, $result) {
        $lang = DB_DataObject::factory('languages');
        $lang->lg = $post['nlg'];
        $update = (bool)$lang->find(true);
        
        $lang->name = $post['name'];
        if ($update) {
            $lang->update();
            $result->add_message("Updated $lang->name");
        } else {
            $lang->insert();
            $result->add_message("Added $lang->name");
        }
    }
}

class action_languageadmin_setWeighting extends action_languageadmin implements ChangeAction {
    function process($aquarius, $post, $result) {
        foreach ( array_keys($post['weight']) as $key ) {
            $lang = DB_DataObject::factory('languages');
            $lang->get($key);
            $lang->weight = $post['weight'][$key];
            $lang->update();
        }
        $result->add_message("Updated weights");
    }
}

class action_languageadmin_toggle_active extends action_languageadmin implements ChangeAction {
    function process($aquarius, $post, $result) {
        $lang = DB_DataObject::factory('languages');
        $lang->lg = $this->lg;
        $lang->find();
        $lang->fetch();
        $lang->active = !$lang->active;
        $lang->update();
        $result->add_message("$lang->name ".($lang->active ? "activated":"deactivated"));
    }
}

class action_languageadmin_list extends action_languageadmin implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
        $lgs   = db_Languages::getLanguages(false);
        foreach ($lgs as $mLG) {
            $mLG->deleteAction = Action::make("confirm",
                Action::make("languageadmin", "delete", $mLG->lg)->actionstr(),
                Action::make("languageadmin", "list", "")->actionstr(),
                "Deleting language",
                "<b>Delete language ".$mLG->name."?</b>"
            );
        }
        $smarty->assign("languages", $lgs);
        $result->use_template('languagelist.tpl');
    }
}


class action_languageadmin_edit extends action_languageadmin implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
        $lang = DB_DataObject::factory('languages');
        if ($this->lg != "null") {
            $lang->lg = $this->lg;
            $lang->find();
            $lang->fetch();
        }
        $smarty->assign("editlang", $lang);
        
        $result->use_template('languageedit.tpl');
    }
}

?>
