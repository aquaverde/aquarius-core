<link rel="stylesheet" href="css/nodetree.css" type="text/css" />

<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
{if !$multi}
    <input id="select_none" name="node_selection" type="radio" class="node_select" data-title='' checked="checked" value=''/>
    <label  style="display:inline" for="select_none">
        &nbsp;{#s_pointing_select_none#}
    </label>
{/if}
{strip}
    <div class="nodetree_container nodetree_root" id="nodetree_entry_{$entry.node->id}" data-subtree_action="{url action=$subtree_action}" data-multi="{$multi}" data-selected='{$selected|JSON|escape}' style="margin: 5px 0 0 -15px;">
        {include file=nodes_select_container.tpl}
    </div>
{/strip}
</div>
<button type="submit" data-dismiss="modal" class="btn btn-default pull-right">{#s_close#}</button>

