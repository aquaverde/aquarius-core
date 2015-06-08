
{if $field.show_selection}
    <select name="{$field.formname}">
        <option>{#select_pointing#}</option>
    {foreach from=$field.nodelist item="nodeinfo"}
        {strip}
        <option value="{$nodeinfo.node->id}" 
            {if $nodeinfo.node->id == $field.value->id} selected="selected" {/if} 
            {if $nodeinfo.depth|@in_array:$field.disabled_depths}
                disabled="disable" style="color: #aaa;"
            {/if} >
            {foreach from=$nodeinfo.connections item=connection}
                {if $connection eq 'clear'} &nbsp;&nbsp;&nbsp; {/if}
                {if $connection eq 'join'} &#x251C; {/if}
                {if $connection eq 'line'} &#x2502; {/if}
                {if $connection eq 'joinbottom'} &#x2514; {/if}
            {/foreach}
            {$nodeinfo.node->get_contenttitle($content->lg)|strip_tags|truncate:55:"...":true:true}
        </option>
        {/strip}
    {/foreach}
    </select>
{else}
    {include_javascript file='contentedit.pointing.js' lib=true}
    <button
        type='button'
        name=''
        value='{$field.popup_action->get_title()}'
        class='btn btn-default btn-xs pointing_selection'
        data-url='{$simpleurl->with_param($field.popup_action)}'
        data-target='{$field.htmlid}'>
        {$field.popup_action->get_title()}
    </button>

    <div class="dim" id="{$field.htmlid}_titlebox" style="margin-top: 3px; {if !$field.selected_nodes}display: none{/if}">
        {#s_pointings_selected#}:
        <span id="{$field.htmlid}_titles" class="dim">
            {foreach from=$field.selected_nodes item=node name="ptitles"}
                {$node->get_contenttitle()|truncate:30}
                {if !$smarty.foreach.ptitles.last} | {/if}
            {/foreach}
        </span>
    </div>
    <input type="hidden" id="{$field.htmlid}_selected" name="{$field.formname}" value="{$field.selected_ids}" />
{/if}


