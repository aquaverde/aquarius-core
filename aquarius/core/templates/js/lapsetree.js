/* Usage:
    <li id="some_unique_id_4452" class="lapsible">
        <ul id="some_unique_id_4452_children">
            ...
        </ul>
    </li>
    <script>
        new LapseTree('name-for-cookie').init();
    </script>
*/

function LapseTree() {
    /* constructor, currently empty */
}

LapseTree.prototype = {
    cookie_name: "lapse_state",
    lapsible_class: "lapsible",
    lapsible_element: "li",
    lapse_switch_image: {
        open: "picts/lapse2-open.gif",
        closed: "picts/lapse2-closed.gif"
    },
    lapseswitch_class: 'lapse_switch',
    lapseswitch_id_suffix: '_switch',
    children_id_suffix: '_children'
}

LapseTree.prototype.eachLapsible = function(proc) {
    getElementsByClass(document, this.lapsible_class, this.lapsible_element).forEach(function(element) {
        proc(element);
    });
}
LapseTree.prototype.init = function() {
    var lapse_tree = this;
    this.opened = get_cookie_val(this.cookie_name, '').split(',');
    
    this.eachLapsible(function(element) {
        var switch_id = element.id+lapse_tree.lapseswitch_id_suffix;
        var switch_element = document.createElement('img');
        switch_element.id = switch_id;
        switch_element.src = lapse_tree.lapse_switch_image.closed;
        switch_element.className = lapse_tree.lapseswitch_class;
        switch_element.onclick = function() { lapse_tree.lapse(element.id);}
        element.insertBefore(switch_element, element.firstChild);
        lapse_tree.sync(element);
    });
}
LapseTree.prototype.sync = function(element) {
    
    
    var switch_id = element.id+this.lapseswitch_id_suffix;
    var switch_element = document.getElementById(switch_id);
    var children_element =  document.getElementById(element.id+this.children_id_suffix);
    if (this.is_open(element.id)) {
        switch_element.src = this.lapse_switch_image.open;
        if (children_element) children_element.style.display = 'block';
    } else {
        switch_element.src = this.lapse_switch_image.closed ;
        if (children_element) children_element.style.display = 'none';
    }
}

LapseTree.prototype.is_open = function(id) {
    return this.opened.indexOf(id) >= 0;
}
LapseTree.prototype.lapse = function(id) {

    
    if (this.is_open(id)) {
        this.opened = this.opened.filter(function(open_id) {return id != open_id});
    } else {
        this.opened.push(id);
    }
    this.sync(document.getElementById(id));
    set_cookie_val(this.cookie_name, this.opened.join(','));
}


function getElementsByClass(node,searchClass,tag) {
    var classElements = new Array();
    var elements = node.getElementsByTagName(tag);
    var pattern = new RegExp("(^| )"+searchClass+"( |$)");
    collectionToArray(elements).forEach(function(element) {
        if (pattern.test(element.className)) {
            classElements.push(element);
        }
    });
    return classElements;
}

function set_cookie_val(cookie_name, cookie_value) {
    document.cookie = cookie_name+'='+escape(cookie_value);
}

function get_cookie_val(cookie_name, default_value) {
    var results = document.cookie.match(cookie_name + '=(.*?)(;|$)');
    if (results) return (unescape(results[1]));
    else         return default_value;
}


