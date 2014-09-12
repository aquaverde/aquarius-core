<?php
/** 
On installation of the CMS an initial database must be loaded. During an update
of the CMS or that of modules, the necessary changes to the DB or other changes
must be applied. This procedure must be robust in that no old
updates are reapplied, and so that updates are applied in the right order.

How it's done

During setup, the list of active modules is loaded. Core is considered a
module as well. If the database is not ready to furnish a list of modules, the
list will be initialized to contain the 'core' module.

For each module, the name of the last update is loaded from table
'module_update'. Modules having no update registered are assumed uninitialized.
For uninitialized modules, the list of available initialization files is loaded
from '$module/init'. Should this folder not exist or not contain any update
files, the module is considered to not require initialization. 

For initialized modules, the list of available updates is loaded from
'$module/update'. Each update file is compared against the name of the last
update, only files whose names are ordered after the last update are taken as
applicable updates.

The available initializations and updates are presented to the user for
confirmation, where from the initializations only one per module may be
selected.

When confirmed, the selected inits and updates are grouped by module
and ordered by name; then applied in this order. After each init or update, its
name is registered in the module_update table. On failure of an update, this
update is registered as failed and the update proceeds.

The whole procedure is repeated as necessary until neither inits nor updates are
available. This way, updates for newly initialized modules will be applied in a
second round.

The expected format of filenames for update files is a date and a reason string,
example: "2038.01.20 change timestamp column to int64"

*/

$requested_updates = get($_POST, 'requested_updates', array());
$available_updates = array();

foreach(array('init', 'update') as $step) {
    // Hack: make $aquarius look like a module for our purposes
    $aquarius->short = 'core';
    
    // Now init scripts may also come with the template repository, so we
    // search both locations and merge the found updates
    $found_updates = array();
    foreach(array($aquarius->install_path, $aquarius->core_path) as $path) {
        $aquarius->path = $path;
        $found_updates = array_merge_recursive($found_updates, find_updates($step, $aquarius));
    }

    apply_updates($found_updates, $requested_updates, $step, $aquarius);
    
    $found_updates = array();
    foreach(array($aquarius->install_path, $aquarius->core_path) as $path) {
        $aquarius->path = $path;
        $found_updates = array_merge_recursive($found_updates, find_updates($step, $aquarius));
    }
    if ($found_updates) {
        $available_updates[$step]['core'] = $found_updates;
        $halt = true;
    }
}
    
$have_modules_table = $aquarius->db->singlequery("
    SELECT COUNT(*) AS count 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'modules'
");

if ($have_modules_table) {
    $aquarius_loader->load('modules');
    foreach(array('init', 'update') as $step) {
        foreach($aquarius->modules as $module) {
            $found_updates = find_updates($step, $module);
            
            apply_updates($found_updates, $requested_updates, $step, $module);
            
            $found_updates = find_updates($step, $module);
            if ($found_updates) {
                $available_updates[$step][$module->short] = array_merge(get(get($available_updates, $step, array()), $module->short, array()), $found_updates);
                $halt = true;
            }
        }
    }
}

function find_updates($step, $module) {
    $short = $module->short;
    $available_updates = array();
    $updates_path = $module->path.$step.'/';
    
    $update_candidates = Aqua_Update::load_updates($updates_path);
       
    global $aquarius;
        
    if (count($update_candidates) > 0) {
        $have_log_table = $aquarius->db->singlequery("
            SELECT COUNT(*) AS count 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = 'update_log'
        ");


        if ($have_log_table) {
            $last_update = $aquarius->db->singlequery("SELECT name FROM update_log WHERE module = '$short' ORDER BY name DESC LIMIT 1");

            if ($step == 'update') {
                foreach($update_candidates as $name => $update) {
                    if ($name > $last_update) {
                        $available_updates[$name] = $update; 
                    }
                    
                }
            }
        } else {
            // If we don't have a log table we assume that initialization will
            // create it.
            if ($step == 'init') $available_updates = $update_candidates;
        }
    }
    
    return $available_updates;
}

function apply_updates($updates, $requested_updates, $step, $module) {
    $short = $module->short;
        
    global $aquarius;
        
    // Check whether running initialization was requested
    foreach($updates as $update_name => $update) {
        if (in_array($update_name, $requested_updates)) {
            Log::info("Applying update $update_name");
            $update_log_entry = Db_DataObject::factory('update_log');
            $update_log_entry->date    = time();
            $update_log_entry->module  = $short;
            $update_log_entry->success = true;
            // Hack: Prefix initialization files with a character that orders them before updates (which start with a number)
            $update_log_entry->name   = ($step=='init'? '*** ' : '').$update_name;
            
            try {
                $update->apply($aquarius, $module);
            } catch(Exception $e) {
                $update_log_entry->success = false;
                message('warn', "Failed applying update '$update_name' for $short. Message: ".$e->getMessage());
            }
            
            if ($update_log_entry->success) {
                if ($step=='init') message('', "Initialized $short with $update_name");
                else message('', "Updated $short with $update_name");
            }
            
            $update_log_entry->insert();
        }
    }
}


class Aqua_Update {
    static function load_updates($update_path) {
        $updates = array();
        if (!is_dir($update_path)) return array();
        foreach (scandir($update_path) as $update_file) {
            $p = pathinfo($update_file);
            $update_class = 'Aqua_Update_'.strtoupper($p['extension']);
            if (class_exists($update_class)) {
                $updates[$p['basename']] = new $update_class($update_path.$update_file);   
            }
        }
        uksort($updates, 'strnatcasecmp');
        return $updates;
    }
}

Class Aqua_Update_SQL {
    function __construct($path) {
        $this->update_file = $path;
    }

    function apply($aquarius, $module) {
        // Hack: Make sure all created tables are in UTF8
        $aquarius->db->query("ALTER DATABASE `".$aquarius->conf('db/name')."` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

        $sql_file = fopen($this->update_file, 'r');
        if ($sql_file === false) throw new Exception("Unable to open ".$this->update_file);

        if (get(fstat($sql_file), 'size') > 100000) {
            // This could take a while...
            set_time_limit(0);
            echo "Please wait...";
            while(@ob_end_flush());
            flush();
        }

        $sql_reader = new SQL_Split($sql_file);
        $sql_reader->next();
        while($sql_reader->valid()) {
            try {
                $aquarius->db->connection->query($sql_reader->current());
            } catch (Exception $sqlerr) {
                throw new Exception($sqlerr->getMessage()." in query ".$sql_reader->key()." ending on line ".$sql_reader->line().":\n");
            }
            $sql_reader->next();
        }
    }
}

Class Aqua_Update_PHP {
    function __construct($path) {
        $this->update_file = $path;
    }

    function apply($aquarius, $module) {
        include $this->update_file;
    }
}

class Aqua_Update_JSON {
    function __construct($path) {
        $this->update_file = $path;
    }

    function apply($aquarius, $module) {
        $importer = new Content_Import();
        $importer->update = true;

        $import = file_get_contents($this->update_file);
        if (FALSE === $import) {
            message('warn', "Unable to read content update $this->update_file");
        } else {
            global $aquarius;
            $aquarius->load(); // FUGLY HACK so sorry
            $importer->import($import);
        }
    }
}




if (count($available_updates) > 0) { ?>

    <div class="bigbox">
    <h2>Updates</h2>
    <form action="" method="post">
<?php 
    foreach($available_updates as $step => $update_list) {
    if (!empty($update_list)) {
    foreach($update_list as $module_name => $update_names) {
?>
        <h3><?php echo $module_name; ?></h3>
        <ul>
<?php     
        $first = true;
        foreach($update_names as $update_name => $_) {  
?>
            <li><label>
                <?php if ($step=='init') { ?>
                    <input type="radio" name="requested_updates[<?php echo htmlentities($module_name);?>]" value="<?php echo htmlentities($update_name);?>" 
                     <?php if ($first) echo "checked='checked'"; ?> 
                    /><?php echo htmlentities($update_name);?>
                 <?php } else { ?>
                    <input type="checkbox" name="requested_updates[]" value="<?php echo htmlentities($update_name);?>" checked="checked" /><?php echo htmlentities($update_name);?>
                 <?php } ?>
           </label></li>
        
<?php       $first = false;
        }
?>      </ul>
<?php
    }
    }
    }
?>
    <input type="submit" name="apply_updates" value="Apply selected updates" class="submit" />
    </form>
    </div>
<?php
}

