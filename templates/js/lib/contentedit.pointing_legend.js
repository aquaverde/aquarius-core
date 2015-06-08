function bind_pointing_legend() {
    // Because new rows may be added via AJAX, we unbind all handlers then
    // reattach. This is the simplest solution.
    jQuery('.pointing_selection_legend')
    .unbind('click')
    .click(function() {
        // Use the pointing_selection setup code and add a callback that
        // modifies the row when nodes are selected/deselected
        // A new row is added when the last empty row is filled
        pointing_selection_setup(this, function(target_id) {
            var $row = jQuery('#'+target_id);
            $row.find('.delete_pointing_legend').show();

            if ($row.hasClass('last')) {
                add_pointing_ajax($row.data('formfield'), $row.data('htmlid'), $row.data('lg'));
            }
        });
    });

    jQuery('.delete_pointing_legend')
    .unbind('click')
    .click(function() {
        var table = jQuery(this).parents('table')[0]; // After node is removed from DOM, we can't get the parent anymore
        jQuery(this).parents('tr').remove();
        reInitTableDnD(table);
    });
}

jQuery(bind_pointing_legend);

function reInitTableDnD(table) {
    var tableDnD = new TableDnD();
    tableDnD.init(table);
}

function add_pointing_ajax(formfield, htmlid, lg) {
    var id = 'pointing_table_'+htmlid;
	var table = document.getElementById(id);
	var tbody = table.tBodies[0];
	var rows = tbody.rows;
	var rows_index = rows.length;
	var last_row = rows[rows_index - 1];

	var new_id = rows_index;
    jQuery.ajax({ url: jQuery(table).data('newurl')
                , data: { formfield: formfield
                        , new_id: new_id
                        , lg: lg
                        }
                }
    ).done(function(new_row) {
        jQuery(tbody).find('tr').removeClass('last');
        jQuery(tbody).append(new_row);
        reInitTableDnD(table);
        bind_pointing_legend();
    });
 
}
