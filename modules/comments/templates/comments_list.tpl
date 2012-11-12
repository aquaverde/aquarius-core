{include file='header.tpl'}
<h1>{#comment_list#}</h1>
<div class="bigbox">
{if $new_comments}
    <div class="bigboxtitle"><h2>{#new_comments#}</h2></div>
    <form action="{url action=$lastaction}" method="post" class="commentsbox">
        <ul class='comments new'>
{foreach from=$new_comments item=comment}
    {include file=comments_detail.tpl selectable=true}
{/foreach}
        </ul>
        {include file=select_buttons.tpl actions=$new_actions}
    </form>
{/if}
    <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>

    <ul class='comments'>
{foreach from=$comments item=comment}
    {include file=comments_detail.tpl selectable=false}
{foreachelse}
    {#no_comments#}
{/foreach}
    </ul>
</div>
{include file='footer.tpl'}