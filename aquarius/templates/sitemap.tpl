{extends main.tpl}

{block name='content'}
<div id="content-wrapper">
	<div id="content">
		
		<h1>{$title2|default:$title} {edit}</h1>	
		
		{$text}
		{$text1}
		
		{menu boxed=true depth=3}
			{if $entry.levelhead}
				<ul class="level{$entry.depth}">
			{/if}
			{if $entry.node}
				<li>
					{if $entry.node->get_contenttitle() != ""}
						<div class="sitemap-item">{link node=$entry.node}{$entry.node->get_contenttitle()|strip_tags}{/link}{edit node=$entry.node}</div>
					{/if}
				</li>
			{/if}
			{if $entry.levelfoot}
				</ul>
			{/if}
		{/menu}	
	</div>	
</div>
{/block}
