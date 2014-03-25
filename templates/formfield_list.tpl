{include file='header.tpl'}

{js}{literal}
    jQuery(function() {
        $("input").labelify({ labelledClass: 'dim' })
    })
{/literal}{/js}

<style>
    {literal}
    .form-control { width: 200px;} 
    .wrapper { border: none;}
    {/literal}</style>
    <h1>Fields</h1>
    <form action="{url action=$lastaction}" method="post">
        <table width="100%" cellpadding="0"  cellspacing="0" class="table table-hover">
            <tr>
                <th>Name</th>
                <th>Description</th>
            {foreach from=$interesting_fields item=title}
                <th>{$title|escape}</th>
            {/foreach}
                <th>Delete</th>
            </tr>
            {foreach from=$groups item=group key=id}
	        <tr>
                <td>{$group.name|escape}&nbsp;({$group.formfields|@count})</td>
                <td><input class="form-control" value="{$group.description|escape}" name="descr[{$group.hashid}]" title="{if $group.description}{else}{formfield_title f=$group.formfields.0}&nbsp;{/if}"/></td>
            {foreach from=$group.differences item=difference}
                <td>
                    {foreach from=$difference key=value item=forms}
                        <div title="{", "|join:$forms}" style="cursor:help;">{$value|truncate:30:'…'|escape}{if $difference|@count > 1}&nbsp;({$forms|@count}){/if}</div>
                    {/foreach}
                </td>
            {/foreach}
                <td>
                    {action action=$group.delete}
                       <button name="{$action}" class="btn btn-sm btn-link"><span class="glyphicon glyphicon-trash"></span></button>
                    {/action}
                </td>
            </tr>
            {/foreach}
        </table>
        {include file=select_buttons.tpl}
    </form>
{include file='footer.tpl'}