<?php
/**
* 
*/
class BackendRTE
{
    private $options = array();
    
    function __construct($rte_lg, $content_lg, $height = 50)
    {
        global $aquarius;
	    
	    if(!$aquarius->conf('admin/rte/browse_path_img') || !$aquarius->conf('admin/rte/browse_path_file')) {
	    	throw new Exception("RTE config problems: 'browse_path_img' or/and 'browse_path_file' not set in admin.conf.php");
	    }
        
		$popup_ilink_url = Action::build(array('nodes_select', 'tree', 0, $content_lg, 'root', false, '', false),array('callback' => 'ilink_callback'));
		
		$popup_filebrowser_url_img = Action::build(array('file_select_rte', 0, $aquarius->conf('admin/rte/browse_path_img'), '', '', 0, '', ''), array('callback' => 'rte_file_select_img'));
		
		$popup_filebrowser_url_file = Action::build(array('file_select_rte', 0, $aquarius->conf('admin/rte/browse_path_file'), '', '', 0, '', ''), array('callback' => 'rte_file_select_file'));

        $image_path = $aquarius->conf('admin/rte/browse_path_img');
        $file_path = $aquarius->conf('admin/rte/browse_path_file');

		//$page_requisits->add_js_lib('ckeditor/ckeditor.js');
		
		$this->options = compact(
		    'rte_lg',
		    'height',
		    'popup_ilink_url',
		    'popup_filebrowser_url_img',
		    'popup_filebrowser_url_file',
		    'image_path',
		    'file_path');
    }
    
    function get_options() {
        return $this->options;
    }
}
