{include file='header.tpl'}
    <h1>{$title}</h1>
    <div class="dialog">
    <form action="{url}" method="post">
        <div>{$message}</div>
        <br/>
        <input type="submit" name="{$yesAction}" class="submit" value="  {#s_yes#}  " />
        <input type="submit" name="{$noAction}" class="cancel" value="  {#s_no#}  " />
    </form>
</div>
{include file='footer.tpl'}