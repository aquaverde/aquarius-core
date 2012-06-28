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
     
        echo '-- Aquarius('.$aquarius->revision().') SQL dump '.$aquarius->conf('db/name').' on '.$aquarius->conf('db/host')."\n\n";
        
        global $DB;
        $this->dump_all($DB);
    }
    
    function dump_all($DB) {
        foreach($DB->listquery('show tables') as $table) {
            $this->dump_table_structure($DB, $table);
            $this->dump_table_data($DB, $table);
        }
    }
    
    function dump_table_structure($DB, $table) {
        echo "\n-- Table `$table`: structure \n";
        echo "DROP TABLE IF EXISTS `$table`;\n";
        echo get(mysql_fetch_row($DB->query("SHOW CREATE TABLE `$table`")), 1).";\n\n";
    }
    
    function dump_table_data($DB, $table) {
        $result = $DB->query("SELECT * FROM `$table`");
        echo "-- Table `$table`: ".mysql_num_rows($result).' rows / '.mysql_num_fields($result)." fields \n";

        if (mysql_num_rows($result) < 1) return;
        
        $field_types = array();
        for ($i = 0; $i < mysql_num_fields($result); $i++) {
            $field = mysql_fetch_field($result, $i);
            $field_types []= $field->type;
        }
        $field_count = count($field_types);
     
        echo "INSERT INTO `$table` VALUES\n";
        $first = true;
        while($row = mysql_fetch_row($result)) {
            echo ($first ? '  (' : ', (');
            foreach($field_types as $pos => $field_type) {
                $value = $row[$pos];
                if (is_null($value)) echo 'null';
                elseif($field_type == 'int') echo $value;
                else echo '"'.mysql_real_escape_string($value).'"';
                if ($pos < $field_count - 1) echo ', ';
            }
            echo ")\n";
            $first = false;
        }
        echo ";\n";
    }
}
