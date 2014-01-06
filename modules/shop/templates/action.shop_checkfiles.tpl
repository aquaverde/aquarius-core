{include file='header.tpl'}
    <h1>Check filenames</h1>
    <div class="dialog">
    <form action="{url action=$lastaction}" method="post">
        <table>
        <tr><th>Group</th><th>Color</th></td>
        {foreach from=$groups key='base' item='es'}
            <tr>
                <td>{$base}</td>
                <td>
                    <table>
                    {foreach from=$es item='parts'}
                        <tr>
                            <td {if !$parts.known_color}style="color:red;"{/if}>{$parts.color}</td>
                            <td>
                                {if !$parts.known_color}
                                    <select name="correct_color[{$parts.base|escape}][{$parts.color|escape}]">
                                    {html_options options=$colors selected=$parts.correction}
                                    </select>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    </table>
                </td>
            </tr>
        {/foreach}
        </table>
        <table>
        <tr><th>File</th><th>Rename</th></td>
        {foreach from=$misfits key='base' item='file'}
            <tr>
                <td>{$file|escape}</td>
                <td><input type="text" name="correct_name[{$file|escape}]"/></td>
            </tr>
        {/foreach}
        </table>
        <input type="submit" name="correct" class="submit" value="  {#s_go#}  " />
        <input type="submit" name="dry" class="submit" value="  dry run  " />
    </form>
</div>
{include file='footer.tpl'}