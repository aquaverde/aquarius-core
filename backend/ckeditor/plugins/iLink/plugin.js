(function(){
    var link_dialog_command = {  
        exec:function(editor){  
            window.ilink_callback = function(target_id, selected_nodes) {
                var node_id;
                var node_title;
                for (id in selected_nodes) {
                    node_id = id;
                    node_title = selected_nodes[id];
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
            } // END OF FUNCTION
            
            if( editor.getSelection() != null 
                && editor.getSelection().getStartElement().getAttribute('href') != null
                && editor.getSelection().getNative() != '') 
            {       
                var aqua_link = editor.getSelection().getStartElement().getAttribute('href');
                var aqua_array = aqua_link.split(":");
                var aqua_node_id = aqua_array[1];
                
                if(aqua_node_id)
                    var media = window.showModalDialog(ilink_select+"&selected="+aqua_node_id,window,"dialogHeight=600px; dialogWidth=500px; center=yes; resizable=yes");
                else 
                    var media = window.showModalDialog(ilink_select,window,"dialogHeight=600px; dialogWidth=500px; center=yes; resizable=yes");
            }
            else {
                var media = window.showModalDialog(ilink_select,window,"dialogHeight=600px; dialogWidth=500px; center=yes; resizable=yes");              
            }
            window.ilink_callback = false;     
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