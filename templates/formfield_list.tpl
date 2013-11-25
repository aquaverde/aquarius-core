{include file='header.tpl'}

{js}{literal}
    jQuery(function() {
        $("input").labelify({ labelledClass: 'dim' })
    })
{/literal}{/js}

<style>{literal}.table td {padding:2px 4px; font-size:12px;} .ef { font-size:12px; border-color: #ccc; width: 200px;} {/literal}</style>
<h1>Formfields</h1>
    <form action="{url action=$lastaction}" method="post">
        <table width="100%" cellpadding="0"  cellspacing="0" class="table table-condensed table-striped">
            <tr>
                <th></th>
                <th>Name</th>
                <th>Description</th>
            {foreach from=$interesting_fields item=title}
                <th>{$title|escape}</th>
            {/foreach}
            </tr>
            {foreach from=$groups item=group key=id}
	        <tr>
                <td>{actionlink action=$group.delete show_title=false}</td>
                <td>{$group.name|escape}&nbsp;({$group.formfields|@count})</td>
                <td><input class="ef" value="{$group.description|escape}" name="descr[{$group.hashid}]" title="{if $group.description}{else}{formfield_title f=$group.formfields.0}&nbsp;{/if}"/></td>
            {foreach from=$group.differences item=difference}
                <td>
                    {foreach from=$difference key=value item=forms}
                        <div title="{", "|join:$forms}" style="cursor:help;">{$value|truncate:30:'…'|escape}{if $difference|@count > 1}&nbsp;({$forms|@count}){/if}</div>
                    {/foreach}
                </td>
            {/foreach}
            </tr>
            {/foreach}
        </table>
        {include file=select_buttons.tpl}
    </form>
{include file='footer.tpl'}