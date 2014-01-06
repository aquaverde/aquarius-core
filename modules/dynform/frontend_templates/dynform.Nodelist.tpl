<table width="100%" border="0" cellspacing="0" cellpadding="0">
    {foreach from=$field.nodetree.children item=ngroup}
    <tr>
        <td colspan="2" class="formLabel">
            <table class="currency" width="100%" border="0" cellspacing="0" cellpadding="0">
            	
            	<tr class="heading">
                	<td class="article">{usecontent node=$ngroup.node}{$title}{/usecontent}</td>
                	<td class="ancestry">&nbsp;</td>
                	<td class="volume">{wording Inhalt}</td>
                	<td class="year">{wording Jahrgang}</td>
                	<td class="price">{wording Preis}</td>
                	<td class="amount">{wording Anzahl}</td>
                </tr>
                {foreach from=$ngroup.children item=nitem}
                    <tr>
                    {usecontent node=$nitem.node}
                        <td>{$form_name|default:$title|escape}</td>
                        <td>{$form_note|escape}</td>
                        <td>{$form_content|escape}</td>
                        <td>{$form_vintage|escape}</td>
                        <td>{$form_price|escape}</td>
                        <td>
                            <input type="text" size="5" maxlength="5" name='{$field.id}[{$nitem.node->id}]' value='' style="width: 5em"/>
                        </td>
                    {/usecontent}
                    </tr>
                {/foreach}
            </table>
        </td>
    </tr>
    {/foreach}
</table>