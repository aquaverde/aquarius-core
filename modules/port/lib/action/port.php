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
        $recurse = (bool)$request['include_children'];
        
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
        $imports = array();

        if(isset($_FILES['import_file'])) {
            $file = $_FILES['import_file']['tmp_name'];
            if(file_exists($file)) {
                $import = file_get_contents($file);
                if (FALSE === $import) {
                    $result->add_message(new Translation("port_upload_unreadable"));
                } else {
                    $import_json = json_decode($import, true);
                    if (NULL === $import_json) {
                        $result->add_message(new Translation("port_json_invalid"));
                    } else {
                        $imports []= $import_json;
                    }
                }
            }
        }
        
        $import_text = $request['import_text'];
        if (strlen($import_text)) {
            $import = json_decode($import_text, true);
            if (NULL === $import) {
                $result->add_message(new Translation("port_json_invalid"));
            } else {
                $imports []= $import;
            }
        }
        
        $import_parent_str = $request['import_selected'];
        $import_parent = db_Node::get_node($import_parent_str);
        if (false == $import_parent) {
            $result->add_message(new Translation("port_no_parent_selected"));
            return;
        }
        
        $id_mapping = array();
        $idmap = function($transport_id, $db_id = false) use (&$id_mapping) {
            if ($db_id) $id_mapping[$transport_id] = $db_id;
            return get($id_mapping, $transport_id, null);
        };
        
        $count = 0;
        foreach($imports as $import) {
            // Import is done in two steps (nodes first, content later) so that
            // the new node ID are available when pointings are resolved
            foreach($import as $entry) {
                $this->import_node($entry, $import_parent->id, $idmap);
                $count += 1;
            }
            foreach($import as $entry) {
                $this->import_content($entry, $idmap);
            }
        }
        
        $result->touch_region(Node_Change_Notice::structural_change_to($import_parent));
        
        $result->add_message(AdminMessage::with_line('info', 'port_imported_message', $count));
    }
    
    function import_node($entry, $db_parent_id, $idmap) {
        $node = new db_Node();
        
        if (isset($entry['parent'])) {
            // A previously inserted node is the parent
            $import_parent_id = $entry['parent'];
            $db_parent_id = $idmap($import_parent_id);
            if (!$db_parent_id) throw new Exception("No previously inserted parent with transport id $import_parent_id found");
        } 

        $node->parent_id = $db_parent_id;
        if (isset($entry['name'])) $node->name = $entry['name'];
        
        $node->form_id = $entry['form']; // This is a very simplistic take on things and will lead to useless results in most cases
        
        $node->insert();
        $idmap($entry['id'], $node->id);
    }
    
    
    function import_content($entry, $idmap) {
        $node_id = $idmap($entry['id']);
        $node = db_Node::get_node($node_id);
        $form = $node->get_form();
        foreach($entry['content'] as $lg => $entry_fields) {
            $content = new db_Content();
            $content->node_id = $node->id;
            $content->lg = $lg;
            $content->insert();
            foreach($form->get_fields() as $field) {
                if (isset($entry_fields[$field->name])) {
                    $value = $entry_fields[$field->name];
                    $type = $field->get_formtype();
                    $content->{$field->name} = $type->import($value, $field, $content->lg, $idmap);
                }
            }
            $content->save_content();
        }
    }
}