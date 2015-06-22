<?php
class Comments_Search {
    /** Restrict to dates before this time (unix timestamp) */
    var $beforedate;

    /** Restrict to dates after this time, inclusive (unix timestamp)
      *
      * Date timestamps are regular epoch timestamps where the time portion is
      * set to the start of the desired day. Thus the result will
      * include all comments of the given date.
      */
    var $afterdate;

    /** Include only comments made before this comment_id */
    var $beforecomment;

    /** Include only comments made after this comment_id */
    var $aftercomment;

    /** Restrict to comments attached to certain nodes
      * Accepts a comma-separated list of node identifiers. */
    var $nodes;

    /** Find comments having all of the words in this string */
    var $words;

    /** Select based on status
      * May be an array of status strings. */
    var $status;
    
    /** limit result to this amount */
    var $limit;
    
    /** shift result by this many comments
      * Only applies when limit is given. */
    var $offset;
    
    function __construct($DB) {
        $this->DB = $DB;
    }

    function find() {
        $wheres = array('1=1');
        $data = [];

        if ($this->beforedate !== null) {
            $wheres []= 'date < ?';
            $data []= intval($this->beforedate);
        }
        if ($this->afterdate !== null) {
            $wheres []= 'date >= ?';
            $data []= intval($this->afterdate);
        }

        if ($this->beforecomment !== null) {
            // Assume that comment ids are always increasing with time
            $wheres []= 'id < ?';
            $data []= intval($this->beforecomment);
        }

        if ($this->aftercomment !== null) {
            // Assume that comment ids are always increasing with time
            $wheres []= 'id > ?';
            $data []= intval($this->aftercomment);
        }

        if (strlen($this->words) > 0) {
            // http://stackoverflow.com/questions/790596/split-a-text-into-single-words
            $words = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $this->words, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                // This is not the most efficient search. It's thorough and errs on the side of including irrelevant stuff. It finds 'Boars' when searching for 'oar'.
                // We expect sporadic searches for names
                // You know how the MySQL fulltext-index works? It doesn't.
                $ors = array();
                $escaped_word = $this->DB->quote($word);
                foreach(array('name', 'email', 'subject', 'body') as $field) {
                    $ors []= "$field LIKE '%$escaped_word%'";
                }
                $wheres []= '('.join(' OR ', $ors).')';
            }
        }

        if ($this->nodes !== null) {
            $nodes = db_Node::get_nodes($this->nodes);
            $ids = array();
            if ($nodes) {
                foreach($nodes as $node) {
                    $ids []= $node->id;
                }
            }
            $wheres []= 'node_id in ('.join(',', $ids).')';
        }

        if ($this->status) {
            $status_list = is_array($this->status) ? $this->status : array($this->status);

            $wheres []= "status IN (".join(",", array_fill(0, count($status_list), '?')).")";
            $data = array_merge($data, $status_list);
        }

        $limit = '';
        if($this->limit) {
            /* When searching with a limit, we also want to communicate whether
            * there are more entries past the limit. To do this, we raise the limit
            * by one, then cut that entry out should it show up. */
            $limit_params = array();
            if ($this->offset) {
                $limit_params []= intval($this->offset);
            }
            $limit_params []= intval($this->limit) + 1; // It is assumed that nobody would limit to INT_MAX
            $limit = 'LIMIT '.join(',', $limit_params);
        }

        $result = $this->DB->queryhash("
            SELECT id, prename, name, email, subject, date, body, node_id, status
            FROM comment
            WHERE ".join(' AND ', $wheres)."
            ORDER BY date DESC
            $limit
        ", $data);

        $more = false;
        if ($this->limit && count($result) > $this->limit) {
            $more = true;
            array_pop($result);
        }

        return new Comment_Search_result($result, $more);
    }

    /** Same as find(), loads the dataobject for each comment */
    function find_objects() {
        $nts = array();
        foreach($this->find() as $nt) {
            $nts []= db_Comment::staticGet($nt['id']);
        }
        return $nts;
    }
}

class Comment_Search_Result {
    /** found comments  */
    var $comments;
    
    /** whether there are more comments over the limit */
    var $more;
    
    function __construct($comments, $more) {
        $this->comments = $comments;
        $this->more = $more;
    }
    
}