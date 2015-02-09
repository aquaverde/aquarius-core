<?php 
/** @package Aquarius.backend */

require_once "lib/file_mgmt.lib.php";

class action_search_replace extends AdminAction {

    var $props = array("class", "command", "lg");

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSiteadmin();
    }

	function get_title() {
        return new Translation('s_r_menu_show');
    }
}

class action_search_replace_search extends action_search_replace implements DisplayAction {
	function process($aquarius, $request, $smarty, $result) {
		$search_string = get($request,'search_string');
		if(strlen($search_string) > 0) {
			$id_list = array();
			$escaped_search_string = str_replace("_","\_",str_replace("%","\%",$search_string));
		
			$query = "	SELECT
							content_field_value.id AS id,
							content_field_value.name,
							content_field_value.value AS value,
							content.cache_title AS title,
							content.node_id AS nodeId,
							content.lg AS lang	 				
						FROM
							content_field_value
						JOIN content_field ON content_field_value.content_field_id = content_field.id
						JOIN content ON content_field.content_id = content.id						
						WHERE
                        content_field_value.value COLLATE utf8_bin LIKE (?)
                        AND content.lg = ?
            ";
            $id_list = $aquarius->db->mapqueryhash("id",$query, array("%$escaped_search_string%", $this->lg));
			$counter = count($id_list);

			$i = 0;
			foreach($id_list as $key => $rr) {
				
				$value_bold = str_replace(
					htmlspecialchars($search_string),
					'<strong style="color:red;">'.htmlspecialchars($search_string).'</strong>',
					htmlspecialchars($rr['value']));
					
				// $position = stripos($rr['value'],$escaped_search_string);
				// $my_minus = 20;
				// $my_plus = 90;
				// 
				// if($position <= 20)
				// 	$my_trigger = $position;
				// if(strlen($value_bold) < ($position + $my_plus))
				// 	$my_plus = strlen($value_bold);
				// 	
				// $value_short = substr($value_bold, $position - $my_trigger, $position + $my_plus);
				// $value_end = '...'.$value_short.'...<br/>';	
				
				$id_list[$key]['value'] = $value_bold;
				$i++;
			}
			
			$smarty->assign("search_string",$search_string);
			$smarty->assign("counter", $counter);
			$smarty->assign("results", $id_list);
		}
		
		$result->use_template("search_replace.tpl");
	}
}

class action_search_replace_replace extends action_search_replace implements ChangeAction {
	function process($aquarius, $post, $result) {
		$search_string 	= get($post,'search_string');
		$replace_string	= get($post,'replace_string');

		if(strlen($post['search_string']) > 0) {
            $db = $aquarius->db;
						
			$query = "	UPDATE content_field_value
						JOIN content_field ON content_field_value.content_field_id = content_field.id
						JOIN content ON content_field.content_id = content.id
						SET
                            content_field_value.value = REPLACE(content_field_value.value, ?, ?)
                        WHERE
                            content.lg = ?
                        ";

            $db->query($query, array($search_string, $replace_string, $this->lg));

            $message = new Translation("s_r_affected");
            $result->add_message($db->affected_rows()." ".$message.". searched for '".$search_string."' replaced with '".$replace_string."'");
        }
		
		else {
			$result->add_message(new Translation("s_r_no_search_string"));
		}
	}
}