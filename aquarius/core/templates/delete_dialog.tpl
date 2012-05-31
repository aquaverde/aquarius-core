{include file='header.tpl'}
<h1>{$lastaction->get_title()|escape}</h1>

<form action="{url}" method="post">
<div class="bigbox">
    <h2>{#s_delete_content#}</h2>
    {$smarty.config.s_confirm_delete_content|sprintf:$node->title}
    <br/>
    <br/>
    {if $children}
    <h2>{#s_confirm_delete_children#}</h2>
    <ul style="margin-top: 10px;">
        {foreach from=$children key=key item=child}
            <li>- {$child->get_contenttitle()|escape}</li>
        {/foreach}
    </ul>
    {/if}
    {include file="select_buttons.tpl"}
</div>
</form>
{include file='footer.tpl'}