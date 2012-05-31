<?
$url = new Url('admin.php');
?>

var uri_index = 0;

function add_row_shorturi() 
{
	var table = $('uri_table');	
	uri_index++;

    new Ajax.Updater(
        table.tBodies[0],
        '<?=$url->with_param(Action::make('Shorturi', 'empty_row'))->str()?>',
        {   method: 'get'
        ,   insertion: Insertion.Bottom
        ,   onComplete: function() {}
        ,   parameters: { 
                        	new_id: uri_index
                        }
        }
    )
}

function delete_uri_row(uri_index)
{
        $('delete_' + uri_index).value = 1;
        $('uri_row_' + uri_index).hide();
}