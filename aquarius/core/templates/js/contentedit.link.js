<?
$url = new Url('admin.php');
?>

function reInitTableDnD(tablename) {
	var table = document.getElementById(tablename);
    var tableDnD = new TableDnD();
	tableDnD.init(table);
}

function add_link_ajax(id, formfield , htmlid) {
	var item = $(htmlid+"_"+id);
	var table = $('link_table_'+htmlid);
	var tbody = table.tBodies[0];
	var rows = tbody.rows;
	var rows_index = rows.length;
	var last_row = rows[rows_index - 1];
	
	if(item == last_row) {		
		new_id = rows_index;
	    new Ajax.Updater(
	        table.tBodies[0],
	        '<?=$url->with_param(Action::make('link_ajax', 'empty_row'))->str()?>',
	        {   method: 'get'
	        ,   insertion: Insertion.Bottom
	        ,   onComplete: function() {}
	        ,   parameters: { formfield: formfield
	                        , new_id: new_id
	                        }
	        }
	    )
	
		setTimeout("this.reInitTableDnD('link_table_"+htmlid+"')", 200);
	}
}

function add_empty_link(index) {
	if(index != (my_index - 1)) return false;
	
	var tbl = document.getElementById('link_table');
	var lastRow = tbl.rows.length;
	var row = tbl.insertRow(lastRow);
	
	var cell1 = row.insertCell(0);
	cell1.setAttribute("width", "100%");
	cell1.setAttribute("nowrap", "nowrap");
	cell1.style.cssText = "vertical-align:middle";
		var div_selecter = document.createElement("div");
		div_selecter.style.cssText = "float:right;margin:0;";
			var t1 = document.createTextNode(s_link_target);
		div_selecter.appendChild(t1);
			var selecter = document.createElement("select");
			selecter.style.cssText = "margin:0; margin-left:6px; width:80px;";
			selecter.setAttribute("name", formname3);
				var option1 = document.createElement("option");
				option1.setAttribute("value", "");
				option1.innerHTML = s_link_target_intern;
				var option2 = document.createElement("option");
				option2.setAttribute("value", "_blank");
				option2.innerHTML = s_link_target_extern;
				var option3 = document.createElement("option");
				option3.setAttribute("value", "popup");
				option3.innerHTML = "popup";
				var option4 = document.createElement("option");
				option4.setAttribute("value", "email");
				option4.innerHTML = "e-mail";
			selecter.appendChild(option1);
			selecter.appendChild(option2);
			selecter.appendChild(option3);
			selecter.appendChild(option4);
		div_selecter.appendChild(selecter);
	cell1.appendChild(div_selecter);
	
		var t2 = document.createTextNode(s_link);
	cell1.appendChild(t2);
	
		var input_link = document.createElement("input");
		input_link.setAttribute("type", "text");
		input_link.setAttribute("value", "");		
		input_link.setAttribute("name", formname);
		input_link.className = "ef";
		input_link.style.cssText = "margin:0px 9px 0 6px; width:35%;";
		input_link.onchange = function(){ // Note: onclick, not onClick
		    add_empty_link(index + 1);
		    return true;
		};
	cell1.appendChild(input_link);
	
		var t3 = document.createTextNode(s_link_text);
	cell1.appendChild(t3);
	
		var input_link_text = document.createElement("input");
		input_link_text.setAttribute("type", "text");
		input_link_text.setAttribute("value", "");		
		input_link_text.setAttribute("name", formname2);
		input_link_text.className = "ef";
		input_link_text.style.cssText = "margin:0 6px; width:18%;";
	cell1.appendChild(input_link_text);
	
	var cell2 = row.insertCell(1);
	cell2.setAttribute("width", "20");
	cell2.setAttribute("nowrap", "nowrap");
	cell2.setAttribute("align", "center");
	cell2.style.cssText = "vertical-align:middle";
	
		var input_weight = document.createElement("input");
		input_weight.setAttribute("type", "text");
		actual_weight += 10;
		input_weight.setAttribute("value", actual_weight);		
		input_weight.setAttribute("name", formname4);
		input_weight.setAttribute("size", "3");
		input_weight.className = "inputweight";
		input_weight.style.cssText = "margin:0;";
	cell2.appendChild(input_weight);
	
	my_index++;
}