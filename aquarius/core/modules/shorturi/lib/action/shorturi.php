<?php
class action_Shorturi extends ModuleAction {
	var $modname = "shorturi";
	var $props = array('class', 'op');
}

class action_Shorturi_manage extends action_Shorturi implements DisplayAction
{

    function valid($user) {
      return (bool)$user;
    }


	function process($aquarius,$request,$smarty,$result) 
	{
		global $DB;

		if(isset($request['save_button']))
		{

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
		
		//IMPORT CONFIG
		// $DB->query("truncate table shorturi");
		// foreach ($this->module->conf("redirections") as $key => $value) 
		// {
		// 	$query = "INSERT INTO shorturi 
		// 					(keyword, redirect) 
		// 				VALUES 
		// 					('".mb_strtolower(urldecode($key))."','".$value."')";

		// 	$DB->query($query);
		// }

		// $frontend = $this->module->aquarius->conf("frontend");
		// foreach ($frontend["domains"] as $key => $value) 
		// {
		// 	if(isset($value["shorturi"]))
		// 	{

		// 		foreach ($value["shorturi"] as $keyword => $newurl) {
		// 			$query = "INSERT INTO shorturi 
		// 							(domain, keyword, redirect) 
		// 						VALUES 
		// 							('".$key."','".mb_strtolower(urldecode($keyword))."','".$newurl."')";

		// 			$DB->query($query);
		// 		}
		// 	}
		// }

		$query = "SELECT * FROM shorturi ORDER BY keyword";
		$redis = $DB->queryhash($query);

		$smarty->assign("uris", $redis);
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

?>