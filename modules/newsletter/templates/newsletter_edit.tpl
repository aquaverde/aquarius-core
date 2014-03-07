{include file='header.tpl'}
{include_javascript file='ajax.js'}
<h1>{#newsletter_edit#}</h1>
{* newsletter sitemap *}
<script>
    {if $open_nodes}
        node_ids =  new Array({$open_nodes|@implode:","});
    {else}
        node_ids = new Array();
    {/if}
</script>
{if $nodelist}
    {assign var='title' value=#s_newsletter#}
	{include file='list_nodes.tpl'}
{/if}
{include file='footer.tpl'}
<script>
admin = false;
</script>
{include_javascript file='ajax_reload_sitemap.js'}
