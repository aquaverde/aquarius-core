
{literal}
<style type="text/css">
#shop_div {
	margin: 10px 0;
}
.shop_pref_box {
    display: block;
    margin: 6px 0;
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
fieldset {
	padding: 6px 10px;
}
input.button {
	margin-bottom: 12px;
}
</style>
{/literal}

<div id="shop_div" class="shop_pref_box" >
{foreach from=$field.attribute_settings item='attribute_properties'}
    <fieldset>
        <legend>{$attribute_properties.attribute->get_title()}</legend>
        {foreach from=$attribute_properties.properties item='property_settings'}
            <div>
                <input id="shop_property_{$property_settings.property->id}" type="{if $attribute_properties.attribute->get_multiple()}checkbox{else}radio{/if}" name="shop_property_{$attribute_properties.attribute->id}_active[]" value="{$property_settings.property->id}" {if $property_settings.settings->active}checked="checked"{/if}/>
                <label for="shop_property_{$property_settings.property->id}" style="display:inline">{$property_settings.property->get_title()}</label>
            </div>
            {if $attribute_properties.attribute->get_changesPrice()}
                Preis: <input type="text" name="shop_property_{$property_settings.property->id}[price]" value="{$property_settings.settings->price|escape}">
            {/if}
            {if $attribute_properties.attribute->get_pictures()}
                {include file=formfield.shop_file.tpl id="shop_property_`$property_settings.property->id`_file" name="shop_property_`$property_settings.property->id`" value=$property_settings.settings->picture}
            {/if}
            {if $attribute_properties.attribute->get_text()}
                Text: <input type="text" name="shop_property_{$property_settings.property->id}[text]" value="{$property_settings.settings->text|escape}">
            {/if}
        {/foreach}
    </fieldset>
{/foreach}
</div>



