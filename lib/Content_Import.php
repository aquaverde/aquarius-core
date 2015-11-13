<?php

/** Reads or updates content into DB */
class Content_Import {

    /** Where to place imported nodes that have no parent set.
      * May be set to a node ID
      * An exception will be thrown if this remains unset and nodes without
      * explicit parent are inserted. */
    var $attach_point = false;


    /** List of node that were imported (their DB ID) */
    var $imported = array();


    /** List of node ID where content was updated, includes newly imported nodes */
    var $updated = array();


    /** Read content and write to DB
      * @param $import_string import string in JSON format
      * @return count of processed nodes
      */
    function import($import_string) {
        $import = json_decode($import_string, true);
        if (NULL === $import) {
            throw new Content_Import_Decoding_Exception(json_last_error());
        }


        // Callback that keeps track of the mapping between import ID and DB ID
        $id_mapping = array();
        $idmap = function($transport_id, $db_id = false) use (&$id_mapping) {
            if ($db_id) $id_mapping[$transport_id] = $db_id;
            return get($id_mapping, $transport_id, null);
        };
        
        // For updates: the id mapper tries to use transport_id as db_id
        // This way references to existing nodes work
        $idmap_update = function($transport_id, $db_id = false) use ($idmap) {
            $db_id = $idmap($transport_id, $db_id);
            if ($db_id) return $db_id;
            if (db_Node::get_node($transport_id)) {
                return $transport_id;
            }
            return false;
        };


        $count = 0;
        // Import is done in two steps (nodes first, content later) so that
        // the new node ID are available when pointings are resolved
        foreach($import as $entry) {
            $this->import_node($entry, get($entry, 'update') ? $idmap_update : $idmap);
            $count += 1;
        }
        foreach($import as $entry) {
            $this->import_content($entry, get($entry, 'update') ? $idmap_update : $idmap);
        }
        return $count;
    }


    function import_node($entry, $idmap) {
        $node = false;
        $insert = false;
        
        // When importing a node, we may create a new one or update the fields
        // of an existing node. Because update is potentially destructive, we do
        // it only when the entry explicilty sets the 'update' flag, and a node
        // with the given id exists
        if (get($entry, 'update', false)) {
            $node = db_Node::get_node($entry['id']);
        }
        
        if (!$node) {
            $node = new db_Node();
            $insert = true;

            if (isset($entry['attach_parent'])) {
                $db_parent = db_Node::get_node($entry['attach_parent']);
                if (!$db_parent) throw new Exception("Attach parent ".$entry['attach_parent']." not found");
                $db_parent_id = $db_parent->id;
            } elseif (isset($entry['parent'])) {
                // A previously inserted node is the parent
                $import_parent_id = $entry['parent'];
                $db_parent_id = $idmap($import_parent_id);
                if (!$db_parent_id) throw new Exception("No previously inserted parent with transport id $import_parent_id found");
            } else {
                if ($this->attach_point === false) throw new Exception("No attach point provided");
                if (!db_Node::get_node($this->attach_point)) throw new Exception("Provided attach point $this->attach_point is not a valid node ID");
                $db_parent_id = $this->attach_point;
            }
            $node->parent_id = $db_parent_id;
        }

        if (isset($entry['active'])) $node->active = (bool)$entry['active'];
        if (isset($entry['name'])) $node->name = $entry['name'];
        if (isset($entry['form'])) $node->form_id = $entry['form'];
        if (isset($entry['box_depth'])) $node->box_depth = (int)$entry['box_depth'];

        if ($insert) {
            $node->insert();
            $idmap($entry['id'], $node->id);
            $this->imported []= $node->id;
        } else {
            $node->update();
            $idmap($node->id, $node->id);
        }
        $this->updated []= $node->id;
    }


    function import_content($entry, $idmap) {
        $node_id = $idmap($entry['id']);
        $node = db_Node::get_node($node_id);
        if (!$node) throw new Exception("Unable to load $node_id");
        $form = $node->get_form();
        
        foreach($entry['content'] as $lg => $entry_fields) {
            $content = $node->get_content($lg);
            if (!$content) {
                $content = new db_Content();
                $content->node_id = $node->id;
                $content->lg = $lg;
                $content->insert();
            }
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


class Content_Import_Decoding_Exception extends Exception {
    function __construct($errno) {
        parent::__construct(get(
            array(
                JSON_ERROR_NONE           => 'No error has occurred',
                JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
                JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX         => 'Syntax error',
                JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            ),
            $errno,
            'unspecified error'
        ));
    }
}