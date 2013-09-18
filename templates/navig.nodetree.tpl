
<link rel="stylesheet" href="css/nodetree.css" type="text/css" />


{include_javascript file='prototype.js' lib=true}
{include_javascript file='nodetree.js'}

<form id="leftnav">
    <div class="nodetree_container" id="nodetree_entry_{$tree.node->id}">
        {include file='navig.nodetree_container.tpl'}
    </div>
</form>

{js}
    var request_url = '{url url=$adminurl escape=false action0=$lastaction|default:false action1="nodetree:navig_children:$lg"}'
    var nodetree;

    {literal}
    window.onload = function() {
        window.nodetree = new NodeTree(request_url)
    }
    {/literal}
{/js}