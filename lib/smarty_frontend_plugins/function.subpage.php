<?php
/** Include a content with it's own template
  *
  * Required params:
  *    node: node to render. If this is a content object, lg is taken from this as well.
  *    lg:   language to render
  *
  * The subpage is rendered in a separate smarty environment, so it will not
  * affect the template that uses it. All other parameters are assigned to the
  * new environment.
  * 
  *
  */
function smarty_function_subpage($params, $smarty) {
    $pnode = get($params, 'node');

    // Extract language if this is a content
    $default_lang = $smarty->get_template_vars('lg');
    if ($pnode instanceof db_Content) $content_lang = $pnode->lg;

    $node = db_Node::get_node($pnode);
    if (!$node) throw new Exception("Unable to load node '$pnode'");
    $lg = get($params, 'lg', $clg);

    global $aquarius;
    $subsmarty = $aquarius->get_smarty_frontend_container($lg, $node);

    $assign_params = $params;
    unset($assign_params['node']);
    unset($assign_params['lg']);
    $subsmarty->assign($assign_params);
    
    $form = $subsmarty->get_template_vars('form'); // ugh
    $template = $form->template;

    // Determine template filename to use (Copy pasta from frontend.php)
    if (!$subsmarty->template_exists($template)) {
        // Try appending '.tpl' to template name
        if ($subsmarty->template_exists($template.'.tpl')) {
            $template = $template.'.tpl';
        } else {
            $smarty->trigger_error("Subpage: Missing template $template in form $form->name for node ".$node->idstr());
        }
    }

    Log::debug("Using template $template to render ".$node->idstr()." as subpage");
    $subsmarty->display($template);
}