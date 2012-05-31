{include file='header.tpl'}
<h1>Dangling references</h1>
    <div class="bigbox">
        <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
    
        <form action="{url action=$lastaction}" method="post">
        <dl>
        {foreach from=$dangling key=refstr item=dangling_list}
            <dt>{$refstr}</dt>
            <dd>
                <table>
            {foreach from=$dangling_list.dangling item=entry name=dangling_entries}
                {if $smarty.foreach.dangling_entries.first}
                    <tr>
                    {foreach from=$entry key=field item=value}
                        <th>{$field}</th>
                    {/foreach}
                    </tr>
                {/if}
                    <tr>
                {foreach from=$entry key=field item=value}
                        <td>{$value}</td>
                {/foreach}
                    </tr>
            {/foreach}
                </table>
            {action action="checkdb:clean:$refstr"}
                <input type="submit" name="{$action}" value="{$dangling_list.spec}" class="submit"/>
            {/action}
            </dd>
        </dl>
        {foreachelse}
            <p>No dangling references found.</p>
        {/foreach}
        
        {action action="checkdb:clean:all"}
        <input type="submit" name="{$action}" value="Clean All" class="submit"/>
        {/action}
        </form>
    
    </div>

    <form action="{url}" method="post"><input type="submit" name="" value="Back" class="submit" /></form>


{include file='footer.tpl'}