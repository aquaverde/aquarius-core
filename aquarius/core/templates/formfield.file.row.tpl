{strip}    
    <tr id="{$fileval.htmlid}" class="file_tr_{$fileval.htmlid}{if !$fileval.file} last_row{/if}" >
        <td width="40" align="center" class="hell" id="{$fileval.htmlid}_thumb">
            {if !$fileval.file}
                <button type='button' class='imagebutton' id='{$fileval.htmlid}_choose_button_th' title="{#s_choose#}" alt="{$field.popup_action->get_title()}" style=""><img src='buttons/browse_file.gif'/></button>
            {/if}
            {include file='formfield.file.thumb.tpl' fileinfo=$fileval.fileinfo}
        </td>
        <td style="vertical-align: top;">

            <input name='{$fileval.form_name}[file]' value='{$fileval.file|escape}' id='{$fileval.htmlid}_file' type='hidden' />

                <button type='button' class='imagebutton' id='{$fileval.htmlid}_choose_button' title="{#s_choose#}" alt="{$field.popup_action->get_title()}" style="text-align:right;float:right; margin-left:5px;"><img src='buttons/browse_file.gif'/></button>
                
                <label for='{$fileval.htmlid}_choose_button' id='{$fileval.htmlid}_file_choose_label' style='text-align:right; float:right;'>
                    {#s_select_file#}
                </label>
                
                <label id='{$fileval.htmlid}_file_label' style='display:inline;'
                >
                    {$fileval.file_label|escape}
                </label>
                
                <input type="text" class="ef legend {if !$fileval.has_legend}empty_legend{/if}" style='margin-top:5px;{if $fileval.file}display:block;{else}display:none;{/if}' value="{if $fileval.has_legend}{$fileval.legend|escape}{else}{#s_upload_legend#}{/if}" id="{$fileval.htmlid}_legend" name="{$fileval.form_name}[legend]"
                />
                
                {if $field.formfield->sup4}
                    <input type="text" class="ef legend {if !$fileval.description}empty_legend{/if}" style='margin-top:5px;{if $fileval.file}display:block;{else}display:none;{/if}' value="{if $fileval.description}{$fileval.description}{else}{$field.formfield->sup4}{/if}" id="{$fileval.htmlid}_description" name="{$fileval.form_name}[description]"
                    />                                  
                {/if}
        </td>
    
        <td width="10" align="center" style="vertical-align:top">
            {if $field.formfield->multi}
            <input id="{$fileval.htmlid}_weight" type="hidden" class="inputweight" name="{$fileval.form_name}[weight]" value="{$fileval.weight}" />
            
            <button type='button' class='imagebutton drag' id="{$fileval.htmlid}_move_row" title="{#s_move#}" alt="{#s_move#}">
    			<img src='buttons/drag.gif' />
            </button>
            {/if}
            
            <button type='button' class='imagebutton' style="padding-top:5px;{if !$fileval.file}display:none;{/if}" id="{$fileval.htmlid}_delete_file" title="{#s_delete#}" alt="{#s_delete#}">
    			<img src='buttons/delete.gif' />
    		</button>
    		
        </td>

    </tr>
{/strip}