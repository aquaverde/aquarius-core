<?php
/** Prepare RTE config options */
class RTE_options implements ArrayAccess {
    private $options = array();
    private $plugins = array();

    function __construct($conf) {
        if(!isset($conf['browse_path_img']) || !isset($conf['browse_path_file'])) {
            throw new Exception("RTE config problems: 'browse_path_img' or 'browse_path_file' not set");
        }

        $this->plugins = $conf['plugins'];

        $this['image_path'] = $conf['browse_path_img'];
        $this['file_path']  = $conf['browse_path_file'];
    }

    function config_for($base_url) {
        $config = array();

        global $aquarius;
        $custom_path = '/aquarius/ckconfig.js';
        if (file_exists($aquarius->root_path.$custom_path)) {
            $config['customConfig'] = $custom_path;
        } else {
            $config['customConfig'] = '/aquarius/core/backend/ckeditor/config.js';
        }

        $select_image_action = Action::build(array('file_select_rte', 0, $this['image_path'], '', '', 0, '', ''), array('callback' => 'rte_file_select_img'));
        $config['filebrowserImageBrowseUrl'] = $base_url->with_param($select_image_action)->str(false);

        $select_file_action = Action::build(array('file_select_rte', 0, $this['file_path'], '', '', 0, '', ''), array('callback' => 'rte_file_select_file'));
        $config['filebrowserBrowseUrl']      = $base_url->with_param($select_file_action)->str(false);

        $ilink_select = Action::build(array('nodes_select', 'tree', 0, $this['content_lg'], 'root', false, '', false),array('callback' => 'ilink_callback'));
        $config['ilink_select']              = $base_url->with_param($ilink_select)->str(false);

        $config['language'] = $this['editor_lg'];

        if (isset($this['height'])) $config['height'] = $this['height'].'px';

        $config['extraPlugins'] = join(',', array_keys($this->plugins));

        return $config;
    }


    /* Get listg of plugins that need loading */
    function plugin_list() {
        return array_filter($this->plugins, 'is_string');
    }



    // ---------------------- ArrayAccess boilerplate --------------------------

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            throw new Exception('Must provide a key to set an option, can\'t append');
        } else {
            $this->options[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->options[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->options[$offset]);
    }
    public function offsetGet($offset) {
        if (isset($this->options[$offset])) return $this->options[$offset];
        throw new Exception("Accessing unset config option $offset");
    }
}
