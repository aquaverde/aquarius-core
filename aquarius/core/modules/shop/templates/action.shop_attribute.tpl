
{include file='header.tpl'}
{$editcontrolsfunction}


{literal}
<style type="text/css">
.shop_pref_box {
    margin-left: 20px;
    background-color: white;
    border: solid #a0a0a0 0.5pt;
    display: block;
}

.shop_p_label {
    font-weight: bold;
}

.prefRow {
    line-height: 14px;
    padding: 5px;
}

.prefRow input {
    margin-right: 7px;
}

.shop_pref {
    margin-bottom: 5px;
}

</style>
{/literal}

{*include file='path.tpl'*}
<h1>{$shop.node->get_contenttitle()}</h1>
<div id="outer">    
<form style="display: inline" name="shop" method="post" action="{url}"  enctype="multipart/form-data">
    

{listattr childsof=$shop.node prodid=$shop.prodid}
<div class="shop_pref">
<span class="shop_p_label">{$entry->title}:</span> <input type="button" name="" value="{#s_select#}" class="button" onclick="
        var div=document.getElementById('shop_{$entry->node_id}div');
        if (div.style.display == 'none')
            div.style.display='block';
        else
            div.style.display='none';
        return false;"/>
    <div id="shop_{$entry->node_id}div" class="shop_pref_box" style="display:block">
    {listattr childsof=$entry var="children" attrid=$entry->node_id prodid=$shop.prodid}{php}Log::debug($this->get_template_vars('attributes'));{/php}
        {if $entry->multiple}
            <input type="hidden" name="shop[attribute][{$entry->node_id}][{$children->node_id}][set]">
            <div class="prefRow"><input type="checkbox" name="shop[attribute][{$entry->node_id}][{$children->node_id}][set]" value="true"
                {if $attributes.set == true}checked="checked"{/if}>
                <span class="shop_p_label">{$children->title}</span>
                {if $entry->pictures}{include file=formfield.shop_file.tpl entry_id=$entry->node_id children_id=$children->node_id field=$attributes.field}{/if}
                <br />
                {if $entry->changesPrice}Preis: <input type="text" name="shop[attribute][{$entry->node_id}][{$children->node_id}][price]" value="{$attributes.price}">{/if}
                <br />
                {if $entry->hasDescription}{/if}
            </div>
        {else}
            <div class="prefRow"><input type="radio" name="shop[attribute][{$entry->node_id}][{$entry->node_id}][set]" value="{$children->node_id}" {if $attributes.set > 0}checked="checked"{/if}>
                <span class="shop_p_label">{$children->title}</span>
                {if $entry->pictures}{include file=formfield.shop_file.tpl entry_id=$entry->node_id children_id=$children->node_id file=$attributes.file}{/if}
                <br />
                {if $entry->changesPrice}Preis: <input type="text" name="shop[attribute][{$entry->node_id}][{$entry->node_id}][price]" value="{$attributes.price}">{/if}
                <br />
                {if $entry->hasDescription}{/if}
            </div>
        {/if}
    {/listattr}
    </div>
</div>
{/listattr}
{strip}
    {action action=$saveaction}
        <input type="hidden" name="check" value="{$content->node_id}{$content->lg}">
        <input type="submit" name="{$doneaction}" value="{#s_done#}" class="submit" onclick="updateRTEs();"/>
        <input type="submit" name="{$saveaction}" value="{#s_save#}" class="submit" onclick="updateRTEs();"/>
    {/action}
        <input type="submit" class="cancel" name="" value="{#s_cancel#}" class="submit"/>
    </form>
{/strip}
</div> 

{include file='footer.tpl'}



