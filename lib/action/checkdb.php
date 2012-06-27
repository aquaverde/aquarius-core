<?

/** Check DB integrity
*/

class action_checkdb extends AdminAction {

    var $props = array('class', 'command');
    
    /** Incomplete table with references between tables
      * This table format grew organically(TM) */
    var $references = array(
            array('require', array('content', 'node_id', 'id'), array('node', 'id')),
            array('require', array('content', 'lg', 'id'), array('languages', 'lg')),
            array('require', array('content_field', 'content_id', 'id'), array('content', 'id')),
            array('require', array('content_field_value', 'content_field_id', 'id'), array('content_field', 'id')),
            array('maybe', array('node', 'parent_id', 'id'), array('node', 'id')),
            array('maybe', array('node', 'form_id', 'id'), array('form', 'id')),
            array('maybe', array('node', 'childform_id', 'id'), array('form', 'id')),
            array('maybe', array('node', 'contentform_id', 'id'), array('form', 'id')),
            array('require', array('form_field', 'form_id', 'id'), array('form', 'id')),
            array('require', array('users2languages', 'lg', 'id'), array('languages', 'lg'))
    );

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function find_dangling($result) {
        global $DB;

        $dangling_entries = array();
        foreach($this->references as $ref) {
            list($rule, $src, $dst) = $ref;
            $query_dangling = 'SELECT src.* FROM '.$src[0].' src LEFT JOIN '.$dst[0].' dst ON src.'.$src[1].' = dst.'.$dst[1].' WHERE dst.'.$dst[1].' IS NULL'; // Find all entries from src that do not point to a valid dst
            if ($rule == 'maybe') $query_dangling .= ' AND src.'.$src[1].' > 0'; // Restrict to entries where an ID is given
            $query_dangling .= ' ORDER BY src.'.$src[2];
            try {
                $dangling = $DB->mapqueryhash($src[2], $query_dangling);
            } catch (Exception $e) {
                $result->add_message("Failed checking constraint ".print_r($ref, true).": ".$e->getMessage());
            }

            // Build a string that describes the operation
            $refstr = $src[0].'.'.$src[1].'->'.$dst[0].'.'.$dst[1];
            $spec = "";
            if ($rule == 'require') $spec = 'delete dangling entries';
            else                    $spec = 'reset references to zero';

            if (count($dangling) > 0) $dangling_entries[$refstr] = compact('dangling', 'spec', 'rule', 'src', 'dst');
        }
        return $dangling_entries;
    }
}

/** Remove dangling references */
class action_checkdb_clean extends action_checkdb implements ChangeAction {
    var $props = array('class', 'command', 'ref');
    function process($aquarius, $post, $result) {
        global $DB;
        $danglings = $this->find_dangling($result);
        foreach ($danglings as $refstr => $entry) {
            extract($entry);
            if ($this->ref == 'all' || $this->ref == $refstr) {
                // Either delete the entry or set the offending reference to zero
                if ($rule == 'require')
                    $DB->query('DELETE FROM '.$src[0].' WHERE '.$src[2].' IN ('.join(',', array_keys($dangling)).')');
                else
                    $DB->query('UPDATE '.$src[0].' SET '.$src[1].' = 0 WHERE '.$src[2].' IN ('.join(',', array_keys($dangling)).')');
                $messages[] = "Cleaned $refstr";
            }
        }
    }
}

class action_checkdb_show extends action_checkdb implements DisplayAction {

    function get_title() {
        return new Translation('menu_super_checkdb');
    }

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('dangling', $this->find_dangling($result));
        $result->use_template('checkdb.tpl');
    }
}
?>