<?php 
class action_filemgr extends AdminAction {
    var $props = array("class", "command", "subdir");
    //var $props = array("class", "command", "file", "spinner");

    /** FIXME: Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }
}

/** Show list of files in a directory */
class action_filemgr_list extends action_filemgr implements DisplayAction {

    var $props = array("class", "command", "subdir", "spinner");

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";
        require_once "lib/spinner.class.php";

        $managerStyle = $this->command;
        $isPopup = in_array('popup', $this->params);

        // check for the selected directory
        if ( !empty($_POST['selectedDir']) ) {
                $this->subdir = $_POST['selectedDir'];
                // if the dir was submited by the dropdown, we must reset
                // the spinner settings too.
                $this->spinner = 0;
        }

        if (empty($this->subdir)) {
            $this->subdir = DEFAULT_SELECTED_DIR;
        }

        // set the spinner to default if not set
        $this->spinner = $this->spinner;

        // fetch the file filter
        $myFilter = requestvar('fileFilter', '');

        // get all the files.....
        $fileArray = listFileInfo($this->subdir, $myFilter);

        $smarty->assign("fileFilter", $myFilter);

        // initialize the spinner if we need one
        $hasSpinner = count($fileArray) > MAX_FILES_PER_PAGE;
        $smarty->assign("hasSpinner", $hasSpinner);
        $spinner = false;
        if ($hasSpinner) {
            $spinner = new Spinner($this->spinner, MAX_FILES_PER_PAGE, count($fileArray), clone $this, create_function('$action, $position', '$action->spinner = $position; return $action;'));
            $smarty->assign("spinner", $spinner);

            // Limit fileArray to entries within the current spinner page
            $fileArray = $spinner->current_slice($fileArray);
        }

        $files = array();
        foreach($fileArray as $file) {
            
            $fileinfo = $file->fileinfo();

            // Create special attr string to limit thumbnail width and height to max 85
            if ($fileinfo['type'] == 'image' && isset($fileinfo['th_size'])) {
                $fileinfo['th_attrs'] = $fileinfo['th_size'][3];
                if ($fileinfo['th_size'][0] > $fileinfo['th_size'][1]) {
                    if ($fileinfo['th_size'][0] > FILE_SHOW_THUMB_MAX) $fileinfo['th_attrs'] = 'width="'.FILE_SHOW_THUMB_MAX.'"';
                } else {
                    if ($fileinfo['th_size'][1] > FILE_SHOW_THUMB_MAX) $fileinfo['th_attrs'] = 'height="'.FILE_SHOW_THUMB_MAX.'"';
                }
            }

            $fileinfo['detail'] = Action::make('filemgr', 'detail', $this->subdir.DIRECTORY_SEPARATOR.$file->name(), $myFilter);

            $files[] = $fileinfo;
        }
        $smarty->assign("files", $files);

                
        // get the directories
        $availableDirectories = get_cached_dirs();

        
        // Load directory settings
        $dir_props = DB_DataObject::factory('directory_properties');
        $dir_props->load($this->subdir);
        $smarty->assign('dir_props', $dir_props);

        if ( !empty($this->params[0]) ) {
            $smarty->assign("selectedFile", $this->params[0]);
        }
    
        if ( !empty($this->params[1]) ) {
            $smarty->assign("fieldID", $this->params[1]);
        }

        $browseaction = Action::make('filemgr', 'browse', $this->subdir, $this->spinner);
        $browseaction->params = $this->params;
        $listaction   = Action::make('filemgr', 'list',   $this->subdir, $this->spinner);
        $listaction->params = $this->params;
        $smarty->assign(compact('browseaction', 'listaction'));
        
        $smarty->assign("availableDirectories", $availableDirectories);
        $smarty->assign("project_url", ABSOLUTE_PROJECT_URL);
        $smarty->assign("selectedDir", $this->subdir);
        $smarty->assign("managerStyle", 'list');
        $smarty->assign("isPopup", $isPopup);

         $result->use_template('filemgr.tpl');
    }
}


/** Same as action_filemgr_list except that files are shown in a grid view. */
class action_filemgr_browse extends action_filemgr_list implements DisplayAction {
    // This is a separate class for historical reasons
    function process($aquarius, $request, $smarty, $result) {
        parent::process($aquarius, $request, $smarty, $result);
        $smarty->assign("managerStyle", 'browse');
    }
}


/** Show details for a file
  *
  * Params:
  *   path: path to the file, relative to filebasedir
  *   filter: filter to use when looking for files to link as prev and next
  */
class action_filemgr_detail extends action_filemgr implements DisplayAction {

    var $props = array("class", "command", "path", "filter");

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $dir = dirname($this->path);
        $name = basename($this->path);

        // Because we want to link to prev and next file, we load the full list
        // of files in the directory then search through it.
        $files = listFileInfo($dir, $this->filter);


        $file = false;
        $attrs = false;
        $last = false;
        $prev = false;
        $next = false;
        $may_delete = false;
        foreach($files as $fileinfo) {
            if ($name === $fileinfo->name()) {
                $attrs = $fileinfo->fileinfo();
                $references = references($fileinfo);

                // Image may be deleted if there are no references
                $may_delete = count($references) < 1;

                $attrs['references'] = array();
                foreach($references as $content_id) {
                    $ref_content = DB_DataObject::factory('content');
                    $ref_content->get($content_id);
                    $attrs['references'][] = $ref_content;
                }

                $file = $fileinfo;
                $prev = $last;
            }

            if ($last && $name === $last->name()) {
                $next = $fileinfo;
            }

            $last = $fileinfo;
        }

        if ($next) {
            $smarty->assign('next', Action::make('filemgr', 'detail', $dir.DIRECTORY_SEPARATOR.$next->name(), $this->filter));
        }

        if ($prev) {
            $smarty->assign('prev', Action::make('filemgr', 'detail', $dir.DIRECTORY_SEPARATOR.$prev->name(), $this->filter));
        }

        $smarty->assign('may_delete', $may_delete);
        if (!empty($request['delete'])) {
            $smarty->assign('delete', Action::make('filemgr', 'delete', $this->path));
        }

        $smarty->assign("file", $file);
        $smarty->assign("attrs", $attrs);
        $smarty->assign("project_url", ABSOLUTE_PROJECT_URL);

        if ($file) {
            $result->use_template('filemgr_detail.tpl');
        } else {
            // If the file was not found, this probably means it was deleted and we
            // fall back to the previous action
        }
    }
}


class action_filemgr_delete extends action_filemgr implements ChangeAction {
    function process($aquarius, $request, $result) {
        require_once "lib/file_mgmt.lib.php";
       
        $file = ensure_filebasedir_path($this->file);
        Log::debug("Deleting ".$file);
        unlink(file);
        $messages[] = array('s_message_file_deleted', $myFile);
    }
}


/** Show controls to upload files into a directory */
class action_filemgr_upload extends action_filemgr implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";
        $smarty->assign("existingFiles", implode(",", listFiles(get($_REQUEST, 'selectedDir', DEFAULT_SELECTED_DIR))));
        $smarty->assign("selectedDir", get($_REQUEST, 'selectedDir', DEFAULT_SELECTED_DIR));
        $smarty->assign("availableDirectories", get_cached_dirs());
        $smarty->assign("maxFileUpload", FILE_MAX_UPLOAD_COUNT);
        $smarty->assign("fileCount", get($_POST, 'fileCount', 1));
        $smarty->assign("upload_files_action", Action::make('filemgr', 'upload_files', $this->subdir));
        $result->use_template('filemgr_upload.tpl');
    }
}

/** Receive files and store them in a directory */
class action_filemgr_upload_files extends action_filemgr implements ChangeAction {
    function process($aquarius, $request, $result) {
        require_once "lib/file_mgmt.lib.php";
        check_for_failed_file_uploads();

        $selected_dir = $_REQUEST['selectedDir'];

        foreach($_FILES as $key => $upload_info) {
            $unzip = isset($_REQUEST[$key.'_zip']);
            $upresult = process_upload($upload_info, $selected_dir, $unzip);
            $result->add_message($upresult['message']);
        }
    }
}

class action_filemgr_showPicture extends action_filemgr implements DisplayAction {
    var $props = array("class", "command", "file");
    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign("file", $this->file);
        $result->use_template('popup_picture.tpl');
    }
}
