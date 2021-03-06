{include_javascript file="maps.js"}
{include_css file="maps.css"}

<div id="line_length"></div>

<input class="location_search" id="location_search_input" type="text" placeholder="{#location_search#}">
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

    if (!window.mapinits) {
        window.mapinits = []
        
        window.mapinit = function() {
            var initfun;
            while(initfun = mapinits.pop()) {
                initfun()
            }
        }
        
        // Maps deferred loading
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.defer = true;
        script.src = "https://maps.googleapis.com/maps/api/js?key={$field.map_options.presets->api_key}&libraries=drawing,places&sensor=false&callback=mapinit";
        document.body.appendChild(script);
    }

    
    window.mapinits.push(function() {
        var resfun = initmap({$field.map_options|@json}, {$field.value|@json})
        if (window.on_tab_init) {
            on_tab_init.push(resfun)
        } else {
            resfun()
        }
    })
    
{/js}
<div id="{$field.formname}_markers">
    {foreach from=$field.value item=gmap_data key = index}
        <div class="mapmenu_box" id="mapmenu_box_{$index}" name="map_boxes" style="display:none;">
            <input type="hidden" name="{$field.formname}[{$index}][lat]" value="{$gmap_data.lat|escape}" id="{$field.htmlid}_{$index}_lat" />
            <input type="hidden" name="{$field.formname}[{$index}][lng]" value="{$gmap_data.lng|escape}" id="{$field.htmlid}_{$index}_lng" />
            <input type="hidden" name="{$field.formname}[{$index}][type]" value="{$gmap_data.type|escape}" id="{$field.htmlid}_{$index}_type" />

            <button name="delete_instance_button_{$index}" value="{#delete_instance#}" id="{$field.formname}_{$index}_delete"" class="btn btn-link"><span class="glyphicon glyphicon-trash"></span></button>

            {if $gmap_data.type == 'point'}
            <label>
                    {#kategorie#}<br>
                <select name="{$field.formname}[{$index}][kat]" id="{$field.formname}_{$index}_kat">
                    {foreach from=$field.marker_types item=marker}
                        <option value="{$marker.id}" {if $gmap_data.kat|default:'' == $marker.id}selected="selected"{/if}>{$marker.selection_name}</option>
                    {/foreach}
                </select>
            </label>
            {/if}
            
            <label>
                {#titel#}<br>
                <input type="text" name="{$field.formname}[{$index}][title]" value="{$gmap_data.title|escape}" id="{$field.htmlid}_{$index}_title" class="form-control" />
            </label>
            
            <label>
                {#beschreibung#}<br>
                <textarea name="{$field.formname}[{$index}][beschr]" id="{$field.htmlid}_{$index}_beschr" class="form-control">{$gmap_data.beschr|escape}</textarea>
                </label>

            <label>
                    {#link#}<br>
                    <input type="text" name="{$field.formname}[{$index}][link]" value="{$gmap_data.link|escape}" id="{$field.htmlid}_{$index}_link" class="form-control" />
            </label>
        </div>
	{/foreach}
</div>



