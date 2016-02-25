<?php
// This JS file is parsed by PHP before delivery, so we can inject the URL
// that can be used to load additional rows. There is probably a better way.
$url = new Url('admin.php');
?>

var uri_index = 0;

function add_row_shorturi() {
    var new_row_action = '<?php echo $url->with_param(Action::make('Shorturi', 'empty_row'))->str() ?>';
    var $table = jQuery('#uri_table tbody');
    uri_index++;

    jQuery.get(new_row_action, { new_id: uri_index }, function(new_row) {
        $table.append(new_row);
    });
}

function delete_uri_row(uri_index) {
    jQuery('#delete_' + uri_index).val(1);
    jQuery('#uri_row_' + uri_index).hide();
}