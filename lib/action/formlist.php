<?php
class action_formlist extends AdminAction implements DisplayAction { 
    
    var $props = array("class", "op");
    
    /** permit for superuser */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
    
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/db/Form.php";

        $formcts = array();
        foreach($aquarius->db->queryhash('
            SELECT
                form.id form_id,
                count(distinct applied.id) as applied_count,
                min(applied.id) as applied_example,
                count(distinct chosenchild.id) + count(distinct chosencont.id) as nodechild_count,
                min(chosenchild.id) as nodechild_example, 
                min(chosencont.id) as nodecont_example,
                count(distinct chosenform.id) as formchild_count,
                min(chosenform.parent_id) as formchild_example
            FROM form
            LEFT JOIN node applied ON form.id = applied.form_id
            LEFT JOIN node chosenchild ON form.id = chosenchild.childform_id
            LEFT JOIN node chosencont ON form.id = chosencont.contentform_id
            LEFT JOIN form_child chosenform ON form.id = chosenform.child_id
            GROUP BY form_id
            ORDER BY form.title;
        ') as $formdesc) {
            $form = DB_DataObject::factory('form');
            $form->get($formdesc['form_id']);
            
            $nodechild_example = min(intval($formdesc['nodechild_example']), intval($formdesc['nodecont_example']));
            
            $formcts []= array(
                'form' => $form,
                'count' => array(
                    'node' => $formdesc['applied_count'],
                    'nodechild' => $formdesc['nodechild_count'],
                    'formchild' => $formdesc['formchild_count'],
                ),
                'example' => array(
                    'node' => $formdesc['applied_example'] ? Action::make('node', 'editprop', $formdesc['applied_example']) : false,
                    'nodechild' => $nodechild_example ? Action::make('node', 'editprop', $nodechild_example) : false,
                    'formchild' => $formdesc['formchild_example'] ? Action::make('formedit', 'edit', $formdesc['formchild_example']) : false
                ),
            );
        }

        $smarty->assign('formcts', $formcts);
        $smarty->assign('action_new', Action::make('formedit', 'edit', 'new'));
        $result->use_template("formlist.tpl");
    }
}
