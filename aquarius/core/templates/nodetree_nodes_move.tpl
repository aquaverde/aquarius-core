{include file='header.tpl'}
<h1>Move nodes</h1>

{include_javascript file='prototype.js' lib=true}
{js}
{literal}
function nodes_selected(_, selected_nodes) {
    for (new_parent in selected_nodes) {
        $('node_target').value = new_parent
        $('node_target_title').update(selected_nodes[new_parent])
    }
}
{/literal}{/js}
<form action="{url}" method="post">
    <div class="bigbox">
    <div class="bigboxtitle"><h2>The following nodes will be moved</h2></div>
    <ul style="margin-top: 10px;">
        {foreach from=$list key=key item=item}
            <li>
                - {$item|escape}
                <input type='hidden' name='list[]' value='{$key|escape}' />
            </li>
        {/foreach}
    </ul>
    <h2>Move nodes to</h2>

    <input
        type='button'
        name=''
        value='{#move_copy_select_from_all#}'
        class='button'
        onclick='open_attached_popup("{$simpleurl->with_param($parent_selection)}&selected="+$("node_target").value,0, "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'/>

        {#move_copy_target_location#}: <span id='node_target_title'>{$common_ancestor->get_contenttitle()}</span><br/><br/>
        <input type='hidden' id='node_target' name='node_target' value='{$common_ancestor->id}'/>
        
        {include file="select_buttons.tpl"}
    </div>
</form>
{include file='footer.tpl'}