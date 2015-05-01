<?php
class action_Shorturi extends ModuleAction {
	var $modname = "shorturi";
	var $props = array('class', 'op');
}

class action_Shorturi_manage extends action_Shorturi implements DisplayAction {

    function valid($user) {
      return (bool)$user;
    }


	function process($aquarius, $request, $smarty, $result) {

		if(isset($request['save_button'])) {
			for($i = 0; $i < count($request['from']); $i++)
			{
				//URI ALREADY IN DB -> UPDATE				
				if(!empty($request['uritableid'][$i]))
				{
					//UPDATE
					if(empty($request['delete'][$i]))
					{
						$uri 			= DB_DataObject::factory("shorturi");

						$uri->get($request['uritableid'][$i]);
						$uri->domain 	= $request['from'][$i];
						$uri->keyword 	= mb_strtolower($request['keyword'][$i]);
						$uri->redirect 	= $request['url'][$i];
						$uri->update();
					}
					//DELETE
					else
					{
						$uri 			= DB_DataObject::factory("shorturi");

						$uri->get($request['uritableid'][$i]);
						$uri->delete();
					}
				}
				//NEW URI -> KEYWORD AND TARGETURL NOT EMPTY -> INSERT 
				else if(!empty($request['keyword'][$i]) && !empty($request['url'][$i]) && empty($request['delete'][$i]))
				{
					$uri 			= DB_DataObject::factory("shorturi");

					$uri->domain 	= $request['from'][$i];
					$uri->keyword 	= mb_strtolower($request['keyword'][$i]);
					$uri->redirect 	= $request['url'][$i];

					$uri->insert();
				}
			}
		}

		$query = "SELECT * FROM shorturi ORDER BY keyword";
		$redis = $aquarius->db->queryhash($query);

		$smarty->assign("uris", $redis);
		$smarty->assign('new_uri', array('id' => ''));
		
		$shorturi_content = array();
		foreach($aquarius->db->queryhash("
            SELECT content.id content_id, content_field_value.value short
            FROM node
            JOIN content ON node.id = content.node_id
            JOIN content_field ON content.id = content_field.content_id
            JOIN content_field_value ON content_field.id = content_field_value.content_field_id
            JOIN form ON node.form_id = form.id
            JOIN form_field ON form.id = form_field.form_id
            WHERE form_field.type = 'shorturi' 
              AND form_field.name = content_field.name
            ORDER BY content_field_value.value
        ") as $shorturi) {
            $content = DB_DataObject::factory('content');
            if (!$content->get($shorturi['content_id'])) continue;

            $node = $content->get_node();
            $shorturi_content[$shorturi['short']] []= array(
                'edit' => Action::make('contentedit', 'edit', $content->node_id, $content->lg),
                'title' => $content->get_title()
            );
        }
        $smarty->assign('shorturi_content', $shorturi_content);

		$result->use_template("manage.tpl");
	
	}

}

class action_Shorturi_empty_row extends action_Shorturi implements DisplayAction
{

	function process($aquarius,$request,$smarty,$result) 
	{

		$smarty->assign('myindex', intval(get($request, 'new_id')));
        $result->use_template('uritable.row.tpl');
	
	}

}
