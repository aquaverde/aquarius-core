//<![CDATA[

	if (GBrowserIsCompatible()) { 
		
		var gmarkers = new Array();
		var markers;
		var lines;
		var bounds = new GLatLngBounds();
		var is_IE = false;
		
		// === Create an associative array of GIcons() ===
		var small_icon = new GIcon();
		small_icon.iconSize = new GSize(16,28);
		small_icon.iconAnchor=new GPoint(6,28);
		small_icon.infoWindowAnchor = new GPoint(14,2);
		
		if(navigator.appName == "Microsoft Internet Explorer") is_IE = true;
		
		if (is_IE) {

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
				
		function getMarker(icontype) {
			return new GIcon(small_icon,"/pictures/gmap_icons/"+icontype);
		}
		
		function init_map(xmlString) {
			var xmlDoc = GXml.parse(xmlString);
			// obtain the array of markers and loop through it
			markers = xmlDoc.documentElement.getElementsByTagName("marker");
			
			for (var i = 0; i < markers.length; i++) {
				// obtain the attribues of each marker
				var lat = parseFloat(markers[i].getAttribute("lat"));
				var lng = parseFloat(markers[i].getAttribute("lng"));
				var point = new GLatLng(lat,lng);
				var title = markers[i].getAttribute("title");
				var desc = markers[i].getAttribute("desc");
				var pic = markers[i].getAttribute("pic");
				var link = markers[i].getAttribute("link");
				var linktext = markers[i].getAttribute("linktext");
				var cat = markers[i].getAttribute("cat");
				var icontype = markers[i].getAttribute("icontype");
				if(markers[i].getAttribute("over_cat")) var overcat = markers[i].getAttribute("over_cat");
				else var overcat = false;
				
				// create the marker
				var marker = createMarker(point,title,desc,pic,link,linktext,cat,overcat,icontype);
				map.addOverlay(marker);
				bounds.extend(point);
			}
			
			init_poly(xmlString);
			
			map.setZoom(map.getBoundsZoomLevel(bounds));
			map.setCenter(bounds.getCenter());
			
      	}
		
		function createMarker(point,title,desc,pic,link,linktext,cat,overcat,icontype) {			
			if(icontype == "-") {
				icontype = "marker_00a.png";
			}
        	var marker = new GMarker(point,getMarker(icontype));

			marker.myhtml = '<div class="maps_bubble" style="width:200px;">';
			if(title != "-")
				marker.myhtml += '<h4 style="font-size:12px;">'+title+'</h4>';
			if(desc != "-")
				marker.myhtml += desc;
			if(pic != "-")
				marker.myhtml += '<img style="background:#EDF1F4;padding:5px;" src="'+pic+'" alt="" />';
			if(link != "-") {
				marker.myhtml += '<a style="text-decoration:none;font-size:12px;" href="'+link+'">';
				if(linktext != "-") marker.myhtml += linktext+'</a>';
				else marker.myhtml += link + '</a>';
			}
			marker.myhtml += '</div>';
			
			marker.myIcontype = icontype;
			marker.mycategory = cat;
			if(overcat != false) marker.myovercat = overcat;
			marker.mytitle = title;
			marker.mydesc = desc;
			marker.mypic = pic;
			marker.mylink = link;

			GEvent.addListener(marker, "click", function() {
				if(title == "-" && desc == "-" && pic == "-" && link == "-") {}
				else
		    		marker.openInfoWindowHtml(marker.myhtml);
        	});
        
			gmarkers.push(marker);
        	return marker;
		}
		
		function init_poly(xmlString) {
			var xmlDoc = GXml.parse(xmlString);
			// ========= Now process the polylines ===========
			lines = xmlDoc.documentElement.getElementsByTagName("line");
			// read each line
			for (var a = 0; a < lines.length; a++) {
				// get any line attributes
				var colour = lines[a].getAttribute("colour");
				var width  = parseFloat(lines[a].getAttribute("width"));
				// read each point on that line
				var points = lines[a].getElementsByTagName("point");
				var pts = [];
				for (var i = 0; i < points.length; i++) {
			   		pts[i] = new GLatLng(parseFloat(points[i].getAttribute("lat")), parseFloat(points[i].getAttribute("lng")));
					bounds.extend(new GLatLng(parseFloat(points[i].getAttribute("lat")), parseFloat(points[i].getAttribute("lng"))));
				}
				var line = new GPolyline(pts,colour,width);
				
				line.mycategory = lines[a].getAttribute("cat");
				if(lines[a].getAttribute("over_cat")) line.myovercat = lines[a].getAttribute("over_cat");
				
				gmarkers.push(line);	
				map.addOverlay(line);
			}
			// ================================================
		}
		
		function show_cat_only(category,sort) {
			bounds = new GLatLngBounds();
			var is_in_cat = false;
			for(var i = 0; i < gmarkers.length; i++) {
				is_in_cat = false;
				if(sort == 'cat') {
					if(gmarkers[i].mycategory == category) is_in_cat = true; 
				}
				else {				
					if(gmarkers[i].myovercat == category) is_in_cat = true;
				}
				
				if(is_in_cat) {
					if(gmarkers[i].isHidden()) { 
						gmarkers[i].show();	
					}
					if(gmarkers[i].myIcontype) bounds.extend(gmarkers[i].getLatLng());
					else {
						for(j = 0; j < gmarkers[i].getVertexCount(); j++) {
							bounds.extend(gmarkers[i].getVertex(j));
						}
					}
				}
				else {
					if(!gmarkers[i].isHidden()) gmarkers[i].hide();
				}
			}
			setBoundCenter();
		}
		
		function myMouseOver(i) {
			GEvent.trigger(gmarkers[i], "mouseover");
		}
	
		function myMouseOut(i) {
			GEvent.trigger(gmarkers[i], "mouseout");
		}
		
		function close_cat_part(cat,box) {										
			if(box.attributes[0].value != "closed" && box.attributes[0].value != "") {
				box.attributes[0].value = "closed";
				document.images[cat+'_closer'].src = "picts/arrow_addon-off.gif";
				for(var i = 0; i < document.getElementsByName(cat+'_table_part').length; i++) {	
					document.getElementsByName(cat+'_table_part')[i].style.display = "none";
					document.getElementsByName(cat+'_table_part')[i].attributes[0].value = "closed";
				}
			}
			else {
				box.attributes[0].value = "open";
				document.images[cat+'_closer'].src = "picts/arrow_addon-on.gif";		
				for(var i = 0; i < document.getElementsByName(cat+'_table_part').length; i++) {
					if (is_IE) document.getElementsByName(cat+'_table_part')[i].style.display = "block";
					else document.getElementsByName(cat+'_table_part')[i].style.display = "table-row";
					document.getElementsByName(cat+'_table_part')[i].attributes[0].value = "open";
				}				
			}
		}
		
		function close_cat_part_poly(name,box) {
			if(box.attributes[0].value != "closed" && box.attributes[0].value != "") {
				box.attributes[0].value = "closed";
				document.images[name+'_poly_closer'].src = "picts/arrow_addon-off.gif";
								
				for(var j = 0; j < getElementsByAttribute('overcat',name+'_overcat_child').length; j++) {		
					getElementsByAttribute('overcat',name+'_overcat_child')[j].style.display = "none";
				}
				
				for(var i = 0; i < document.getElementsByName(name+'_table_childs').length; i++) {		
					document.getElementsByName(name+'_table_childs')[i].style.display = "none";
				}
			}
			else {
				box.attributes[0].value = "open";
				document.images[name+'_poly_closer'].src = "picts/arrow_addon-on.gif";		
				
				for(var j = 0; j < getElementsByAttribute('overcat',name+'_overcat_child').length; j++) {
					if(getElementsByAttribute('overcat',name+'_overcat_child')[j].attributes[0].value == "open") {
						if (is_IE) getElementsByAttribute('overcat',name+'_overcat_child')[j].style.display = "block";		
						else getElementsByAttribute('overcat',name+'_overcat_child')[j].style.display = "table-row";
					}
				}
				
				for(var i = 0; i < document.getElementsByName(name+'_table_childs').length; i++) {		
					if (is_IE) document.getElementsByName(name+'_table_childs')[i].style.display = "block";
					else document.getElementsByName(name+'_table_childs')[i].style.display = "table-row";
				}				
			}			
		}
		
		function getElementsByAttribute(attr,val)
		{
			container = document;
			var all = container.getElementsByTagName('*');
			var arr = [];
			
			for(var k=0; k<all.length; k++) {
				if(all[k].getAttribute(attr) == val) arr[arr.length] = all[k];
			}
				
			return arr;
		}

		// BEGIN FRONTEND-----------------------------
		function simpleMarker(lat,lng,zoom,title,desc,pic,link,cat,icontype) {
			var point = new GLatLng(parseFloat(lat),parseFloat(lng));
			var marker = createMarker(point,title,desc,pic,link,linktext,cat,false,icontype);
			map.addOverlay(marker);
			
			map.setCenter(point, parseInt(zoom));
		}
		
		function addMarker(lat,lng,title,desc,pic,link,linktext,cat,icontype) {			
			var point = new GLatLng(parseFloat(lat),parseFloat(lng));
			var marker = createMarker(point,title,desc,pic,link,linktext,cat,false,icontype);
			
			map.addOverlay(marker);
			bounds.extend(point);
		}
		
		function addPoly(lats,lngs) {
			var mylats = lats.split(",");
			var mylng = lngs.split(",");
			var pts = [];
			var colour = "#000000";
			var width = "8";
			
			for (var i = 0; i < mylats.length; i++) {
				pts[i] = new GLatLng(parseFloat(mylats[i]), parseFloat(mylng[i]));
				bounds.extend(new GLatLng(parseFloat(mylats[i]), parseFloat(mylng[i])));
			}
			map.addOverlay(new GPolyline(pts,colour,width));
		}
		
		function setBoundCenter() {
			map.setZoom(map.getBoundsZoomLevel(bounds));
			map.setCenter(bounds.getCenter());
		}
		
		function show_hide_cat(cat,checkbox) {
			if(checkbox.checked) {
				for(var i = 0; i < gmarkers.length; i++) {
					if(gmarkers[i].mycategory == cat) 
						if(gmarkers[i].isHidden()) gmarkers[i].show();
				}
			}
			else {
				for(var i = 0; i < gmarkers.length; i++) {
					if(gmarkers[i].mycategory == cat) 
						if(!gmarkers[i].isHidden()) gmarkers[i].hide();
				}
			}
		}
		
		function show_hide_overcat(overcat,checkbox) {
			if(checkbox.checked) {
				for(var i = 0; i < gmarkers.length; i++) {
					if(gmarkers[i].myovercat == overcat) 
						if(gmarkers[i].isHidden()) gmarkers[i].show();
				}
			}
			else {
				for(var i = 0; i < gmarkers.length; i++) {
					if(gmarkers[i].myovercat == overcat) 
						if(!gmarkers[i].isHidden()) gmarkers[i].hide();
				}
			}
		}
		
		function show_cat(cat) {
			for(var i = 0; i < gmarkers.length; i++) {
				if(gmarkers[i].mycategory == cat) 
					if(gmarkers[i].isHidden()) gmarkers[i].show();
			}
		}
		
		function show_over_cat(overcat) {
			for(var i = 0; i < gmarkers.length; i++) {
				if(gmarkers[i].myovercat == overcat) 
					if(gmarkers[i].isHidden()) gmarkers[i].show();
			}
		}
		
		function hide_all() {
			for(var i = 0; i < gmarkers.length; i++) {
					gmarkers[i].hide();
			}			
		}
		// END FRONTEND-----------------------------
		
		//CREATE THE MAP
		var map = new GMap2(document.getElementById("map"));
		map.setCenter(new GLatLng(d_lat,d_lng), parseInt(d_zoom));
		map.addControl(new GLargeMapControl3D());
		map.addControl(new GMapTypeControl());
		map.addControl(new GOverviewMapControl(new GSize(140,120)));
						
	}
	
	else {
		alert("Your Browser is not compatible with GoogleMaps");
	}
	
//]]>