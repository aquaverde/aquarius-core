{include file='header.tpl'}

{include_javascript file='prototype.js' lib=true}
{include_javascript file='module.shorturi.js'}

<h1>{#Shorturi#}</h1><br/>

<form action="{url action=$lastaction}" method="post">

<table class="table" id="uri_table">
    {foreach from=$uris item=uri key=myindex}
        {include file='uritable.row.tpl'}
    {/foreach}
    {include file='uritable.row.tpl' uri=$new_uri myindex=$uris|@count}

    <script type="text/javascript">
    <!--
        uri_index = {$uris|@count};
    // -->
    </script>
    <tr>
        <td colspan="4">
            <button type="submit" name="save_button" class="btn btn-default btn-xs btn-success" onclick="add_row_shorturi();"><span class="glyphicon glyphicon-neg glyphicon-plus-sign white"></span>
{#s_new#}</button>
        </td>
    </tr>

</table>
<button type="submit" name="save_button" class="btn btn-primary">{#save#}</button>
</form>


{if $shorturi_content}
<div style="margin-top: 2em">
    <h2>{#shorturi_in_content#}</h2>
    <table class="table">
    {foreach $shorturi_content as $matches}
        <tr>
            <td>{$matches@key|escape}</td>
            <td>{foreach $matches as $match}{actionlink action=$match.edit title=$match.title}{if !$match@last}, {/if}{/foreach}</td>
        </tr>
    {/foreach}
    </table>
<div>
{/if}


{include file='footer.tpl'}
