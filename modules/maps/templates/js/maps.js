"use strict";

function initmap(config, markers_in) {
    var markers = [];
    var external_added = false
    var icon_types = config.icon_types
    var html_id = config.htmlid
    var default_icon = config.icon_types[Object.keys(config.icon_types)[0]]
    var bounds = new google.maps.LatLngBounds();
    var fit_bounds = markers_in.length > 0;
    
    var mapOptions = {
        zoom: config.presets.position.zoom,
        center: new google.maps.LatLng(config.presets.position.lat, config.presets.position.lon),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDoubleClickZoom: true
    }

    var map_container = document.getElementById("map_"+config.htmlid)

    var map = new google.maps.Map(map_container, mapOptions)

    /* Add the existing markers */
    for (var i = 0; i < markers_in.length; i++) {
        var m = markers_in[i];
        (function() { // scoping the id for the closures, sorry
            var id = i
            if(m["type"] == "point") {
                add_marker(m["lat"], m["lng"], m["kat"], id);
            } else {
                add_poly(m["lat"], m["lng"], id);
            }
            box_handlers(id)                 
        })()
    }
    
    /* Add a search box */
    var location_search_input = document.getElementById('location_search_input')
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(location_search_input)
    
    var searchBox = new google.maps.places.SearchBox(location_search_input);

    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        
        for (var i = 0, place; place = places[i]; i++) {
            if (config.multi == 0) {
                // There can be only one
                remove_all()
            }
            
            var marker = new google.maps.Marker({
                map: map,
                icon: config.icon_types[Object.keys(config.icon_types)[0]],
                title: place.name,
                position: place.geometry.location
            });

        
            var type = "point";
            var id = markers.length
            markers.push(marker)
            create_new_instance(id, type, place.geometry.location.lat(), place.geometry.location.lng(), place.name)
            show_box(id)
            map.panTo(place.geometry.location)
        }
    })
    
    // Bias location search towards locations that are close to the current area
    google.maps.event.addListener(map, 'bounds_changed', function() {
        var bounds = map.getBounds();
        searchBox.setBounds(bounds);
    })


    var drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: null,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [
                google.maps.drawing.OverlayType.MARKER,
                google.maps.drawing.OverlayType.POLYLINE
            ]
        },
        markerOptions: {
            animation: google.maps.Animation.DROP,
            icon: new google.maps.MarkerImage(default_icon),
            draggable: true,
            flat: true,
            raiseOnDrag: true
        },
        polylineOptions: {
            strokeColor: config.presets.polyline.color,
            strokeWeight: config.presets.polyline.width,
            fillColor: config.presets.polyline.color,
            fillOpacity: 1,
            clickable: false,
            zIndex: 1,
            editable: true
        }
    })
    drawingManager.setMap(map);
    
    
    drawingManager.addListener('markercomplete', function(marker) {
        if (config.multi == 0) {
            // There can be only one
            remove_all()
        }
        
        var type = "point";
        var id = markers.length
        markers.push(marker)
        create_new_instance(id, type, marker.getPosition().lat(), marker.getPosition().lng());
        show_box(id);
    })
    
    
    drawingManager.addListener('polylinecomplete', function(line) {
        if (config.multi == 0) {
            // There can be only one
            remove_all()
        }
        
        var id = markers.length  
        markers[id] = line                                   
        create_new_instance(id, 'poly')
        show_box(id)
        poly_handlers(id, line)
    })
    
    function remove_all() {
        while(markers[0]) markers.pop().setMap(null);
        var m = document.getElementById(config.formname+'_markers')
        while (m.hasChildNodes()) {
            m.removeChild(m.lastChild);
        }
    }
    
    function poly_handlers(id, line) {
        var set_polyvals = function() {
            var lats = []
            var lons = []
            
            line.getPath().forEach(function(pt) {
                lats.push(pt.lat())
                lons.push(pt.lng())
            })
            
            document.getElementById(html_id + "_" + id + "_lat").value = lats.join(',')
            document.getElementById(html_id + "_" + id + "_lng").value = lons.join(',')
        }
        set_polyvals()
        line.addListener("dragend", set_polyvals);
        line.getPath().addListener("insert_at", set_polyvals);
        line.getPath().addListener("remove_at", set_polyvals);
        line.getPath().addListener("set_at", set_polyvals);
    }

    
    function mkIcon(icon) {
        return new google.maps.MarkerImage(
            icon,
            new google.maps.Size(25, 29), // size
            new google.maps.Point(0,0),   // origin
            new google.maps.Point(12, 29) // anchor
        )
    }
    
    function add_marker(lat, lng, kat, id) {
        var point = new google.maps.LatLng(lat, lng);
        
        var icon = config.icon_types[kat];
        if (!icon) {
            icon = default_icon
        }

        var marker = new google.maps.Marker({
            position: point,
            icon: mkIcon(icon), 
            map: map,
            editable: true,
            draggable: true,
            clickable: true,
        });
     
        
        markers[id] = marker;
        bounds.extend(point);
    }
    
    function add_poly(lats_str, lngs_str, id) {
        var pts = [];
        
        if (lats_str && lngs_str) {
            var lats = lats_str.split(",");
            var lngs = lngs_str.split(",");          

            for(var i = 0; i < lats.length; i++) {
                var point = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(lngs[i]));
                pts.push(point);
                bounds.extend(point);
            }
        }

        var opts = {
            strokeColor: config.presets.polyline.color,
            strokeWeight: config.presets.polyline.width,
            path: pts,
            editable: true
        }
        
        var polyline = new google.maps.Polyline(opts);
        markers[id] = polyline;
        
        poly_handlers(id, polyline)

        polyline.setMap(map)
    }


    function create_new_instance(index, type, lat, lng, title) {
        var formname = config.formname
        
        /* this was written by one code soldier, takin the DOM and runnin with it
         * fuck it's a pile of DOMb copypasta */
        var parent = document.getElementById(formname+'_markers');
                    
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
        newinput_lat.setAttribute("value", lat);
        newdiv.appendChild(newinput_lat);
        
        //LNG
        var newinput_lng = document.createElement("input");
        newinput_lng.setAttribute("type", "hidden");
        newinput_lng.setAttribute("name", formname + "[" + index + "]" + "[lng]");
        newinput_lng.setAttribute("id", html_id + "_" + index + "_lng");
        newinput_lng.setAttribute("value", lng);
        newdiv.appendChild(newinput_lng);
        
        //TYPE
        var newinput_type = document.createElement("input");
        newinput_type.setAttribute("type", "hidden");
        newinput_type.setAttribute("name", formname + "[" + index + "]" + "[type]");
        newinput_type.setAttribute("id", html_id + "_" + index + "_type");
        newinput_type.setAttribute("value", type);
        newdiv.appendChild(newinput_type);

        
        //DELETE BUTTON
        var button_delete_instance = document.createElement("input");
        button_delete_instance.className = "button";
        button_delete_instance.setAttribute("type","button");
        button_delete_instance.setAttribute("name","delete_instance_button_" + index);
        button_delete_instance.setAttribute("id", formname + "_" + index + "_delete");
        button_delete_instance.setAttribute("value",t_delete_instance);
        newdiv.appendChild(button_delete_instance);        
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        if (type == 'point') {
            //POINTING
            var p  = document.createElement("p");
            var t = document.createTextNode(t_kategorie);
            var attr = document.createAttribute("style")
            p.style.cssText = "min-width:100px;float:left;";
            p.appendChild(t);
            newdiv.appendChild(p);  
            var newinput_point = document.createElement("select");
            newinput_point.setAttribute("name", formname + "[" + index + "]" + "[kat]")
            newinput_point.setAttribute("id", formname + "_" + index + "_kat")
            for(var i = 0; i < config.marker_types.length; i++) {
                var marker = config.marker_types[i]
                var option_point = document.createElement("option");
                option_point.setAttribute("value", marker["value"]);
                option_point.innerHTML = marker["selection_name"];
                newinput_point.appendChild(option_point);
            }
            newdiv.appendChild(newinput_point);
        }
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        //TITLE
        var p  = document.createElement("p");
        var t = document.createTextNode(t_titel);
        attr = document.createAttribute("style")
        p.style.cssText = "min-width:100px;float:left;";
        p.appendChild(t);
        newdiv.appendChild(p);
        var newinput_title = document.createElement("input");
        newinput_title.setAttribute("type", "text");
        newinput_title.setAttribute("name", formname + "[" + index + "]" + "[title]");
        newinput_title.setAttribute("id", html_id + "_" + index + "_title");
        newinput_title.className = "gmap-textfield";
        newinput_title.setAttribute("value", title == undefined ? '' : title);
        newdiv.appendChild(newinput_title);
        
        var brbr = document.createElement("br");
        newdiv.appendChild(brbr);
        
        //BESCHREIBUNG
        var p  = document.createElement("p");
        var t = document.createTextNode(t_beschreibung);
        attr = document.createAttribute("style")
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
        
        box_handlers(index)

    }

    function box_handlers(id) {
        var marker = markers[id]
        var cat_select = document.getElementById(config.formname+'_'+id+'_kat')
        if (cat_select) cat_select.onchange = function() { 
            var kat = this.value
            var icon = config.icon_types[kat];
            
            if (!icon) {
                // just take any
                icon = config.icon_types[Object.keys(config.icon_types)[0]]
            }
            
            marker.setIcon(icon);
        }
        
        document.getElementById(config.formname+'_'+id+'_delete').onclick = function() {
            marker.setMap(null);
        
            var parent = document.getElementById(config.formname+'_markers');
            var to_delete = document.getElementById('mapmenu_box_' + id);

            parent.removeChild(to_delete);
            return true;
        }   
        
        marker.addListener("click", function(point) {
            show_box(id);                                
        });
        
        marker.addListener("mouseover", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "#FFF";
        });
        
        marker.addListener("mouseout", function() {
            document.getElementById('mapmenu_box_' + id).style.background = "none";
        });
        
        marker.addListener("dragend", function() {
            document.getElementById(config.htmlid + "_" + id + "_lat").value = marker.getPosition().lat()
            document.getElementById(config.htmlid + "_" + id + "_lng").value = marker.getPosition().lng()
        });   
        
    }


    function show_box(index) {
        var my_duration = 0.3;
        var elms = document.getElementsByName('map_boxes')
        for(var i = 0; i < elms.length; i++) {
            var ac_document = elms[i];
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

    
    function resize() {
        if (fit_bounds) {
            fit_bounds = false;
            map.fitBounds(bounds)
            google.maps.event.addListenerOnce(map, 'bounds_changed', function() { if (map.getZoom() > 12) map.setZoom(12); });
        }

        var center = map.getCenter();
        google.maps.event.trigger(map, 'resize');
        map.setCenter(center);
    }
    
    if (fit_bounds) map.fitBounds(bounds)
    return resize;
}
