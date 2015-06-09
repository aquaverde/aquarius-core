(function(){
    var link_dialog_command = {
        exec: function(editor) {
            var link = ilink_select;
            if( editor.getSelection() != null 
                && editor.getSelection().getStartElement().getAttribute('href') != null
                && editor.getSelection().getNative() != '') 
            {
                var aqua_link = editor.getSelection().getStartElement().getAttribute('href');
                var aqua_array = aqua_link.split(":");
                var aqua_node_id = aqua_array[1];

                if (aqua_node_id) link = link + +"&selected="+aqua_node_id;
            }

            nodes_select(ilink_select, aqua_node_id, function(selected) {
                var node_id;
                var node_title;
                for (id in selected) {
                    node_id = id;
                    node_title = selected[id];
                }

                var name;
                if(node_id) {
                    if (CKEDITOR.env.ie) {
                       name = editor.getSelection().document.$.selection.createRange().text;
                    } else {
                       name = editor.getSelection().getNative();
                    }

                    if(name != '' && name[0] != "<" && name[1] != "!" && name[2] != "-" && name[3] != "-")
                        editor.insertHtml("<a href='aquarius-node:" + node_id + "' >"+name+"</a>");
                    else
                        editor.insertHtml("<a href='aquarius-node:" + node_id + "' >"+node_title+"</a>");
                }
            });
        }
    }

    var name = "iLink";
    CKEDITOR.plugins.add(name, {
        init:function(editor){  
            editor.addCommand(name, link_dialog_command);  
            editor.ui.addButton('iLink',{  
                label:'Intern Link',   
                icon: this.path + 'images/anchor.gif',  
                command:name  
            });  
        }  
    })
})()