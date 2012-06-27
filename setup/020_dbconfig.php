<?
/** Look for a DB configuration, test it  and add or change it if it
  * doesn't work.
  */

$localconf_file = $install_dir.'config.local.php';
$localconf = new Aqua_Config_File($localconf_file);
$config = $localconf->value('db');


if (requestvar('change_db_settings')) {
    $config['host'] = requestvar('db_host');
    $config['name'] = requestvar('db_name');
    $config['user'] = requestvar('db_user');
    $pass = requestvar('db_pass');
    if (!empty($pass)) $config['pass'] = $pass;
}

// Try to connect to DB
$db_host = get($config, 'host');
$db_name = get($config, 'name');
$db_user = get($config, 'user');
$db_pass = get($config, 'pass');


$db_connection = false;
if (!empty($db_host) && !empty($db_name) && !empty($db_user) && !empty($db_pass)) {
    $db_connection = mysql_connect($db_host, $db_user, $db_pass, true);
    if (!$db_connection) {
        message('warn', "Failed connecting to server $db_user@$db_host");
    }
    if ($db_connection) {
        $result = mysql_select_db($db_name, $db_connection);
        if (!$result) {
            $result = mysql_query("CREATE DATABASE `".mysql_real_escape_string($db_name)."`");
            if (!$result) {
                message('warn', "Failed selecting DB $db_name on $db_host");
                $db_connection = false;
            } else {
                message('', "Created DB $db_name on $db_host");
                $result = mysql_select_db($db_name, $db_connection);
                if (!$result) {
                    message('warn', "Failed selecting DB $db_name on $db_host");
                    $db_connection = false;
                }
            }
        }
    }
}

if ($db_connection) {
    if (requestvar('change_db_settings')) {
        foreach(array('host', 'name', 'user', 'pass') as $param) {
            $localconf->set('db/'.$param, $config[$param]);
        }
        $result = $localconf->write();
        if ($result == 0) {
            message('', "DB config unchanged");
        } elseif ($result == 1) {
            message('', "Changed DB settings");
        } else {
            message('warn', "Couldn't write $localconf_file");
            $halt = true;
        }
    }
} else {

    // Use legacy values when present
    if (empty($db_host) && defined('DB_HOST')) $db_host = DB_HOST;
    if (empty($db_name) && defined('DB_NAME')) $db_name = DB_NAME;
    if (empty($db_user) && defined('DB_USERNAME')) $db_user = DB_USERNAME;
    if (empty($db_pass) && defined('DB_PASSWORD')) $legacypass = DB_PASSWORD;
    else $legacypass = '';
    
    // Supply defaults
    if (strlen($db_host) == 0) $db_host = 'localhost';
    
    echo "
    <div class='bigbox'>
    <h2>Database config</h2>
    <form action='' method='post' name='change_db_settings'>
        <label>Hostname <input type='text' name='db_host' value='".htmlentities($db_host)."'/></label>
        <label>Database name <input type='text' name='db_name' value='".htmlentities($db_name)."'/></label>
        <label>Username <input type='text' name='db_user' value='".htmlentities($db_user)."'/></label>
        <label>Password <input type='text' name='db_pass' value='".htmlentities($legacypass)."'/> (keeps old password when left empty)</label>
        <input type='submit' name='change_db_settings' class='submit' value='Connect and write to config'/>
    </form>
    </div>
    ";
    $halt = true;
}

