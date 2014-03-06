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


function clean_dict(dict) {
    var clean = {}
    for (var key in dict) {
        if (dict[key]) clean[key] = dict[key]
    }
    return clean
}


// jQuery noConflict section
;(function($) {
	$(function() {
        
        $("[title]").tooltip({
            placement: 'top',
            delay: {show: 500, hide: 100}
        });
        
        var is_dragging = false;
        $(".nodetree_root").sortable({
            items: "li:not(.nodetree_plus)",
            revert: true,
            axis: "y",
            grid: [30,15],
            cursor: "move",
            handle: ".move",
            start: function(event, ui) {
                is_dragging = true;
                
                var moved = ui.item.data('node');
                var req_class = '.accepts_' + ui.item.data('form')
                //$(req_class).css({border: '1px dashed green'});
            },
            stop: function(event, ui) {
                is_dragging = false;
                $(".nodetree_helper").hide();
                
                var moved = ui.item.data('node')
                var new_parent = ui.item.parents('ul').data('parent')
                var new_prev = ui.item.prev().data('node')
                
                var container = ui.item.parent().closest('.nodetree_root')
                container.find('ul').css({'border': 'none'})
                
                //nodetree.moveorder(moved, new_parent, new_prev)
            },
            beforeStop: function(event, ui) {
                $(".nodetree_helper").hide();
            },
        }).on('mousemove', function(e) {
            $("ul", this).each(function() {
                var $this = $(this),
                    thisPosTop = $this.offset().top,
                    thisH = $this.height(),
                    $helper = $this.find(".nodetree_helper"),
                    $nodetreePlus = $this.find(".nodetree_plus");

                if (is_dragging) {
                    var $sorthelper = $(".ui-sortable-helper"),
                        helperPosTop = $sorthelper.offset().top;
                                        
                    if (helperPosTop >= thisPosTop && helperPosTop <= parseInt(thisPosTop + thisH)) {
                        //if ($this.children().length < 3) $nodetreePlus.removeClass('nodetree_plus');
                        $sorthelper.css('left', $this.children().offset().left -15)
                        console.log('in')
                    }
                    else {
                        //if ($this.children().length < 3) $nodetreePlus.addClass('nodetree_plus');
                        $sorthelper.css('left','auto')
                        console.log('out')
                    }
                }
            });
        });
        
        $(".nodetree_toggle").on('click', function() {
            var $this = $(this);
            
            if ($this.hasClass('expand')) {
                nodetree.update($this.data('node'), {open: 0});
                $this.removeClass('expand').addClass('contract');
            }
            else {
                nodetree.update($this.data('node'), {open: 1});
                $this.removeClass('contract').addClass('expand');
            }
        });

        $(".dropdown-toggle").dropdown();
		
		window.setTimeout(function() {
			$(".alert-success").fadeTo(500, 0).slideUp(500, function() {
				$(this).remove(); 
			});
		}, 2500);
        
	});
})(jQuery);
