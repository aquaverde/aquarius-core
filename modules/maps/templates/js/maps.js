"use strict";

function initmap(config, markers) {
    var makers = []
    var id_count = 0
    var external_added = false
    var icon_types = config.icon_types
    var html_id = config.htmlid

    var bounds = new google.maps.LatLngBounds();
    var editing = false;
    
    var mapOptions = {
        zoom: config.presets.position.zoom,
        center: new google.maps.LatLng(config.presets.position.lat, config.presets.position.lon),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDoubleClickZoom: true
    }

    var map_container = document.getElementById("map_"+config.htmlid, mapOptions)

    var map = new google.maps.Map(map_container, mapOptions)
    
    for (var i = 0; i < markers.length; i++) {
        if(markers[i]["type"] == "point") {
            var id = add_marker(markers[i]["lat"],markers[i]["lng"],markers[i]["kat"]);
            var select = document.getElementById(config.formname+'_'+id+'_kat')
            select.onchange = function() { change_marker_icon(id, this.value) }
        }
        else {
            add_poly(markers[i]["lat"],markers[i]["lng"]);
        }                       
    }
    
    if(markers.length > 0) {
        map.fitBounds(bounds);
    }   
    
    map.addListener("click", function(e) {
        
        var type;
        var id
        
        if (!config.multi) {
            // There can be only one
            id_count = 0;
            while(markers[0]) gmarker.pop().setMap(null);
        }
        
        if(document.getElementById('radio_set_point').checked) {
            id = add_marker(e.latLng.lat(), e.latLng.lng());
            type = "point";
        } else if(document.getElementById('radio_set_poly').checked) {
            id = add_poly(null,null,true);
            type = "poly";
        }
                                                                        
        create_new_instance(id, type);
        show_box(id);
        
        document.getElementById(html_id + "_" + id + "_lat").value = e.latLng.lat();
        document.getElementById(html_id + "_" + id + "_lng").value = e.latLng.lng();
        document.getElementById(html_id + "_" + id + "_type").value = type;
    })
    
    
    function change_marker_icon(id, icon_id) {
        if(markers[id] === undefined) return false; // It's a polyline
        
        // Pick the first icon if the selected on is invalid
        if(icon_id === "" || icon_types[icon_id] === undefined) {
            icon_id = config.marker_types[0].id
        }
        
        markers[id].setIcon(icon_types[icon_id]);
    }
    
    
    function mkIcon(icon) {
        return new google.maps.MarkerImage(
            icon,
            new google.maps.Size(25, 29), // size
            new google.maps.Point(0,0),   // origin
            new google.maps.Point(12, 29) // anchor
        )
    }
    
    function add_marker(lat, lng, kat) {
        var id = id_count++
        var point = new google.maps.LatLng(lat, lng);
        
        var icon = config.icon_types[kat];
        if (!icon) {
            // just take any
            console.log('hola marker '+kat)
            icon = config.icon_types[Object.keys(config.icon_types)[0]]
        }

        var marker = new google.maps.Marker({
            position: point,
            draggable: true, 
            icon: mkIcon(icon), 
            map: map,
            title:' test'
        });
        
        
        marker.addListener("dragend", function() {
            document.getElementById(config.htmlid + "_" + id + "_lat").value = marker.getPosition().lat();
            document.getElementById(config.htmlid + "_" + id + "_lng").value = marker.getPosition().lng();
            editing = false
        });
        
        marker.addListener("click", function() {
            show_box(id)
        });
        
        marker.addListener("mouseover", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "#FFF";
        });
        
        marker.addListener("mouseout", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "none";
        });         
        
        markers[id] = marker;
        bounds.extend(point);
        return id;
    }
    
    function add_poly(lats_str, lngs_str, is_new) {    
        var id = id_count++
        var pts = [];
        
        if(lats_str != null && lngs_str != null) {
            var lats = lats_str.split(",");
            var lngs = lngs_str.split(",");          

            for(var i = 0; i < lats.length; i++) {
                var point = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(lngs[i]));
                pts.push(point);
                bounds.extend(point);
            }
        }           
        
        var opts = {
            strokeColor: presets.polyline.color,
            strokeWeight: presets.polyline.width,
            path: pts
        }
        
        var polyline = new google.maps.Polyline(opts);
        markers[id] = polyline;
        
        
        map.addListener(polyline, "endline", function() {
            var pts = [];
            for (i = 0; i < polyline.getVertexCount(); i++) {
                pts.push(polyline.getVertex(i));
            }
            
            var lats = "";
            var lngs = "";
            for(var i = 0; i < pts.length; i++) {
                lats += pts[i].lat(); 
                lngs += pts[i].lng();
                if(i != pts.length - 1) {
                    lats += ",";
                    lngs += ",";
                }
            }   
            document.getElementById(config.formname + "_" + id + "_lat").value = lats;
            document.getElementById(config.formname + "_" + id + "_lng").value = lngs;
            
            editing = false
        });
        
        map.addListener(polyline, "click", function(point) {
            show_box(id);                                
        });
        
        map.addListener(polyline, "mouseover", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "#FFF";
        });
        
        map.addListener(polyline, "mouseout", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "none";
        });
        
        polyline.setMap(map)
        
        /* CARGO CULT island ahead */
        if(is_new) {
            polyline.enableDrawing();
            polyline.enableEditing();
        }
        
        return id;
    }
    
    function create_new_instance(index, type) {
        var formname = config.formname
        
        /* this was written by one code soldier, takin the DOM and runnin with it
         * fuck it's a pile of DOMb copypasta */
        var parent = document.getElementById('my_map_markers');
                    
        var newdiv = document.createElement('div');
        newdiv.className = "mapmenu_box";
        newdiv.setAttribute('id','mapmenu_box_' + index);
        newdiv.setAttribute('name','map_boxes');
        newdiv.style.cssText = "display:none;";

        //LAT
        var newinput_lat = document.createElement("input");
        newinput_lat.setAttribute("type", "hidden");
        newinput_lat.setAttribute("name", formname + "[" + index + "]" + "[lat]");
        newinput_lat.setAttribute("id", html_id + "_" + index + "_lat");
        newinput_lat.setAttribute("value", "");
        newdiv.appendChild(newinput_lat);
        
        //LNG
        var newinput_lng = document.createElement("input");
        newinput_lng.setAttribute("type", "hidden");
        newinput_lng.setAttribute("name", formname + "[" + index + "]" + "[lng]");
        newinput_lng.setAttribute("id", html_id + "_" + index + "_lng");
        newinput_lng.setAttribute("value", "");
        newdiv.appendChild(newinput_lng);
        
        //TYPE
        var newinput_type = document.createElement("input");
        newinput_type.setAttribute("type", "hidden");
        newinput_type.setAttribute("name", formname + "[" + index + "]" + "[type]");
        newinput_type.setAttribute("id", html_id + "_" + index + "_type");
        newinput_type.setAttribute("value", "point");
        newdiv.appendChild(newinput_type);
        
        var newdiv_search = document.createElement('div');
        newdiv_search.className = "mapmenu_box_search";
            
        //INPUT SEARCH
        var newinput_search = document.createElement("input");
        newinput_search.setAttribute("type","text");
        newinput_search.setAttribute("value",t_search_by_address);
        newinput_search.setAttribute("id", "sba_"+index);
        //newinput_search.setAttribute("onfocus", "clear_sba(" + index + ");");
        newinput_search.onfocus = function(){ // Note: onclick, not onClick
            clear_sba(index);
            return true;
        };
        newdiv_search.appendChild(newinput_search);
        
        var t = document.createTextNode(" ");
        newdiv_search.appendChild(t);
            
        //SEARCH BUTTON
        var newinput_search_button = document.createElement("input");
        newinput_search_button.setAttribute("type","button");
        newinput_search_button.setAttribute("id", "sba_button_"+index);
        newinput_search_button.className = "button";
        newinput_search_button.setAttribute("value",t_search);
        //newinput_search_button.setAttribute("onclick", "searchAddress(" + index + ")");
        newinput_search_button.onclick = function(){ // Note: onclick, not onClick
            searchAddress(index);
            return true;
        };
        newdiv_search.appendChild(newinput_search_button);
            
        newdiv.appendChild(newdiv_search);  
        
        //DELETE BUTTON
        var button_delete_instance = document.createElement("input");
        button_delete_instance.className = "button";
        button_delete_instance.setAttribute("type","button");
        button_delete_instance.setAttribute("name","delete_instance_button_" + index);
        button_delete_instance.setAttribute("id", "delete_instance_button_" + index);
        button_delete_instance.setAttribute("value",t_delete_instance);
        button_delete_instance.onclick = function(){
            delete_instance(index);
            return true;
        };
        newdiv.appendChild(button_delete_instance);         
                    
        //POLY_PARENT
        if(type == 'point' && 1==0) {
            var newdiv_parent = document.createElement('div');
            newdiv_parent.setAttribute('id','mapmenu_box_hasparent_' + index);
            
            var newinput_parent_checkbox = document.createElement("input");
            newinput_parent_checkbox.setAttribute("type","checkbox");
            newinput_parent_checkbox.setAttribute("name",formname + "[" + index + "]" + "[poly_point]");
            newinput_parent_checkbox.setAttribute("id", html_id + "_" + index + "_poly_point");
            newinput_parent_checkbox.setAttribute("value","1");
            newinput_parent_checkbox.className = "checkbox";
            newdiv_parent.appendChild(newinput_parent_checkbox);
            var m_span = document.createElement("span");
            m_span.className = "little";
            var t = document.createTextNode(t_is_on_way);
            m_span.appendChild(t);
            newdiv_parent.appendChild(m_span);
            newdiv.appendChild(newdiv_parent);
        }               
        
        //EDIT POLY
        if(type == 'poly') {
            var newinput_edit_poly_button = document.createElement("input");
            newinput_edit_poly_button.className = "button";
            newinput_edit_poly_button.setAttribute("type","button");
            newinput_edit_poly_button.setAttribute("name","edit_poly_button_" + index);
            newinput_edit_poly_button.setAttribute("id", "edit_poly_button_" + index);
            newinput_edit_poly_button.setAttribute("value",t_edit_poly);
            newinput_edit_poly_button.style.cssText = "float:left;margin-right:5px;";
            newinput_edit_poly_button.onclick = function(){
                edit_poly(index);
                return true;
            };
            newdiv.appendChild(newinput_edit_poly_button);
        }
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        //POINTING
        var p  = document.createElement("p");
        var t = document.createTextNode(t_kategorie);
        var attr = document.createAttribute("style")
        //p.setAttribute('style','min-width:100px;float:left;');
        p.style.cssText = "min-width:100px;float:left;";
        p.appendChild(t);
        newdiv.appendChild(p);  
        var newinput_point = document.createElement("select");
        newinput_point.setAttribute("name", formname + "[" + index + "]" + "[kat]");
        newinput_point.onchange = function() {
            change_marker_icon(index,newinput_point.value);
            return true;
        };
        for(var i = 0; i < config.marker_types.length; i++) {
            var marker = config.marker_types[i]
            var option_point = document.createElement("option");
            option_point.setAttribute("value", marker["value"]);
            option_point.innerHTML = marker["selection_name"];
            newinput_point.appendChild(option_point);
        }
        newdiv.appendChild(newinput_point);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        //TITLE
        var p  = document.createElement("p");
        var t = document.createTextNode(t_titel);
        attr = document.createAttribute("style")
        //p.setAttribute('style','min-width:100px;float:left;');
        p.style.cssText = "min-width:100px;float:left;";
        p.appendChild(t);
        newdiv.appendChild(p);
        var newinput_title = document.createElement("input");
        newinput_title.setAttribute("type", "text");
        newinput_title.setAttribute("name", formname + "[" + index + "]" + "[title]");
        newinput_title.setAttribute("id", html_id + "_" + index + "_title");
        newinput_title.className = "gmap-textfield";
        newinput_title.setAttribute("value", "");
        newdiv.appendChild(newinput_title);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        //BESCHREIBUNG
        var p  = document.createElement("p");
        var t = document.createTextNode(t_beschreibung);
        attr = document.createAttribute("style")
        //p.setAttribute('style','min-width:100px;float:left;');
        p.style.cssText = "min-width:100px;float:left;";
        p.appendChild(t);
        newdiv.appendChild(p);
        var newinput_beschr = document.createElement("textarea");
        newinput_beschr.setAttribute("name", formname + "[" + index + "]" + "[beschr]");
        newinput_beschr.setAttribute("id", html_id + "_" + index + "_beschr");
        newinput_beschr.setAttribute("rows", "8");
        newinput_beschr.setAttribute("cols", "40");
        newdiv.appendChild(newinput_beschr);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);           
        
        //LINK
        var p  = document.createElement("p");
        var t = document.createTextNode(t_link);
        attr = document.createAttribute("style")
        //p.setAttribute('style','min-width:100px;float:left;');
        p.style.cssText = "min-width:100px;float:left;";
        p.appendChild(t);
        newdiv.appendChild(p);
        var newinput_link = document.createElement("input");
        newinput_link.setAttribute("type", "text");
        newinput_link.setAttribute("name", formname + "[" + index + "]" + "[link]");
        newinput_link.setAttribute("id", html_id + "_" + index + "_link");
        newinput_link.className = "gmap-textfield";
        newinput_link.setAttribute("value", "");
        newdiv.appendChild(newinput_link);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);       
        
        parent.appendChild(newdiv);                                 

    }


    function delete_instance(index) {
        var type = document.getElementById(html_id + "_" + index + "_type").value;

        markers[index].setMap(null);
       
        var parent = document.getElementById('my_map_markers');
        var to_delete = document.getElementById('mapmenu_box_' + index);

        parent.removeChild(to_delete);
    }
    
    
    function edit_poly(index) {
        markers[index].enableDrawing();
        markers[index].enableEditing();
        
        editing = true
    }
    

    function show_box(index) {
        var my_duration = 0.3;
        
        for(var i = 0; i < document.getElementsByName('map_boxes').length; i++) {
            var ac_document = document.getElementsByName('map_boxes')[i];
            
            if(ac_document.id == ('mapmenu_box_' + index)) {
                if(ac_document.style.display != 'none') Effect.BlindUp(ac_document, {duration: my_duration});
                else Effect.BlindDown(ac_document, {duration:my_duration}); 
            }
            else {
                Effect.BlindUp(ac_document, { duration: my_duration });
            }                                                   
        }                                       
    }
    
    
    function init_kml() {
        var kml = new GGeoXml(kml_file);
        map.addOverlay(kml);
    }


    /* returns a function that can be called when the map must be resized */
    return function() {
        var center = map.getCenter();
        google.maps.event.trigger(map, 'resize');
        map.setCenter(center);
    }
}
