{literal}
/* global legend file extension
 * loads legend when file is selected */
fileselectors.register_event_listener({
    update_file: function(fileselector) {
        var update_legend = function(legend_text) {
            fileselector.clear_legend()
            fileselector.legend.value = legend_text
        }

        var filepath = fileselector.file.value
        var file = filepath.substring(filepath.lastIndexOf('/') + 1);
        var subdir = fileselector.get_subdir(filepath)

        new Ajax.Request(
            '{/literal}{url url=$simpleurl action=$field.legend_load_action escape=false}{literal}',
            {   method: 'get'
            ,   onSuccess: function(transport) { update_legend(transport.responseText) }
            ,   parameters: { formfield: fileselector.manager.formfield,
                              file: file,
                              subdir: subdir
                            }
            }
        )
    }
})
{/literal}