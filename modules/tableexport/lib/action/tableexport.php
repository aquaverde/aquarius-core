<?php 
require_once("pear/DB/DataObject/FormBuilder.php");
require_once("pear/HTML/QuickForm/Renderer/ArraySmarty.php");

class action_tableexport extends ModuleAction {
	var $modname = "tableexport";
    var $props = array('class', 'op', 'id');

    function valid($user) {
      return (bool)$user;
    }
    
    function get_title() {
        switch($this->op) {
        case 'view1':
            return new Translation("tableexport_menu");
        case 'view':
            return new Translation("tableexport_view");
        case 'export':
            return new Translation("tableexport_export");
        case 'deleteallconfirm':
            return new Translation("tableexport_deleteall");
        default:
            return null;
        } 
    }
}


class Action_Tableexport_view extends action_tableexport implements DisplayAction {
    var $props = array('class', 'op');
    function process($aquarius, $get, $smarty, $result) {
            $bookings_proto = DB_DataObject::factory(TABLEEXPORT_TABLE);

            $bookings_proto->orderBy(TABLEEXPORT_ORDERCOLUMN);
            $bookings_proto->find();

            $columns = array_keys($bookings_proto->table());
            $keyfields = $bookings_proto->keys();
            $keyfield = $keyfields[0];
            $bookings = array();
            while($bookings_proto->fetch()) {
                $bookings[] = $bookings_proto->toArray();
            }

            $smarty->assign('columns', $columns);
            $smarty->assign('bookings_proto', $bookings_proto);
            $smarty->assign('bookings', $bookings);
            $smarty->assign('keyfield', $keyfield);
            $result->use_template("tableexport_view.tpl");
    }
}
class Action_Tableexport_view1 extends action_tableexport_view implements DisplayAction {} // It was already like that when I got it

class Action_Tableexport_edit extends action_tableexport implements ChangeAction {
    function process($aquarius, $post, $result) {
            global $admin_lg;
            if($this->id != 'null') {
                $bookings_proto = DB_DataObject::factory(TABLEEXPORT_TABLE);
                $keyfields = $bookings_proto->keys();
                $keyfield = $keyfields[0];
                $bookings_proto->$keyfield = $this->id;
                $bookings_proto->find(true);
                
                $fg = DB_DataObject_FormBuilder::create($bookings_proto);
                $form =
                $fg->getForm("admin.php?lg=".$admin_lg."&".str(
                    Action::make('tableexport','edit',$this->id)));
                //$form->updateElementAttr($form->_elements, 'class="ef"');
                $form->addElement('submit', 'cancel', 'Cancel');
                $renderer =& new HTML_QuickForm_Renderer_Array();
                $form->accept($renderer);

                $smarty->tmplname = "tableexport_edit.tpl";
                $smarty->assign('form_data', $renderer->toArray());
                if ($form->validate()) {
                    $form->process(array(&$fg,'processForm'), false);
                    $form->freeze();
                    $action = Action::make('tableexport','view');
                    $result->add_message(new Translation("tableexport_entry_edited"));
                }
            }
    }
}


class Action_Tableexport_delete extends action_tableexport implements ChangeAction {
    function process($aquarius, $post, $result) {
            if($this->id != 'null') {
                $bookings_proto = DB_DataObject::factory(TABLEEXPORT_TABLE);
                $key = $bookings_proto->keys();
                $bookings_proto->$key[0] = $this->id;
                $bookings_proto->find();
                $bookings_proto->delete();
            }
            $result->add_message(new Translation("tableexport_entry_deleted"));
    }
}


class Action_Tableexport_deleteallconfirm extends action_tableexport implements DisplayAction {
    function process($aquarius, $get, $smarty, $result) {
            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $count = $bookings->find();
            $smarty->assign('count', $count);
            $smarty->assign('deleted', false);
            $result->use_template("tableexport_delete.tpl");
    }
}


class Action_Tableexport_deleteall extends action_tableexport implements ChangeAction {
    function process($aquarius, $post, $result) {
            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $bookings->find();
            while($bookings->fetch()) {
                $bookings->delete();
            }
    }
}


class Action_Tableexport_export extends action_tableexport implements DisplayAction {
    var $props = array('class', 'op');
    function process($aquarius, $get, $smarty, $result) {
        $result->use_template("tableexport_export.tpl");
    }
}


class Action_Tableexport_exportdown extends action_tableexport implements SideAction {
    var $props = array('class', 'op');
    function process($aquarius, $get) {
            ob_clean();

            $latin1 = $this->module->conf('latin1');
            $delimiter = $this->module->conf('delimiter');
            header('Content-type: text/csv'.($latin1 ? '' : '; charset=utf-8'));
            header('Content-Disposition: attachment; filename="'.TABLEEXPORT_FILENAME.'"');

            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $bookings->find();

            $out = fopen("php://output", "w");

            $columns = array_keys($bookings->table());

            aqua_fputcsv($out, $columns, $delimiter);

            while($bookings->fetch()) {
                $fields = $bookings->toArray();
                if ($latin1) {
                    $fields = array_map(function($field) {
                        return iconv("UTF-8", "ISO-8859-1//TRANSLIT", $field);
                    }, $fields);
                }
                aqua_fputcsv($out, $fields, $delimiter);
            }
    }
}


