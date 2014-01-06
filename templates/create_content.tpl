{include file='header.tpl'}

{include_javascript file='prototype.js' lib=true}
{js}{literal}
function nodes_selected(_, selected_nodes) {
    for (id in selected_nodes) {
        $('node_id').value = id
        $('node_title').update(selected_nodes[id])
    }
}
{/literal}{/js}

<h1>{#menu_super_createcontent#}</h1>
<form action="{url url=$url action=$lastaction}" method="post">
<div class="bigbox">
<div class="bigboxtitle"><h2>1. {#createcontent_select_node#}</h2></div>
<div style="margin-top:10px;">

<input type="hidden" id="node_id" name="node_id" value=""/>
<button
    type='button'
    name='node_select' id='node_select'
    class='button'
    onclick='open_attached_popup("{$simpleurl->with_param($node_select_action)}", "parent_select", "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'>
    {$node_select_action->get_title()}
</button>
<div style="margin-top: 3px;" >
    <span id="node_title"></span>
</div>

</div>
<div style="margin-top:10px;">
<input type="checkbox" id="recursive" name="recursive"/>&nbsp;&nbsp;
<label for="recursive" style="display:inline;">{#createcontent_recursive#}</label>
</div>
</div>
<div class="bigbox" style="margin-top:10px;">
<div class="bigboxtitle"><h2>2. {#createcontent_src_target_lang#}</h2></div>
<fieldset style="display:inline;vertical-align:top;margin-right:10px;">
<legend>{#createcontent_src_lang#}</legend>
{foreach from=$languages item="lang"} 
	<div style="margin-top:2px;">
	<input type="radio" style="" id="src_{$lang->lg}" name="src_lang" value="{$lang->lg}" />&nbsp;&nbsp;
	<label for="src_{$lang->lg}" style="display:inline;">{$lang->name}</label>
	</div>
{/foreach}
</fieldset>
<fieldset style="display:inline;vertical-align:top;">
<legend>{#createcontent_target_lang#}</legend>
{foreach from=$languages item="lang"} 
	<div style="margin-top:2px;">
	<input type="checkbox" id="dst_{$lang->lg}" name="target_lang[]" value="{$lang->lg}" />&nbsp;&nbsp;
	<label for="dst_{$lang->lg}" style="display:inline;">{$lang->name}</label>
	</div>
{/foreach}
</fieldset>

<div style="margin-top:10px;">
<input id="ignore" type="radio"    name="merge_strategy" value="ignore_existing" checked="checked">&nbsp;&nbsp;
<label for="ignore" style="display:inline;">{#createcontent_ignore_existing#}</label><br/>
<input id="merge" type="radio"     name="merge_strategy" value="merge">&nbsp;&nbsp;
<label for="merge" style="display:inline;">{#createcontent_merge_content#}</label><br/>
<input id="overwrite" type="radio" name="merge_strategy" value="overwrite">&nbsp;&nbsp;
<label for="overwrite" style="display:inline;">{#createcontent_overwrite_content#}</label><br/>
</div>

</div>

<div class="bigbox" style="margin-top:10px;">
<div class="bigboxtitle"><h2>3. {#menu_super_createcontent#}</h2></div>
{action action="create_content:create:`$lg`"}
<input type="submit" name="{$action}" value="{#menu_super_createcontent#}" class="btn btn-default" />
{/action}
</div>
</form>
{include file='footer.tpl'}