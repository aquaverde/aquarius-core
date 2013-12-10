<?php
$url = new Url('admin.php');
?>

/* Dict of fileselectors, required for popup callback */
var fileselectors = {}

/* Callback for file selection popup */
function file_select(id, filename) {
    fileselectors[id].change(filename)
}

/* Manage addition and removal of file selectors (rows) */
function FileSelectorList(formfield, selectors, next_id, popup_action, multi) {
    this.next_id = next_id; // ID counter for new selectors
    this.multi = multi;
    this.formfield = formfield
    this.popup_action = popup_action
    this.fieldName = 'file_table_f'+formfield;
    this.field = $(this.fieldName)
    selectors.forEach(function(id) {
        this.register(id)
    }.bind(this))
    this.event_listeners = []
}

with (FileSelectorList) {
    prototype.register = function (id) {
        fileselectors[id] = new FileSelector(this, id)
    }

    prototype.remove = function (id, fileselector) {
        fileselectors[id] = null
        fileselectors = clean_dict(fileselectors);
        if (fileselectors.length < 1) {
            add_empty_selector()
        }
        fileselector.container.remove()
    }

    prototype.add_empty_selector = function () {
        new_id = this.next_id++
        reg_id = "f" + this.formfield + "_" + new_id;
        new Ajax.Updater(
            this.field.tBodies[0],
            '<?php echo $url->with_param(Action::make('file_ajax', 'empty_row'))->str()?>',
            {   method: 'get'
            ,   insertion: Insertion.Bottom
            ,   onComplete: function() { this.register(reg_id) }.bind(this)
            ,   parameters: { formfield: this.formfield
                            , subdir: ''
                            , new_id: new_id
                            }
            }
        )
    }

    prototype.get_row_above = function(row) {
        var siblings = $(row).previousSiblings();
        if(siblings[0]) {
            var row_id = siblings[0].id;
            var input_file = $(row_id+"_file").value;
            return input_file;
        }
        return false;
    }

    prototype.register_event_listener = function(listener) {
        this.event_listeners.push(listener)
    }

    prototype.fire_event = function(event, param) {
        this.event_listeners.forEach(function(listener) { if (listener[event]) listener[event](param) })
    }
}

/* Creating a ContenteditFileManager automatically sets up event handlers for the fields corresponding to the given id. */
function FileSelector(manager, id) {
    this.manager = manager
    this.id = id // HTML element id
    this.container = $(id)
    this.file = $(id+'_file')
    this.file_label = $(id+'_file_label')
    this.file_choose_button = $(id+'_choose_button')
    if($(id+'_choose_button_th')) this.file_choose_button_left = $(id+'_choose_button_th');
    this.thumb = $(id+'_thumb') // Thumbnail container element
    this.legend = $(id+'_legend') // Legend input element
    if($(id+'_description')) this.description = $(id+'_description') // Description textarea element
    this.delete_button = $(id+'_delete_file') //File delete button

    this.opening_popup = false // Ugly workaround to prevent people from opening two popups by double-clicking

    /* Register event handlers */
    this.file_choose_button.observe('click', this.open_popup.bind(this))
    if($(id+'_choose_button_th')) this.file_choose_button_left.observe('click', this.open_popup.bind(this))
    this.delete_button.observe('click', this.deleteButtonEvent.bind(this));
    this.legend.observe('focus', this.clear_legend.bind(this))
    if($(id+'_description')) 
    {
        this.description.observe('focus', this.clear_description.bind(this));
    }

    $('contentedit-form').observe('submit', this.clear_legend.bind(this))
    $('contentedit-form').observe('submit', this.clear_description.bind(this))
}

function reInitTableDnD(tablename) {
    var table = document.getElementById(tablename);
    var tableDnD = new TableDnD();
    tableDnD.init(table);
}

with (FileSelector) {

    /* Called from external sources to change subdir and file */
    prototype.change = function(file) {
        this.update_file(file)
        
        if($(this.container).hasClassName('last_row') && this.manager.multi) {
            $(this.container).removeClassName('last_row')
        
            this.delete_button.style.display = "block";
            this.legend.style.display = "block";
            this.manager.add_empty_selector();
            
            setTimeout("this.reInitTableDnD('"+this.manager.fieldName+"')", 200);
        }
    }
    
    prototype.deleteButtonEvent = function() {
        this.update_file('');
        var myclass = 'file_tr_' + this.id;		
        var elements = $('file_table_f' + this.manager.formfield).getElementsByClassName(myclass);
        var childs = $('file_table_f' + this.manager.formfield).childElements();
        childs = childs[0].childElements();
        
        var child_count = childs.length;
        var invis_count = 0;		
        for(var j = 0; j < child_count; j++) {
            if(childs[j].style.display == 'none') invis_count++;
        }
        var diff = ((child_count / 2) - (invis_count / 2));
        
        if(diff > 1) {
            for(var i = 0; i < elements.length; i++) {
                elements[i].style.display = 'none';
            }
        }	
    }

    prototype.update_file = function(file) {
        this.file.value = file
        this.update_thumbnail()
        if(file == '') {
            this.file_label.update('')
            this.legend.style.display = 'none';
            if (this.description) this.description.style.display = "none";
        }
        else {
            this.file_label.update(this.get_proper_filename(this.file.value))
            this.legend.style.display = "block";
            if (this.description) this.description.style.display = "block";
        }
        this.manager.fire_event('update_file', this)
    }

    prototype.get_proper_filename = function(file) {
        var arr = file.split("/");
        if(arr.length > 1) return arr[(arr.length - 1)];
        else return file;
    }

    prototype.get_subdir = function(file) {
        var arr = file.split("/");
        var subdir = '';
        if(arr.length > 1) {
            for(var i = 0; i < arr.length - 1; i++) {
                subdir += arr[i];
                if(i != arr.length - 2) subdir += "/";
            }
        }
        return subdir;
    }

    prototype.update_thumbnail = function() {
        new Ajax.Updater(
            this.thumb,
            '<?php echo $url->with_param(Action::make('file_ajax', 'thumb'))->str()?>',
            {   method: 'get'
            ,   parameters: { formfield: this.manager.formfield
                            , file: this.file.value
                            }
            }
        )
    }

    prototype.open_popup = function (event) {
        Event.stop(event)
        if(this.opening_popup) return // Ugly workaround for people that double click
        this.opening_popup = true
        var subdir = this.get_subdir(this.file.value);
        if($(this.container).hasClassName('last_row')) {
            if(this.manager.get_row_above(this.container)) {
                subdir = this.get_subdir(this.manager.get_row_above(this.container));
            }
        }
        url = '<?php echo $url?>?' + this.manager.popup_action+
                    '&file='+encodeURIComponent(this.get_proper_filename(this.file.value))+
                    '&subdir='+encodeURIComponent(subdir)+
                    '&target_id='+this.id
        var pop = window.open(url, "select_file", "height=650,width=600,status=yes,resizable=yes,scrollbars=yes")
        pop.focus()

        /* Ensure the popup is closed when the content is saved */
        Event.observe(window, 'unload', function() { pop.close() })
        this.opening_popup = false
    }


    prototype.clear_legend = function (event) {
        if (this.legend.hasClassName('empty_legend')) {
            this.legend.value = ""
            this.legend.removeClassName('empty_legend');
        }
    }
    
    prototype.clear_description = function (event) {
        if (this.description.hasClassName('empty_legend')) {
            this.description.value = ""
            this.description.removeClassName('empty_legend');
        }
    }
}

/* An AJAX updater subclass that shows a 'wait' cursor as long as the request is going on
* Same as Ajax.Updater with additional constructor parameter wait_area where the cursor will be changed.
* Example: new Ajax.WaitingUpdater(field_to_update, document.body, 'http://whereIGetMyUpdates', params)
*/
Ajax.WaitingUpdater = Class.create(Ajax.Updater, {
initialize: function($super, container, wait_area, url, options) {
    options = Object.clone(options);
    var original_onCreate = options.onCreate;
    options.onCreate = (function(response, json) {
    wait_area.style.cursor = 'wait'
    if (Object.isFunction(original_onCreate)) original_onCreate(response, json);
    }).bind(this);
    var original_onComplete = options.onComplete;
    options.onComplete = (function(response, json) {
    wait_area.style.cursor = 'default'
    if (Object.isFunction(original_onComplete)) original_onComplete(response, json);
    }).bind(this);

    $super(container, url, options);
}
})

