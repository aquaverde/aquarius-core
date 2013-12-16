<div id="dynform_block_wrapper">
    <form action="{url action0=$lastaction}" method="post" id="dynform_form" name="dynform_form">
    <div class="">
        <h2>Dynform</h2>
        <table border="0" cellpadding="0" cellspacing="0" class="table" id="boxtable">

            {dynform_load content_id=$content->id}  
            {if !$unassigned_node}
            
                {foreach from=$blocks item=block}
                    <tr>
                        {*// ---- BLOCK DISPLAY ---- *}
                        <td valign="top" width="70%" class="dynform_block_td">
                            <div class="dynform_line_wrapper">
                                <div class="dynform_block_header">
                                    {action action="dynform:editblock:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:0"}
                                        <a href="{url action0=$lastaction action1=$action}">
                                            {dynform_block_name id=$block->id lg=$content->lg}
                                        </a>
                                    {/action}
                                </div>
                                <div class="dynform_block_controls">
                                
                                    {*/* ---- BLOCK CONTROLS ---- */*}
                                    
                                    <div class="dynform-control-icon">
                                    {action action="dynform:editblock:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:0"}
                                        <a href="{url action0=$lastaction action1=$action}"><span class="glyphicon glyphicon-pencil"></span></a>
                                    {/action}
                                    </div>
                                    
                                    <div class="dynform-control-icon">
                                        {actionlink action="dynform:moveblockup::::`$block->id`:"}
                                    </div>
                                    
                                    <div class="dynform-control-icon">
                                        {actionlink action="dynform:moveblockdown::::`$block->id`:"}
                                    </div>
                                    
                                    <div class="dynform-control-icon">
                                    {action action="dynform:deleteblock:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:0"}
                                        <a href="{url action0=$lastaction action1=$action}"><span class="glyphicon glyphicon-remove"></span></a>
                                    {/action}
                                    </div>
                                
                                </div>
                            </div>
                            
                            {dynform_load_block block_id=$block->id}
                            {foreach from=$block_fields item=field}
                                <div class="dynform_line_wrapper">
                                    <div class="dynform_field_desc_wrapper">
                                        <div class="dynform_field_title">
                                            {action action="dynform:editfield:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:`$field->id`"}
                                                <a href="{url action0=$lastaction action1=$action}">{dynform_field_name id=$field->id lg=$content->lg} {if $field->required}*{/if}</a>&nbsp;
                                            {/action}
                                        </div>
                                        <div class="dynform_field_type">
                                            {dynform_fieldtype id=$field->type} 
                                        </div>
                                    </div>
                                    <div class="dynform_block_controls">
                                    
                                        {*/* ---- FIELD CONTROLS ---- */*}
                                        
                                        <div class="dynform-control-icon">
                                        {action action="dynform:editfield:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:`$field->id`"}
                                            <a href="{url action0=$lastaction action1=$action}"><span class="glyphicon glyphicon-pencil"></span></a>
                                        {/action}
                                        </div>
                                        
                                        <div class="dynform-control-icon">
                                        {actionlink action="dynform:movefieldup:::::`$field->id`"}
                                        </div>
                                        
                                        <div class="dynform-control-icon">
                                        {actionlink action="dynform:movefielddown:::::`$field->id`"}
                                        </div>
                                        
                                        <div class="dynform-control-icon">
                                        {action action="dynform:deletefield:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:`$field->id`"}
                                            <a href="{url action0=$lastaction action1=$action}"><span class="glyphicon glyphicon-remove"></span></a>
                                        {/action}
                                        </div>
                                    
                                    </div>
                                </div>
                            {/foreach}
                            
                            <div class="dynform_line_wrapper_no_border">
                                <div class="dynform_field_desc_wrapper">
                                    <form>
                                    <div class="dynform_field_title">
                                        {action action="dynform:addfield:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:0"}
                                            <button type="submit" name="{$action}" value="{#new_field#}" class="btn btn-link"><span class="glyphicon glyphicon-plus"></span> {#new_field#}</button>
                                        {/action}
                                    </div>
                                    <div class="dynform_field_type">
                                        {assign var="blockid" value=$block->id}
                                        {dynform_fieldtypes_popup name="new_fieldtype_$blockid"}&nbsp;
                                    </div>
                                    </div>
                                </div>
                                <div class="dynform_block_controls">&nbsp;</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="dynform_seperator_td">
                            &nbsp;
                        </td>
                    </tr>
                {/foreach}
            
                <tr class="bottom">
                    <td style="height:30px;">
                        {action action="dynform:addnewblock:`$content->id`:`$content->lg`:`$node->id`:0:0"}
                            <button type="submit" name="{$action}" value="{#new_field#}" class="btn btn-link"><span class="glyphicon glyphicon-plus"></span> {#new_block#}</button>
                        {/action}
                    </td>
                </tr>
            
            {/if}
            
        </table>    
    </div>
    </form>
</div>