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
                if (node_id) {
                    var selection = editor.getSelection();

                    var range = editor.getSelection().getRanges()[0];
                    var link = editor.document.createElement('a')
                    link.setAttribute('href', 'aquarius-node:'+node_id);
                    link.append(range.extractContents());

                    if (link.getText().length == 0) {
                        link.append(new CKEDITOR.dom.text(node_title));
                    }

                    range.insertNode(link);
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