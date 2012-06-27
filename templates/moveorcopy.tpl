{include file='header.tpl'}
<h1>{#s_node#} "{$node->get_contenttitle()}": {#s_move_copy#}</h1>
{include_javascript file='prototype.js' lib=true}
{js}
var original_parent = '{$parent->id}'
{literal}
function nodes_selected(_, selected_nodes) {
    for (new_parent in selected_nodes) {
        set_parent(new_parent, selected_nodes[new_parent], true)
    }
}

function set_parent(new_parent, title, from_all) {
    $('node_target').value = new_parent
    $('node_target_title').update(title)
    $('move_button').disabled = new_parent == original_parent
    if (from_all) {
        var select = $('destination_select')
        var option_present = false
        if (select) {
            for (option_index in select.options) {
                var option = select.options[option_index]
                if (option.value == new_parent) {
                    select.options.selectedIndex = option_index
                    option_present = true
                }
            }
        }
        if (!option_present && select) {
            select.options[0].update('* '+title)
            select.selectedIndex=0
        }
    }

    // Jig a class so that IE renders our changes
    $('node_target_title').toggleClassName('idefix')
}
{/literal}{/js}
<form action="{url}" method="post">
    <div class="bigbox" id="movecopy_target">
    {if $possible_parents || $select_from_all}
        <h2>{#s_moveorcopy_target#}</h2>
        {if $possible_parents}
            <select class="select" onchange="{literal}var sel=this.options[this.selectedIndex]; set_parent(sel.value, sel.title, false){/literal}" id="destination_select">
                <option disabled="disabled"></option>
                {foreach from=$possible_parents item=nodeinfo}{strip}
                    <option value="{$nodeinfo.node->id}"{if !$nodeinfo.is_possible_parent} disabled="disabled"{/if}{if $nodeinfo.node->id == $node->parent_id} selected="selected"{/if} title="{$nodeinfo.node->get_contenttitle()|escape}">
                        {foreach from=$nodeinfo.connections item=connection}
                            {if $connection eq 'clear'} &nbsp;&nbsp;&nbsp; {/if}
                            {if $connection eq 'join'} &#x251C; {/if}
                            {if $connection eq 'line'} &#x2502; {/if}
                            {if $connection eq 'joinbottom'} &#x2514; {/if}
                        {/foreach}
                        {$nodeinfo.node->get_contenttitle()|escape}
                    </option>{/strip}
                {/foreach}
            </select><br/><br/>
        {/if}
        {if $select_from_all}
            <input
                type='button'
                name=''
                value='{#move_copy_select_from_all#}'
                class='button'
                onclick='open_attached_popup("{$simpleurl->with_param($select_from_all)}&selected="+$("node_target").value,0, "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'/><br/><br/>
        {/if}
    {/if}

        <h2>{#move_copy_choose_action#}</h2>
        {#move_copy_selected_page#}: {$node->get_contenttitle()}<br/>
        {#move_copy_target_location#}: <span id='node_target_title'>{$parent->get_contenttitle()}</span><br/><br/>
        <input type='hidden' id='node_target' name='node_target' value='{$parent->id}'/>
        {if $copyaction}<input type="submit" name="{$copyaction}" value="{$copyaction->get_title()}"  class="submit" style="margin:0;" />{/if}
        {if $moveaction}<input type="submit" name="{$moveaction}" value="{$moveaction->get_title()}"  class="submit" style="margin:0;" id='move_button' disabled='disabled'/>{/if}
        <input type="submit" name="" value="{#s_cancel#}"  class="cancel submit" style="margin:0;"  />
    </div>
</form>
{include file='footer.tpl'}