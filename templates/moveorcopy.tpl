{include file='header.tpl'}
<h1>{#s_node#} "{$node->get_contenttitle()}": {#s_move_copy#}</h1>

{include_javascript file='contentedit.pointing.js' lib=true}
<script>
var original_parent = '{$parent->id}'

jQuery(function() {
    jQuery('.parent_select').click(function() {
        pointing_selection_setup(this, function(target_id, selected) {
            for (new_parent in selected) {
                set_parent(new_parent, selected[new_parent], true);
            }
        });
    });
});

function set_parent(new_parent, title, from_all) {
    jQuery('#move_button').attr('disabled', new_parent == original_parent);
    if (from_all) {
        var select = document.getElemmentById('destination_select');
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
}
</script>

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
                class='btn btn-default parent_select'
                data-url="{$simpleurl->with_param($select_from_all)}"
                data-selected_field="node_target"
                data-target="node_target"
            >
            <br/><br/>
        {/if}
    {/if}

        <h2>{#move_copy_choose_action#}</h2>
        {#move_copy_selected_page#}: {$node->get_contenttitle()}<br/>
        {#move_copy_target_location#}: <span id='node_target_titles'>{$parent->get_contenttitle()}</span><br/><br/>
        <input type='hidden' id='node_target' name='node_target' value='{$parent->id}'/>
        {if $copyaction}<input type="submit" name="{$copyaction}" value="{$copyaction->get_title()}"  class="btn btn-default" />{/if}
        {if $moveaction}<input type="submit" name="{$moveaction}" value="{$moveaction->get_title()}"  class="btn btn-default" id='move_button' disabled='disabled'/>{/if}
        <input type="submit" name="" value="{#s_cancel#}"  class="btn btn-default"  />
    </div>
</form>
{include file='footer.tpl'}