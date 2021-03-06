{include file='header.tpl'}

<link rel="stylesheet" href="css/nodetree.css" type="text/css" />

{include_javascript file='nodetree.js'}

<h1>{#s_sitemap#}</h1>

<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lg|language_name}</h2></div>

    {strip}
    <form action="{url action0=$lastaction}" id="nodetree" method="post">
        <div class="nodetree_container" id="nodetree_entry_{$entry.node->id}" data-parent="{$entry.node->id}">
            {include file='nodetree_container.tpl' base=true}
        </div>
    {/strip}

        <div class="action pull-right">
            <select name="command">
            {foreach from=$forallaction->commands() key=command item=text}
                <option value="{$command}">{$smarty.config.$text}</option>
            {/foreach}
            </select>&nbsp;
            <button type="submit" name="{$forallaction}" class="btn btn-default btn-xs">OK</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    var load_url = '{url escape=false action0=$lastaction action1="nodetree:children:$lg:`$lastaction->section`"}'
    var move_url = '{url escape=false action0=$lastaction action1="node:moveorder"}'
    var root = document.getElementById('nodetree_entry_{$entry.node->id}')
    var nodetree = new NodeTree(root, load_url, move_url)
</script>

{include file='footer.tpl'}
