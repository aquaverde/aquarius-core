<?php 
class action_newsletter_edit extends AdminAction implements DisplayAction {

    var $props = array('class', 'root', 'lg');

    /** Permits for all users */
    function permit_user($user) {
        return true;
    }

    function process($aquarius, $request, $smarty, $result) {
        $root = db_Node::get_node($this->root);
        $open_nodes = NodeTree::get_open_nodes('sitemap');
        array_unshift($open_nodes, $root->id); // Always open root node
        $tree = NodeTree::editable_tree($root, $this->lg, $open_nodes);
        NodeTree::add_controls($tree, $open_nodes, 'sitemap', true, $this->lg);
        $tree['show_toggle'] = false; // Hack: do not show toggle for root node

        $smarty->assign('entry', $tree);
        $smarty->assign('forallaction', Action::make('nodetree', 'forall'));

        // Hack: let the temaplate know that we want the sitemap controls
        $this->section = 'sitemap';

        $result->use_template("nodetree.tpl");
    }
}
?>