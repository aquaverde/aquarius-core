{comments->load offset=$smarty.get.offset}

<h3>{wording Comments}</h3>
{w key="There are %s comments" p=$comments|@count}
<ul class='comments'>
{foreach from=$comments item=comment}
    <li class='comment'>
        <span class='head'   >{w key='%s wrote on %s' p1=$comment.name p2=$comment.date|date_format escape=false}</span>
        <span class='subject'>{$comment.subject}</span>
        <span class='body'   >{$comment.body}</span>
    </li>
{/foreach}
</ul>

<div id='comment_form'>
{if $comment_posted}
    {wording Thanks for your comment. It will be published after review.}
{else}
    {wording Post new comment}
    <form action='#comment_form' method='post'>
        <label>{wording Name}<input type='text' name='name'  value="{$comment_form_settings.name|escape}"/></label>
        <label>{wording Email}<input type='text' name='liame'  value="{$comment_form_settings.email|escape}"/></label>
        <input type='text' name='email'  value="" style="display: none"/>{* spam trap *}
        <label>{wording Subject}<input type='text' name='subject' /></label>
        <label>{wording Message}<textarea name='body'></textarea></label>
        <input type='submit' name='submit_comment' value='{wording Submit comment}' />
    </form>
{/if}
</div>