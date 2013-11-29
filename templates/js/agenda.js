function close_cat_part(cat,group,box) {
	if(box.attributes[0].value != "closed") {
		box.attributes[0].value = "closed";
		document.images[cat+'_'+group+'_closer'].src = "picts/arrow_addon-off.gif";
		for(var i = 0; i < document.getElementsByName(cat+'_'+group+'_table_part').length; i++) {		
			document.getElementsByName(cat+'_'+group+'_table_part')[i].style.display = "none";
			document.getElementsByName(cat+'_'+group+'_table_part')[i].attributes[0].value = "closed";
		}
	}
	else {
		box.attributes[0].value = "open";
		document.images[cat+'_'+group+'_closer'].src = "picts/arrow_addon-on.gif";		
		for(var i = 0; i < document.getElementsByName(cat+'_'+group+'_table_part').length; i++) {		
			document.getElementsByName(cat+'_'+group+'_table_part')[i].style.display = "table-row";
			document.getElementsByName(cat+'_'+group+'_table_part')[i].attributes[0].value = "open";
		}				
	}
}

function close_cat_part_poly(name,box) {
	if(box.attributes[0].value != "closed") {
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
				getElementsByAttribute('overcat',name+'_overcat_child')[j].style.display = "table-row";
			}
		}
		
		for(var i = 0; i < document.getElementsByName(name+'_table_childs').length; i++) {		
			document.getElementsByName(name+'_table_childs')[i].style.display = "table-row";
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