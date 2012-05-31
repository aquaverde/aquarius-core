{include_javascript file=scriptaculous.js lib=true}
{include_javascript file=effects.js lib=true}

<div id="line_length"></div>

<div id="map"></div>
{get_pointing_array node='gmapc'}
{load_icon_types node='gmapc'}

{js}
	var gmap_data = {$field.value|@json};
	var multi = {$field.formfield->multi};
	var html_id = "{$field.htmlid}";
	var formname = "{$field.formname}";	
	var pointing_json = {$my_pointing|@json};
	
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
	
	var d_lat = {$field.lat};
	var d_lng = {$field.lon};
	var d_zoom = {$field.zoom};
	var d_icontype = "{php}echo MAP_DEFAULT_ICONTYPE;{/php}";
	
	var d_poly_color = "{php}echo POLY_COLOR;{/php}";
	var d_poly_width = "{php}echo POLY_WIDTH;{/php}";
	var d_poly_trans = "{php}echo POLY_TRANS;{/php}";
	
	var icontypes = new Array();
	{foreach from=$my_icons item=my_icon key=icon_id}
		icontypes[{$icon_id}] = "{$my_icon}";
	{/foreach}
{/js}

<div id="my_map_markers">
	<div class="mapmenu_box">
		<div style="float:left;">
		    {#add#}:
			<input type="radio" name="radios_type" value="1" id="radio_set_point" checked="checked" />&nbsp;{#point#}&nbsp;
			<input type="radio" name="radios_type" value="1" id="radio_set_poly" />&nbsp;{#way#}
		</div>
		
		<div class="mapmenu_box_search">
			<input type="text" id="sba_general" value="{#search_by_address#}" onfocus="clear_sba('general');" />
			<input type="button" id="sba_button_general" onclick = "searchAddress('general');" value="{#search#}" class="button" />
		</div>
	</div>
	{foreach from=$field.value item=gmap_data key = index}
		<div class="mapmenu_box" id="mapmenu_box_{$index}" name="map_boxes" style="display:none;">
			<input type="hidden" name="{$field.formname}[{$index}][lat]" value="{$gmap_data.lat|escape}" id="{$field.htmlid}_{$index}_lat" />
			<input type="hidden" name="{$field.formname}[{$index}][lng]" value="{$gmap_data.lng|escape}" id="{$field.htmlid}_{$index}_lng" />
			<input type="hidden" name="{$field.formname}[{$index}][zoom]" value="{$gmap_data.zoom|escape}" id="{$field.htmlid}_{$index}_zoom" />
			<input type="hidden" name="{$field.formname}[{$index}][type]" value="{$gmap_data.type|escape}" id="{$field.htmlid}_{$index}_type" />

		    <div class="mapmenu_box_search">
				<input type="text" id="sba_{$index}" value="{#search_by_address#}" onfocus="clear_sba({$index});" />
				<input type="button" id="sba_button_{$index}" onclick = 'searchAddress({$index});' value="{#search#}" class="button" /><br />
		    </div>	   		

			<input type="button" name="delete_instance_button_{$index}" value="{#delete_instance#}" id="delete_instance_button_{$index}" onclick="delete_instance({$index});" class="button">


			{if $gmap_data.type != 'poly' && 1 == 0}
			<div id="mapmenu_box_hasparent_{$index}">
				<input type="checkbox" name="{$field.formname}[{$index}][poly_point]" value="1" id="{$field.htmlid}_{$index}_poly_point" class="checkbox" 
				{if $gmap_data.poly_point == '1'}
					checked="checked"
				{/if}
				/>&nbsp;<span class="little">{#is_on_way#}</span>
			</div>
			{/if}

			{if $gmap_data.type == 'poly'}
				<div id="edit_poly_{$index}" style="float:left;margin-right:5px;">
					<input type="button" name="edit_poly_button_{$index}" value="{#edit_poly#}" id="edit_poly_button_{$index}" onclick="edit_poly({$index});" class="button" />
				</div>
			{/if}
            
            <br/><br/>
            
			<p style="min-width:100px;float:left;">{#kategorie#}</p>{select_pointings node='gmapc' name=`$field.formname`[$index][kat] selected=$gmap_data.kat gmap_selecter=true marker_id=$index}
			<br />	
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

<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={php}echo MAP_KEY;{/php}"> </script>
{include_javascript file=gmap_marker.js}
<script type="text/javascript">
	init_map()
	
	{if $content->kml_file}
		var kml_file = '{$smarty.const.PROJECT_URL}{$content->kml_file.file}';
		init_kml();
    {/if}
	
	if (on_tab_init)
        on_tab_init.push(check_resize)
</script>

