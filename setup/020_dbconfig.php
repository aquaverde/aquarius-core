<?php 
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
    $config['port'] = requestvar('db_port');
    $pass = requestvar('db_pass');
    if (!empty($pass)) $config['pass'] = $pass;
}

// Try to connect to DB
$db_host = get($config, 'host');
$db_name = get($config, 'name');
$db_user = get($config, 'user');
$db_pass = get($config, 'pass');
$db_port = get($config, 'port');


$db_connection = false;
if (!empty($db_host) && !empty($db_name) && !empty($db_user) && !empty($db_pass)) {
    $srv = $db_host.($db_port ? ":$db_port" : "");
    $db_connection = mysqli_connect($srv, $db_user, $db_pass);
    if (!$db_connection) {
        message('warn', "Failed connecting to server $db_user@$db_host");
    }
    if ($db_connection) {
        $result = $db_connection->select_db($db_name);
        if (!$result) {
            $result = $db_connection->query("CREATE DATABASE `".$db_connection->escape_string($db_name)."`");
            if (!$result) {
                message('warn', "Failed selecting DB $db_name on $db_host");
                $db_connection = false;
            } else {
                message('', "Created DB $db_name on $db_host");
                $result = $db_connection->select_db($db_name);
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
        foreach(array('host', 'name', 'user', 'pass', 'port') as $param) {
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
        <table>
            <tr>
                <td><label for='db_host'>Hostname</label></td>
                <td><input type='text' name='db_host' value='".htmlentities($db_host)."' id='db_host' size='8'/>:<input type='text' size='3' name='db_port' value='".htmlentities($db_port)."'/></td>
                <td>(port optional)</td>
            </tr>
            <tr>
                <td><label for='db_name'>Database name</label></td>
                <td><input type='text' name='db_name' value='".htmlentities($db_name)."' id='db_name' size='15'/></td>
            </tr>
            <tr>
                <td><label for='db_user'>Username</label></td>
                <td><input type='text' name='db_user' value='".htmlentities($db_user)."' id='db_name' size='15'/></td>
            </tr>
            <tr>
                <td><label for='db_pass'>Password</label></td>
                <td><input type='text' name='db_pass' value='".htmlentities($legacypass)."' id='db_pass' size='15'/></td>
                <td>(leave empty to keep unchanged)</td>
            </tr>
            <tr>
                <td></td>
                <td colspan='2'><input type='submit' name='change_db_settings' class='submit' value='Connect and write to config'/></td>
            </tr>
        </table>
        
    </form>
    </div>
    ";
    $halt = true;
}

