{include file='header.tpl'}
{php}
$form = $this->get_registered_object("form");
$action = $this->get_registered_object("action");
$url = $this->get_template_vars("url");
$form->updateAttributes(array('action'=>($url->with_param($action)->str())));
$form->display();
{/php}
{include file='footer.tpl'}