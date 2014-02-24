{include file='header.tpl'}

<link rel="stylesheet" href="css/nodetree.css" type="text/css" />

{include_javascript file='prototype.js' lib=true}
{include_javascript file='nodetree.js'}

<h1>Sitemap</h1>

<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lg|language_name}</h2></div>

    {strip}
    <form action="{url action0=$lastaction}" id="nodetree" method="post">
        <ul class="nodetree_container" id="nodetree_entry_{$entry.node->id}" style="margin: 15px 0 0 -15px;" data-parent="{$entry.node->id}">
            {include file='nodetree_container.tpl' base=true}
        </ul>
    {/strip}
        &nbsp;
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
    var nodetree = new NodeTree(load_url, move_url)
</script>

{include file='footer.tpl'}
