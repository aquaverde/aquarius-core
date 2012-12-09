<?php

/** Update the cached fields of all nodes
    @package Aquarius.backend
*/

class action_db_maintenance extends AdminAction {

    /** These actions are superadmin only */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function get_title() {
        return new FixedTranslation('DB Maintenance');
    }

}

/** Display possible actions */
class action_db_maintenance_dialog extends action_db_maintenance implements DisplayAction {

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('actions', array(
            Action::make('db_maintenance', 'update_node_cache'),
            Action::make('db_maintenance', 'generate_classes'),
            Action::make('checkdb', 'show'),
            Action::make('db_maintenance', 'wtfutf'),
            Action::make('db_maintenance', 'export'),
            Action::make('db_maintenance', 'runcron')
        ));
        $result->use_template('confirm_actions.tpl');
    }
}

/** Rebuild the node cache of all nodes, including tree index */
class action_db_maintenance_update_node_cache extends action_db_maintenance implements ChangeAction {

    function get_title() {
        return new Translation('menu_super_updatecache');
    }

    function process($aquarius, $post, $result) {
        require_once ("lib/db/Node.php");
        db_Node::get_root()->update_cache();
        db_Node::update_tree_index();
        $result->add_message(new Translation('s_message_node_cache_rebuilt'));
    }
}

class action_db_maintenance_generate_classes extends action_db_maintenance implements ChangeAction {    

    function get_title() {
        return new Translation('menu_super_genclasses');
    }

    function process($aquarius, $post, $result) {
        // Load the Generator
        require_once ("DB/DataObject/Generator.php");
        
        // Because the generator can't deal with a path relative to the include
        // paths, we have to give it an explicite path.
        $pear_options = &PEAR::getStaticProperty('DB_DataObject','options');
        $pear_options['class_location'] = $aquarius->core_path.'lib/db/';
        
        $generator = new DB_DataObject_Generator;
        $generator->start();
        Log::info('Updated DB_DataObject classes.');
        $result->add_message(new Translation('s_message_generated_classes'));
    }
}

/** Change the collation of the database, its tables and their fields to utf8_unicode_ci */
class action_db_maintenance_wtfutf extends action_db_maintenance implements ChangeAction {

    function get_title() {
        return new FixedTranslation('Set all tables to UTF8 encoding');
    }

    /** Some tables should not have their collation changed
      * Currently, we know of the cache_dir table, where a case-sensitive collation must be used */
    var $table_blacklist = array('cache_dirs');
    
    function process($aquarius, $post, $result) {
        global $DB;
        $encoding = 'utf8_unicode_ci';
        try {
            $DB->query("ALTER DATABASE ".$aquarius->conf('db/name')." DEFAULT CHARACTER SET utf8 COLLATE $encoding");
        } catch (Exception $e) {
            $result->add_message("Failed setting default charset of DB");
        }
        $tables = $DB->listquery("SHOW TABLES");
        foreach ($tables as $table) {
            if (!in_array($table, $this->table_blacklist)) $DB->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8 COLLATE $encoding");
        }
        $result->add_message("Character set of DB and all its tables set to $encoding");
    }
}

/** Run cron jobs on demand */
class action_db_maintenance_runcron extends action_db_maintenance implements ChangeAction {

    function get_title() {
        return new FixedTranslation('Run daily jobs now');
    }

    function process($aquarius, $post, $result) {
        Log::info("Cron: Running jobs on demand");
        $aquarius->execute_hooks('daily');
        Log::info("Cron: Finished jobs");
        $result->add_message(count(get($aquarius->hooks, 'daily', array()))." cron jobs executed");
    }
}

/** Dump database as SQL file download
  * DB views not supported.
  */
class action_db_maintenance_export extends action_db_maintenance implements SideAction {

    function get_title() { return new FixedTranslation("Export database"); }

    function process($aquarius, $request) {
        set_time_limit(0);
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="'.$_SERVER['SERVER_NAME']." ".$aquarius->conf('db/name').' '.date('YmdHis').'.sql"');

        $dumper = new DB_Dump($aquarius->db);
        $dumper->dump_all(fopen('php://stdout', 'w'));
    }
}

/** Dump database into file in init folder
  */
class action_db_maintenance_dump extends action_db_maintenance implements ChangeAction {

    function get_title() { return new FixedTranslation("Dump database"); }

    function process($aquarius, $post, $result) {
        set_time_limit(0);
        $fname=$_SERVER['SERVER_NAME']." ".$aquarius->conf('db/name').' '.date('Y.m.d His').'.sql';
        
        $dumper = new DB_Dump($aquarius->db);
        
        $fpath = $aquarius->install_path.'init/'.$fname;
        $f = fopen($fpath, 'w');
        if (!$f) {
            $result->add_message(AdminMessage::with_html('warn', "Unable to open $fpath for writing"));
            return;
        }
        
        $dumper->dump_all($f);
        $result->add_message("DB dumped to $fpath");
    }
}
