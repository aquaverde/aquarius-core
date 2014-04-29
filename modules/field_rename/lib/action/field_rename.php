<?php 
/** Copy content fields to a new name */
class action_field_rename extends ModuleAction {
	var $modname = "field_rename";
    var $props = array('class', 'op');

    function get_title() {
        return new Translation('field_rename');
    }
    
    /** Only superusers may rename fields */
    function valid($user) {
      return $user->isSuperadmin();
    }

    function get_params($post) {
        $params = validate_or_die($post, array(
            'original_name'                 => 'string',
            'new_name'                      => 'string',
            'new_type'                      => 'string empty',
            'original_base_name'            => 'string empty',
            'original_supplementary_name'   => 'string empty',
            'new_supplementary_type'        => 'string empty'
        ));
        return $params;
    }

    function field_list($params) {
        global $DB;

        $selects = array('SELECT cf.content_id AS content_id', 'cf.id AS content_field_id', 'cf.name AS original_name', 'cfv.value AS value', 'cfv.name AS original_type');
        $joins   = array('FROM content_field AS cf'
                        ,'JOIN content_field_value cfv ON cf.id = cfv.content_field_id');
        $wheres  = array("WHERE cf.name LIKE '".mysql_real_escape_string($params['original_name'])."'");
        $order  = "ORDER BY content_id, original_name";

        if (!empty($params['original_supplementary_name'])) {
            $selects[] = 'cfv_supp.value AS supp_value';
            $selects[] = 'cfv_supp.name AS supp_name';
            $joins[]   = "LEFT JOIN content_field AS cf_supp
                            ON cf.content_id = cf_supp.content_id
                            AND cf_supp.name = REPLACE(cf.name, '".mysql_real_escape_string($params['original_base_name'])."', '".mysql_real_escape_string($params['original_supplementary_name'])."')";
            $joins[]   = 'LEFT JOIN content_field_value cfv_supp
                            ON cf_supp.id = cfv_supp.content_field_id';
        }

        $query = join(', ', $selects)."\n".join("\n", $joins)."\n".join(' AND ', $wheres)."\n".$order;
        return $DB->mapqueryhash('content_field_id', $query);
    }
}

class action_field_rename_show extends action_field_rename implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        if (isset($request['preview'])) {
            $params = $this->get_params($request);
            $smarty->assign('preview', true);
            $smarty->assign('params', $params);
            $smarty->assign('field_list', $this->field_list($params));
        }
        $smarty->assign("renameaction", Action::make('field_rename', 'rename'));
        $result->use_template('field_rename.tpl');
    }
}

class action_field_rename_rename extends action_field_rename implements ChangeAction {
    function process($aquarius, $post, $result) {
        global $DB;
        $params = $this->get_params($post);
        $list = $this->field_list($params);
        $weight = 0;
        foreach($list as $field) {
            $cf = DB_DataObject::factory('content_field');
            $cf->content_id = $field['content_id'];
            $cf->name = $params['new_name'];
            $cf->weight = $weight++;
            $success = $cf->insert();
            if (!$success) throw new Exception("Failed inserting content field $cf->name");

            $cfv = DB_DataObject::factory('content_field_value');
            $cfv->content_field_id = $cf->id;
            if (!empty($params['new_type'])) {
                $cfv->name = $params['new_type'];
            } else {
                $cfv->name = $field['original_type'];
            }
            $cfv->value = $field['value'];
            $success = $cfv->insert();
            if (!$success) throw new Exception("Failed inserting content field value $cfv->value");

            if (!empty($field['supp_value'])) {
                $cfv = DB_DataObject::factory('content_field_value');
                $cfv->content_field_id = $cf->id;
                if ($params['new_supplementary_type']) {
                    $cfv->name = $params['new_supplementary_type'];
                } else {
                    $cfv->name = $field['supp_name'];
                }
                $cfv->value = $field['supp_value'];
                $success = $cfv->insert();
                if (!$success) throw new Exception("Failed inserting supplementary content field value $cfv->value");
            }
        }

        $result->touch_region('content');
        $result->add_message(new Translation("s_message_renamed_fields", array($params['original_name'], $params['new_name'], count($list))));
    }
}

