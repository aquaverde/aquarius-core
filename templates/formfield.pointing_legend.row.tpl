<tr id="{$field.htmlid}_{$fileval.myindex}" {if $last OR $fileval.ajax}class="last"{/if} 
    data-formfield="{$field.formfield->id}"
    data-htmlid="{$field.htmlid}"
    data-lg="{$content->lg}"
>
    <td style="width:50px;">
        <button
            type='button'
            name=''
            value='{$field.popup_action->get_title()}'
            class='btn btn-default btn-xs pointing_selection_legend'
            data-url="{$simpleurl->with_param($field.popup_action)}"
            data-target="{$field.htmlid}_{$fileval.myindex}">
            {$field.popup_action->get_title()}
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
            <input type="hidden" id="{$field.htmlid}_{$fileval.myindex}_selected" name="{$field.formname}" value="{if $fileval.node}{$fileval.node->id}{/if}" />
            <input type="text" class="form-control" placeholder="Text"  style="width:250px;" name="{$field.formname2}" value="{$fileval.legend|default:''|escape}"/>
        </div>
    </td>

    <td width="25" align="center">
        
        <button 
            type='button' 
            class='imagebutton delete_pointing_legend' 
            style="{if !$fileval.node}display:none;{/if}" 
            id="{$field.htmlid}_{$fileval.myindex}_delete_button" 
            title="{#s_delete#}" 
            alt="{#s_delete#}">
			    <span class="glyphicon glyphicon-trash"></span>
		</button>

	</td>

    <td width="30"  align="center">
        <button type='button' class='imagebutton' id="{$field.htmlid}_{$fileval.myindex}_move_row" title="{#s_move#}" alt="{#s_move#}">
            <span class="glyphicon glyphicon-move" title="" data-original-title="{#s_move#}"></span>
        </button>
    </td>
</tr>

