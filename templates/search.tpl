{include file="header.tpl"}

<div class="bigbox">
    <h1>{#s_freitextsuche#} <form action="" style='display:inline'><input name='search' value='{$searchString|escape}'><input type='submit' name='{"search:"|makeaction}' value='{#s_search#}'></form></h1>
    {foreach from=$edit_content key='counter' item='edit'}
    <div class="even,odd"><a href="{url action0=$lastaction action1=$edit.action}">{$counter+1}. {$edit.content->title|default:#no_title#|escape}
    {if $edit.content->text1} <span class="dim">({$edit.content->text1|strip_tags|truncate:80})</span>
    {elseif $edit.content->text} <span class="dim">({$edit.content->text|strip_tags|truncate:80})</span>{/if}
    </a></div>
    {/foreach}
</div>

{include file="footer.tpl"}