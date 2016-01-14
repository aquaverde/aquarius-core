<?php

require_once("pear/DB/DataObject/FormBuilder.php");
require_once("pear/HTML/QuickForm/Renderer/ArraySmarty.php");

class action_fe_address extends ModuleAction {
    var $modname = "fe_user_management";
    var $props = array('class', 'op', 'id');

    function valid($user) {
        return true;
    }

    function get_title() {
        switch($this->op) {
        case 'export':
            return new Translation("fe_address_export");
        default:
            return null;
        }
    }

    function execute() {
        global $aquarius;

        $smarty = false;
        $messages = array();

        $insert = false;
        $user = null;
        $address = DB_DataObject::factory('fe_address');
        switch($this->op) {


        case "export":
            // Read filter restrictions
            $user_search = "";
            $group_search = 0;
            if (!in_array('filter_reset', $this->params)) {
                $user_search = requestvar('user_search');
                $group_search = intval(requestvar('group_search'));
            }

            // Get the adresses
            $fe_address = DB_DataObject::factory('fe_address');

            $query = "SELECT `fe_address`.* FROM {$fe_address->__table}, fe_users, fe_user_address ";

            // add the fe_groups2user in the request, in the case we need it
            if($group_search > 0) {
                $query .= ", fe_groups2user ";
            }

            $query .= "WHERE fe_user_address.fe_user_id = fe_users.id and fe_user_address.fe_address_id = fe_address.id ";

            if ( !empty($user_search) ) {
                $query .= "and fe_users.name LIKE '%" . mysql_real_escape_string($user_search) . "%' ";
            }

            // Maybe restrict search to one group
            if ( $group_search > 0 ) {
                $query .= "and fe_groups2user.user_id = fe_users.id and fe_groups2user.group_id = $group_search";
            }

            $fe_address->query($query);
            $adresses = $fe_address;
            $adressesarray = array();
            while($adresses->fetch()) {
                $adressesarray[] = $adresses->toArray();
            }

            $export_title = new Translation('s_export');

            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename="'.PROJECT_TITLE.'-'.$export_title.'-'.date('Ymd-His').'.csv"');

            array_walk_recursive($adressesarray, create_function(
                '&$item,$key', '$item = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item);')
            );

            $fp = fopen('php://output','w');

            $columns = array_keys($adresses->table());

            fputcsv($fp, $columns,';','"');

            foreach($adressesarray as $line) {
                fputcsv($fp, $line,';','"');
            }

            fclose($fp);

            exit();
            break;

        default:
            throw new Exception("Operation unknown: '$this->op'");
        }
    }
}

class action_fe_address_edit extends action_fe_address implements DisplayAction {


    function get_title() {
        return new Translation("fe_address_edit");
    }

    function process($aquarius, $request, $smarty, $result) {
        $user = null;
        $address = DB_DataObject::factory('fe_address');

        $found = $address->get($this->id);
        if (!$found) throw new Exception("Failed loading fe_address with ID '$this->id'");

        $formbuilder = DB_DataObject_FormBuilder::create($address);
        $form = $formbuilder->getForm();
        $renderer = new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $form->validate();
        $result->use_template("fe_address_edit.tpl");
        $smarty->assign('form_data', $renderer->toArray());
        $smarty->assign('insert', false);
        $smarty->assign('actions', array(Action::make('fe_address', 'save', $this->id), Action::make('cancel')));
    }
}


class action_fe_address_add extends action_fe_address implements DisplayAction  {

    function get_title() {
        return new Translation("fe_address_add");
    }

    function process($aquarius, $request, $smarty, $result) {
        $user = null;
        $address = DB_DataObject::factory('fe_address');

        $insert = true;
        $user = DB_DataObject::factory('fe_users');
        $found = $user->get($this->id);
        if (!$found) throw new Exception("Failed loading fe_user with ID '$this->id'");

        $formbuilder = DB_DataObject_FormBuilder::create($address);
        $form = $formbuilder->getForm();

        $renderer = new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);

        $result->use_template("fe_address_edit.tpl");
        $smarty->assign('form_data', $renderer->toArray());
        $smarty->assign('insert', true);
        $smarty->assign('actions', array(Action::make('fe_address', 'save', $this->id), Action::make('cancel')));

    }
}


class action_fe_address_save extends action_fe_address implements ChangeAction  {

    function get_title() {
        return new Translation("fe_address_edit");
    }

    function process($aquarius, $post, $result) {
        $insert = get($post, 'insert');
        $user = null;
        $address = DB_DataObject::factory('fe_address');

        if ($insert) {
            $user = DB_DataObject::factory('fe_users');
            $found = $user->get($this->id);
            if (!$found) throw new Exception("Failed loading fe_user with ID '$this->id'");
        } else {
            $found = $address->get($this->id);
            if (!$found) throw new Exception("Failed loading fe_address with ID '$this->id'");
        }

        $formbuilder = DB_DataObject_FormBuilder::create($address);
        $form = $formbuilder->getForm();

        $form->process(array($formbuilder,'processForm'), false);
        if ($insert) {
            $user_address = DB_DataObject::factory('fe_user_address');
            $user_address->fe_user_id = $user->id;
            $user_address->fe_address_id = $address->id;
            $user_address->insert();
        }
        $result->add_message(new Translation("fe_address_saved"));


    }
}