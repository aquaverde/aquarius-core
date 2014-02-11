/* Opens a popup window */
function openBrWindow(theURL,winName,features) { //v2.0
  mypop = window.open(theURL,winName,features);
  mypop.focus();  //fixed focus bug
}

/* Open a popup that will be closed when the window is unloaded */
var opening_popup = false
function open_attached_popup(url, title, params) {
    if (opening_popup) return false /* double-click legacy */
    opening_popup = true
    var pop = window.open(url, title, params)
    Event.observe(window, 'unload', function() { pop.close() })
    pop.focus()
    opening_popup = false
    return false
}

/* ??? find out where this is used */
function updateList(list, textBox) {
  textBox.value = '';
  for(i = 0; i < list.options.length; i++) {
    if (i == 0) {
      textBox.value += list.options[i].value;
    } else {
      textBox.value += ';' + list.options[i].value;
    }
  }
}

/* checks or unchecks all checkboxes of a given form */
function selectAll(myForm, myChk) {
	for ( var i = 0 ; i < myForm.length; i++ ) {
		if (myForm[i].type == "checkbox" ) {
			myForm[i].checked = myChk.checked;
		}
	}
}

/* sets all checkboxes within an element */
function setAllCheckboxes(element, checked) {
    for (i in element.childNodes) {
        var child = element.childNodes[i]
        if (child.type == 'checkbox')
            child.checked = checked
        else
            setAllCheckboxes(child, checked)
    }
}

/* Update a select form field to have the option with the given value selected
   If the field has no option with that value, nothing is changed. If there are multiple options with that value, the first is chosen.
*/
function select_by_value(select_field, value) {
    var options = select_field.options
    for (var i = 0; i < options.length; i++) {
        if (options[i].value == value) {
            select_field.selectedIndex = i
            return true
        }
    }
    return false
}

/* Get selected value from selection dropdown
   Returns empty string if nothing is selected
*/
function select_value(select_field) {
    if (select_field.selectedIndex >= 0) {
        return select_field.options[select_field.selectedIndex].value
    }
    return ''
}


/* Compatibility functions for necessary array operations below */

// http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Objects:Array:forEach
if (!Array.prototype.forEach)
{
  Array.prototype.forEach = function(fun /*, thisp*/)
  {
    var len = this.length;
    if (typeof fun != "function")
      throw new TypeError();

    var thisp = arguments[1];
    for (var i = 0; i < len; i++)
    {
      if (i in this)
        fun.call(thisp, this[i], i, this);
    }
  };
}

// http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Objects:Array:filter
if (!Array.prototype.filter)
{
  Array.prototype.filter = function(fun /*, thisp*/)
  {
    var len = this.length;
    if (typeof fun != "function")
      throw new TypeError();

    var res = new Array();
    var thisp = arguments[1];
    for (var i = 0; i < len; i++)
    {
      if (i in this)
      {
        var val = this[i]; // in case fun mutates this
        if (fun.call(thisp, val, i, this))
          res.push(val);
      }
    }

    return res;
  };
}

// http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Objects:Array:map
if (!Array.prototype.map)
{
  Array.prototype.map = function(fun /*, thisp*/)
  {
    var len = this.length;
    if (typeof fun != "function")
      throw new TypeError();

    var res = new Array(len);
    var thisp = arguments[1];
    for (var i = 0; i < len; i++)
    {
      if (i in this)
        res[i] = fun.call(thisp, this[i], i, this);
    }

    return res;
  };
}

// homebrew (http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Reference:Objects:Array:indexOf)
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function(thing) {
    for (var i = 0; i < this.length; i++) {
      if (thing === this[i]) return i;
    }
    return -1;
  };
}

// Convert HTMLCollection list to a common array
function collectionToArray(col) {
    a = new Array();
    for (i=0; i<col.length; i++) a[i] = col[i];
    return a;
}

function treewalk(element, proc) {
    proc(element);
    collectionToArray(element.childNodes).forEach(function(element) {treewalk(element, proc)})
}

function check_all(element, state){
    treewalk(element, function(child) {
        if (child.type == 'checkbox') child.checked = state
    })
}

function hideOuter() {
    document.getElementById('outer').style.display = "none";
    document.getElementById('outer-closed').style.display = "block";		
}
function showOuter() {
    document.getElementById('outer').style.display = "block";
    document.getElementById('outer-closed').style.display = "none";		
}

function clean_dict(dict) {
    var clean = {}
    for (var key in dict) {
        if (dict[key]) clean[key] = dict[key]
    }
    return clean
}

jQuery( document ).ready(function() {
    jQuery('[data-toggle]').addClass('tooltipp').tooltip();
    
    window.setTimeout(function() {
        jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function(){
            jQuery(this).remove(); 
        });
    }, 2500);
    
});
