{include file='header.tpl'}
<h1>Move nodes</h1>

{include_javascript file='nodetree.js'}
{include_javascript file='nodes_select.js' lib=true}
{include_javascript file='contentedit.pointing.js' lib=true}
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
        class='button pointing_selection'
        data-url="{$simpleurl->with_param($parent_selection)}"
        data-selected_field="node_target"
        data-target="node_target"
    />

        {#move_copy_target_location#}: <span id='node_target_titles'>{$common_ancestor->get_contenttitle()}</span><br/><br/>
        <input type='hidden' id='node_target' name='node_target' value='{$common_ancestor->id}'/>
        
        {include file="select_buttons.tpl"}
    </div>
</form>
{include file='footer.tpl'}