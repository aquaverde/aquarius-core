
<div class='contentedit contentedit_rte'> 
	<label for="options" title="text">{#text#}</label>
	<label for="" title=""></label>
<div>
	{*include file='backend_rte.tpl' rte_options=$rte_options RTEformname='field[options]' RTEformvalue=$field->options RTEhtmlID='1'*}
	<textarea class="form-control" name="field[options]" rows="3" cols="80" id="options">{$field->options|escape}</textarea>

</div>
</div>
