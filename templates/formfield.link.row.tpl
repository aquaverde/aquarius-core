<tr id="{$field.htmlid}_{$fileval.myindex}">
        <td>
        <label class="pull-left">{if $field.formfield->sup3}{$field.formfield->sup3}{else}{#s_link#}{/if}
            <input type="text" class="form-control" style="margin:0px 6px 0 3px; width:350px;" name="{$field.formname}" value="{$fileval.link}" {if $field.formfield->multi}onchange="add_link_ajax('{$fileval.myindex}', '{$field.formfield->id}', '{$field.htmlid}')"{/if} /></label>
        <label class="pull-left">{if $field.formfield->sup4}{$field.formfield->sup4}{else}{#s_link_text#}{/if}
        <input type="text" class="form-control" style="margin:0 3px; width:{if $field.formfield->sup2 != 1}200{else}350{/if}px;" name="{$field.formname2}" value="{$fileval.text|escape}"/></label>
    </td>
		{if $field.formfield->sup2 != 1}
            <td class="formfield_cell_right">
                <label>{#s_link_target#}<br>
                <select style="" name="{$field.formname3}">
                    <option {if $fileval.target != '_blank'} selected="selected"{/if} value="">{#s_link_target_intern#}</option>
                    <option {if $fileval.target == '_blank'} selected="selected"{/if} value="_blank">{#s_link_target_extern#}</option>
                    <option {if $fileval.target == 'popup'} selected="selected"{/if} value="popup">Popup</option>
                    <option {if $fileval.target == 'email'} selected="selected"{/if} value="email">E-Mail</option>
                </select>
                </label>
            </td>
        {/if}
        {if $field.formfield->multi}
            <td width="10"  align="center" class="formfield_cell_right">
                <input type="hidden" class="inputweight" style="margin:0" name="{$field.formname4}" value="{$fileval.weight}" id="{$field.htmlid}_{$fileval.myindex}_weight"/>
                <button type='button' class='imagebutton' id="{$field.htmlid}_{$fileval.myindex}_move_row" title="{#s_move#}" alt="{#s_move#}">
                    <span class="glyphicon glyphicon-move" title="{#move#}"></span>
                </button>
            </td>
        {/if}
</tr>