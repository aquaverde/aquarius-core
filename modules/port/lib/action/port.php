<?php

/** Export and import Aquarius content */
class action_port extends ModuleAction {
    var $modname = "port";
    var $props = array('class', 'op');

    function valid($user) {
      return $user->isSuperadmin();
    }
}


class action_port_export extends action_port {
    function export($request) {
        $roots = array_filter(explode(',', $request['export_selected']));
        $recurse = (bool)get($request, 'include_children');
        
        // Export nodes first
        $exported = array();
        foreach($roots as $root_str) {
            $this->export_node($root_str, $recurse, null, function($export_item) use (&$exported) {
                $exported []= $export_item;
            });
        }
        
        return $exported;
    }
    
    function export_node($node_str, $recurse, $parent, $yield) {
        $node = db_Node::get_node($node_str);
        if (!$node) throw new Exception("Unable to load $node_str");
        
        $entry = array();
        $entry['id'] = $node->id;
        if ($parent) $entry['parent'] = $parent;
        if ($node->name) $entry['name'] = $node->name;
        $entry['form'] = $node->form_id;
        $entry['active'] = (bool)$node->active;
        
        $form = $node->get_form();
        $fields = $form->get_fields();
        $entry['content'] = array();
        
        foreach($node->get_all_content() as $content) {
            $field_values = $content->get_fields();
            $export_values = array();
            foreach($fields as $field) {
                if (isset($field_values[$field->name])) {
                    $type = $field->get_formtype();
                    $export_values[$field->name] = $type->db_set_field($field_values[$field->name], $field, $content->lg);
                }
            }

            $entry['content'][$content->lg] = array_filter($export_values, function($f) { return !($f === null || (is_array($f) && count($f) == 0)); });
        }

        $yield($entry);

        if ($recurse) {
            foreach ($node->children() as $child) {
                $this->export_node($child, true, $entry['id'], $yield);
            }
        }
    }
}


class action_port_export_show extends action_port_export implements DisplayAction {
    function get_title() {
        return new Translation('port_export_show');
    }

    function process($aquarius, $request, $smarty, $result) {
        $export = $this->export($request);
        if (count($export) < 1) {
            $result->add_message(new Translation("port_none_selected"));
            return;
        }
        $smarty->assign('export', json_encode($export, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null));
        $smarty->assign('actions', array(
            Action::make('port', 'export_download'),
            Action::make('cancel')
        ));

        $result->use_template('port_export_show.tpl');
    }
}


class action_port_export_download extends action_port_export implements SideAction {
    function get_title() {
        return new Translation('port_export_download');
    }

    function process($aquarius, $request) {
        $export = $this->export($request);

        $first_content = first(get(first($export), 'content'));
        $name = first(first(get($first_content, 'title', first($first_content))));
        $export_count = count($export);

        header('Content-type: application/json');
        header('Content-Disposition: attachment; filename="'.$_SERVER['SERVER_NAME']." $name (total $export_count) ".date('YmdHis').'.json"');

        echo json_encode($export);
        exit();
    }
}


class action_port_import extends action_port implements ChangeAction {
    function get_title() {
        return new Translation('port_dialog_title_import');
    }

    function process($aquarius, $request, $result) {
        $import_parent_str = $request['import_selected'];
        $import_parent = db_Node::get_node($import_parent_str);
        if (false == $import_parent) {
            $result->add_message(new Translation("port_no_parent_selected"));
            return;
        }

        $importer = new Content_Import();
        $importer->attach_point = $import_parent->id;


        $count = 0;

        if(isset($_FILES['import_file'])) {
            $file = $_FILES['import_file']['tmp_name'];
            if(file_exists($file)) {
                $import = file_get_contents($file);
                if (FALSE === $import) {
                    $result->add_message(new Translation("port_upload_unreadable"));
                } else {
                    try {
                        $count += $importer->import($import); // The importer importly imports the imported import
                    } catch(Content_Import_Decoding_Exception $e) {
                        Log::debug($e);
                        $result->add_message(new Translation("port_json_invalid"));
                    }
                }
            }
        }

        $import_text = $request['import_text'];
        if (strlen($import_text)) {
            try {
                $count += $importer->import($import_text);
            } catch(Content_Import_Decoding_Exception $e) {
                Log::debug($e);
                $result->add_message(new Translation("port_json_invalid"));
            }
        }

        $result->touch_region(Node_Change_Notice::structural_change_to($import_parent));

        $result->add_message(AdminMessage::with_line('info', 'port_imported_message', $count));
    }
}