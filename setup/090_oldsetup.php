<?php

function check_lang() {
    $lang = DB_DataObject::factory('languages');
    $lang->active = true;
    if ($lang->count() > 0) {
        return 'ok';
    } else {
        $new_lg = get($_POST, 'create_lang');
        if ($new_lg) {
            $lang->lg = $new_lg;
            return $lang->insert()?'done':'failed';
        }
        return 'create code: <input type="text" name="create_lang"/> <input type="submit" value="Create"/>';
    }
}

function check_admin() {
    $admin = DB_DataObject::factory('users');
    $admin->active = true;
    $admin->status = 0;
    if ($admin->count() > 0) {
        return 'ok';
    } else {
        $new_admin = get($_POST, 'create_admin');
        if ($new_admin) {
            $admin->name = $new_admin;
            $admin->password = md5($_POST['create_admin_pw']);
            return $admin->insert()?'done':'failed';
        }
        return 'create: <input type="text" name="create_admin"/> pw: <input type="password" name="create_admin_pw"/> <input type="submit" value="Create"/>';
    }
}

?>



<div class='bigbox'>
<h2>Initializing Aquarius</h2>
<form method="post">
        <ul class="setupitems">
            <li>Current FS path: <?php echo dirname(__FILE__)?></li>
            <li>Active include paths: <?php echo get_include_path()?></li>
            <li>Active language: <?php echo check_lang()?></li>
            <li>Admin user: <?php echo check_admin()?></li>
        </ul>
    </div>
</form>
<br/>
<div class="bigbox">
<h2>Checking directories &amp; permissions</h2>
<ul class="setupitems">
<?php 
$paths = array(
    $aquarius->install_path."cache"                         => array('req'=>'must'),
    $aquarius->root_path."download"                         => array('req'=>'should'),
    $aquarius->root_path."pictures"                         => array('req'=>'should'),
    $aquarius->root_path."pictures/content"                 => array('req'=>'should'),
    $aquarius->root_path."pictures/richtext"                => array('req'=>'should')
);

foreach($paths as $path=>$info) {
        echo "<li>Checking $path: ";
        $success = is_dir($path);
        if (!$success) {
            echo "(creating directory) ";
            $success = @mkdir($path, 0777, true);
        }
        if ($success) {
            echo "(testing write) ";
            $success = @fclose(@fopen($path."/test.write.permissions", 'a')) && unlink($path."/test.write.permissions");
            if (!$success) {
                echo "(relaxing permissions) ";
                $success = @chmod($path, 0777);
            }
        }
        if ($success) echo "<span style=\"color:green;\">done</span>.";
        else echo "<b style=\"color:red;\">failed.</b> This directory ".$info['req']." be writable.";
        echo "</li>
";
}

?>
</ul>
</div>
<br/>
<div class="bigbox">
<h2>Enjoy the website</h2>
<p>
<a href="./index.php">> Backend (admin)</a><br/>
<a href="/">> Frontend (website)</a>
</p>
</div>

</body>
</html>
