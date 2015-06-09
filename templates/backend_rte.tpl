{assign var='config' value=$rte_options->config_for($simpleurl)}

{include_javascript file='nodetree.js'}
{include_javascript file='nodes_select.js' lib=true}

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
    {foreach $rte_options->plugin_list() as $name => $path}
    CKEDITOR.plugins.addExternal({$name|json}, {$path|json}, '');
    {/foreach}
    var editor_{$RTEhtmlID} = CKEDITOR.replace({$RTEformname|json}, {$config|json});
</script>

