{include file='header.tpl'}

{js}{literal}
    jQuery(function() {
        $("input").labelify({ labelledClass: 'dim' })
    })
{/literal}{/js}

<style>{literal}.table td {padding:2px 4px; font-size:11px;} .ef { font-size:11px; border-color: #ccc;} {/literal}</style>
<h1>Formfields</h1>
    <form action="{url action=$lastaction}" method="post">
        <table width="100%" cellpadding="0"  cellspacing="0" class="table">
            <tr>
                <th></th>
                <th>Name</th>
                <th>Description</th>
            {foreach from=$interesting_fields item=title}
                <th>{$title|escape}</th>
            {/foreach}
            </tr>
            {foreach from=$groups item=group key=id}
	        <tr class="{cycle values="odd,even"}">
                <td>{actionlink action=$group.delete show_title=false}</td>
                <td>{$group.name|escape}&nbsp;({$group.formfields|@count})</td>
                <td><input class="ef" style="width: 220px" value="{$group.description|escape}" name="descr[{$group.hashid}]" title="{if $group.description}{else}{formfield_title f=$group.formfields.0}&nbsp;{/if}"/></td>
            {foreach from=$group.differences item=difference}
                <td>
                {if $difference|@count > 1}
                    {foreach from=$difference key=value item=count}
                        {$value|truncate:30:'…'|escape}&nbsp;({$count})<br/>
                    {/foreach}
                {else}
                    {foreach from=$difference key=value item=count}
                        {$value|truncate:30:'…'|escape}
                    {/foreach}
                {/if}
                </td>
            {/foreach}
            </tr>
            {/foreach}
        </table>
        {include file=select_buttons.tpl}
    </form>
{include file='footer.tpl'}