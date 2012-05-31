var check = false;

requirefuncs = new Object();

requirefuncs.require_text = function(element) {
    return element.value.length > 0;
}

requirefuncs.require_numeric = function(element) {
    val = parseInt(element.value);
    return element.value.length > 0 && !isNaN(val);
}
requirefuncs.require_email = function(element) {
    return element.value.match(/^[-a-zA-Z0-9!#$%&'*+/=?^_`{|}~.]+@([-a-zA-Z0-9]+\.)+[a-zA-Z]{2,7}$/);
}

requirefuncs.require_zip = function(element) {
    field_ok = element.value.length == 4 ||  element.value.length == 5;
    zip = parseInt(element.value);
    field_ok = field_ok && !isNaN(zip);
    return field_ok;
}

requirefuncs.require_radio = function(element) {
	var elements = document.getElementsByName(element.name) ;
	var field_ok = false ; 
	collectionToArray(elements).forEach(function(elem) {
  		if(elem.checked) field_ok = true ; 
    });
	return field_ok ; 
}

requirefuncs.require_pulldown = function(element) {
	return element.value != '0' ; 
}

requirefuncs.require_checkbox = function(element) {
	var elements = document.getElementsByName(element.name) ;
	var field_ok = false ; 
	collectionToArray(elements).forEach(function(elem) {
  		if(elem.checked) field_ok = true ; 
    });
	return field_ok ; 
}

requirefuncs.require_password = function(element) {
    element_confirm = document.getElementById(element.id+'_confirm');
    return element_confirm && element_confirm.value == element.value;
}

function checkFormHandler(event) {
    var element;
    if(event != undefined) {
        element = event.target;
    } else {
        element = window.event.srcElement;
    }
    checkFormElement(element);
}

function checkFormElement(element, checkedForm) {
    if(!check) {
        return true;
    }
    var label;
    var searchedLabel = element.id + "Label";
    var label = document.getElementById(searchedLabel);
    var classes = element.className.split(" ");
    var i = 0;
    var field_ok;
    classes.forEach(function(myclass) {
        if(requirefuncs[myclass] != undefined) {
            var fieldclasses = element.className.replace(/checkerror/g, "");
            var labelclasses = label && label.className.replace(/checkerror/g, "");
            field_ok = requirefuncs[myclass](element);
            if(field_ok) {
                element.className = fieldclasses;
                if (label) label.className = labelclasses;
                
            } else {
                element.className = fieldclasses + " checkerror";
                if (label) label.className = labelclasses + " checkerror";
            }
            showErrorMessage(document.getElementById(element.id+'Error'), !field_ok);
        }
    });
    return field_ok;
}

function checkFormSubmit(checkedForm) {
    check = true;
    var formOK = true;
    checkedForm.onkeyup = checkFormHandler;
    checkedForm.onclick = checkFormHandler;
    checkedForm.onchange = checkFormHandler ;
    
    var inputElements = getElementsByClass(checkedForm, "require_[a-z]*","input");
    inputElements.forEach(function(elem) {
        if(!checkFormElement(elem, checkedForm)) {
            formOK = false;
        }
    });
    var textareaElements = getElementsByClass(checkedForm, "require_[a-z]*","textarea");
    textareaElements.forEach(function(elem) {
        if(!checkFormElement(elem, checkedForm)) {
            formOK = false;
        }
    });
    var selectElements = getElementsByClass(checkedForm, "require_[a-z]*","select");
    selectElements.forEach(function(elem) {
        if(!checkFormElement(elem, checkedForm)) {
            formOK = false;
        }
    });
    showErrorMessage(document.getElementById("errorMessage"), !formOK);
    return formOK;
}

function showErrorMessage(message, error) {
    if (message) {
        if (error) message.className = "errorVisible";
        else       message.className = "errorInvisible";
    }
}

function isEmailAddr(str) 
{
	return str.match(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/);
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

// Convert HTMLCollection list to a common array
function collectionToArray(col) {
    a = new Array();
    for (i=0; i<col.length; i++) a[i] = col[i];
    return a;
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

if (!Array.prototype.compare) {
	Array.prototype.compare = function(testArr) {
		if (this.length != testArr.length) return false;
		for (var i = 0; i < testArr.length; i++) {
			if (this[i].compare) { 
				if (!this[i].compare(testArr[i])) return false;
			}
			if (this[i] !== testArr[i]) return false;
		}
		return true;
	}
}

function email(name, domain,tld, replace) {
	var link = "<a href='mailto:" + name + "@" + domain + "." + tld + "'>" + name + "@" + domain + "." + tld + "</a>";
    if (replace) $('#'+replace).replaceWith(link);
	else document.write(link);
}


function init() {
	var lis = $('slide-images').getElementsByTagName('li');
	for( i=0; i < lis.length; i++){
		if(i!=0){
			lis[i].style.display = 'none';
		}
	}
	end_frame = lis.length -1;
	start_slideshow(start_frame, end_frame, delay, lis);
}

function start_slideshow(start_frame, end_frame, delay, lis) {
	setTimeout(fadeInOut(start_frame,start_frame,end_frame, delay, lis), delay);
}

function fadeInOut(frame, start_frame, end_frame, delay, lis) {
	return (function() {
		lis = $('slide-images').getElementsByTagName('li');
		Effect.Fade(lis[frame]);
		if (frame == end_frame) { frame = start_frame; } else { frame++; }
		lisAppear = lis[frame];
		setTimeout("Effect.Appear(lisAppear);", 0);
		setTimeout(fadeInOut(frame, start_frame, end_frame, delay), delay + 1850);
	})
}


 var ptitsize = 11
 var grandsize = 13
 var maxSize = 15
 var minSize = 11
 
function plusgrand()
{

 tds = document.getElementsByTagName("div")
 for (x = 0; x < tds.length; x++) {
 	thisTd = tds[x]
 	if (thisTd.id =="content")
 	{
 	if (thisTd.style.fontSize && (size < maxSize))
 	{
 		size = parseInt(thisTd.style.fontSize)+1
 	}else{
 		size = grandsize
 	}
 	thisTd.style.fontSize = size + "px"
 	}
 }
}
function pluspetit()
{

 tds = document.getElementsByTagName("div")
 for (x = 0; x < tds.length; x++) {
 	thisTd = tds[x]
 	if (thisTd.id =="content")
 	{
 	if (thisTd.style.fontSize && (size > minSize))
 	{
 		size = parseInt(thisTd.style.fontSize)-1
 	}else
 	{
	 	size = ptitsize
 	}
 	thisTd.style.fontSize = size + "px"
 	}
 }
}
