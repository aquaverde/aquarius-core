<?php
Action::use_class('file_ajax');

/** Provide global legend for file */
class action_file_ajax_legend extends action_file_ajax implements SideAction {

    var $props = array('class', 'lg');

    function process($aquarius, $request) {
        global $aquarius;

        extract($this->load_from($request));

        $filepath = '/'.$path.'/'.requestvar('file');
        
        echo $aquarius->db->singlequery("SELECT legend FROM file_legend WHERE file=? AND (ISNULL(lg) OR lg=?) ORDER BY lg DESC", array($filepath, $this->lg));
    }
}
