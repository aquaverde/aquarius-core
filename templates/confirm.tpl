{include file='header.tpl'}
    <h1>{$title}</h1>
    <div class="dialog">
    <form action="{url}" method="post">
        <div>{$message}</div>
        <br/>
        <input type="submit" name="{$yesAction}" class="btn btn-default" value="  {#s_yes#}  " />
        <input type="submit" name="{$noAction}" class="btn btn-default" value="  {#s_no#}  " />
    </form>
</div>
{include file='footer.tpl'}