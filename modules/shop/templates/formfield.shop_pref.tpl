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
{list childsof=$shop.node}
<div class="shop_pref">
<span class="shop_p_label">{$entry->title}:</span> <input type="button" name="" value="{#s_select#}" class="button" onclick="
        var div=document.getElementById('shop_{$entry->id}div');
        if (div.style.display == 'none')
            div.style.display='block';
        else
            div.style.display='none';
        return false;"/>
<div id="shop_{$entry->id}div" class="shop_pref_box" style="display:none">
{list childsof=$entry var="children"}
    {if $entry->multiple}
        <div class="prefRow"><input type="checkbox" name="shop[attribute][{$entry->id}][{$children->id}]"><span class="shop_p_label">{$children->title}</span>{if $entry->pictures}{include file=formfield.shop_file.tpl entry_id=$entry->id children_id=$children->id}{/if}</div>
    {else}
        <div class="prefRow"><input type="radio" name="shop[attribute][{$entry->id}][{$children->id}]"><span class="shop_p_label">{$children->title}</span>{if $entry->pictures}{include file=formfield.shop_file.tpl entry_id=$entry->id children_id=$children->id}{/if}</div>
    {/if}
{/list}
</div>
</div>
{/list}