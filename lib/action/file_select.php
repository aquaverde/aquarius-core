<?php 
/** File selection popup action
  * Action parameters:
  *   target_id (string):
  *       Reference ID from the opener.
  *
  *   dir (path):
  *       Root directory (relative to web root) for file selection.
  *
  *   subdir (path):
  *       Selected directory relative from dir (may be empty). Hack: to disallow subdir selection, pass '/' here.
  *
  *   file (string):
  *       Currently selected filename (may be empty)
  *
  *   page (integer or 'all'):
  *       Currently selected page. (May be empty, defaults to first page or page where selected file is presented)
  *
  *   filter (string):
  *       filename filter. (May be empty)
  *
  *   browse (string):
  *       Whether to list one file per row ('list') or multiple files per row ('browse'). If this parameter is empty the session value file_manager_style[dir], the config value DEFAULT_MANAGER_STYLE, or finally 'list' is used. On every execution the session value file_manager_style[dir] is set to the selected value. This means that the file selection style for every directory is remembered during the session (if the browse parameter is left empty intially).
  *
  *   The 'target_id', 'subdir', 'file' and 'filter' parameters may be given as request parameters. In this case they replace the action parameters.
  *
  *  When a file is selected, the popup calls the function 'file_selected' in the opener. The parameters 'target_id', 'subdir', and 'file_name' are sent.
  */
class action_file_select extends AdminAction implements DisplayAction {
    var $props = array('class', 'target_id', 'dir', 'subdir', 'file', 'page', 'filter', 'browse');
    var $named_props = array('callback');

    function get_title() {
        return new Translation('s_select_file');
    }

    /** Provides icon depending on browse setting. Hack: if member 'on' is set to true, returns a 'turned on' icon. */
    function get_icon() {
        $onstr = (isset($this->on) && $this->on) ? '-on' : '';
        return $this->select_browse() == 'browse' ? "buttons/browser_thumb$onstr.gif" : "buttons/browser_list$onstr.gif";
    }

    /** This action may be used to reveal files in FILEBASEDIR to logged-in users.
      * There is no restriction on file access for logged-in users */
    function permit_user($user) {
        return (bool)$user;
    }

    /** Get browse parameter or use default */
    function select_browse() {
        global $aquarius;
        // Choose the first valid value as browse setting
        foreach (array(
            $this->browse,
            get($aquarius->session_get('file_manager_style', array()), $this->dir),
            DEFAULT_MANAGER_STYLE,
            'list'
        ) as $browse_option) {
            $option = get(array('list'=>'list', 'browse'=>'browse'), $browse_option);
            if ($option) return $option;
        }
        throw new Exception("This doesn't happen. Seriously.");
    }

    function process($aquarius, $request, $smarty, $result) {
        require_once 'lib/file_mgmt.lib.php';
        require_once 'lib/paginator.php';

        $file_uploaded = false;
        check_for_failed_file_uploads();

        if(isset($_FILES['input_file_upload']) && !empty($_FILES['input_file_upload']['name'])) {
            $root_path = $this->dir;
            if (!$root_path) throw new Exception("$this->dir is not in FILEBASEDIR");

            $current_path = false;
            if ($this->subdir == '/' || empty($this->subdir)) {
                $current_path = $root_path;
            } elseif(!empty($this->subdir)) {
                $current_path = $root_path.$this->subdir;
            }

            $upload = process_upload($_FILES['input_file_upload'],$current_path);
            if ($upload['message']) $result->add_message($upload['message']);
            $file_uploaded = !$upload['error'];
        }

        $root_path = ensure_filebasedir_path($this->dir);
        if (!$root_path) throw new Exception("$this->dir is not in FILEBASEDIR");

        // Override action parameters with parameters from request if present
        foreach(array('target_id', 'subdir', 'file', 'filter') as $param_name) {
            $param = get($request, $param_name, Null);
            if ($param !== Null) {
                $this->$param_name = $param;
            }
        }

        $current_path = false;

        if ($this->subdir == '/' || empty($this->subdir)) {
            $current_path = $root_path;
        } elseif(!empty($this->subdir)) {
            $current_path = ensure_filebasedir_path($root_path.'/'.$this->subdir);
        }

        $this->browse = $this->select_browse();
        $presets = $aquarius->session_get('file_manager_style', array());
        $presets[$this->dir] = $this->browse;
        $aquarius->session_set('file_manager_style', $presets);


        $browse = $this->browse == 'browse';
        $smarty->assign('browse', $browse);

        
        $change_action = clone $this;
        $change_action->page = 0;
        $change_action->file = '';
        $smarty->assign('change_action', $change_action);
        
        $files     = false;
        $rows      = false;
        $paginator = false;
        if ($current_path) {
            $file_list = listFiles($current_path, $this->filter);
            if(count($file_list) != 0) {
                $paginator = new Paginator($file_list, MAX_FILES_PER_PAGE, $this->page, $this);
                /* Now that we have a paginator, we can determine the default page, if this is required. */
                if (!is_numeric($this->page) && empty($this->page)) {
                    $paginator->select_page_by_item($this->file);
                    $this->page = $paginator->current_page;
                }

                $files = array();
                foreach ($paginator->current_items() as $file_name) {
                    $fileinfo = new FileInfo($current_path.'/'.$file_name);
                    $fileinfo->selected = $this->file == $fileinfo->name();
                    $files[] = $fileinfo;
                }
            }
        }

        require_once "lib/action_decorators.php";
        $browse_view = clone $this;
        $browse_view->browse = 'browse';
        $browse_view = new ActionTitleChange($browse_view, new Translation('s_view_th'));
        $list_view = clone $this;
        $list_view->browse = 'list';
        $list_view = new ActionTitleChange($list_view, new Translation('s_view_list'));
        $view_actions = array($browse_view, $list_view);
        foreach($view_actions as $action) $action->on = $action->browse == $this->browse;
        $smarty->assign('view_actions', $view_actions);

        $smarty->assign('target_id', $this->target_id);
        $smarty->assign('files', $files);
        $smarty->assign('rows', $rows);
        $smarty->assign('paginator', $paginator);

        $subdirs = false;
        if ($this->subdir != '/') {
            $subdirs = get_cached_dirs($this->dir);
            foreach($subdirs as $i => $subdir) $subdirs[$i] = substr($subdir, strlen($this->dir));
        }
        
        $smarty->assign('filter', $this->filter);
        $smarty->assign('subdir', $this->subdir);
        $smarty->assign('subdirs', $subdirs);
        $smarty->assign('callback', $this->callback ? $this->callback : 'file_select');
        if($file_uploaded) {
            $uppath = $this->subdir ? $this->subdir."/".$upload['new_name'] : $upload['new_name'];
            $smarty->assign('file_uploaded', $uppath);
        }
        
        $result->use_template('file_select.tpl');
    }
}
?>