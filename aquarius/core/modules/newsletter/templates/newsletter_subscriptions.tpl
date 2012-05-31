{include file='header.tpl'}

<h1>{$newsletter_node->get_contenttitle()}</h1>

{assign var='boxtitle' value=#newsletter_subscriptions#}
{assign var='alladdresses' value=false }

{include file='newsletter_sub_addresses.tpl'}

{include file='footer.tpl'}