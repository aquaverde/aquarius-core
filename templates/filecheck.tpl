{include file="header.tpl"}
    <h1>{$lastaction->get_title()}</h1>
    <ul>
    {foreach $file_paths key=path item=state}
        {if $state=='fs'  }<li style=""><span class="glyphicon glyphicon-question-sign"></span> {$path}</li>{/if}
        {if $state=='fsdb'}<li style="background-color: #AFA"><span class="glyphicon glyphicon-ok-sign"></span> {$path}</li>{/if}
        {if $state=='db'  }<li style="background-color: #FAA"><span class="glyphicon glyphicon-remove-sign"></span> {$path}</li>{/if}
    {/foreach}
    </ul>
{include file="footer.tpl"}