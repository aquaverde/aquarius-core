{comments->load offset=$smarty.get.offset}

{wording Comments}
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

{wording Post new comment}
<form action='' method='post'>
    <label>{wording Name}<input type='text' name='name'  value="{$comment_form_settings.name|escape}"/></label>
    <label>{wording Email}<input type='text' name='email'  value="{$comment_form_settings.email|escape}"/></label>
    <input type='text' name='email'  value="" style="display: none"/>{* spam trap *}
    <label>{wording Subject}<input type='text' name='subject' /></label>
    <label>{wording Message}<textarea name='body'></textarea></label>
    <input type='submit' name='submit_comment' value='{wording Submit comment}' />
</form>