//<![CDATA[

	if (GBrowserIsCompatible()) { 
		
		var gmarkers = new Array();
		var markers;
		var lines;
		var bounds = new GLatLngBounds();
		var mycats = new Array();
		var external_added = false;
		var map;
		
		var htmls = [];
      	// arrays to hold variants of the info window html with get direction forms open
      	var to_htmls = [];
      	var from_htmls = [];
		
		// === Create an associative array of GIcons() ===
		var small_icon = new GIcon();
		small_icon.iconSize = new GSize(25,29);
		small_icon.iconAnchor=new GPoint(12,29);
		small_icon.infoWindowAnchor = new GPoint(14,2); 
		
		var very_small_icon = new GIcon();
		very_small_icon.iconSize = new GSize(7,7);
		very_small_icon.iconAnchor=new GPoint(3,3);
		very_small_icon.infoWindowAnchor = new GPoint(7,7);
		
		function check_resize() 
		{
			// HACK: Check whether box size changed (changes between display: none and display: block)
            map.checkResize();

			map.setZoom(map.getBoundsZoomLevel(bounds));
			map.setCenter(bounds.getCenter());
		}
				
		function getMarker(icontype) {
			if(icontype == 'dot.png') return new GIcon(very_small_icon,"/pictures/gmap_icons/"+icontype);
			return new GIcon(small_icon,"/pictures/gmap_icons/"+icontype);
		}
				
		function init_kml() {
			var kml = new GGeoXml(kml_file);
			map.addOverlay(kml);
		}
		
		function createMarker(point,title,desc,pic,link,linktext,cat,overcat,icontype) {
			createMarker(point,title,desc,pic,link,linktext,cat,overcat,icontype,false);
		}
		
		function createMarker(point,title,desc,pic,link,linktext,cat,overcat,icontype,external) {
			if(icontype == "-") {
				icontype = d_icon;
			}
        	var marker = new GMarker(point,getMarker(icontype));

			marker.myhtml = '<div class="maps_bubble_container"><div class="maps_bubble">';
			if(title != "-" && title != '')
				marker.myhtml += '<h4 style="font: 12px arial, verdana, sans-serif; color: #4e473d; text-transform: uppercase;">'+title+'</h4>';
			if(pic != "-" && pic != '')
				marker.myhtml += '<br/><img style="width: 26%; height: 26%; border: 1px #4e473d solid; margin: 0 5px 0 0; float: left;" src="'+pic+'" alt="" />';	
			if(desc != "-" && desc != '')
				marker.myhtml += '<span style="font: 10px arial, verdana, sans-serif; color: #4e473d;">'+desc+'</span>';
			if(link != "-" && link != '') {
				if(external)
					marker.myhtml += '<br style="clear: both;" /><a class="res_link" href="javascript:show_marker_ex_content(\''+encodeURI(link)+'\');" >';
				else	
					marker.myhtml += '<br style="clear: both; margin-bottom: 16px;" /><a class="res_link" href="'+link+'" target="_blank">';
				if(linktext != "-" && linktext != '') marker.myhtml += linktext+'</a>';
				else marker.myhtml += link + '</a>';
			}
			marker.myhtml += '</div>';
			
			//BEGIN DIRECTION___________________________________________________________________________
			var i = gmarkers.length;
			
			// The info window version with the "to here" form open
	        to_htmls[i] = marker.myhtml + '<div class="map_bubble_direction" style="font-size: 10px; color: #4e473d;">'+route_berechnen+': <b>'+hierher+'<\/b> - <a href="javascript:fromhere(' + i + ')">'+von_hier+'<\/a>' +
	           '<br>'+startadresse+':<form action="javascript:getDirections()">' +
	           '<input class="dir_input" type="text" SIZE=40 MAXLENGTH=40 name="saddr" id="saddr" value="" /><br>' +
	           '<INPUT class="dir_submit" value="'+los+'" TYPE="SUBMIT" style="font-size: 10px;"><br>' +
	           zu_fuss + ' <input type="checkbox" name="walk" id="walk" style="font-size: 10px;" /> &nbsp; '+auto+' <input type="checkbox" name="highways" id="highways" style="font-size: 10px;" />' +
	           '<input type="hidden" id="daddr" value="'+title+"@"+ point.lat() + ',' + point.lng() + 
	           '" style="font-size: 10px;" /></div></div>';
	        // The info window version with the "from here" form open
	        from_htmls[i] = marker.myhtml + '<div class="map_bubble_direction" style="font-size: 10px; color: #4e473d;">'+route_berechnen+': <a href="javascript:tohere(' + i + ')">'+hierher+'<\/a> - <b>'+von_hier+'<\/b>' +
	           '<br>'+zieladresse+':<form action="javascript:getDirections()">' +
	           '<input class="dir_input" type="text" SIZE=40 MAXLENGTH=40 name="daddr" id="daddr" value="" /><br>' +
	           '<INPUT class="dir_submit" value="'+los+'" TYPE="SUBMIT" style="font-size: 10px;"><br>' +
	           zu_fuss + ' <input type="checkbox" name="walk" id="walk" style="font-size: 10px;" /> &nbsp; '+auto+' <input type="checkbox" name="highways" id="highways" style="font-size: 10px;" />' +
	           '<input type="hidden" id="saddr" value="'+title+"@"+ point.lat() + ',' + point.lng() +
	           '" style="font-size: 10px;" /></div></div>';
			
			marker.myhtml += '<div class="map_bubble_direction" style="font-size: 10px;">';
			marker.myhtml += route_berechnen+': <a href="javascript:tohere('+i+')">'+hierher+'</a> - <a href="javascript:fromhere('+i+')">'+von_hier+'</a>';
			marker.myhtml += '</div></div>';
			
			//END DIRECTION_____________________________________________________________________________
						
			marker.myIcontype = icontype;
			marker.mycategory = cat;
			if(overcat != false) marker.myovercat = overcat;
			marker.mytitle = title;
			marker.mydesc = desc;
			marker.mypic = pic;
			marker.mylink = link;
			marker.ext = external;

			GEvent.addListener(marker, "click", function() {
				if(title == "-" && desc == "-" && pic == "-" && link == "-") {}
				else
		    		marker.openInfoWindowHtml(marker.myhtml);
        	});
        
			gmarkers.push(marker);
			htmls[i] = marker.myhtml;
        	return marker;
		}
		
		// ===== request the directions =====
		function getDirections() {
			// ==== Set up the walk and avoid highways options ====
			var opts = {};
			if (document.getElementById("walk").checked) {
		   		opts.travelMode = G_TRAVEL_MODE_WALKING;
			}
			if (document.getElementById("highways").checked) {
		   		opts.avoidHighways = true;
			}
			// ==== set the start and end locations ====
			var saddr = document.getElementById("saddr").value
			var daddr = document.getElementById("daddr").value
			gdir.load("from: "+saddr+" to: "+daddr, opts);
		}
		
		// This function picks up the click and opens the corresponding info window
		function myclick(i) {
        	gmarkers[i].openInfoWindowHtml(htmls[i]);
      	}
		// functions that open the directions forms
		function tohere(i) {
			gmarkers[i].openInfoWindowHtml(to_htmls[i]);
		}
		function fromhere(i) {
			gmarkers[i].openInfoWindowHtml(from_htmls[i]);
		}
						
		// BEGIN FRONTEND-----------------------------
		function simpleMarker(lat,lng,zoom,title,desc,pic,link,cat,icontype) {
			var point = new GLatLng(parseFloat(lat),parseFloat(lng));
			var marker = createMarker(point,title,desc,pic,link,linktext,cat,false,icontype);
			map.addOverlay(marker);
			
			map.setCenter(point, parseInt(zoom));
		}
		
		function load_node_map() {
			if(!map_data) return false;						
			
			init_global_map();
			for(i = 0; i < map_data.length; i++) {
				if(map_data[i]['type'] == 'point') {
					addMarker(
						map_data[i]["lat"],
						map_data[i]["lng"],
						map_data[i]["title"],
						map_data[i]["desc"],
						map_data[i]["pic"],
						map_data[i]["link"],
						map_data[i]["link_text"],
						map_data[i]["cat"],
						map_data[i]["icontype"]);
				}
				else {
					addPoly(map_data[i]["lat"], map_data[i]["lng"]);
				}						
			}
			
			if(gmarkers.length > 0) setBoundCenter();
			
			if(typeof( window[ 'kml_file' ] ) != "undefined") init_kml();
		}
		
		function addMarker(lat,lng,title,desc,pic,link,linktext,cat,icontype) {
			addMarker(lat,lng,title,desc,pic,link,linktext,cat,icontype,false);
		}
		
		function addMarker(lat,lng,title,desc,pic,link,linktext,cat,icontype,external) {			
			var point = new GLatLng(parseFloat(lat),parseFloat(lng));
			var marker = createMarker(point,title,desc,pic,link,linktext,cat,false,icontype,external);									
			
			map.addOverlay(marker);
			if(!external) bounds.extend(point);
		}
		
		var ex_first = false;
		function add_remove_external() {
			if(!ex_first) {
				ex_first = true;
				external_added = true;
							
				$.getJSON(map_xml_file, function(data) {
					add_external(data);
				});
				
				return;											
			}			
			if(!external_added) {
				for(var j = 0; j < gmarkers.length; j++) {
					if(gmarkers[j].ext == true) {
						gmarkers[j].show();
					}
				}				
				external_added = true;				
			}
			else {
				for(var j = 0; j < gmarkers.length; j++) {
					if(gmarkers[j].ext == true) {
						gmarkers[j].hide();
					}
				}
				external_added = false;
			}
		}
		
		function add_external(xml_data) {						
			for(i = 0; i < xml_data.length; i++) {
				addMarker(
					xml_data[i]["lat"],
					xml_data[i]["lng"],
					xml_data[i]["name"],
					xml_data[i]["desc"],
					xml_data[i]["picture"],
					xml_data[i]["link"],
					online_buchen,
					xml_data[i]["cat"],
					"dot.png",
					true);						
			}
		}
		
		function addPoly(lats,lngs) {
			var mylats = lats.split(",");
			var mylng = lngs.split(",");
			var pts = [];
			var colour = d_color;
			var width = d_width;
			var op = d_trans;
			
			for (var i = 0; i < mylats.length; i++) {
				pts[i] = new GLatLng(parseFloat(mylats[i]), parseFloat(mylng[i]));
				bounds.extend(new GLatLng(parseFloat(mylats[i]), parseFloat(mylng[i])));
			}
			map.addOverlay(new GPolyline(pts,colour,width,op));
		}
		
		function setBoundCenter() {
			map.setZoom(map.getBoundsZoomLevel(bounds));
			map.setCenter(bounds.getCenter());
			
			if(map.getZoom() > 13) map.setZoom(13);
		}
		
		var map_is_big = false;
		function bigmap() {
			//$("#map").removeClass("map_normal");
			//$('#map').addClass("map_big");
			$('#close_button_bigmap').css('display', 'block');
			var my_height = $(window).height() - 150;
			var my_width = $(window).width() - 100;
			tb_show('Google Map',
			  '#TB_inline?height='+my_height+'&width='+my_width+'&inlineId=map_container&modal=true',true);
			map.checkResize();
			map_is_big = true;								
		}
		
		function normalmap() {
			tb_remove();
			$('#close_button_bigmap').css('display', 'none');
			setTimeout("ie_map_size_fix()",200);
			map_is_big = false;
		}	
		
		function ie_map_size_fix() {
			$('#map_container').css('display', 'none').css('display', 'block');
			map.checkResize();
		}
		
		function show_marker_ex_content(url) {
			if(map_is_big) {
				normalmap();
				setTimeout("smec_hack('" + url + "')" ,300);
			} else {
				smec_hack(url);
			}
		}
		
		function smec_hack(url) {
			tb_show(online_buchen,
				url + '?TB_iframe=true&width=800&height=450'
				);
		}
		// END FRONTEND-----------------------------
		
		//CREATE THE MAP
		var gdir;
		function init_global_map() {
			map = new GMap2(document.getElementById("map"));
			map.setCenter(new GLatLng(d_lat,d_lng), parseInt(d_zoom));
			map.addControl(new GLargeMapControl3D());
			map.addControl(new GMapTypeControl());
			map.addMapType(G_PHYSICAL_MAP);
			map.setMapType(G_PHYSICAL_MAP);
			//map.addControl(new GOverviewMapControl(new GSize(140,120)));
		
			// === create a GDirections Object ===
	      	gdir=new GDirections(map, document.getElementById("directions"));
		
			// === Array for decoding the failure codes ===
			var reasons=[];
			reasons[G_GEO_SUCCESS]            = "Success";
			reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address: The address was either missing or had no value.";
			reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address:  No corresponding geographic location could be found for the specified address.";
			reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address:  The geocode for the given address cannot be returned due to legal or contractual reasons.";
			reasons[G_GEO_BAD_KEY]            = "Bad Key: The API key is either invalid or does not match the domain for which it was given";
			reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Queries: The daily geocoding quota for this site has been exceeded.";
			reasons[G_GEO_SERVER_ERROR]       = "Server error: The geocoding request could not be successfully processed.";
			reasons[G_GEO_BAD_REQUEST]        = "A directions request could not be successfully parsed.";
			reasons[G_GEO_MISSING_QUERY]      = "No query was specified in the input.";
			reasons[G_GEO_UNKNOWN_DIRECTIONS] = "The GDirections object could not compute directions between the points.";

			// === catch Directions errors ===
			GEvent.addListener(gdir, "error", function() {
			  	var code = gdir.getStatus().code;
			  	var reason="Code "+code;
			  	if (reasons[code]) {
			    	reason = reasons[code]
			  	} 

			  	alert("Failed to obtain directions, "+reason);
			});
			
			// HACK FOR CHECKBOX chb_map_external CACHED WHEN RELOAD SITE						
			if(document.getElementById('chb_map_external') != null && typeof document.getElementById('chb_map_external') != 'undefined') document.getElementById('chb_map_external').checked = false;  
		}						
	}

//]]>
