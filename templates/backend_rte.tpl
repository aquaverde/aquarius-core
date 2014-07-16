{assign var='config' value=$rte_options->config_for($simpleurl)}

<script>
     var ilink_select = {$config.ilink_select|json}; 	

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
    CKEDITOR.plugins.addExternal('iLink', '/aquarius/core/backend/ckeditor/plugins/iLink/plugin.js', '')
    CKEDITOR.plugins.addExternal('charcounter', '/aquarius/core/backend/ckeditor/plugins/charcounter/plugin.js', '')
    CKEDITOR.config.extraPlugins = 'iLink,charcounter';
    var editor_{$RTEhtmlID} = CKEDITOR.replace({$RTEformname|json}, {$config|json});
</script>

