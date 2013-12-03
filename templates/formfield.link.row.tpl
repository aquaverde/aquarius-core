<tr id="{$field.htmlid}_{$fileval.myindex}">
    <td>
		{if $field.formfield->sup2 != 1}
            <div style="float:right;">
                {#s_link_target#}
                <select style="margin:0; margin-left:3px; " name="{$field.formname3}">
                    <option {if $fileval.target != '_blank'} selected="selected"{/if} value="">{#s_link_target_intern#}</option>
                    <option {if $fileval.target == '_blank'} selected="selected"{/if} value="_blank">{#s_link_target_extern#}</option>
                    <option {if $fileval.target == 'popup'} selected="selected"{/if} value="popup">Popup</option>
                    <option {if $fileval.target == 'email'} selected="selected"{/if} value="email">E-Mail</option>
                </select>
            </div>
        {/if}
        {if $field.formfield->sup3}{$field.formfield->sup3}{else}{#s_link#}{/if}
            <input type="text" class="ef" style="margin:0px 6px 0 3px; width:250px;" name="{$field.formname}" value="{$fileval.link}" {if $field.formfield->multi}onchange="add_link_ajax('{$fileval.myindex}', '{$field.formfield->id}', '{$field.htmlid}')"{/if} />
        {if $field.formfield->sup4}{$field.formfield->sup4}{else}{#s_link_text#}{/if}
        <input type="text" class="ef" style="margin:0 3px; width:{if $field.formfield->sup2 != 1}150{else}200{/if}px;" name="{$field.formname2}" value="{$fileval.text|escape}"/>
    </td>
    {if $field.formfield->multi}
        <td width="30"  align="center">
            <input type="hidden" class="inputweight" style="margin:0" name="{$field.formname4}" value="{$fileval.weight}" id="{$field.htmlid}_{$fileval.myindex}_weight"/>
            <button type='button' class='imagebutton' id="{$field.htmlid}_{$fileval.myindex}_move_row" title="{#s_move#}" alt="{#s_move#}">
                <img src='buttons/drag.gif' />
            </button>
        </td>
    {/if}
</tr>