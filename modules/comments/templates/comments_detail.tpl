<li class="comment">
    <label>
        {if $selectable}
            <input class='select' type='checkbox' name='comment_select[]' value='{$comment.id}'/><br>
        {else}
            <form action="{url action=$lastaction}" method='post'>
                <input type="hidden" name="comment_select[]" value="{$comment.id}">
                {if $comment.status=='rejected'}
                    {actionlink action="comments_accept:accepted"}
                {else}
                    {actionlink action="comments_accept:rejected"}
                {/if}
            </form>
        {/if}
    <ul class='header'>
        <li class='date'>{$comment.date|date_format:'%d.%m.%Y %H:%M'}</li>
        <li class='name'>{$comment.prename} {$comment.name}</li>
        <li class='email'>{$comment.email}</li>
        {load node=$comment.node_id ignore=true var=comment_content}
        {if $comment_content_node}<li class='node'>{#comment_topic#}: {actionlink action="contentedit:edit:`$comment.node_id`:$lg" title=$comment_content_node->get_contenttitle() icon_placement=after}</li>{/if}
    </ul>
    </label>
    <div class='body'>
    <h3>{$comment.subject}</h3>
    {$comment.body}</div>
</li>
