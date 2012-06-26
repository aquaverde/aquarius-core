<?php
class action_search extends AdminAction implements DisplayAction {

    var $props = array("class", "search");

    /** Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }

    /** Assigns array with search results to smarty container */
    function process($aquarius, $params, $smarty, $result) {
        global $DB;
        global $lg;

        // Maybe take the search string from POST (for forms)
        if ( !empty($params['search']) ) $this->search = $params['search'];
        $searchstring = $this->search;

        $lg = db_Languages::ensure_code($lg, false);
        if(is_numeric($searchstring)) {
            $node_ids = $DB->listquery("
                SELECT id FROM node WHERE id = $searchstring"
            );
        } else {
            // Query the DB
            $searchlike = "'%".mysql_real_escape_string($searchstring)."%'";
            $searchagainst = "AGAINST ('".mysql_real_escape_string($searchstring)."')";
            $node_ids = $DB->listquery("
                SELECT node_id FROM (
                    SELECT
                        node.id as node_id,
                        SUM(MATCH(content_field_value.value) $searchagainst + (content_field_value.value LIKE $searchlike)*0.1) + (node.name = $searchname) AS relevance
                    FROM          node
                        JOIN      content ON node.id = content.node_id
                        LEFT JOIN content_field ON content.id = content_field.content_id
                        LEFT JOIN content_field_value ON content_field.id = content_field_value.content_field_id
                    WHERE content.lg = '$lg'
                    GROUP BY content_field.content_id
                ) AS hits
                WHERE relevance > 0
                ORDER BY relevance DESC"
            );
        }

        // Load the content objects from the IDs, building edit actions (which conveniently checks permissions)
        $edit_content = array();
        foreach($node_ids as $node_id) {
            $action = Action::make('contentedit', 'edit', $node_id, $lg);
            if ($action) {
                $content = db_Content::get_cached($node_id, $lg);
                $content->load_fields(); // Dammit
                $edit_content[] = compact('action', 'content'); 
            }
        }


        $smarty->assign("searchString", $searchstring);
        $smarty->assign("edit_content", $edit_content);
        $result->use_template('search.tpl');
	}
}

