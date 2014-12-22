<?php 
require_once("pear/DB/DataObject/FormBuilder.php");
require_once("pear/HTML/QuickForm/Renderer/ArraySmarty.php");

class action_dynform extends ModuleAction {
    var $modname = "dynform" ;
    var $props = array("class", "command", "content_id", "lg", "node_id", "block_id", "field_id") ;

    function valid($user) {
      return (bool)$user;
    }

    /** Load Dynformlib, content, lg and node; create dynform entry if it does not exist for this node. Return all of this. */
    function load($smarty = false) {

        $node = or_die(db_Node::get_node($this->node_id), "Unable to load node for %s", $this->node_id);
        $lg = $this->lg;
        $content = $node->get_content();
        
        $dynform =  new db_Dynform ; // DB_DataObject::factory('dynform') ;
        $dynform->node_id = $node->id ;
        
        $number_of_rows = $dynform->find() ; 
        if(0 == $number_of_rows) {  // create a new form
            $dynform->insert() ; 
        }
        else {
            $dynform->fetch() ; 
        }
        
        if ($smarty) {
            //LOAD RTE
            $page_requisites = new Page_Requisites();
            $page_requisites->add_js_lib('/aquarius/core/vendor/ckeditor/ckeditor/ckeditor.js');
            $smarty->assign('page_requisites', $page_requisites);
            
            global $aquarius;
            $rte_options = new RTE_options($aquarius->conf('admin/rte'));
            $rte_options['editor_lg'] = db_Users::authenticated()->adminLanguage;
            $rte_options['content_lg'] = db_Users::authenticated()->adminLanguage;
            $smarty->assign('rte_options', $rte_options);

        }
        
        return compact('content', 'lg', 'node', 'dynform');
    }
}


/*----- BLOCKS -----*/

class action_dynform_addnewblock extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        extract($this->load($smarty));
        $smarty->assign('node', $node) ;
        $smarty->assign('content', $content) ;
        $smarty->assign('actions', array(Action::make('dynform', 'addnewblocksubmit', false, $lg, $node->id, false, false), Action::make('cancel')));
        $result->use_template("dynform_add_block.tpl");
    }
}


class action_dynform_addnewblocksubmit extends action_dynform implements ChangeAction {
    function get_title() { return new Translation('s_save'); }
    function process($aquarius, $post, $result) {
        extract($this->load());
        $new_name = trim($post['newblockname']) ;
        $new_id = false;
        if ($new_name) {
            // add a basic dynform block, the root of this block shared among all lg's

            $dynform_block = DB_DataObject::factory('dynform_block') ;
            $dynform_block->dynform_id = $dynform->id ;
            $dynform_block->name = $new_name ;

            $dblock = DB_DataObject::factory('dynform_block') ;
            $dblock->query("SELECT weight FROM {$dblock->__table} WHERE dynform_id=".$dynform->id." ORDER BY weight DESC LIMIT 1") ;
            $dblock->fetch() ;
            $new_weight = $dblock->weight + 10 ;

            $dynform_block->weight = $new_weight ;
            $dynform_block->insert() ;
            $new_id = $dynform_block->id;

            // add also language specific translation for the current lg

            $blockdata = DB_DataObject::factory('dynform_block_data') ;
            $blockdata->block_id = $dynform_block->id ;
            $blockdata->lg = $this->lg ;
            $blockdata->name = $new_name ;
            $blockdata->insert() ;
        }
        $result->touch_region('content');
    }
}


class action_dynform_deleteblock extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        extract($this->load($smarty));
        $block = new db_Dynform_block ;
        $block->id = $this->block_id ;
        $found = $block->find() ;
        if ($found) $block->fetch() ;

        $smarty->assign('node', $node) ;
        $smarty->assign('content', $content) ;
        $smarty->assign('block', $block) ;
        $result->use_template("dynform_delete_block.tpl");
    }
}


class action_dynform_deleteblocksubmit extends action_dynform implements ChangeAction {
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->delete_block($this->block_id) ;
        $result->touch_region('content');
    }
}


class action_dynform_moveblockdown extends action_dynform implements ChangeAction {
    function get_icon() { return "buttons/df_move_down.gif"; }
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->move_block($this->block_id, "DOWN") ;
        $result->touch_region('content');
    }
}


class action_dynform_moveblockup extends action_dynform implements ChangeAction {
    function get_icon() { return "buttons/df_move_up.gif"; }
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->move_block($this->block_id, "UP") ;
        $result->touch_region('content');
    }
}


class action_dynform_editblock extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $block = new db_Dynform_block ;
        $block->id = $this->block_id ;
        $found = $block->find() ;
        if ($found)
        {
            extract($this->load($smarty));
            $block->fetch() ;
            $smarty->assign('node', $node) ;
            $smarty->assign('content', $content) ;
            $smarty->assign('block', $block) ;
            $smarty->assign('actions', array(Action::make('dynform', 'editblocksubmit', false, $lg, $node->id, $block->id, false), Action::make('cancel')));
            $result->use_template("dynform_edit_block.tpl");
        }
    }
}


class action_dynform_editblocksubmit extends action_dynform implements ChangeAction {
    function get_title() { return new Translation('s_save'); }
    function process($aquarius, $post, $result) {
        $new_name = trim($post['newblockname']) ;
        $bd = new db_Dynform_block_data ;
        $bd->block_id = $this->block_id ;
        $bd->lg = $this->lg ;
        $found = $bd->find() ;
        if ($found)
        {
            $bd->fetch() ;
            $bd->name = $new_name ;
            $bd->update() ;
        }
        else
        {
            $bd->name = $new_name ;
            $bd->insert() ;
        }
        $result->touch_region('content');
    }
}



/*----- FIELDS -----*/

class action_dynform_addfield extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $DL = new Dynformlib();
        extract($this->load($smarty));
        $block = new db_Dynform_block ;
        $block->id = $this->block_id ;
        $found = $block->find() ;
        if ($found) $block->fetch() ;
        $smarty->assign('node', $node) ;
        $smarty->assign('content', $content) ;
        $smarty->assign('block', $block) ;
        $smarty->assign('command', "add") ;
        $ftype = $request['new_fieldtype_'.$this->block_id] ;
        $smarty->assign('field_type', $DL->get_fieldtype_id($ftype)) ;
        $result->use_template($DL->get_fieldtype_template($ftype));

        $possible_option_fields = $DL->get_options_from_form_fields() ;
        if ($possible_option_fields)
        {
            $option_fields_select = '
                <select name="field[options]" size="1">' ;
                    foreach ($possible_option_fields as $pof)
                    {
                        $option_fields_select .= '<option value="'.$pof['key'].'">
                            '.$pof['value'].'
                            </option>' ;
                    }
            $option_fields_select .= '
                </select>' ;

            $smarty->assign('option_fields_select', $option_fields_select) ;
        }
    }
}


class action_dynform_editfield extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $DL = new Dynformlib();
        extract($this->load($smarty));
        $block = new db_Dynform_block ;
        $block->id = $this->block_id ;
        $found = $block->find() ;
        if ($found) $block->fetch() ;
        $field = new db_Dynform_field ;
        $field->id = $this->field_id ;
        $found = $field->find() ;
        if ($found) $field->fetch() ;
        // charge the language specific field data
        $datafield = new db_Dynform_field_data ;
        $datafield->field_id = $field->id ;
        $datafield->lg = $this->lg ;
        $found = $datafield->find() ;
        if ($found)
        {
            $datafield->fetch() ;
            $field->name = $datafield->name ;
            $field->options = $datafield->options ;
        }
        
        $smarty->assign('node', $node) ;
        $smarty->assign('content', $content) ;
        $smarty->assign('block', $block) ;
        $smarty->assign('field', $field) ;
        $smarty->assign('command', "edit") ;
        $smarty->assign('field_type', $field->type) ;
        $smarty->assign('saveaction', Action::make('dynform', 'editfieldsubmit', false, $lg, $node->id, $block->id, $field->id));
        $result->use_template($DL->get_fieldtype_template($DL->get_fieldtype_name($field->type)));

        $possible_option_fields = $DL->get_options_from_form_fields() ;
        if ($possible_option_fields)
        {
            $option_fields_select = '
                <select name="field[options]" size="1">' ;
                    foreach ($possible_option_fields as $pof)
                    {
                        //$desc = $fields[$pof]->description ;
                        //$desc = substr($desc, 0, strpos($desc, "(")) ;
                        $option_fields_select .= '<option value="'.$pof['key'].'" ' ;
                        if ($field->options == $pof['key']) $option_fields_select .= 'selected="selected"' ;
                        $option_fields_select .= '>
                            '.$pof['value'].'
                            </option>' ;
                    }
            $option_fields_select .= '
                </select>' ;
            $smarty->assign('option_fields_select', $option_fields_select) ;
        }
    }
}


class action_dynform_addfieldsubmit extends action_dynform implements ChangeAction {
    function process($aquarius, $post, $result) {
        $post_vars = $post['field'] ;

        $dfield = new db_Dynform_field ;
        $dfield->query("SELECT weight FROM {$dfield->__table} WHERE block_id=".$this->block_id." ORDER BY weight DESC LIMIT 1") ;
        $dfield->fetch() ;
        $new_weight = $dfield->weight + 10 ;

        // root field entry
        $field = new db_Dynform_field ;
        $field->block_id = $this->block_id ;
        $field->type = $post_vars['type'];
        $field->name = trim($post_vars['name']) ;
        $field->required = (bool)get($post_vars, 'required', false);
        if (array_key_exists("width", $post_vars)) $field->width = $post_vars['width'] ;
        if (array_key_exists("num_lines", $post_vars)) $field->num_lines = $post_vars['num_lines'] ;
        $field->weight = $new_weight ;
        $field->insert() ;

        $lgs = new db_Languages ;
        $lgs->find() ;
        while ($lgs->fetch())
        {
            // lg specific entry
            $data_field = new db_Dynform_field_data ;
            $data_field->field_id = $field->id ;
            $data_field->name = $field->name ;
            $data_field->lg = $lgs->lg ;
            if (array_key_exists("options", $post_vars)) $data_field->options = trim($post_vars['options']) ;
            $data_field->insert() ;
        }
        $result->touch_region('content');
    }
}


class action_dynform_editfieldsubmit extends action_dynform implements ChangeAction {
    function process($aquarius, $post, $result) {
        $post_vars = $post['field'] ;

        // root field entry
        $field = new db_Dynform_field ;
        $field->id = $this->field_id ;
        $found = $field->find() ;
        if ($found)
        {
            $field->fetch() ;
            $field->required = (bool)get($post_vars, 'required', false);
            if (array_key_exists("width", $post_vars)) $field->width = $post_vars['width'] ;
            if (array_key_exists("num_lines", $post_vars)) $field->num_lines = $post_vars['num_lines'] ;
            $field->update() ;

            // lg specific entry
            $data_field = new db_Dynform_field_data ;
            $data_field->field_id = $field->id ;
            $data_field->lg = $this->lg ;
            $found2 = $data_field->find() ;
            if ($found2)
            {
                $data_field->fetch() ;
                $data_field->name = $post_vars['name'] ;
                if (array_key_exists("options", $post_vars)) $data_field->options = trim($post_vars['options']) ;
                $data_field->update() ;
            }
            else
            {
                $data_field->name = $post_vars['name'] ;
                $data_field->options = trim($post_vars['options']) ;
                $data_field->insert() ;
            }
            $result->touch_region('content');
        }
    }
}


class action_dynform_changefieldtype extends action_dynform implements ChangeAction {
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $field = new db_Dynform_field ;
        $field->id = $this->field_id ;
        $found = $field->find() ;
        if ($found)
        {
            $field->fetch() ;
            $newtype = $post['new_fieldtype'] ;
            $field->type = $DL->get_fieldtype_id($newtype) ;
            $field->update() ;

            $datafield = new db_Dynform_field_data ;
            $datafield->field_id = $field->id ;
            $datafield->lg = $this->lg ;
            $found = $datafield->find() ;
            if ($found)
            {
                $datafield->fetch() ;
                $field->name = $datafield->name ;
                $field->options = $datafield->options ;
            }
            $result->touch_region('content');
        }
    }
}


class action_dynform_deletefield extends action_dynform implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        extract($this->load($smarty));
        $block = new db_Dynform_block ;
        $block->id = $this->block_id ;
        $found = $block->find() ;
        if ($found) $block->fetch() ;

        $field= new db_Dynform_field ;
        $field->id = $this->field_id ;
        $found = $field->find() ;
        if ($found) $field->fetch() ;

        $smarty->assign('node', $node) ;
        $smarty->assign('content', $content) ;
        $smarty->assign('block', $block) ;
        $smarty->assign('field', $field) ;
        $result->use_template("dynform_delete_field.tpl");
    }
}


class action_dynform_deletefieldsubmit extends action_dynform implements ChangeAction {
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->delete_field($this->field_id) ;
        $result->touch_region('content');
    }
}


class action_dynform_movefielddown extends action_dynform implements ChangeAction {
    function get_icon() { return "chevron-down"; }
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->move_field($this->field_id, "DOWN") ;
        $result->touch_region('content');
    }
}


class action_dynform_movefieldup extends action_dynform implements ChangeAction {
    function get_icon() { return "chevron-up"; }
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib();
        $DL->move_field($this->field_id, "UP") ;
        $result->touch_region('content');
    }
}

