<?php 
/** @package Aquarius.frontend
  */

/** Searches words of search string in content title and content_field.
  * <pre>
  * Params:
  *   search: the search string.
  *   lg: Search for content in that language (uses current language by default)
  *   start: index of first node to put into $search.items
  *   length: count of nodes to put into items (max 100)
  *   notfound_recovery: Look in request URL and referrer URL for search strings. This is mainly used on error pages, to give the users options that might interest them.
  *   purge: Remove undesired nodes and their descendants from results, list of nodes
  * </pre>
  * If any of the params search, lg, start or length are not specified, the plugin looks for them in the request.
  *
  * <pre>
  * Assigns results to $result variable:
  *   $result.run: True if a search was performed
  *   $result.next: Index of the next $length items (0 if none)
  *   $result.count: amount of items
  *   $result.items: List of matching entries where each entry has:
  *      nr: counter from start index
  *      node: the node object
  *      content: loaded content object
  *      relevance: the relevance of the entry
  * All params used will be assigned to $result as well.
  * </pre>

  */
class Content_Search {
    /** Query string
      * Plain string of whitespace separated words. Max 255 chars, an arbitrary limit to make sure we're not running huge requests on the DB */
    var $search = '';

    /** Index into result list for first result to be returned. */
    var $start = 0;

    /** Number of entries to return, max 100. */
    var $length = 10;

    /** language code to search in */
    var $lg;
    
    /** Branches to hide in the result */
    var $purge = array();
    
    function parameter_types() {
        return array('search' => 'string'
                    ,'start'  => 'int'
                    ,'length' => 'int'
                    ,'lg'     => 'string'
                    ,'purge'  => 'string'
                    );
    }


    /** Read parameters 'search', 'start' and 'length' from dict.
      * @param $params */
    function read($params) {
        foreach(validate($params, $this->parameter_types()) as $key => $value) {
            $this->$key = $value;
        }
    }


    function sanitize() {
        $valid = true;
        $this->search = substr(trim($this->search), 0, 255);
        $this->start = max(0, $this->start);
        $this->length = min(100, max(1, $this->length));
        $this->lg = db_Languages::ensure_code($this->lg);
        $this->purge = db_Node::get_nodes($this->purge);
        return strlen($this->lg) > 0;
    }


    /** Get WHERE clauses for search */
    function restrictions() {
        $restrictions = array();

        // Hide restricted nodes unless user has access
        $hide_restricted = "node.cache_access_restricted_node_id = 0";
        $user = db_Fe_users::authenticated();
        if ($user) {
            $permitted = $user->get_access_nodes();
            $restrictions['access'] = "($hide_restricted OR node.cache_access_restricted_node_id IN (".join(',', $permitted)."))";
        } else {
            $restrictions['access'] = $hide_restricted;
        }

        // Node and content must be active
        $restrictions['active'] = "(node.active AND content.active)";
        
        if ($this->purge) {
            $purgatory = array();
            foreach($this->purge as $purge_node) {
                $purgatory []= "(node.cache_left_index < $purge_node->cache_left_index OR node.cache_right_index > $purge_node->cache_right_index)";
            }
            $restrictions['purge'] = '('.join(' AND ', $purgatory).')';
        }

        return $restrictions;
    }

    /** Run search based on parameters
      * Parameters are sanitized prior to execution. A dictionary containing the search results is returned. These things are included in the result dict:
      *   run: Bool whether a search was done
      *   items: list of things found
      *   count: how many items on this page
      *   next: Index for the next page, false if there are no more pages
      *
      * Each item has the following entries:
      *   nr:        ordinal number for this item
      *   relevance: calculated relevance
      *   node:      the node
      *   content:   and its content
      *
      * Also all parameters of the search are included in that result dict. */
    function find() {
        global $DB;

        $run = $this->sanitize();
        if (!$run) {
            $result->error = 'initialization failed';
        }

        $run = $run && strlen($this->search) > 1;
        $items = array();
        $next = false;

        if ($run) {
            $search_escaped = mysql_real_escape_string($this->search);

            if ($start == 0) {
                // Log the search
                $DB->query("INSERT INTO content_search SET lg='$lg', query='".$search_escaped."'");
            }

            $wheres = $this->restrictions();
            $wheres []= "content.lg = '$this->lg'";
            $wherestr = join('
                AND ', $wheres);

            $results = $DB->query("
            SELECT * FROM (
                SELECT
                    node.id AS node,
                    content.id AS content,
                    SUM(
                        MATCH(content_field_value.value) AGAINST ('".$search_escaped."')
                        + (content_field_value.value LIKE '%".$search_escaped."%') * 0.1
                    ) AS relevance
                FROM          node
                    JOIN      content ON node.id = content.node_id
                    LEFT JOIN content_field ON content.id = content_field.content_id
                    LEFT JOIN content_field_value ON content_field.id = content_field_value.content_field_id
                WHERE $wherestr
                GROUP BY content_field.content_id
                HAVING relevance > 0
            ) AS hits
            ORDER BY relevance DESC
            LIMIT $this->start,".($this->length+1));

            $nr = $this->start;
            while($item = mysql_fetch_assoc($results)) {
                if (count($items) >= $this->length) {
                    $next = $this->start+$this->length;
                    break;
                }
                $node = db_Node::staticGet($item['node']);
                $content = db_Content::staticGet($item['content']);
                $content->load_fields();
                $relevance = $item['relevance'];
                $nr++;
                $items[$nr] = compact('nr', 'node', 'relevance', 'content');
            }
        }
        $params = validate(object_to_array($this), $this->parameter_types());
        $count = count($items);
        $result = $params+compact('run', 'items', 'next', 'count');
        return $result;
    }
}
