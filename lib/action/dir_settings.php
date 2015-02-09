<?php 
/** @package Aquarius.backend */

require_once "lib/file_mgmt.lib.php";

class action_dir_settings extends AdminAction {

    var $props = array("class", "command");

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
}

class action_dir_settings_edit extends action_dir_settings implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $directoriesData = get_cached_dirs();
        $dirs = array();
        foreach($directoriesData as $dir) {
            $dir_props = DB_DataObject::factory('directory_properties');
            $dir_props->load($dir, false);
            $dirs[$dir] = $dir_props->toArray();
        }

        $smarty->assign("typeOptions", array('m' => 'm', 'w' => 'w', 'h' => 'h'));
        $smarty->assign("dirData", $dirs);
        $smarty->assign("resize", PICTURE_RESIZE == 'm' ? 'MaxSize' : 'Width');
        $result->use_template("directory_settings.tpl");
    }
}

class action_dir_settings_save extends action_dir_settings implements ChangeAction {
    function process($aquarius, $post, $result) {
        foreach(get($post, 'dir_setting') as $dir => $settings) {
            $dir_props = DB_DataObject::factory('directory_properties');
            $loaded = $dir_props->load($dir, false);
            $original_props = clone $dir_props;

            foreach($settings as $name => $value) {
                $dir_props->$name = $value;
            }

            $changed = $dir_props != $original_props;
            if ($changed) {
                $loaded ? $dir_props->update() : $dir_props->insert();
                $result->add_message(new FixedTranslation("Saved changes to ".$dir_props->directory_name));
            }
        }
    }
}

class action_dir_settings_cache_dirs_dialog extends action_dir_settings implements DisplayAction {


    /** Superadmins only */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function get_title() {
        return new FixedTranslation('Update dir cache...');
    }

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('actions', array(Action::make('dir_settings', 'cache_dirs')));
        $result->use_template('confirm_actions.tpl');
    }
}

class action_dir_settings_cache_dirs extends action_dir_settings implements ChangeAction {

    /** Superadmins only */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function get_title() {
        return new FixedTranslation('Update dir cache');
    }

    function process($aquarius, $post, $result) {
        $dirs = getAllDirs('');
        $aquarius->db->query("TRUNCATE TABLE cache_dirs");
        foreach($dirs as $dir)
            $aquarius->db->query('INSERT INTO cache_dirs SET path = ?', array($dir));

        $result->add_message(new FixedTranslation("Cached ".count($dirs)." dirs"));
    }
}
