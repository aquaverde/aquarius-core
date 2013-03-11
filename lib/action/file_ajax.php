<?php 
/** Provide access to file selection templates for AJAX requests
  *
  * All actions take the following request parameters:
  *   formfield: Id of the formfield (path, and subdir options are taken from that field)
  *   subdir: currently chosen subdir
  *
  * The following operations are supported:
  *
  * options -> formfield.file.file_options.tpl:
  *     Option list for use in a <select> input.
  *     Additional request parameter 'selected' with name of currently selected file.
  *
  * thumb -> formfield.file.thumb.tpl:
  *     File thumbnail
  *     Additional request parameter 'file' with name of file.
  *
  * empty_row -> formfield.file.row.tpl:
  *     Empty file row.
  *     Expects additional request parameter 'next_id' with integer for row id
  *
  */
class action_file_ajax extends AdminAction {

    var $props = array("class", "request");

    /** Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }

    /** Load formfield and subdir from request parameters
      */
    function load_from($request) {
        return $this->load(get($request, 'formfield'), get($request, 'subdir'));
    }

    /** Load and sanitize variables
      * @param form_field_id id of the corresponding form field
      * @param subdir selected subdirectory, or false (this will be ignored if the formfield does not allow selection of subdirs)
      *
      * Returns dictionary with these entries:
      *   form_field: form field
      *   use_subdir: Wether selection of subdirs is enabled
      *   subdir: currently selected subdir, or false
      *   path: path relative from filebasedir
      *   absolute_path: Filesystem path to currently selected directory
      */
    function load($form_field_id, $subdir) {
        require_once "lib/file_mgmt.lib.php";

        $form_field = db_Form_field::staticGet($form_field_id);
        if (!$form_field) throw new Exception("Invalid form_field id '$form_field_id' in request");

        $path = $form_field->sup3;

        $use_subdir = (bool)$form_field->sup1;
        if ($use_subdir) {
            $path .= '/'.$subdir;
        }

        $absolute_path = ensure_filebasedir_path($path);
        if (!$absolute_path) throw new Exception("$path is not in FILEBASEDIR");
        $path = substr($absolute_path, strlen(FILEBASEDIR));

        return compact('form_field', 'use_subdir', 'subdir', 'path', 'absolute_path');
    }
}

class action_file_ajax_empty_row extends action_file_ajax implements DisplayAction {


    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $result->skip_return();

        extract($this->load_from($request));

        if (!$form_field->multi) throw new Exception("Trying to get empty row for form field $form_field->name, which does not support multiple values");

        $new_id = intval(get($request, 'new_id'));

        /* The Contentedit action knows how to prepare fields, so we use that despite the ugly interfacing issues with internal data structures.
         * When this breaks (it will) examine changes in lib/action/contentedit.php and lib/formtypes/file.php and adapt this.
         * The long-term solution is of course to seaparate field preparation from the contentedit action, but that's far off. */
        Action::use_class('contentedit');

        // We want one empty file value, using $new_id for the file value
        $value = array($new_id => array());
        $field = action_contentedit_edit::prepare_container(false, false, $form_field, $form_field->name, $value, array());

        $fileval = first($field['value']);

        $smarty->assign('field', $field);
        $smarty->assign('fileval', $fileval);
        $result->use_template('formfield.file.row.tpl');
    }
}

class action_file_ajax_options extends action_file_ajax implements DisplayAction {

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $result->skip_return();

        extract($this->load_from($request));

        $files = array();
        if (!$use_subdir || $subdir) $files = listFiles($absolute_path);
        var_dump($absolute_path);
        $smarty->assign('files', $files);
        $smarty->assign('selected', get($request, 'selected'));
        $result->use_template('formfield.file.file_options.tpl');
    }
}

class action_file_ajax_thumb extends action_file_ajax implements DisplayAction {

    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $result->skip_return();

        extract($this->load_from($request));

        $fileinfo = false;
        $file = get($request, 'file');
        if (!empty($file)) {
            $file_path = $absolute_path.'/'.get($request, 'file');
            $checked_file_path = ensure_filebasedir_path($file_path);
            if (!$checked_file_path) throw new Exception("$file_path is not in FILEBASEDIR");
            $fileinfo = new Fileinfo($checked_file_path);
        }
        
        $smarty->assign('fileinfo', $fileinfo);
        $result->use_template('formfield.file.thumb.tpl');
    }
}
