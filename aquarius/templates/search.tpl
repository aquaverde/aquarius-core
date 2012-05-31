{extends main.tpl}

{block name='content'}
<div id="content-wrapper">
	<div id="content">
		
		<h1>{$title2|default:$title} {edit}</h1>	
	
		<form action="" method="get" name="searchform" class="form-block">
			<input type="text" name="search" value="{$smarty.request.search|escape}" class=""/>
			<input type="submit" value="{wording search}" class="button"/>&nbsp;
		</form>
	
		{search length=25}
		{if $result.run}
			{if $result.count < 1}
				{wording found nothing}
			{else}
				<div class="search">
				{foreach from=$result.items item=item}
					{link node=$item.node}{$item.node->get_contenttitle()|strip_tags}{/link}{edit node=$item.node}<br/>
					{if $item.content->shorttext}{$item.content->shorttext|strip_tags|truncate:200}<br/>
					{elseif $item.content->text}{$item.content->text|strip_tags|truncate:200}<br/>
					{elseif $item.content->text1}{$item.content->text1|strip_tags|truncate:200}<br/>
					{/if}
					<div style="height:5px"></div>
				{/foreach}
				</div>
			{/if}
			{if $result.next}
				{assign var="search" value=$result.search}
				{assign var="start" value=$result.next}
				<br/><a href="{href node=$node varparams="search,start"}">&gt; {wording more results}</a>
			{/if}
		{/if}
	</div>
</div>
{/block}
