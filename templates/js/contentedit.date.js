<?php
$url = new Url('admin.php');
?>

function add_date_ajax(formfield , htmlid) 
{
    var new_id = window["ac_index_" + htmlid] + 1;
    var whereToAdd = $('my_dates_' + htmlid);
    
    new Ajax.Updater(
        whereToAdd,
        '<?php echo $url->with_param(Action::make('date_ajax', 'empty'))->str()?>',
        {   method: 'get'
        ,   insertion: Insertion.Bottom
        ,   onComplete: function() {}
        ,   parameters: { formfield: formfield
                        , new_id: new_id
                        }
        }
    )
    
    window["ac_index_" + htmlid]++;
}