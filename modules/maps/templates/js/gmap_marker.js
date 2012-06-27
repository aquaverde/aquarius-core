//<![CDATA[
    if (GBrowserIsCompatible()) {
        // Array for decoding the failure codes
        var reasons=[];
        reasons[G_GEO_SUCCESS]            = "Success";
        reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address: The address was either missing or had no value.";
        reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address:  No corresponding geographic location could be found for the specified address.";
        reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address:  The geocode for the given address cannot be returned due to legal or contractual reasons.";
        reasons[G_GEO_BAD_KEY]            = "Bad Key: The API key is either invalid or does not match the domain for which it was given";
        reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Queries: The daily geocoding quota for this site has been exceeded.";
        reasons[G_GEO_SERVER_ERROR]       = "Server error: The geocoding request could not be successfully processed.";
        reasons[403]                      = "Error 403: Probably an incorrect error caused by a bug in the handling of invalid JSON.";
        
        // variabs
        
        var gmarker = [];
        var polys = [];
        var id_count = 0;
        var external_added = false;
        
        //var pts = [];
        var bounds = new GLatLngBounds();
         
        var single = false;             

        // ICON
        var small_icon = new GIcon();
        small_icon.iconSize = new GSize(25,29);
        small_icon.iconAnchor=new GPoint(12,29);
        small_icon.infoWindowAnchor = new GPoint(14,2);
        
        //map
        var map = new GMap2(document.getElementById("map"));
            map.addControl(new GLargeMapControl3D());
            map.addControl(new GMapTypeControl());
            map.setCenter(new GLatLng(d_lat,d_lng), parseInt(d_zoom));
            map.disableDoubleClickZoom();
        
        function check_resize() 
        {
            // HACK: Check whether box size changed (changes between display: none and display: block)
            map.checkResize();
            
            if(gmap_data.length > 0) {
                map.setZoom(map.getBoundsZoomLevel(bounds));
                map.setCenter(bounds.getCenter());
            }
            else map.setCenter(new GLatLng(d_lat,d_lng), parseInt(d_zoom));
        }
        
        function change_marker_icon(id, icon_id) {
            //POLYLINE
            if(gmarker[id] === undefined) return false;
            
            if(icon_id === "" || icontypes[icon_id] === undefined) gmarker[id].setImage("/pictures/gmap_icons/" + d_icontype);
            else gmarker[id].setImage(icontypes[icon_id]);
        }
        
        function getMarker(icontype) {
            return new GIcon(small_icon,"/pictures/gmap_icons/"+icontype);
        }
        
        function add_marker(lat,lng,id) {
            add_marker(lat,lng,id,null);
        }
                        
        function add_marker(lat,lng,id,kat) {
            var point = new GLatLng(lat,lng);
            
            if(kat == null) var marker = new GMarker(point,{draggable: true, icon: getMarker(d_icontype)});
            else var marker = new GMarker(point,{draggable: true, icon: new GIcon(small_icon, icontypes[kat])});
            
            marker.marker_id = id;
            
            GEvent.addListener(marker, "dragend", function() {
                document.getElementById(html_id + "_" + marker.marker_id + "_lat").value = marker.getLatLng().lat();
                document.getElementById(html_id + "_" + marker.marker_id + "_lng").value = marker.getLatLng().lng();
            });
            
            GEvent.addListener(marker, "click", function() {
                show_box(marker.marker_id);
            });
            
            GEvent.addListener(marker, "mouseover", function() {
                document.getElementById('mapmenu_box_' + marker.marker_id).style.background = "#FFF";
            });
            
            GEvent.addListener(marker, "mouseout", function() {
                document.getElementById('mapmenu_box_' + marker.marker_id).style.background = "none";
            });         
            
            gmarker[id] = marker;
            bounds.extend(point);
            
            map.addOverlay(gmarker[id]);
            
            id_count++;
        }
        
        function add_poly(lats,lngs,id) {
            add_poly(lats,lngs,id,false);
        }
        
        function add_poly(lats,lngs,id,newp) {                                              
            var my_pts = [];
            
            if(lats != null && lngs != null) {
                var my_lats = lats.split(",");
                var my_lngs = lngs.split(",");          

                for(var i = 0; i < my_lats.length; i++) {
                    var point = new GLatLng(parseFloat(my_lats[i]),parseFloat(my_lngs[i]));
                    my_pts.push(point);
                    bounds.extend(point);
                }
            }           
            
            var polyline = new GPolyline(my_pts,d_poly_color,d_poly_width,d_poly_trans);            
            polyline.polyline_id = id;
            polyline.marker_id = id;
            poly_listener(polyline,id);
            polys[id] = polyline;
            
            map.addOverlay(polyline);
            
            id_count++;
            if(newp) {
                polyline.enableDrawing();
                polyline.enableEditing();
                
                clear_map_point_listener();
            }
        }
        
        function single_point_listener() {
            GEvent.addListener(map, "click", function(marker, point) {
                if(marker) return;
                
                else {
                    map.clearOverlays();
                    
                    var type;
                    
                    if(document.getElementById('radio_set_point').checked) 
                    {
                        add_marker(point.lat(),point.lng(),0);
                        type = "point";
                    }
                    else if(document.getElementById('radio_set_poly').checked) 
                    {
                        add_poly(null,null,0,true);
                        type = "poly";
                    }                   

                    document.getElementById(html_id + "_0_lat").value = point.lat();
                    document.getElementById(html_id + "_0_lng").value = point.lng();
                    document.getElementById(html_id + "_0_type").value = type;
                }
            });
        }
        
        function multi_point_listener() {
            GEvent.addListener(map, "click", function(marker, point) {
                if(marker) return;
                
                var type;

                if(document.getElementById('radio_set_point').checked) 
                {
                    add_marker(point.lat(),point.lng(),id_count);
                    type = "point";
                }
                else if(document.getElementById('radio_set_poly').checked) 
                {
                    add_poly(null,null,id_count,true);
                    type = "poly";
                }
                
                var index_hack = id_count - 1;                                                                  
                create_new_instance(index_hack, type);
                show_box(index_hack);
                
                document.getElementById(html_id + "_" + index_hack + "_lat").value = point.lat();
                document.getElementById(html_id + "_" + index_hack + "_lng").value = point.lng();
                document.getElementById(html_id + "_" + index_hack + "_type").value = type;
                
            });
        }
        
        function create_new_instance(index, type) {
            var parent = document.getElementById('my_map_markers');
                        
            var newdiv = document.createElement('div');
            newdiv.className = "mapmenu_box";
            newdiv.setAttribute('id','mapmenu_box_' + index);
            newdiv.setAttribute('name','map_boxes');
            //newdiv.setAttribute('style','display:none;');
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
                //button_delete_instance.setAttribute("onclick", "delete_instance(" + index + ");");
                button_delete_instance.onclick = function(){ // Note: onclick, not onClick
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
                //newinput_edit_poly_button.setAttribute("style","float:left;margin-right:5px;");
                newinput_edit_poly_button.style.cssText = "float:left;margin-right:5px;";
                //newinput_edit_poly_button.setAttribute("onclick", "edit_poly(" + index + ");");
                newinput_edit_poly_button.onclick = function(){ // Note: onclick, not onClick
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
                    attr = document.createAttribute("style")
                    //p.setAttribute('style','min-width:100px;float:left;');
                    p.style.cssText = "min-width:100px;float:left;";
                    p.appendChild(t);
                newdiv.appendChild(p);  
                var newinput_point = document.createElement("select");
                newinput_point.setAttribute("name", formname + "[" + index + "]" + "[kat]");
                newinput_point.onchange = function(){ // Note: onclick, not onClick
                    change_marker_icon(index,newinput_point.value);
                    return true;
                };
                for(var i = 0; i < pointing_json.length; i++) {
                    var option_point = document.createElement("option");
                    option_point.setAttribute("value", pointing_json[i]["value"]);
                    if(pointing_json[i]["name"] != null) option_point.innerText = pointing_json[i]["name"];
                    option_point.text = pointing_json[i]["name"];
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

            //REMOVE INSTANCE FROM MAP
            if(type == "point") map.removeOverlay(gmarker[index]);
            else map.removeOverlay(polys[index]);
            
            //REMOVE HTML-BOX
            var parent = document.getElementById('my_map_markers');
            var to_delete = document.getElementById('mapmenu_box_' + index);

            parent.removeChild(to_delete);
        }
        
        function poly_listener(polyline, id) {
            GEvent.addListener(polyline,"endline", function() {
                var pts = [];
                for(i = 0;i < polyline.getVertexCount(); i++) {
                    var point = new GLatLng(polyline.getVertex(i).lat(),polyline.getVertex(i).lng());
                    pts.push(point);
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
                document.getElementById(html_id + "_" + id + "_lat").value = lats;
                document.getElementById(html_id + "_" + id + "_lng").value = lngs;              
                
                if(!single) multi_point_listener();
            });
            
            GEvent.addListener(polyline,"click", function(point) {
                show_box(id);                                
            });
            
            GEvent.addListener(polyline, "mouseover", function() {
                document.getElementById('mapmenu_box_' + id).style.background = "#FFF";
            });
            
            GEvent.addListener(polyline, "mouseout", function() {
                document.getElementById('mapmenu_box_' + id).style.background = "none";
            });
        }
        
        function draw_poly(index) {
            map.removeOverlay(gmarker[index]);
            //gmarker.remove(index);
            
            document.getElementById(html_id + "_" + index + "_type").value = "poly";
            add_poly(null,null,index,true);
        }
        
        function edit_poly(index) {
            polys[index].enableDrawing();
            polys[index].enableEditing();
            
            clear_map_point_listener();
        }
        
        function set_point(index) {
            map.removeOverlay(polys[index]);    
            document.getElementById(html_id + "_" + index + "_type").value = "point";
            document.getElementById("sba_button_" + index).style.display = "block"; 
            document.getElementById("sba_" + index).style.display = "block";            
            document.getElementById("mapmenu_box_hasparent_" + index).style.display = "block";
            
            document.getElementById("edit_poly_" + index).style.display = "none";                   
        
            actual_point = index;
            
            if(single) single_point_listener();
            else multi_point_listener();
        }
        
        function searchAddress(index) {
            var geo = new GClientGeocoder();
            var address = document.getElementById("sba_" + index).value;
            var type;
            if(document.getElementById('radio_set_point').checked) type = "point";
            else type = "poly";
            
            if(!address) {
                 document.getElementById("sba_" + index).value = "no address";  
            }
            else {
                geo.getLocations(address, function (result) { 
                    // If that was successful
                    if (result.Status.code == G_GEO_SUCCESS) {
                        // Lets assume that the first marker is the one we want             
                        var p = result.Placemark[0].Point.coordinates;
                        var lat=p[1];
                        var lng=p[0];

                        if(index == 'general') {
                            if(type == "point") {
                                add_marker(lat,lng,id_count);
                                
                                var index_hack = id_count - 1;                                                                  
                                create_new_instance(index_hack,type);
                                show_box(index_hack);

                                document.getElementById(html_id + "_" + index_hack + "_lat").value = lat;
                                document.getElementById(html_id + "_" + index_hack + "_lng").value = lng;
                                document.getElementById(html_id + "_" + index_hack + "_type").value = type;
                            }
                        }
                        else {
                            type = document.getElementById(html_id + "_" + index + "_type").value;
                            if(type == "point") {
                                map.removeOverlay(gmarker[index]);
                                add_marker(lat,lng,index);
        
                                document.getElementById(html_id + "_" + index + "_lat").value = lat;
                                document.getElementById(html_id + "_" + index + "_lng").value = lng;
                            }
                        }
                        
                        map.setCenter(new GLatLng(lat,lng));
                                                            
                    }
                    // ====== Decode the error status ======
                    else {
                        var reason="Code "+result.Status.code;
                        if (reasons[result.Status.code]) {
                            reason = reasons[result.Status.code]
                        } 
                        document.getElementById("sba_" + index).value = reason; 
                    }
                });
            }
        }
        
        function clear_sba(index) {
            document.getElementById("sba_" + index).value = "";
        }
        
        function show_box(index) {
            if (typeof (window.external) != 'undefined') {

                //yes, this is evil browser sniffing, but only IE has this bug

                document.getElementsByName = function(name, tag) {
                    if (!tag) {
                        tag = '*';
                    }
                    var elems = document.getElementsByTagName(tag);
                    var res = []
                    for (var i = 0; i < elems.length; i++) {
                        att = elems[i].getAttribute('name');
                        if (att == name) {
                            res.push(elems[i]);
                        }
                    }
                    return res;
                }

            }
            
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
        
        function clear_map_point_listener() {
            GEvent.clearListeners(map,"click");
        }
        
        function init_kml() {
            var kml = new GGeoXml(kml_file);
            map.addOverlay(kml);
        }
        
        function set_map_center() {
            if(gmap_data.length > 0) 
            {
                map.setZoom(map.getBoundsZoomLevel(bounds));
                map.setCenter(bounds.getCenter());
            }       
        }
            
        function init_map() {
            if(multi == 0) {
                single = true;
                if(gmap_data[0] != null) {
                    if(gmap_data[0]["type"] == "point" || gmap_data[0]["type"] == null) {
                        add_marker(gmap_data[0]["lat"],gmap_data[0]["lng"],0,gmap_data[0]["kat"]);                  
                        single_point_listener();
                    }
                    else {
                        add_poly(gmap_data[0]["lat"],gmap_data[0]["lng"],0);
                        clear_map_point_listener();
                    }
                }
                else {
                    single_point_listener();
                }
            }
            else {
                if(gmap_data[0] != null) {
                    for(i = 0; i < gmap_data.length; i++) {
                        if(gmap_data[i]["type"] == "point") {
                            add_marker(gmap_data[i]["lat"],gmap_data[i]["lng"],i,gmap_data[i]["kat"]);
                        }
                        else {
                            add_poly(gmap_data[i]["lat"],gmap_data[i]["lng"],i);
                        }                       
                    }
                    multi_point_listener();
                }
                else {
                    multi_point_listener()
                }               
            }
            
            set_map_center();
        }
        
    }
    
    else {
        alert("Your Browser is not compatible with GoogleMaps");
    }
//]]>