{include file='header.tpl'}
<h1>{#tableexport_export#}</h1>
<div class="bigbox">
<h2>{#tableexport_exported#}</h2>
{action action="tableexport:exportdown:$lg"}
<a href="{url action0=$action}" class="button">{#tableexport_download_csv#}</a>
{/action}
</div>
{include file='footer.tpl'}