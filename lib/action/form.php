<?

/** The following commands are recognized by this action:
  * new: Creates a new form and executes an edit action on it as well
  * delete: removes the form
  * copy: copies a form
*/

require_once 'XML/Serializer.php' ; 
require_once 'XML/Unserializer.php' ; 


class action_form extends AdminAction {

    var $props = array("class", "command", "id");

    /** Allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function load() {
        $form = DB_DataObject::factory('form');
        $formid = intval($this->id);
        $loaded = $form->get($formid);
        if (!$loaded) throw new Exception("Failed loading form '$this->id'");
        return $form;
    }
}

class action_form_delete extends action_form implements ChangeAction {
    function process($aquarius, $post, $result) {
        $form = $this->load();
        $form->delete();
        $result->add_message(AdminMessage::with_line('ok', 's_message_form_deleted', $form->title));
    }
}

class action_form_copy extends action_form implements ChangeAction {
    function process($aquarius, $post, $result) {
        $form = $this->load();
        $newtitle = $form->title."_copy";
        $form->duplicate($newtitle);
        $result->add_message(AdminMessage::with_line('ok', 's_message_form_duplicated', $newtitle));
    }
}

class action_form_export extends action_form implements SideAction {
    function process($aquarius, $request) {
        $form = $this->load();
        $serializer_options = array (
            'addDecl' => TRUE,
            'encoding' => 'ISO-8859-1',
            'indent' => '  ',
            'rootName' => 'form',
            'defaultTagName' => 'form_field',
        );
        $form->get_fields() ;
        $Serializer = new XML_Serializer($serializer_options);
        $status = $Serializer->serialize($form);
        if (PEAR::isError($status)) throw new Exception("Unable to serialize form $form->title");

        $filename = $form->title ;
        $filename = preg_replace('/[^a-zA-Z0-9\ _]/', '', $filename) ;
        $filename = str_replace(' ', '_', $filename) ;
        $filename .= ".xml" ;
        while(@ob_end_clean());
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        echo $Serializer->getSerializedData();
    }
}

class action_form_import extends action_form implements DisplayAction {
    var $props = array("class", "command");
    function process($aquarius, $request, $smarty, $result) {
        $result->use_template("formupload.tpl");
    }
}


class action_form_import_form_submit extends action_form implements ChangeAction {
    var $props = array("class", "command");
    function process($aquarius, $post, $result) {
        if (!$_FILES) {
            $result->add_message(AdminMessage::with_line('error', 'form_import_no_file_upload'));
            return;
        }

        $fp = fopen ($_FILES['xmlfile']['tmp_name'], 'r');
        if (!$fp) {
            $result->add_message(AdminMessage::with_line('error', 'form_import_failed_opening'));
            return;
        }
        if ($fp) {
            $contents = fread($fp, $_FILES['xmlfile']['size']);
            $Unserializer = new XML_Unserializer();
            $status = $Unserializer->unserialize($contents);
            if (PEAR::isError($status)) {
                $result->add_message(AdminMessage::with_line('error', 'form_import_parse_error', $status->getMessage()));
                return;
            }
            $formdata = $Unserializer->getUnserializedData();

            $form = new db_Form();
            foreach(array('title', 'template', 'sort_by', 'sort_reverse', 'fall_through', 'show_in_menu') as $field) {
                $form->$field = $formdata[$field];
            }
            $form->title = $form->title.'_imported';
            $form->insert() ;

            $fields = $formdata['fields'] ;
            foreach ($fields as $field) {
                $dbfield = new db_Form_field ;
                $dbfield->form_id = $form->id ;
                $dbfield->name = $field['name'] ;
                $dbfield->description = utf8_decode($field['description']) ;
                $dbfield->sup1 = $field['sup1'] ;
                $dbfield->sup2 = $field['sup2'] ;
                $dbfield->sup3 = $field['sup3'] ;
                $dbfield->sup4 = $field['sup4'] ;
                $dbfield->weight = $field['weight'] ;
                $dbfield->type = $field['type'] ;
                $dbfield->multi = $field['multi'] ;
                $dbfield->language_independent = $field['language_independent'] ;
                $dbfield->permission_level = $field['permission_level'] ;
                $dbfield->insert() ;
            }

             $result->add_message(AdminMessage::with_line('ok', 'form_import_success', $form->title));
        }
    }
}

?>