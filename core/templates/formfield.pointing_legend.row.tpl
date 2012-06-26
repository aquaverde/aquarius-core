<tr id="{$field.htmlid}_{$fileval.myindex}" {if $smarty.foreach.pls.last OR $fileval.ajax}class="lastOne"{/if}>
    <td style="width:50px;">
        <button
            type='button'
            name=''
            value='{$field.popup_action->get_title()}'
            class='button'
            onclick='open_attached_popup("{$simpleurl->with_param($field.popup_action)}&amp;selected="+$("{$field.htmlid}_{$fileval.myindex}_selected").value + "&amp;target_id={$fileval.popupid}" , {$fileval.popupid|json}, "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'>{$field.popup_action->get_title()}
        </button>
    </td>
    
    <td>
        <div id="{$field.htmlid}_{$fileval.myindex}_titlebox" style="margin-top: 3px; float:left; {if !$fileval.node}display: none{/if}">
            <span id="{$field.htmlid}_{$fileval.myindex}_titles">
                {if $fileval.node}
                    {$fileval.node->get_contenttitle()|truncate:50}
                {/if}
            </span>
        </div>
        <div style="float:right;">
            <input type="hidden" id="{$field.htmlid}_{$fileval.myindex}_selected" name="{$field.formname}" value="{$fileval.node->id}" />
            Text&nbsp;<input type="text" class="ef" style="margin:0 3px; width:250px;" name="{$field.formname2}" value="{$fileval.legend|escape}"/>
        </div>
    </td>

    <td style="width:19px;">
        
        <button 
            type='button' 
            class='imagebutton' 
            style="{if !$fileval.node}display:none;{/if}" 
            id="{$field.htmlid}_{$fileval.myindex}_delete_button" 
            title="{#s_delete#}" 
            alt="{#s_delete#}" 
            onclick="remove_pointing_legend('{$field.htmlid}_{$fileval.myindex}','{$field.htmlid}');">
			    
			    <img style="padding-left:1px;" src='buttons/delete.gif' />
		
		</button>

	</td>

    <td width="30"  align="center">
        <input type="hidden" class="inputweight" style="margin:0" name="{$field.formname3}" value="{$fileval.weight}" id="{$field.htmlid}_{$fileval.myindex}_weight"/>
        <button type='button' class='imagebutton' id="{$field.htmlid}_{$fileval.myindex}_move_row" title="{#s_move#}" alt="{#s_move#}">
            <img src='buttons/drag.gif' />
        </button>
    </td>
</tr>
