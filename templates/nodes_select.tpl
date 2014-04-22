{include file=header.tpl}

<link rel="stylesheet" href="css/nodetree.css" type="text/css" />
<style type="text/css">
    {literal}
    body {
        min-width: 0;
    }
    {/literal}
</style>

{include_javascript file='nodetree.js'}

{js target_id=$target_id callback=$callback}
    var selected = {$selected}
    var xWin = window.dialogArguments;

    {literal}
    function changed_multi_selection(node_id, node_title, is_selected) {
        if (is_selected) {
            selected[node_id] = node_title
        } else {
            selected[node_id] = null
        }

        selected = clean_dict(selected)
        if(xWin) xWin.$callback('$target_id', selected)
        else opener.$callback('$target_id', selected)
    }

    function changed_selection(node_id, node_title) {
        selected  = {}
        if (node_id) selected[node_id] = node_title
        if(xWin) xWin.$callback('$target_id', selected)
        else opener.$callback('$target_id', selected)
        window.close()
    }

    function selected_ids() {
        var ids = []
        for (var node_id in selected) {
            ids.push(node_id)
        }
        return ids.join(',')
    }
    {/literal}
{/js}

<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
{if !$multi}
    <input id="select_none" name="node_selection" type="radio" onclick='changed_selection(null, "")' checked="checked"/>
    <label  style="display:inline" for="select_none">
        &nbsp;{#s_pointing_select_none#}
    </label>
{/if}
{strip}
    <div class="nodetree_container" id="nodetree_entry_{$entry.node->id}" style="margin: 5px 0 0 -15px;">
        {include file=nodes_select_container.tpl}
    </div>
{/strip}
</div>
<input type="submit" name="" value="{#s_close#}" onclick="window.close()" class="btn btn-default pull-right"/>

<script type="text/javascript">
    var root = document.getElementById('nodetree_entry_{$entry.node->id}')
    var nodetree = new NodeTree(root, '{url escape=false action=$subtree_action}', null, function() { return { selected: selected_ids() } })
</script>

{include file=footer.tpl}
