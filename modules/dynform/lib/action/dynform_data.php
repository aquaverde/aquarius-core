<?php 
require_once('lib/libdynform.php') ; 

/**
  * Action class for the Admin Interface: Dynform data export
  * See also the file $DYNFORM_MODULE_ROOT/dynform.php
  */
  
class action_dynform_data extends ModuleAction {
    var $modname = "dynform" ;
    var $props = array("class", "command", "form_id", "lg") ;

    function valid($user) {
      return (bool)$user;
    }
    
    protected function _form_title() {
        $form = new db_Dynform();
        $form->get($this->form_id);
        $node = db_Node::get_node($form->node_id);
        $title = '∅';
        if (!$node) Log::warn("Unable to load node associated with form id ".$this->form_id);
        else $title = $node->get_contenttitle();
        return $title;
    }
}

class Action_Dynform_Data_delete_entries_dialog extends action_dynform_data implements DisplayAction {

    function get_title() {
        return new Translation($this->lg ? 'delete_lg_entries' : 'delete_all_data');
    }
    
    function get_icon() {
        return 'buttons/delete.gif';
    }

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('title', $this->get_title());
        if ($this->lg) {
            $smarty->assign('message', new Translation('delete_lg_entries_confirm', array($this->lg)));
        } else {
            $smarty->assign('message', new Translation('delete_all_data_confirm', array($this->_form_title())));
        }

        $smarty->assign('actions', array_filter(array(
            Action::make('dynform_data', 'delete_all', $this->form_id, $this->lg, false),
            Action::make('dynform_data', 'delete_all', $this->form_id, $this->lg, true),
            Action::make('cancel')
        )));
        $result->use_template('select.tpl');
    }
}


class Action_Dynform_Data_delete_all extends action_dynform_data implements ChangeAction {
    var $props = array("class", "command", "form_id", "lg", "delete_form") ;

    function valid($user) {
        if ($this->delete_form) {
            // Can't only delete one language but delete the form
            if ($this->lg) return false;
        
            // Only superadmins can delete forms
            return $user->isSuperadmin();
        }
        
        return true;
    }

    function get_title() {
        return new Translation($this->lg ? 'delete_lg_entries' : ($this->delete_form ? 'delete_entries_and_form' : 'delete_entries'), array($title));
    }

    function process($aquarius, $post, $result) {
        $entry = new db_Dynform_entry;
        $entry->dynform_id = $this->form_id;
        if ($this->lg) $entry->lg = $this->lg;
        $entry->find();
        $entries_ids = array();
        while ($entry->fetch()) {
            $entries_ids[] = $entry->id;
            $entry->delete();
        }

        foreach ($entries_ids as $entry_id) {
            $entrydata = new db_Dynform_entry_data;
            $entrydata->entry_id = $entry_id;
            $entrydata->find();
            while ($entrydata->fetch()) {
                $entrydata->delete();
            }
        }

        if ($this->delete_form) {
            $form = new db_Dynform();
            $form->id = $this->form_id;
            if ($form->find(true)) {
                $DL = new Dynformlib ; // OO much?
                $DL->delete_dynform(db_Node::get_node($form->node_id));
                $form->delete();
            }
        }
    }
}


class Action_Dynform_Data_delete_dialog extends action_dynform_data implements DisplayAction {
    function get_title() { return new Translation('delete_entry'); }
    function get_icon()  { return 'buttons/delete.gif'; }

    function process($aquarius, $request, $smarty, $result) {
        $entry = new db_Dynform_entry ; 
        $entry->id = $this->form_id; 
        $entry->find(true);
        
        $smarty->assign('title', $this->get_title());
        $smarty->assign('message', new Translation('delete_entry_confirm', array($entry->time)));

        $smarty->assign('actions', array_filter(array(
            Action::make('dynform_data', 'delete_entry', $this->form_id, $this->lg, true),
            Action::make('cancel')
        )));
        $result->use_template('select.tpl');
    }
}

class Action_Dynform_Data_delete_entry extends action_dynform_data implements ChangeAction {
    function get_title() { return new Translation('delete_entry'); }
    function process($aquarius, $post, $result) {
        $entry = new db_Dynform_entry ; 
        $entry->id = $this->form_id ; 
        $entry->find() ;
        $entry->fetch() ;
        $entry_data = new db_Dynform_entry_data ; 
        $entry_data->entry_id = $entry->id ; 
        $entry_data->find();
        while ($entry_data->fetch()) $entry_data->delete() ;
        $entry->delete() ;
    }
}


class Action_Dynform_Data_edit_entry extends action_dynform_data implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
        $DL = new Dynformlib ; // OO much?
        $entry = new db_Dynform_entry ; 
        $entry->id = $this->form_id ; 
        $entry->find() ; 
        $entry->fetch() ;
        $smarty->assign("entry_id", $this->form_id) ;
        $smarty->assign("form_name", $DL->get_form_name($entry->dynform_id, $this->lg)) ; 
        $smarty->assign("lg_filter", $this->lg) ; 
        $result->use_template("dynform_edit_entry.tpl");
    }
}


class Action_Dynform_Data_edit_entry_submit extends action_dynform_data implements ChangeAction {
    function process($aquarius, $post, $result) {
        $DL = new Dynformlib ; // OO much?
        $entry = new db_Dynform_entry ; 
        $entry->id = $this->form_id ; 
        $entry->find() ; 
        $entry->fetch() ; 
        $DL->update_entry($entry->id, $post) ; 
    }
}


class Action_Dynform_Data_delete_selected extends action_dynform_data implements ChangeAction {
    function process($aquarius, $post, $result) {
        $recordsToDelete = requestvar('sel_records');
        if(!empty($recordsToDelete)) {
            foreach($recordsToDelete as $recordToDelete) {
                $record = DB_DataObject::factory('dynform_entry');
                $record->id=$recordToDelete;
                $record->delete();
                $record = DB_DataObject::factory('dynform_entry_data');
                $record->entry_id=$recordToDelete;
                $record->delete();
            }
        }
    }
}


class Action_Dynform_Data_Show extends action_dynform_data implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
        $DL = new Dynformlib ; // OO much?
        
        $columntitles = $DL->get_column_titles($this->form_id, $this->lg) ;
        $shown_lg = false;
        if (empty($this->lg)) {
            $records = $DL->get_entries_data($this->form_id) ; 
            $lg_desc = new Translation("all_languages") ;
        } else {
            $records = $DL->get_entries_data($this->form_id, $this->lg) ;
            $lg_desc = $this->lg ;
            $shown_lg = $this->lg;
        }
        
        // Instead of adding pagination we just show a warning that there are too many entries and clip the output
        $max_record_count = 2000;
        $record_count = count($records);
        if ($record_count > $max_record_count) {
            $records = array_slice($records, $max_record_count);
            $result->add_message(AdminMessage::with_line('warn', 'message_clipped_output', $max_record_count, $record_count));
        }

        // Records are returned as dicts, but we want plain lists of values for 
        // simple looping pleasure in smarty
        $column_ids = array_keys($columntitles);
        $ordered_records = array();
        foreach($records as $record) {
            $line = array();
            foreach ($column_ids as $column_id) {
                $line []= get($record, $column_id, '');
            }
            $ordered_records [$record['id']]= $line;
        }
        
        $smarty->assign("columntitles", $columntitles) ; 
        $smarty->assign("records", $ordered_records); 
        $smarty->assign("form_name", $DL->get_form_name($this->form_id, $this->lg)) ; 
        $smarty->assign("lg_desc", $lg_desc) ; 
        $smarty->assign("form_id", $this->form_id) ; 
        if (!$shown_lg) $smarty->assign("show_all_lgs", true) ; 
        $smarty->assign("shown_lg", $shown_lg) ;
        $smarty->assign("lg_filter", $this->lg) ;

        $result->use_template("dynform_list_data.tpl");
    }
}


class Action_Dynform_Data_Export extends action_dynform_data implements DisplayAction {
    function get_title() { return new Translation('csv_export'); }
    function get_icon()  { return 'buttons/export.gif'; }

    function process($aquarius, $get, $smarty, $result) {
        $DL = new Dynformlib ; // OO much?
        
        $columntitles = $DL->get_column_titles($this->form_id, $this->lg) ; 
        if ($this->lg == "null") $lg_desc = new Translation("all_languages") ;
        else $lg_desc = $this->lg ;

        $smarty->assign("columntitles", $columntitles) ;  
        $smarty->assign("form_name", $DL->get_form_name($this->form_id, $this->lg)) ; 
        $smarty->assign("lg_desc", $lg_desc) ; 
        $smarty->assign("form_id", $this->form_id) ; 
        if(isset($_COOKIE['dynform_csvec'][$this->form_id])) {
            $smarty->assign("cooki_array", explode(";", $_COOKIE['dynform_csvec'][$this->form_id]));
        }
        
        $smarty->assign('actions', array_filter(array(
            Action::make('dynform_data', 'export_file', $this->form_id, $this->lg),
            Action::make('cancel')
        )));
        
        $result->use_template("dynform_export.tpl");
    }
}


class Action_Dynform_Data_Export_File extends action_dynform_data implements SideAction {
    function get_title() { return new Translation('csv_export'); }
    function get_icon()  { return 'buttons/export.gif'; }

    function process($aquarius, $request) {
        $DL = new Dynformlib ; // OO much?
        
        // GET INFOS 
        if ($this->lg == "null") {
            $records = $DL->get_entries_data($this->form_id) ; 
            $lg_desc = new Translation("all_languages") ;
            $filename_lg = "";
        } else {
            $records = $DL->get_entries_data($this->form_id, $this->lg) ;
            $lg_desc = $this->lg;
            $filename_lg = "_".$this->lg;
        }
        $columns = $DL->get_column_titles($this->form_id, $this->lg);

        $checked_boxes = $_REQUEST['checkboxes_export'];
        
        // Remember desired export fields in a cookie
        setcookie('dynform_csvec['.$this->form_id.']', 
                  implode(";", $checked_boxes), 
                  time() + (10 * 31536000) // What?
        );
        
        
        $desired_columns = array();
        foreach($columns as $field_id => $title) {
            if (in_array($field_id, $checked_boxes)) {
                $desired_columns[$field_id] = str($title);
            }
        }
        
        $filename = $DL->get_form_name($this->form_id, $this->lg).$filename_lg.'_'.date("d_m_Y") ;
        
        $filename = preg_replace('/[^a-zA-Z0-9\ _]/', '', $filename) ; 
        $filename = str_replace(' ', '_', $filename) ; 
        $filename .= ".csv" ;

        // Prepare for output
        while (@ob_end_clean());
        $out = fopen('php://output', 'w') ;

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        // Header line
        if(!function_exists("fputcsv")) {
            aqua_fputcsv($out, $desired_columns,';','"') ;
        } else {
            fputcsv($out, $desired_columns,';','"') ;
        }
        
        // Entries
        foreach($records as $record) {
            $line = array();
            foreach ($desired_columns as $field_id => $_) {
                $line []= get($record, $field_id, '');
            }
            if(!function_exists("fputcsv")) {
                aqua_fputcsv($out, $line,';','"') ;
            } else {
                fputcsv($out, $line,';','"') ;
            }
        }
        fclose($out);

        exit();
    }
}


