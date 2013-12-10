  
    <tr id="{$fileval.htmlid}" class="file_tr_{$fileval.htmlid}{if !$fileval.file} last_row{/if}" >
        <td width="40" align="left" class="hell" id="{$fileval.htmlid}_thumb">
            {include file='formfield.file.thumb.tpl' fileinfo=$fileval.fileinfo}
        </td>
        <td style="vertical-align: top;">
            <input name='{$fileval.form_name}[file]' value='{$fileval.file|escape}' id='{$fileval.htmlid}_file' type='hidden' />
            <button type='button' class='btn btn-xs pull-right' id='{$fileval.htmlid}_choose_button'><span class="glyphicon glyphicon-folder-open"></span> &nbsp;{$field.popup_action->get_title()}...</button>
            <label id='{$fileval.htmlid}_file_label' style='display:inline;'>
                {$fileval.file_label|escape}
            </label>
            <input type="text" class="ef legend {if !$fileval.has_legend}empty_legend{/if}" style='{if $fileval.file}display:block;{else}display:none;{/if}' value="{if $fileval.has_legend}{$fileval.legend|escape}{else}{#s_upload_legend#}{/if}" id="{$fileval.htmlid}_legend" name="{$fileval.form_name}[legend]" />
            {if $field.formfield->sup4}
                <input type="text" class="ef legend {if !$fileval.description}empty_legend{/if}" style='{if $fileval.file}display:block;{else}display:none;{/if}' value="{if $fileval.description}{$fileval.description}{else}{$field.formfield->sup4}{/if}" id="{$fileval.htmlid}_description" name="{$fileval.form_name}[description]"
                />                                  
            {/if}
        </td>
        <td width="10" align="center" class="formfield_cell_right">
            {if $field.formfield->multi}
                <input id="{$fileval.htmlid}_weight" type="hidden" class="inputweight" name="{$fileval.form_name}[weight]" value="{$fileval.weight}" />
                <button type='button' class='imagebutton drag' id="{$fileval.htmlid}_move_row" title="{#s_move#}" alt="{#s_move#}" data-toggle="tooltip">
                    <span class="glyphicon glyphicon-move"></span>
                </button>
            {/if}
            <button type='button' class='btn btn-link' style="{if !$fileval.file}display:none;{/if}" id="{$fileval.htmlid}_delete_file" title="{#s_delete#}" data-toggle="tooltip" alt="{#s_delete#}">
    			<span class="glyphicon glyphicon-remove"></span>
    		</button>
        </td>
    </tr>
