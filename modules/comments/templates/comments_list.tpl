{include file='header.tpl'}
<h1>{#comments_list#}</h1>
{if $new_comments}
    <h2>{#new_comments#}</h2>
    <form action="{url action=$lastaction}" method="post" class="commentsbox">
        <ul class='comments new'>
            {foreach from=$new_comments item=comment}
                {include file='comments_detail.tpl' selectable=true}
            {/foreach}
        </ul>
        {include file='select_buttons.tpl' actions=$new_actions}
    </form>
{/if}
<h2>{$lastaction->get_title()}</h2>
<ul class='comments'>
    {foreach from=$comments item=comment}
        {include file='comments_detail.tpl' selectable=false}
    {foreachelse}
        {#no_comments#}
    {/foreach}
</ul>
{include file='footer.tpl'}