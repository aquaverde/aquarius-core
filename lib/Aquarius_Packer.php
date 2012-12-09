<?php
/** Create an installer pack */
class Aquarius_Packer {
    static $export_aliases = array(
        'bare' => array('aquarius/core'),
        'full' => array('bare', '?aquarius/config.php', '?aquarius/ckconfig.js', '?aquarius/dbadmin'),
        'site' => array('interface', 'lib', 'css', 'download', 'pictures', 'aquarius/templates', 'robots.txt', '.htaccess', 'favicon.ico', 'aquarius/init'),
        'all'  => array('full', 'site'),
    );

    var $ignore = array('.git', 'adminer');
    
    var $message_handler;
    
    static function exec_enabled() {
        $disabled = explode(', ', ini_get('disable_functions'));
        return !in_array('exec', $disabled);
    }
    
    function message($msg, $continued = false) {
        if ($this->message_handler) $this->message_handler->message($msg, $continued);
    }

    function pack($aquarius, $targets, $options=array()) {
        // work from core dir first
        $coredir = $aquarius->core_path;
        $changed = chdir($coredir);
        if (!$changed) throw new Exception("Unable to change dir to $coredir");
        
        
        $version = false;
        
        if (self::exec_enabled()) {
            // Try to detect the installation version
            $version = exec('git describe 2>&1', $_, $version_error);

            // Cache version information in file, or use that file if git didn't work
            if ($version_error === 0) {
                file_put_contents('revision', $version);
            } else {
                $version = @file_get_contents('revision');
            }
        }
        if ($version === false) {
            $version = 'unspecified';
        }
        
        $this->message("Version: $version");
        
        // Build list of names to go into filename
        date_default_timezone_set('UTC');
        $export_names = array('aquarius');
        $export_names []= date("Ymd-Hi");
        $export_names []= $version;
        
        // Preset options
        $options = array_merge(array(
            'inline' => false,
            'compress' => 'bz'
        ), $options);
        
        // Do we include the archive in the installer file?
        $inline = $options['inline'];

        // Change to web root before expanding aliases
        $rootdir = $aquarius->root_path;
        $changed = chdir($rootdir);
        if (!$changed) throw new Exception("Unable to change dir to $rootdir");
        
        $exports = array();
        $export_names []= join('.', $targets);
        while($desired_export = array_shift($targets)) {
            if (isset(self::$export_aliases[$desired_export])) {
                $targets = array_merge(
                    self::$export_aliases[$desired_export],
                    $targets
                );
            } else {
                // Read 'optional' flag
                $is_optional = $desired_export[0] == '?';
                if ($is_optional) $desired_export = substr($desired_export, 1);
                
                // Remove trailing slash if there is one (common source of problems)
                if (substr($desired_export, -1) == DIRECTORY_SEPARATOR) {
                    $desired_export = substr($desired_export, 0, -1);
                }
                if (!file_exists($desired_export)) {
                    if (!$is_optional) throw new Exception("Path does not exist: $desired_export");
                } else {
                    $exports []= $desired_export;      
                }
            }
        }
        
        $exports = array_unique($exports);
        sort($exports);
        
        if (empty($exports)) throw new Exception("No exports.");

        // Create a somewhat unique tag so we don't overwrite other files
        $export_tag = substr(uniqid(), -5);
        $this->message("Export tag: $export_tag");
        
        // Get a location we can export to
        $export_dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'Aquarius-Export-'.$export_tag;
        mkdir($export_dir);
        $work_dir = getcwd();
        
        $this->message("Creating export in $export_dir");
        
        $export_names []= $export_tag;
        
        $this->message("Exporting: ", true);
        $file_list = array();
        foreach($exports as $i => $base) {
            $this->message(($i==0?'':', ').$base, true);
            $new = "$base.$export_tag.new";
            $old = "$base.$export_tag.old";
            $file_list []= compact('base', 'new', 'old');
            $export_files []= $new;

            $export_parent_dir = $export_dir.DIRECTORY_SEPARATOR.dirname($base);
            if (!is_dir($export_parent_dir)) {
                mkdir($export_parent_dir, 0700, true);
            }
            $export_new_dir = $export_dir.DIRECTORY_SEPARATOR.$new;
            try {
                $num = $this->cp($base, $export_new_dir);
                $this->message("($num)", true);
            } catch(Exception $e) { throw new Exception("failed exporting $base: ".$e->getMessage()); }
        }
        $this->message(".");
        
        // Load the tar library
        $tar_lib_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'Packer_Tar.php';
        include($tar_lib_path);

        $export_name = join('_', $export_names);
        $export_name = preg_replace('%aquarius/%', '', $export_name); // Remove 'aquarius/' path prefix
        $export_name = preg_replace('%/%', '', $export_name); // Remove '/' from name

        $archive_format = $options['compress'];
        switch($archive_format) {
        case 'bz':
            $archive_format = 'bz2';
            $archive_suffix = '.bz';
            break;
        case 'gz':
            $archive_format = 'gz';
            $archive_suffix = '.gz';
            break;
        case 'no':
            $archive_format = null;
            $archive_suffix = '';
            break;
        default:
            throw new Exception("Invalid compression format '$archive_format'.\n");
        }
        
        
        $tar_name = $export_name.'.tar'.$archive_suffix;
        $this->message("Creating $tar_name\n", true);
        $tar_path = $export_dir.DIRECTORY_SEPARATOR.$tar_name;
        $tar = new Archive_Tar($tar_path, $archive_format);

        chdir($export_dir);

        $tar->add($export_files);

        $this->message("Done.");

        $archive_md5 = md5_file($tar_path);
        $this->message("Archive MD5 sum: $archive_md5");

        $archive_contents = file_get_contents($tar_path);

        $this->message("Writing installer: ", true);

        $build_date = date("Y.m.d-H.i");
        $build_host = php_uname('n');
        $content_list = join(' ', $exports);

        // Do I have style?
        $css = '<style type="text/css">'.file_get_contents($aquarius->core_path.DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'admin.css').'</style>';

        $export_pack_basename = $work_dir.DIRECTORY_SEPARATOR.$export_name;
        $export_pack_instname = $export_pack_basename.'.php';
        
        // Writing scripts that write scripts is one of my favourite pastimes.
        // It got out of hand here. Could use some templating love.
        file_put_contents($export_pack_instname, "<?php 
/* Aquarius installer pack 
 * Date: $build_date
 * Host: $build_host
 * Tag: $export_tag
 * Version: $version
 * Content: $content_list
 */
".'

set_time_limit(0);
error_reporting(E_ALL);
ini_set("display_errors","1");
date_default_timezone_set("UTC");

$op = false;
$ops = array();
if (isset($_POST)) $ops = array_keys($_POST);
if (isset($argv)) $ops = array_merge($ops, $argv);

if (in_array("setup", $ops)) {
    include "aquarius/core/lib/Maintenance_Mode_Control.php";
    Maintenance_Mode_Control::enable(2);
    header("Location: aquarius/admin/setup.php");
    exit;
}


echo "<html>'.addslashes($css).'
    <body>
        <div class=\"dim\">";


// Included tar library follows
?>'.file_get_contents($tar_lib_path).'<?php

// tell PEAR to tell us
PEAR::setErrorHandling(PEAR_ERROR_TRIGGER);

if (in_array("puttar", $ops)) {
    $tar_name = '.var_export($tar_name, true).';
'.($options['inline'] ? '
    $tar_fp = fopen($tar_name, "wb");
    if (!$tar_fp) die("Unable to open $tar_name for writing");
    $pack_fp = fopen(__FILE__, "rb");
    if (!$pack_fp) die("Unable to open ".__FILE__." for reading");
    fseek($pack_fp, __COMPILER_HALT_OFFSET__);
    for(; $buffer = fread($pack_fp, 1048576), ($buffer !== FALSE && $buffer !== ""); ) {
        echo "Writing ".ceil(strlen($buffer) / 1024)."k</br>";
        fwrite($tar_fp, $buffer);
    }
    fclose($tar_fp);
    fclose($pack_fp);
' : '').'

    if (!file_exists($tar_name)) {
        die("Missing archive $tar_name");
    }

    $actual_md5sum = md5_file($tar_name);
    $expected_md5sum = '.var_export($archive_md5, true).';
    if ($actual_md5sum != $expected_md5sum) {
        die("Install-archive $tar_name damaged. (Expected MD5 sum: $expected_md5sum, actual: $actual_md5sum. Make sure to use binary mode when uploading via FTP)");
    } else {
        echo "<!-- Verfied MD5 sum: $expected_md5sum -->\n";
    }

    $op = "puttard";
}

$file_list = '.var_export($file_list, true).';

if (in_array("unpack", $ops)) {

    $tar_name = '.var_export($tar_name, true).';
    echo "<!-- Unpacking $tar_name ... ";
    $tar = new Archive_Tar($tar_name, '.var_export($archive_format, true).');
    $success = $tar->extract();
    if (!$success) {
        die("-->Failed extracting $tar_name");
    }
    echo "done. -->\n";
    $op = "unpacked";

    $mode = octdec($_POST["mode"]);
    if ($mode > 0) {
        function chmodr($path, $filemode, &$fails = false) {
            $success = chmod($path, $filemode);
            $all_alright = $success;
            $some_alright = $success;
            if (!$success && $fails !== false) {
                $fails []= $path;
            }

            if (is_dir($path)) {
                $dh = opendir($path);
                $descendant_fails = array();
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != "..") {
                        $fullpath = $path."/".$file;
                        $success = chmodr($fullpath, $filemode, $descendant_fails);
                        if ($success) $some_alright = true;
                        else $all_alright = false;
                    }
                }
                if (!$all_alright && $some_alright) {
                    $fails = array_merge($fails, $descendant_fails);
                }
            }
            return $all_alright;
        }

        foreach($file_list as $file_names) {
            $path = $file_names["new"];
            echo "<!-- Chmodding ".decoct($mode)." $path:";
            $fails = array();
            chmod(dirname($path), $mode);
            $success = chmodr($path, $mode, $fails);
            if ($success) {
                echo "done. -->\n";
            } else {
                echo "failed setting permissions in ".join(", ", $fails)."-->\n";
            }
        }
    }
}





if (in_array("replace", $ops)) {
    foreach($file_list as $file_names) {
    $base_name = $file_names["base"];
    if (file_exists($base_name)) {
        $old_name = $file_names["old"];
        $success = rename($base_name, $old_name);
        if ($success === FALSE) die("Unable to move $base_name to $old_name");
        else echo "<!-- Moved $base_name to $old_name -->\n";
    }
    $new_name = $file_names["new"];
    $success = rename($new_name, $base_name);
    if ($success === FALSE) die("Unable to move $new_name to $base_name");
    else echo "<!-- Moved $new_name to $base_name -->\n";
    }
    $op = "replaced";
}



if (in_array("undo", $ops)) {
    foreach($file_list as $file_names) {
    $base_name = $file_names["base"];
    if (file_exists($base_name)) {
        $new_name = $file_names["new"];
        $success = rename($base_name, $new_name);
        if ($success === FALSE) echo("Failed moving $base_name to $new_name\n");
        else echo "<!-- Moved $base_name to $new_name -->\n";
    }
    $old_name = $file_names["old"];
    $success = rename($old_name, $base_name);
    if ($success === FALSE) echo("Unable to move $old_name to $base_name\n");
    else echo "<!-- Moved $old_name to $base_name -->\n";
    }
    $op = "undone";
}

echo "</div>";

if ($op == "replaced") {
    echo \'
    <h1>Files installation</h1>
    <div class="bigbox">
    <h2>New files installed</h2>
    Check out the new installation<br/>
    <form method="post">
        <input type="submit" name="setup"  value="Proceed to setup" class="submit"/>
    </form>
    </div><br/>
    <form method="post">
        <input type="submit" name="undo" value="Undo install" class="button"/>
    </form>
    \';
} else {
    
    $test_file = __FILE__.".test";
    @unlink($test_file);
    $write_success = touch($test_file);
    if (!$write_success) die("No write permissions for webserver in ".dirname(__FILE__).". Forget it.");

    $test_perms = stat($test_file);
    @unlink($test_file);
    $upload_perms = stat(__FILE__);
    
    $suggested_mode = "";
    $perm_warning = "";
    if ($test_perms["uid"] == $upload_perms["uid"]) {
        $perm_warning = "<div style=\'margin-bottom: 5px;\' class=\'dim\'>Based on a quick check, this installer assumes that files are created with the same owner as the uploader of this installer. Adjusting file permissions seems unneccessary.</div>";
        $suggested_mode = "";
    } elseif($test_perms["gid"] == $upload_perms["gid"]) {
        $perm_warning = "<div style=\'margin-bottom: 5px;\' class=\'dim\'>Based on a quick check, this installer assumes that files <b class=\'dim\'>must be created group-readable and writable</b> so that both user and webserver may access them.</div>";
        $suggested_mode = "0770";
    } else {
        $perm_warning = "<div style=\'margin-bottom: 5px;\' class=\'dim\'>Based on a quick check, this installer assumes that files <b class=\'dim\'>must be created world-readable and writable</b> so that both user and web-server can read and write to installed files. Depending on your setup, <b>this may be stupid if not incredibly, stupendously dangerous</b>. </div>";
        $suggested_mode = "0777";
    }
    
    
    
    echo \'
<h1>Install aquarius CMS</h1>
<div class="bigbox">
    <h2>Start installation</h2>
    <form method="post">
        <input type="hidden" name="puttar" value="1"/>
        <input type="hidden" name="unpack" value="1"/>
        <label> \'.$perm_warning.\' Override file access mode: <input type="input"  name="mode" class="inputsmall"  value="\'.$suggested_mode.\'" length="4"/> (0700 permits owner only, 0777 permits everyone)</label>
        <input type="submit" name="replace" class="submit" style="margin-top:0" value="Install new files"/>
    </form>
</div>
<br/>
<div class="bigbox">
    <h2>Undo</h2>
    If you find that the files installed by this pack create problems, you can move
    the old ones back into place.
    <form method="post">
        <input type="submit" name="undo" class="button" style="margin-top:3px;" value="Undo: Move old files back after a replace"/>
    </form>
</div>
<br/>
<div class="bigbox">
    <h2>Install pack information</h2>
    <table>
        <tr><td width="70">Build date</td><td>'.$build_date.'</td></tr>
        <tr><td>Build host</td><td>'.$build_host.'</td></tr>
        <tr><td>Build tag</td><td>'.$export_tag.'</td></tr>
        <tr><td>Revision</td><td>'.$version.'</td></tr>
        <tr>
            <td>Contents</td>
            <td><ul><li>'.join('</li><li>', $exports).'</li></ul></td>
        </tr>
    </table>
    <br/>
</div>
        \';
}
echo "
</body>
</html>
";'.($options['inline'] ? '__halt_compiler();' : ''));
        $this->message("done.");

        if ($options['inline']) {
            $this->message("Appending archive: ", true);
            file_put_contents($export_pack_instname, file_get_contents($tar_path), FILE_APPEND);
        } else {
            $this->message("Copying archive: ", true);
            copy($tar_path, $export_pack_basename.'.tar'.$archive_suffix);
        }
        $this->message("done.");

        $this->message("Wrote installer to $export_pack_instname");

        // Things my mother taught me
        $this->message("Deleting temp dir $export_dir");
        $removed = false;
        if (self::exec_enabled()) {
            // It's just faster
            exec('rm -r "'.escapeshellcmd($export_dir).'"', $_, $del_error);
            $removed = $del_error === 0;
        } else {
            include 'file_mgmt.lib.php';
            $removed = rmall($export_dir);
        }
        
        if (!$removed) {
            // Oh well, the pack was generated so we don't fail here.
            // Somebody else gets paid to pick up the garbage, right?
            $this->message("Error while deleting $export_dir");
        }
        
        return $export_pack_instname;
    }


    function cp($src, $dst, $exclude=array()) {
        $name = basename($src);
        if ($name == '.' || $name == '..' || in_array($name, $exclude)) {
            return 0;
        }
        
        $rsrc = realpath($src);
        if (!$rsrc) throw new Exception("Nonexisting source path '$src'");

        if(is_file($src)) {
            if(copy($rsrc, $dst)) {
                touch($dst, filemtime($rsrc));
                return 1;
            } else {
                throw new Exception("Unable to copy file '$name' ('$rsrc' to '$dst')");
            }
        } else if(is_dir($rsrc)) {
            $success = mkdir($dst);
            if (!$success) throw new Exception("Unable to create destination directory $dst");
            $num = 1;
            foreach(scandir($rsrc) as $contained) {
                $num += $this->cp($rsrc.DIRECTORY_SEPARATOR.$contained, $dst.DIRECTORY_SEPARATOR.$contained, $exclude);
            }
            return $num;
        } else {
            throw new Exception("$rsrc is neither file nor dir, please try to adhere to our preconceptions of a simple world");
        }
    }
}

