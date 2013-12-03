<?php 
/** Function to generate URLs in smarty. It takes objects of the Url class and adds actions. Actions can be strings describing actions or actual action class objects. Example:
    {url url=$url action0="node:toggle_active:`$node->id`" action1=$someaction}
    
Params:
    - action*: All parameters that start with action must either have an action or an action string as value
    - escape: whether to escape the resulting url (with htmlspecialchars())
    - delimiter: in case you want to change the delimiter for actions strings, default is ':'.
    - url: in case you want to use a another url, instead of the default 'url' from the template
*/
function smarty_function_url($params, $template) {
    $template->loadPlugin('smarty_modifier_makeaction');
    $url = get($params, 'url', $template->get_template_vars('url'));
    $escape = get($params, 'escape', true);
    $delimiter = get($params, 'delimiter', ':');
    
    foreach($params as $name=>$val) {
        // Simple: Every parameter that starts with 'action' in its name must be an action.
        if (preg_match("/^action/", $name)) {
            $action = smarty_modifier_makeaction($val, $delimiter);
            $url = $url->with_param($action);
        }
    }
    return $url->str($escape);
}
?>