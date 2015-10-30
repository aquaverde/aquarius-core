{include file="header.tpl"}
    <h1>{$lastaction->get_title()}</h1>
    <ul>
    {foreach $file_paths key=path item=state}
        {if $state=='fs'}<li style="padding-left: 0.3em"><span class="glyphicon glyphicon-unchecked" title="unused: file present but not found in DB"></span> {$path}</li>
        {/if}
        {if $state=='fsdb'}<li style="padding-left: 0.3em; background-color: #CFC"><span class="glyphicon glyphicon-ok-sign" title="used: file present and used"></span> {$path}</li>{/if}
        {if $state=='db'}<li style="padding-left: 0.3em; background-color: #FAA"><span class="glyphicon glyphicon-remove-sign" title="missing: not found but used in DB"></span> {$path}</li>{/if}
    {/foreach}
    </ul>
{include file="footer.tpl"}
