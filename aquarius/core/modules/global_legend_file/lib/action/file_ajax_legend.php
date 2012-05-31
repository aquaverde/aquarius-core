<?
Action::use_class('file_ajax');

/** Provide global legend for file */
class action_file_ajax_legend extends action_file_ajax implements SideAction {

    var $props = array('class');

    function process($aquarius, $request) {
        global $DB;

        extract($this->load_from($request));

        $escaped_path = mysql_real_escape_string('/'.$path.'/'.requestvar('file'));

        echo $DB->singlequery("SELECT legend FROM file_legend WHERE file='$escaped_path'");
    }
}
?>
