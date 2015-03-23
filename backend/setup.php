<?php
/** Aquarius setup handler
  * Executes the files 'aquarius/setup/XXX_*.php (alphabetic order) until one sets
  * $halt = true.
  */

// We need to know what's going on
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);

date_default_timezone_set('UTC');

$install_dir = realpath(dirname(__FILE__).'/../..').'/';
require_once $install_dir.'core/lib/log.php';
require_once $install_dir.'core/lib/utility.lib.php';
require_once $install_dir.'core/lib/Maintenance_Mode_Control.php';


try {

    Maintenance_Mode_Control::check();

    echo "<html>
<head>
    <title>aquarius setup</title>
    <link href='css/admin.css' rel='stylesheet' media='screen' type='text/css' />
    <style type='text/css'>body { padding: 60px 90px; }</style>
</head>
<body>

<h1>aquarius setup</h1>
";
    $halt = false;
    $setup_dir = $install_dir.'core/setup/';
    foreach(scandir($setup_dir) as $setup_file) {
        if (preg_match('/^[0-9]{3}.*\\.php$/', $setup_file)) {
            echo "<!-- Running $setup_file -->\n";
            require($setup_dir.$setup_file);
            if ($halt) break;
        }
    }
    echo "
</body>
</html>";

} catch (Exception $exception) {
    process_exception($exception);
}

/* Setup convenience functions */
function message($class, $str) {
    $estr = htmlspecialchars($str);
    echo "<div class='message $class'>$estr</div>";
}

/** Update config files.
  *
  * We try to damage config files as little as possible.
  */
class Aqua_Config_File {
    var $filename;
    var $config;
    var $content;
    var $changed = false;

    /** Read the configuration file */
    function __construct($filename) {
        $this->filename = $filename;
        $this->original = false;
        if (file_exists($this->filename)) {
            $this->content = file_get_contents($this->filename);

            // Hack: remove ending closing tag
            $this->content = preg_replace('%\\?>$%', '', $this->content);

            // Read config values
            $config = array();
            $included = include_once $this->filename;
            if ($included) @include $this->filename; // Ugly hack to avoid seeing 'already defined' errors for legacy configs
            $this->config = $config;
        } else {
            $date = date('Y.m.d');
            $this->content = "<?php /* Aquarius configuration generated $date */\n";
            $this->config = array();
        }
    }

    /** Fetch current config value */
    function value($config_path) {
        return conf($this->config, $config_path);
    }

    /** Change or add a config parameter
      *
      * This could go wrong in many ways we don't even want to know. To make
      * things obvious, lines are never overwritten, but new ones added to the
      * end of the file. This keeps destruction to a minimum because old lines
      * are preserved but overridden.
      */
    function set($config_path, $value) {
        $change = conf($this->config, $config_path) !== $value;
        if ($change) {
            $path_parts = explode('/', $config_path);
            if (empty($path_parts)) throw new Exception("Cowardly refusing to reset whole config.");

            $config_line = "\$config['".join("']['", $path_parts)."'] = ".var_export(str($value), true).';';
            $line = sprintf("%-60s // %s\n", $config_line, date('Y.m.d'));

            $this->content .= $line;
        }
        $this->changed = $this->changed || $change;
        return $change;
    }

    function write() {
        if (!$this->changed) return 0;
        $result = file_put_contents($this->filename, $this->content);
        return $result ? 1 : -1;
    }
}
