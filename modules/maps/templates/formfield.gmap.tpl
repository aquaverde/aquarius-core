{include_javascript file=scriptaculous.js lib=true}
{include_javascript file=effects.js lib=true}
{include_javascript file=maps.js defer=true}
{include_css file=maps.css}

<div id="line_length"></div>

<input id="location_search_input" type="text" placeholder="{#location_search#}">
<div class="map" id="map_{$field.htmlid}"></div>

{js}
    var t_search_by_address = "{#search_by_address#}";
    var t_search = "{#search#}";
    var t_point = "{#point#}";
    var t_way = "{#way#}";
    var t_is_on_way = "{#is_on_way#}";
    var t_edit_poly = "{#edit_poly#}";
    var t_titel = "{#titel#}";
    var t_beschreibung = "{#beschreibung#}";
    var t_kategorie = "{#kategorie#}";
    var t_link = "{#link#}";
    var t_delete_instance = "{#delete_instance#}";

    // Maps deferred loading
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.defer = true;
    script.src = "http://maps.googleapis.com/maps/api/js?key={$key}&libraries=drawing,places&sensor=false&callback=initmap_{$field.htmlid}";
    document.body.appendChild(script);
    
    // Callback that loads the map
    var initmap_{$field.htmlid} = function() {ldelim}
        var resfun = initmap({$field.map_options|@json}, {$field.value|@json})
        if (on_tab_init) {ldelim}
            on_tab_init.push(resfun)
            {rdelim}
    {rdelim}
{/js}

<div id="{$field.formname}_markers">
	{foreach from=$field.value item=gmap_data key = index}
		<div class="mapmenu_box" id="mapmenu_box_{$index}" name="map_boxes" style="display:none;">
			<input type="hidden" name="{$field.formname}[{$index}][lat]" value="{$gmap_data.lat|escape}" id="{$field.htmlid}_{$index}_lat" />
			<input type="hidden" name="{$field.formname}[{$index}][lng]" value="{$gmap_data.lng|escape}" id="{$field.htmlid}_{$index}_lng" />
			<input type="hidden" name="{$field.formname}[{$index}][zoom]" value="{$gmap_data.zoom|escape}" id="{$field.htmlid}_{$index}_zoom" />
			<input type="hidden" name="{$field.formname}[{$index}][type]" value="{$gmap_data.type|escape}" id="{$field.htmlid}_{$index}_type" />

			<input type="button" name="delete_instance_button_{$index}" value="{#delete_instance#}" id="{$field.formname}_{$index}_delete"" class="button">

			<br/>
            
            {if $gmap_data.type == 'point'}
			<p style="min-width:100px;float:left;">{#kategorie#}</p>
			<select name="{$field.formname}[{$index}][kat]" id="{$field.formname}_{$index}_kat">
                {foreach from=$field.marker_types item=marker}
                    <option value="{$marker.id}" {if $gmap_data.kat == $marker.id}selected="selected"{/if}>{$marker.selection_name}</option>
                {/foreach}
			</select>
			<br />
			{/if}
			<p style="min-width:100px;float:left;">{#titel#}</p>
			<input type="text" name="{$field.formname}[{$index}][title]" value="{$gmap_data.title|escape}" id="{$field.htmlid}_{$index}_title" class="gmap-textfield" />
			<br />
			<p style="min-width:100px;float:left;">{#beschreibung#}</p>
			<textarea name="{$field.formname}[{$index}][beschr]" id="{$field.htmlid}_{$index}_beschr" rows="8" cols="40">{$gmap_data.beschr|escape}</textarea>

			<br />
			<p style="min-width:100px;float:left;">{#link#}</p>
			<input type="text" name="{$field.formname}[{$index}][link]" value="{$gmap_data.link|escape}" id="{$field.htmlid}_{$index}_link" class="gmap-textfield" />
			<br />
		</div>
	{/foreach}
</div>



