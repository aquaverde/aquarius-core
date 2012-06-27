<?
/** Add site-specific keys to local config */

$localconf_file = $install_dir.'config.local.php';
$localconf = new Aqua_Config_File($localconf_file);

$set = array();

$echokey = $localconf->value('echokey');
if (empty($echokey)) {
    $echokey  = base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $echokey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $echokey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $echokey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $localconf->set('echokey', $echokey);
    $set []= "echo key";
}

$secretkey = $localconf->value('secretkey');
if (empty($secretkey)) {
    $secretkey  = base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $secretkey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $secretkey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $secretkey .= base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);
    $localconf->set('secretkey', $secretkey);
    $set []= "secret key";
}

$configured_timezone = $localconf->value('timezone');
$timezone = $configured_timezone;
if (strlen($timezone) > 0) {
    if (!date_default_timezone_set($timezone)) {
        message('warn', "Configured timezone $timezone is invalid, will be reset.");
        $timezone = false;
    }
}

if (!$timezone) {
    $timezone = ini_get('date.timezone');
    if ($timezone && date_default_timezone_set($timezone)) {
        message('', "Configuring timezone $timezone taken from PHP ini");
    } else { 
        $timezone = false;
    }
}

if (!$timezone) {
    $timezone = 'Europe/Berlin';
    if (date_default_timezone_set($timezone)) {
        message('', "Configuring default timezone $timezone.");
    } else {
        throw new Exception("Could not use default timezone $timezone. Leaving this world for a better place where time is time and timezones are naught.");
    }
}

if ($configured_timezone != $timezone) {
    $localconf->set('timezone', $timezone);
    $set []= 'timezone';
}


if (!empty($set)) {
    $result = $localconf->write();
    if ($result == 1) {
        message('', "Added configuration lines: ".join(', ', $set));
    } else {
        message('warn', "Couldn't write $localconf_file");
        echo "<form action=''><input type='submit' value='retry'/></form>";
        $halt = true;
    }
}
