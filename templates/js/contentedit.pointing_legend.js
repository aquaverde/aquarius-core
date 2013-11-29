<?
$url = new Url('admin.php');
?>

function nodes_selected_pl(target_id, selected_nodes) {
    var ids = []
    var titles = []
    for (node_id in selected_nodes) {
        ids.push(node_id)
        titles.push(selected_nodes[node_id])
    }
    $(target_id+'_selected').value = ids.join(',')

    $(target_id+'_titles').update(titles.join(' | '))
    var titlebox = $(target_id+'_titlebox')
    titlebox.style.display = ids.length == 0 ? 'none' : 'block'
    
    var deleteButton = $(target_id+"_delete_button")
    deleteButton.style.display = ids.length == 0 ? 'none' : 'block'

    // Jig a class so that IE renders our changes
    titlebox.toggleClassName('idefix')
    deleteButton.toggleClassName('idefix')
    
    if($(target_id).hasClassName('lastOne'))
    {
        var splitter = target_id.split("_");
        var htmlid = splitter[0];

        $(target_id).removeClassName('lastOne');
        window['add_pointing_ajax_' + htmlid]();
    }
}

function reInitTableDnD(tablename) {
	var table = document.getElementById(tablename);
    var tableDnD = new TableDnD();
	tableDnD.init(table);
}

function add_pointing_ajax(formfield, htmlid, lg) {
	var table = $('pointing_table_'+htmlid);
	var tbody = table.tBodies[0];
	var rows = tbody.rows;
	var rows_index = rows.length;
	var last_row = rows[rows_index - 1];

	    
	var new_id = rows_index;
    new Ajax.Updater(
        table.tBodies[0],
        '<?=$url->with_param(Action::make('pointing_legend_ajax', 'empty_row'))->str()?>',
        {   method: 'get'
        ,   insertion: Insertion.Bottom
        ,   onComplete: function() {}
        ,   parameters: { formfield: formfield
                        , new_id: new_id
                        , lg: lg
                        }
        }
    )

	setTimeout("this.reInitTableDnD('pointing_table_"+htmlid+"')", 200);

}

function remove_pointing_legend(id, htmlid)
{
    $(id + "_selected").value = "";
    $(id).style.display = "none";
    
    setTimeout("this.reInitTableDnD('pointing_table_"+htmlid+"')", 200);
    
    // try {
    //     var table = $('pointing_table_'+htmlid);
    //     var rowCount = table.rows.length;
    // 
    //     for(var i=0; i<rowCount; i++) {
    //         var row = table.rows[i];
    //         if(row.id == id) {
    //             table.deleteRow(i);
    //             return;
    //         }
    //     }
    //     
    //     setTimeout("this.reInitTableDnD('pointing_table_"+htmlid+"')", 200);
    // } catch(e) {}
}