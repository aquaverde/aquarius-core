{include file='header.tpl'}
<h1>{#tableexport_deleteall#}</h1>
{if !$deleted}
<div class="bigbox">
<h2>{$count} {#tableexport_entries#}</h2>
<form style="display: inline" name="deleteBookings" method="post" action="{url action1=$lastaction}"  enctype="multipart/form-data">
<p>
{capture name=message1 assign=message}
    {#tableexport_deleteall_confirm#}
{/capture}
{capture name=title1 assign=title}
    {#tableexport_deleteall#}
{/capture}
{confirm yes="tableexport:deleteall:$lg"
    no=''
    title=$title
    message=$message}
        <input type="submit" name="{$action}" value="{#s_delete#}" class="button" />
{/confirm}
</p>
</form>
</div>
{else}
<div class="bigbox">
</div>
{/if}
{include file='footer.tpl'}
