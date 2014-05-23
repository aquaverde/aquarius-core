{assign var='ilink_action' value=$simpleurl->with_param($rte_options.popup_ilink_url)}
{assign var='rte_file_action_img' value=$simpleurl->with_param($rte_options.popup_filebrowser_url_img)}
{assign var='rte_file_action_file' value=$simpleurl->with_param($rte_options.popup_filebrowser_url_file)}

<script>
    var myilink = {$ilink_action->str(false)|json}; 	
        
    function rte_file_select_img(target_id, file) {    
        rte_file_select(target_id, "img", file);
    }

    function rte_file_select_file(target_id, file) {
        rte_file_select(target_id, "file", file);
    }

    function rte_file_select(target_id, type, file) {
        var file_path = "";
        if (file) {
            if (type == "img") {
                file_path = "/{$rte_options.image_path}";
            } else {
                file_path = "/{$rte_options.file_path}";
            }
            
            var arr = file.split("/");
            if(arr.length == 1) file_path += "/";
            
            file_path += file;
        }
        
        CKEDITOR.tools.callFunction(target_id, file_path);
    }
</script>

<textarea name="{$RTEformname}" class="mle">
	{$RTEformvalue}
</textarea>
<div class="clear"></div>
<script>
    CKEDITOR.config.customConfig = '/aquarius/core/backend/ckeditor/config.js'
    CKEDITOR.config.language = {$rte_options.rte_lg|json};
    CKEDITOR.config.filebrowserImageBrowseUrl = {$rte_file_action_img->str(false)|json};
    CKEDITOR.config.filebrowserBrowseUrl = {$rte_file_action_file->str(false)|json};
    CKEDITOR.config.filebrowserWindowWidth = 500;
    CKEDITOR.config.filebrowserWindowHeight = 600;

    var editor_{$RTEhtmlID} = CKEDITOR.replace({$RTEformname|json});
    {if $rte_options.height > 50}
        editor_{$RTEhtmlID}.config.height = "{$rte_options.height}px";
    {/if}
</script>

