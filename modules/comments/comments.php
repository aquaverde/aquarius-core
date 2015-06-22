<?php

class Comments extends Module {
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend', 'frontend_page', 'contentedit_addon', 'daily') ;
    var $short = "comments" ;
    var $name  = "User comments per page" ;

    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_root',
            56,
            new Menu('menu_comments', false, false, array(
                10 => new Menu(false, Action::make('comments_list'))
            ))
        );
    }

    /** Deletes old rejected comments */
    function daily() {
        // I just can't resist using biblical terms when it comes to deleting
        // comments. THE INFERNO IS NIGH.
        $broom_of_damnation = strtotime('-'.$this->conf('limbo_duration'));
        
        global $DB;
        $DB->query("DELETE FROM comment WHERE status = 'rejected' AND date < $broom_of_damnation");
        $deleted = $DB->affected_rows();
        if ($deleted > 0) {
            Log::info("Deleted $deleted rejected comments");
        } else {
            Log::debug("No rejected comments to delete");
        }
    }

    function contentedit_addon($node, $content, $form) {
        if ($node->id) {
            $comment = DB_DataObject::factory('comment');
            $comment->node_id = $node->id;
            $count = $comment->count();
            
            if ($count > 0) {
                $list_action = Action::make('comments_list', $node->id);
                
                return array(
                    'template' => 'comments_addon.tpl', 
                    'data' => compact('count', 'list_action')
                );
            }
        }
        return null;
    }

    /** Register posted comments
     * Request variables:
     *   submit_comment: trigger
     *   name, email, subject, body: comment fields
     *
     * Should the comment subject be missing, it will be generated from the
     * first few words in the body. Note that the email field need not contain
     * a valid address.
     *
     * All comment fields will be stored as HTML, sanitized as follows:
     *   name, subject, email: all sequences of whitespace are compacted to a single space character
     *   body: more or less left as-is, with HTML linebreaks inserted after escaping
     * 
     * Hook 'comments_posted' is called after insertion of the comment, with the
     * newly inserted comment as argument.
     *
     * The smarty variable 'comment_posted' is set when a comment was posted.
     */
    function frontend_page($smarty) {
        if (isset($_POST['submit_comment'])) {
            global $aquarius;

            $sent = $_POST;
            $bend_email_field = $this->conf('bend_email_field');
            if ($bend_email_field) {
                $have_email_content = isset($sent['email']) && strlen($sent['email']) > 0;
                if ($have_email_content) {
                    Log::debug("Suspected spam, it filled out the 'email' form filed that we assume was hidden. Comment will not be registered.");
                    
                    // Act as if it works to keep them guessing
                    $smarty->assign('comment_posted', 1);
                    
                    // Byebye
                    return;
                }
                
                // Replace email field
                $sent['email'] = get($sent, $bend_email_field);
            }
            
            $carry_fields = array();
            foreach($this->conf('carry_fields') as $field_name) {
                if (strlen($sent[$field_name]) > 0) {
                    $carry_fields[$field_name] = $sent[$field_name];
                }
            }
            $aquarius->session_set('comment_form_settings', $carry_fields);

            $comment_node = $smarty->get_template_vars('node');
            $lg = $smarty->get_template_vars('lg');

            $new_comment = DB_DataObject::factory('comment');
            $new_comment->date = time();
            $new_comment->node_id = $comment_node->id;
            $new_comment->accepted = false;
            $new_comment->lg = false;
            foreach(array('name', 'email', 'subject', 'body') as $field) {
                $new_comment->$field = requestvar($field);
            }

            // Generate subject line in case its empty
            if (strlen($new_comment->subject) < 1) {
                // Grab the first ten words in body
                $head_words = preg_split('/\p{Z}+/', $new_comment->body, 11);
                
                // Add words till we have something like a subject
                while ($head_word = array_shift($head_words)) {
                    if (strlen($head_word) + mb_strlen($new_comment->subject) >= $this->conf('subject_length')) {
                        // enough
                        break;
                    }
                    $new_comment->subject .= "$head_word ";
                }
            }
            
            // Sanitize name, subject, email
            foreach(array('prename', 'name', 'subject', 'email') as $field) {
                // Compress sequences of any whitespace characters to one ordinary space character
                $new_comment->$field = htmlspecialchars(preg_replace('/\p{Z}+/', ' ', trim($new_comment->$field)));
            }
            
            // Sanitize body
            $new_comment->body = nl2br(htmlspecialchars(trim($new_comment->body)));
            
            // Ignore empty comments
            if ($new_comment->body == "") return;
            
            
            Log::info("Adding new comment: '$new_comment->subject' by '$new_comment->name $new_comment->email'");
            $new_comment->insert();
            
            $this->maybe_send_notice($new_comment);
            
            $aquarius->execute_hooks('comment_posted', $new_comment);
            
            $smarty->assign('comment_posted', 1);
        }
    }
    
    /** Send new-comment notice
      * Sent only if there are currently no new comments. This avoids sending
      * a notice for comments until the administrator reacted to the inital
      * notice. This saves bytes and nerves. */
    function maybe_send_notice($new_comment) {
        $send_notice_to = $this->conf('notice_email');
        $test_email = $this->conf('notice_email_test');
        if ($test_email && $test_email == $new_comment->email) {
            $send_notice_to = $test_email;
        }
        if ($send_notice_to) {
            global $DB;
            $new_comments_search = new Comments_Search($DB);
            $new_comments_search->status = 'new';
            $new_comments_search->limit = 1; // Poor man's count
            $found = $new_comments_search->find();
            if ($found->more) {
                Log::debug("Not sending new-comment notice because unconfirmed comments are already present");
            } else {
                $mailc = $this->aquarius->get_smarty_backend_container('de'); // FIXME: The whole world doesn't speak german
                $mailc->assign('new_comment', $new_comment);
                $mailc->assign('to', $send_notice_to);

                $mail = new AquaMail($mailc, 'new_comment_notice_mail.tpl');
                $mail->send();
            }
        }
    }

    function frontend_interface() {
        return $this;
    }
    
    /** Load list of comments for a node
     *  
     * @param node what node to load comments for (preset: current node)
     * @param limit amount of comments to load (preset: 10)
     * @param offset amount of comments to skip (preset: 0)
     * @param assign assign list of comments to this variable name (preset: comments)
     * 
     * Comment fields are sanitized, some fields may contain selected HTML
     * formatting tags. No further escaping is required in HTML.
     *  
     */
    function load($params, $smarty) {
        $node = DB_Node::get_node(get($params, 'node', $smarty->get_template_vars('node')));
        $limit = intval(get($params, 'limit', 10));
        $offset = intval(get($params, 'offset', 0));
        $assign = get($params, 'assign', 'comments');
        
        // Don't segregate between languages. This was done out of a sense of
        // duty to further communication between language groups. This message
        // was written to make you feel bad should you add language segregation
        // to the comment section. 
        global $DB;
        $comments = new Comments_Search($DB);
        $comments->status = 'accepted';
        $comments->nodes = $node->id;
        $found = $comments->find();
        
        $smarty->assign($assign, $found->comments);
        
        global $aquarius;
        $smarty->assign('comment_form_settings', $aquarius->session_get('comment_form_settings'));
    }
    
    /** Count comments for a node
     *  
     * @param node what node to load comments for (preset: current node)
     * 
     *  
     */
    function count($params, $smarty) {
        $node = DB_Node::get_node(get($params, 'node', $smarty->get_template_vars('node')));
        $comment = DB_DataObject::factory('comment');
        $comment->node_id = $node->id;
        $comment->status = 'accepted';
        return $comment->count();
    }
}
