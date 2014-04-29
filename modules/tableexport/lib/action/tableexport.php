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
    
    function execute() {
        global $aquarius;
        
        require_once "lib/db/Node.php";
        
        $smarty = false;
        $messages = array();
        $action = false;
        
        switch($this->op) {
        case 'view1':
        case 'view':
            $smarty = $aquarius->get_smarty_backend_container();
            
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
            $smarty->tmplname = "tableexport_view.tpl";
            break;
        
        case 'edit':
            global $admin_lg;
            if($this->id != 'null') {
                
                $smarty = $aquarius->get_smarty_backend_container();
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
                    $smarty = null;
                    $action = Action::make('tableexport','view','null');
                    $messages[] = new Translation("tableexport_entry_edited");
                }
            }
            break;
        case 'delete':
            if($this->id != 'null') {
                $bookings_proto = DB_DataObject::factory(TABLEEXPORT_TABLE);
                $key = $bookings_proto->keys();
                $bookings_proto->$key[0] = $this->id;
                $bookings_proto->find();
                $bookings_proto->delete();
            }
            $messages[] = new WordingTranslation("tableexport_entry_deleted");
            break;
        case 'export':

            $smarty = $aquarius->get_smarty_backend_container();
            $smarty->tmplname = "tableexport_export.tpl";
            break;
        case 'exportdown':
            ob_clean();
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="'.TABLEEXPORT_FILENAME.'"');
            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $count = $bookings->find();
            $bookingsarray = array();
            while($bookings->fetch()) {
                $bookingsarray[] = $bookings->toArray();
            }
            array_walk_recursive($bookingsarray, create_function(
                '&$item,$key', '$item = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item);'));

            $out = fopen("php://output", "w");
          
            $columns = array_keys($bookings->table());
             if(!function_exists("fputcsv")) {
                aqua_fputcsv($out, $columns,';','"');
            } else {
                fputcsv($out, $columns,';','"');
            }
            if($count > 0) {
                foreach($bookingsarray as $line) {
                    if(!function_exists("fputcsv")) {
                        aqua_fputcsv($out, $line,';','"');
                    } else {
                        fputcsv($out, $line,';','"');
                    }
                }
            }
            exit();
            break;
        case 'deleteallconfirm':
            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $count = $bookings->find();
            $smarty = $aquarius->get_smarty_backend_container();
            $smarty->assign('count', $count);
            $smarty->assign('deleted', false);
            $smarty->tmplname = "tableexport_delete.tpl";
            break;
        case 'deleteall':
            $bookings = DB_DataObject::factory(TABLEEXPORT_TABLE);
            $bookings->find();
            while($bookings->fetch()) {
                $bookings->delete();
            }
            break;
        default:
            throw new Exception("Operation unknown: '$this->op'");
        }
        return compact('messages', 'smarty', 'action');
    }
}

