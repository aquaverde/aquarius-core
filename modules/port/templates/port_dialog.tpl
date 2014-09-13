{include file='header.tpl'}
{js}
function nodes_selected(target_id, selected_nodes) {
    var ids = []
    var titles = []
    for (node_id in selected_nodes) {
        ids.push(node_id)
        titles.push(selected_nodes[node_id])
    }
    
    jQuery('#'+target_id+'_selected').val(ids.join(','))

    jQuery('#'+target_id+'_titles').text(titles.join(' | '))
}
{/js}

<h1>{#port_dialog_title#}</h1>

<div class="bigbox">
    <h2>{#port_dialog_title_export#}</h2>

    <form action="{url action=$lastaction}" method="post">
        <input type="hidden" id="export_selected" name="export_selected" value=""/>
        <label>
            {#port_export_roots#}
            <button
                type='button'
                name='node_select' id='node_select'
                class='button'
                onclick='open_attached_popup("{$simpleurl->with_param($roots_select_action)}", "roots_select", "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'
            >
                {#s_usr_choose#}
            </button>
            <span id="export_titles"></span>
        </label>
        <label><input type="checkbox" name="include_children">{#port_include_children#}</label>
        <label><input type="radio" name="update" value="0">{#port_new#}</label>
        <label><input type="radio" name="update" value="1" checked>{#port_update#}</label>

        {include file='select_buttons.tpl' actions=$export_actions}
    </form>
</div>
<div class="bigbox">
    <h2>{#port_dialog_title_import#}</h2>
    <form action="{url action=$lastaction}" enctype="multipart/form-data" method="post" accept-charset="utf-8">
        <input type="hidden" id="import_selected" name="import_selected" value=""/>
        <label>
            {#port_import_root#}
            <button
                type='button'
                name='node_select' id='node_select'
                class='button'
                onclick='open_attached_popup("{$simpleurl->with_param($import_root_select_action)}", "roots_select", "height=450,width=350,status=yes,resizable=yes,scrollbars=yes"); return false;'
            >
                {#s_usr_choose#}
            </button>
            <span id="import_titles"></span>
        </label>
        <label>{#port_import_text#} <textarea style="width: 100%" name='import_text'>{$smarty.request.import_text}</textarea></label>
        <input type="file" name="import_file" />
        
        {include file='select_buttons.tpl' actions=$import_actions}
    </form>
</div>
{include file='footer.tpl'}
