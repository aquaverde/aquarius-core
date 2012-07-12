<?php

function aqua_fputcsv($filePointer, $dataArray, $delimiter, $enclosure){
    // Write a line to a file
    // $filePointer = the file resource to write to
    // $dataArray = the data to write out
    // $delimeter = the field separator
    
    // Build the string
    $string = "";
    $writeDelimiter = FALSE;
    foreach($dataArray as $dataElement){
    if($writeDelimiter) $string .= $delimiter;
    $string .= $enclosure . $dataElement . $enclosure;
    $writeDelimiter = TRUE;
    } // end foreach($dataArray as $dataElement)
    
    // Append new line
    $string .= "\n";
    
    // Write the string to the file
    fwrite($filePointer, $string);
    
} // end function fputcsv($filePointer, $dataArray, $delimiter)

/** Filter array by key
  * Like array_filter(), but filters on keys instead of values. */
function array_kfilter($input, $callback=false) {
    $result = array();
    foreach($input as $key => $value) {
        if ($callback) $keep = call_user_func_array($callback, array($key));
        else $keep = (bool)$key;
        if ($keep) $result[$key] = $value;
    }
    return $result;
}

/** Replace the values in the $base array with the values of the keys in the $in array.
* Returns the replaced values.
* This function has been renamed from array_replace  because PHP 5.3 brings its own array_replace function. */
function array_replace_aqua(&$base, $in) {
    $out = array();
    foreach($in as $key=>$val) {
        if (isset($base[$key])) $out[$key] = $base[$key];
        $base[$key] = $val;
    }
    return $out;
}


/** Convert an associative array to an object.
  * @param $array the array to be converted
  * @param $recurse=false optional argument to enable conversion of inner arrays
  * @return Object whith properties named after the array's keys containing the corresponding values. */
function array_to_object($array, $recurse = false) {
    $object = new stdClass();
    
    foreach ($array as $key => $value) {
        // Make sure key is a valid variable name
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key)) {
            if (is_array($value) && $recurse) {
                $object->$key = array_to_object($value);
            } else {
                $object->$key = $value;
            }
        } else {
            Log::warn("Ignoring key '$key', not a valid identifier");
        }
    }
    return $object;
}

/** Convert an object to an associative array.
  * @param $object the object to be converted
  * @param $recurse=false optional argument to enable conversion of inner objects
  * @return Array with keys set to object's properties. */
function object_to_array($object, $recurse = false) {
    $array = array();
    foreach ($object as $property => $value) {
        if (is_object($value) && $recurse) {
            $array[$property] = object_to_array($value);
        } else {
            $array[$property] = $value;
        }
    }
    return $array;
} 

/** Remove one array nesting level
  * @param $array an array of arrays (elements that are not arrays are ignored)
  * @return nested arrays merged together
  * Example:
  * <pre>
  * array_flatten(array(
  *      0 => array(1,2)
  *      1 => array(2,3,'glog'=>'efg')
  *  'bla' => array('glog'=>'glub')
  *      2 => 'ignore me'
  *    )
  * ==
  * array(0=>1,1=>2,2=>2,3=>3,'glog'=>'glub');
  * </pre>
  */
function array_flatten($array) {
    $flat = array();
    foreach ($array as $nested_array)
        if (is_array($nested_array))
            $flat = array_merge($flat, $nested_array);
    return $flat;
}

/** Create dictionary from list of objects and dictionaries(assoc arrays) by using a field as key.
  * Entries that are not objects or arrays are ignored.
  * Example:
  *   $fruits = array(
  *     array('name' => 'apple', 'id' => 5, 'colour' => 'green'),
  *     array('name' => 'pear', 'id' => 67, 'colour' => 'green'),
  *     array('name' => 'orange', 'id' => 2, 'colour' => 'orange'),
  *   )
  *
  *   dict_by_field($fruits, 'id') => array(
  *     5 => array('name' => 'apple', 'id' => 5, 'colour' => 'green'),
  *     67 => array('name' => 'pear', 'id' => 67, 'colour' => 'green'),
  *     3 => array('name' => 'orange', 'id' => 2, 'colour' => 'orange'),
  *   )
  *
  *   dict_by_field($fruits, 'colour') => array(
  *     'green' => array('name' => 'pear', 'id' => 67, 'colour' => 'green'),
  *     'orange' => array('name' => 'orange', 'id' => 2, 'colour' => 'orange'),
  *   )
  **/
function field_dict($list_of_dicts, $key_field) {
    $map = array();
    foreach($list_of_dicts as $dict) {
        if (is_object($dict)) {
            $map[$dict->$key_field] = $dict;
        } else if (is_array($dict)) {
            $map[$dict[$key_field]] = $dict;
        }
    }
    return $map;
}

function checkEmail($Email, $doDeepCheck = true) {
   require_once("Validate.php");
   return Validate::email($Email);
}
	
/** clean_magic() (TM) cleans incoming $thing (strings and arrays with nested strings) from stupid magic quotes.
 * Removes quotes only if get_magic_quotes_gpc() is on.*/
function clean_magic($thing) {
    if (get_magic_quotes_gpc())
        if (is_string($thing))
            $thing = stripslashes($thing);
        else if (is_array($thing)) {
            $newthing = array();
            foreach($thing as $key=>$value) $newthing[clean_magic($key)] = clean_magic($value);
            $thing = $newthing;
        }
    return $thing;
}

/** Prepend a prefix to the filename in a path.
  * Example: file_prefix("/around/here/picture.jpg", "th_") => "/around/here/th_picture.jpg" */
function file_prefix($path, $prefix) {
    return dirname($path).'/'.$prefix.basename($path);
}

/** 
 * Convert a thing to a string. Works like strval(), but uses __toString() for objects. strval() does this since PHP 5.2, but we can't rely on having a recent PHP.
 return string
 */
function str($thing) {
    if (is_object($thing) && method_exists($thing, '__toString'))
        return $thing->__toString();
    else
        return strval($thing);
}

//-------------------------------------------------------------------------------------------------------------------
function WriteSize($pict)
{
	$size = GetImageSize($pict) ;
	return $size[3] ;
}
	
/** Get an array item or $default if it's not defined (this function does not generate an 'index undefined' warning)
*/
function get($array, $name, $default = false) {
	if (isset($array[$name]))
		return $array[$name];
	else
		return $default;
}

/** Get a value out of nested arrays
  * @param $conf nested array
  * @param $path path to the config value, separated by slashes ('/')
  * @param $default Optional default value if item is not found, defaults to null
  *
  * Example:
  * $conf = array(
  *     'frontend' => array(
  *         'domain' => 'example.org',
  *         'redirect_domains' => array(
  *             'search.example.org' => 'search',
  *             'products.example.org' => 'products'
  *         )
  *     ),
  *     'backend' => array(
  *         'filemanager' => array(
  *             'max_size' => 100 * 1024,
  *             'base_dir' => 'public_html/files/'
  *         )
  *     )
  * );
  *
  * // Get frontend domain
  * $frontend_domain = conf($conf, 'frontend/domain');
  *
  * // Get all filemanager config vals
  * $filemanager_conf = conf($conf, 'backend/filemanager');
  *
  * // Get max_size
  * $max_size = conf($conf, 'backend/filemanager/max_size');
  * // alternatively
  * $max_size = conf($filemanager_conf, 'max_size');
  */
function conf($conf, $path, $default = null) {
    $path_items = array_filter(explode('/', $path));
    foreach($path_items as $path_item) {
        if (is_array($conf) && isset($conf[$path_item])) {
            $conf = $conf[$path_item];
        } else {
            return $default;
        }
    }
    return $conf;
}

/** Filter a dicitionary to only contain named entries and check for their existence
  * @param $dict to be filtered
  * @param $filter dictionary that looks like the expected $array, values specify allowed types of the expected value
  * @param &$errors Add field names of invalid or missing fields to this array (errors are silently ignored if this is not set)
  * @param $keep Whether to keep values not mentioned in filter (default: false)
  * @return Filtered $dict
  * Currently allowed types are:
  * 'string' => value is a nonempty string
  * 'int' => value is an int (accepts and rounds down floats)
  * 'float' => value is a float (accepts ints)
  * 'bool' => value is a boolean (0 or 1, actually)
  * 'object' => value is an object
  * 'array' => value is an array (matches empty and not set and always sets value to array)
  * 'empty' => value is empty -> null
  * 'isset' => value is set -> true
  * 'notset' => value is not set -> null
  * More types such as 'email' may be added. The difference between 'isset' and 'empty' is that 'isset' does not care what the value is, so long as it's present, it just replaces the value by boolean true. 'empty' requires the parameter to be empty, that is, not contain anything, and it sets the value to null. 'notset' on the other hand accepts any value, even unset ones, and just sets them to null.
  *
  * The idea is that you can chain types until one matches, which will then be used as value. Examples:
  *  'int'        accepts '0', 0, '-1234', '234' but not '' or missing values
  *  'int notset' accepts everything 'int' alone accepts, but does not require the value to be present
  *
  *  $example_dict = array('anint'=>'123', 'astring'=>'gotit', 'empty'=>'', 'exists'=>null)
  *
  *  validate($example_dict, array(
  *    'astring' => 'string'
  *  )); => array('astring' => 'gotit')
  *
  *  validate($example_dict, array(
  *    'astring' => 'string'
  *    'anint'   => 'int'
  *  )); => array('astring' => 'gotit', 'anint' => 123)
  *
  *  validate($example_dict, array(
  *    'astring' => 'string'
  *    'anint'   => 'string'
  *  )); => array('astring' => 'gotit', 'anint' => '123')
  *
  *  validate($example_dict, array(
  *    'exists' => 'isset'
  *  )); => array('exists' => true)
  *
  *  $errors = array();
  *  validate($example_dict, array(
  *    'somethingelse' => 'isset'
  *  ), $errors); => $errors contains 'somethingelse'
  *
  *  validate($example_dict, array(
  *    'anint'         => 'isset notset'
  *    'somethingelse' => 'isset notset'
  *  )); => array('anint' => true, 'somethingelse' => null)
  *
  */
function validate($dict, $filter, &$errors = array(), $keep=false) {
    if ($keep) $validated = $dict;
    else $validated = array();
    foreach($filter as $key => $typestr) {
        $value = null;
        $isset = isset($dict[$key]);
        if ($isset) $value = $dict[$key];
        $empty = empty($value);
        $valid = false;
        $types = explode(' ', $typestr);
        while(!$valid && $type = array_shift($types)) {
            switch($type) {
            case 'string':
                $valid = !$empty && is_string($value);
                break;
            case 'int':
                if (!$empty && is_numeric($value)) {
                    $valid = true;
                    $value = intval($value);
                }
                break;
            case 'float':
                if (!$empty && is_numeric($value)) {
                    $valid = true;
                    $value = floatval($value);
                }
                break;
            case 'bool':
                if (!$empty) {
                    $valid = true;
                    $value = (bool)$value;
                }
                break;
            case 'object':
                if (is_object($value)) {
                    $valid = true;
                }
                break;
            case 'array':
                $valid = $isset;
                if ($valid && !is_array($value)) {
                    $value = array();
                }
                break;
            case 'empty':
                $valid = $isset;
                if ($valid) $value = null;
                break;
            case 'isset':
                $valid = $isset;
                if ($valid) $value = true;
                break;
            case 'notset':
                $valid = true;
                $value = null;
                break;
            default:
                throw new Exception("Unknown type $type");
            }
        }

        if ($valid) $validated[$key] = $value;
        else $errors[] = $key;
    }
    return $validated;
}

/** Same as validate(), but throws exception on errors */
function validate_or_die($dict, $filter) {
    $errors = array();
    $validated = validate($dict, $filter, $errors);
    if (!empty($errors)) throw new Exception('Invalid value for '.join(', ', $errors));
    return $validated;
}

/** Throw Exception if first parameter is (===) false or null, else return it.
  * @param $required the value that must be something else than false or null
  * @param $message Exception message, optional
  * If additional parameters are given, $message is sprintf'd with them. Example:
  *   $looking_for = 'home';
  *   $node = or_die(db_Node::get_node($looking_for), "Unable to find node %s", $looking_for); */
function or_die($required, $message=false) {
    if ($required === false || $required === null) {
        if (func_num_args() > 2) {
            $message = call_user_func_array('sprintf', array_slice(func_get_args(), 1));
        } else if ($message === false) {
            $message = "Missing value";
        }
        throw new Exception($message);
    }
    return $required;
}

/** get the first entry of an array, or null if it's not an array or empty
  * Just a convenience wrapper around reset() with a better name. (In the unlikely case that you are using an array pointer and were tripped by the use of reset(): stop using array pointers.) */
function first($a) {
    if (is_array($a) && !empty($a)) return reset($a);
    return null;
}

/** Read a $_REQUEST variable by $name and return its value or $default if it's not defined.
 * Uses clean_magic() (TM).
   @see clean_magic()
*/
function requestvar($name, $default = false) {
    return clean_magic(get($_REQUEST, $name, $default));
}

function convert_chars($str, $fill_char="_"){
    
    $chars = array(
        'ä'=>'a',
        'ö'=>'o',
        'ü'=>'u',
        'Ä'=>'A',
        'Ö'=>'O',
        'Ü'=>'U',
        'à'=>'a',
        'è'=>'e',
        'é'=>'e',
        'Ç'=>'C',
        'ç'=>'c',
        'â'=>'a',
        'ê'=>'e',
        'ô'=>'o',
        'û'=>'u',
        'î'=>'i',
        'ï'=>'i',
        'É'=>'E',
        'È'=>'E',
        'À'=>'A',
        'Â'=>'A',
        'Ê'=>'E',
        'Î'=>'I',
        'Ô'=>'O',
        'Û'=>'U',
        '?'=>$fill_char,
        '/'=>$fill_char,
        ' '=>$fill_char,
        chr(39)=>$fill_char,
        chr(34)=>$fill_char,
        "'"=>'',
    );
    $txt = trim(strtr($str, $chars));

    $txt = preg_replace('/[^A-Za-z0-9_\.-]/', '', $txt);

    return $txt;
}

function convert_chars_url($txt) {
    $txt = trim($txt);
    $txt = preg_replace('/[\'"\?]/', '', $txt);
    $txt = convert_chars($txt, '-');
    $txt = preg_replace('/[^A-Za-z0-9_-]/', '', $txt);
    $txt = preg_replace('/--/', '-', $txt);
    $txt = preg_replace('/--/', '-', $txt);
    return $txt;
}

/** Read the lang directory to get the available interface wordings */
function getInterfaceLanguages() {
    global $aquarius;
	$dir = new DirectoryIterator($aquarius->core_path."lang");
	$interfaceLgs = array();
	
	while($dir->valid()) {
		$file = $dir->getFilename();
		if (strpos($file, ".") !== 0) {
            $myLang = substr($file,0,2);
            $interfaceLgs[$myLang] = $myLang;
		}
		
		$dir->next();
	}
	
	
	return $interfaceLgs;
}

// Mailing Function to prevent mail header injection.
// Rejects (returns false) the mail if $to or $subject contains a newline or carriage return.
// Additional headers must be passed as array, newlines are not allowed in the header lines.
// If the headers are ok, the function passes all arguments to PHP's native mail() function.
// Note that additional headers must be passed as array of lines, not as string with multiple lines
function nmail($to, $subject, $message, $headers=array()) {
  if (ereg("[\r\n]", $to.$subject)) return false; // Reject if there are newlines in $to or $subject (PHP's mail seems to parse $to, but there's still no need to have newlines in it)

  $headerstr = "";
  foreach ($headers as $headerln) {
	if (ereg("[\r\n]", $headerln)) return false; // Reject if there are newlines in a header line
	else $headerstr .= $headerln."\n";
  }      
  return mail($to, $subject, $message, $headerstr);
}

/** returns an array of all items of a table (15.jan.07 - stf) */
function getList($prototype) {
	$list = array();
	$prototype->find();
	while ($prototype->fetch()) {
		$list[] = clone($prototype);
	}
	
	return $list;
}

/** Search the $array for an object (or array) that has an $attribute (resp. key) equal to the given $value, the index (key) of the first match is returned. 
 * Example: $array = array(
 *            array('id'=>12),
 *            array('id'=>13),
 *            array('id'=>14)
 *          );
 *          indexOfAttr($array, 'id', 13) == 1 || die('NEVAR');
 * Returns -1 if there's no match (there's the possibility that the array has a key -1, but that's YOUR Problem)
 */
function indexOfAttr($array, $attribute, $value) {
	foreach($array as $key=>$object) {
		if (is_object($object)) if ($object->$attribute === $value) return $key;
		elseif (is_array($object)) if ($object[$attribute] === $value) return $key;
		// Ignore other stuff
	}
	return -1;
}

/** Same as strcmp, but compares integers
  * Do not use this if you straddle -INT_MAX */
function intcmp($i1, $i2) { return intval($i1) - intval($i2); }

/** Generate a hash that can be used as 'shared secret' between backend and frontend.
  * It's quite a weak secret, the main purpose is to 'identify' users that are working in the backend so that they can preview inactive content in the frontend. We include the IP address so that its use is limited to hosts 'close' to the backend user. (We want, for example, to prevent the preview URL's with hidden content showing up in a search engine listing) */
function preview_hash($base) {
    return md5($_SERVER['REMOTE_ADDR'].$base);
}

/** Parse date string into UNIX epoch timestamp 
  * @param $datestr Date as string either in the format of the config variable DATE_FORMAT or in strtotime() format.
  * @param $format=DATE_FORMAT optional format to use instead of DATE_FORMAT. strtotime() is still attempted if this fails.
  * @param $use_strtotime=true optional flag to disable strtotime parsing
  * @return epoch timestamp or null if parsing failed. (If you are reading this in 2038, on a 32-bit platform, I do not want to be you)
  * To make parsing a little bit more human friendly, even if DATE_FORMAT specifies a four-digit year, a two digit year is accepted as well. Strtotime() allows the use of convenient expressions like 'now' or '+1 week', unfortunately there's no localized version of it. */
function parse_date($datestr, $format=DATE_FORMAT, $use_strtotime=true) {
    $date = null;
    $t = false;
    if (!empty($datestr)) {
        $t = strptime($datestr, $format);
        if ($t == false || $t['tm_year'] < 0) $t = strptime($datestr, strtr($format, 'Y', 'y')); // Hack: retry parsing date with two-digit year
    }
    if ($t != false) {
        // Nasty error in PHP versions < 5.1.7 leaves strptime() fields uninitialized
        if (version_compare(phpversion(), '5.1.7') < 0) {
            // This means the function cannot be used to parse times on those PHP versions.
            $t['tm_hour'] = $t['tm_min'] = $t['tm_sec']= 0;
        }
        // There are systems where strptime() is returning years counting from 1900 (eg. 109 for 2009) without mktime() recognizing this. 
        if ($t['tm_year'] < 1000) {
            $t['tm_year'] += 1900;
        }
        $date = mktime($t['tm_hour'], $t['tm_min'], $t['tm_sec'], $t['tm_mon']+1, $t['tm_mday'],$t['tm_year']);
    }

    if (!$date && $use_strtotime) {
        // Try with relative date parsing
        $date = strtotime($datestr);
        if ($date === false) $date = null;
    }
    return $date;
}


/** Turn value into JSON value (JavaScript Object Notation)
  * @deprecated please use native function json_encode.*/
function json($thing) {
    return json_encode($thing);
}

/* JSON support for legacy PHP. */
if (!function_exists('json_encode')) {
    function json_encode($thing) {
        require_once "lib/JSON.php";
        $conv = new Services_JSON();
        return $conv->encode($thing);
    }
}

if (!function_exists('json_decode')) {
    function json_decode($thing, $assoc = false) {
        require_once "lib/JSON.php";
        $conv = new Services_JSON($assoc? SERVICES_JSON_LOOSE_TYPE : 0);
        return $conv->decode($thing);
    }
}


/** Get value from LazyCache object, or just return it if it's anythiong else */
function force($thing) {
    if ($thing instanceof LazyCache) return $thing->get();
    return $thing;
}

/** Ensure all output is flushed and exit
  * Believe it or not we had instances of empty pages because very short content was not sent to the user agent. */
function flush_exit() {
    while(@ob_end_flush());
    flush();
    exit();
}

/** Turn PEAR errors into standard exceptions */
function pear_error_to_exception($err) {
    // Big, steaming-pile-of-shit HACK
    if ($err instanceof DB_DataObject_Error and $err->code == -2) {
        $offending_call = $err->backtrace[5];
        Log::warn('ignoring staticGet() error around '.$offending_call['file'].':'.$offending_call['line'].'. In the future, an exception will be thrown! (Better fix it now)');
        return false;
    }

    throw new AquaException(array($err->getMessage(), $err->getUserInfo()));
}

/** Handle exception */
function process_exception($exception) {
    // Ensure no partial output hanging around, but only if we're not debugging
    global $echolevel;
    if ($echolevel >= Log::INFO) {
        // Remove all content from output buffers
        while(@ob_end_clean());
    }
    
    // Set proper status code so that user agents (and robots) act accordingly
    header("HTTP/1.1 500 Failed processing request");

    // Informal message for user
    echo '<html>
    <body>
        <div style="border: 2px solid #FBC2C4; background-color: #FBE3E4; color:#8A1F11; margin: 15px; padding: 12px; width: 50%; font-size: 12px; background-image: url(picts/error.png); background-repeat: no-repeat; background-position: 8px 10px; padding-left: 70px;">
        <b>Failed processing request</b><br/><i>Error message:</i></br>'.$exception->getMessage().'
        </div>
        <!--
        
'.error_haiku().'
        
        -->
    </body>
</html>';
    flush();

    // Hope the system is in a state that allows writing to the log file (This is done last since it may fail itself)
    Log::fail($exception);

    exit();
}


function handle_fatal_errors() {
    $error = error_get_last();
    if ($error['type'] == E_ERROR) {
        process_exception(new Exception("Fatal error in ".basename($error['file'])." line ".$error['line'].": ".$error['message']));
    }
    if ($error['type'] == E_COMPILE_ERROR) {
        $message = preg_replace('%\\(include_path=.*\\)%', '', $error['message']); // Drop include_path information so visitors don't get so much information about paths
        process_exception(new Exception("Compile error in ".basename($error['file'])." line ".$error['line'].": ".$message));
    }
    if ($error['type'] == E_PARSE) {
        process_exception(new Exception("Parse error in ".basename($error['file'])." line ".$error['line'].": ".$error['message']));
    }
}

/** Wrap classes transparently into another object
  * Useful for decorators. */
class Wrapper {
    var $__baseobject;

    /** Create a wrapper around base object
      * @param base object to be contained in wrapper */
    function __construct($base) {
        $this->__baseobject = $base;
    }

    public function __get($member) {
        return $this->__baseobject->$member;
    }

    public function __set($member, $value) {
        $this->__baseobject->$member = $value;
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this->__baseobject, $name), $arguments);
    }

    public function __toString() {
        return $this->__baseobject->__toString();
    }
}

/** Exception class that allows adding additional information */
class AquaException extends Exception {
    var $detail_message = "";
    
    function __construct($messages) {
        if (is_array($messages)) {
            $message = array_shift($messages);
            $this->detail_message = array_shift($messages);
        } else {
            $message = $messages;
        }
        parent::__construct($message);
    }
    
    function getDetailMessage() {
        return parent::getMessage().'. '.$this->detail_message;
    }
}

/** Return a haiku related to computer errors */
function error_haiku() {
    // http://www.funny2.com/haiku.htm
    $hai = <<<EOH
Rather than beep
Or a rude error message:
These words: "File Not Found".

Errors have occurred.
We won't tell you where or why -
Lazy programmers!

Chaos reigns within.
Reflect, repent, and reboot
Order will return.

For a new PC,
Center of my universe,
I abandon all.

The code was willing!
It considered your request,
But the chips were weak.

Everything is gone.
Your life's work has been destroyed.
Squeeze trigger? (yes/no)

A file that big?
It might be very useful.
But now it is gone.

No keyboard present
Hit F1 to continue
Zen engineering?

Website has been moved
We'd tell you where, but then we'd
Have to delete you.

The web site you seek
Cannot be located but
Countless more exist.

Aborted effort:
Close all that you have worked on.
You ask way too much.

Yesterday it worked.
Today it is not working.
Aquarius is like that.

Printer not ready.
Could be a fatal error.
Have a pen handy?

With searching comes loss
And the presence of absence:
"My novel" not found.

The Tao that is seen
Is not the true Tao, until
You bring fresh toner.

Stay the patient course.
Of little worth is your ire.
The network is down.

A crash reduces
Your expensive computer
To a simple stone.

Three things are certain:
Death, taxes, and lost data.
Guess which has occurred.

Seeing my great fault
Through darkening blue windows
I begin again.

You step in the stream,
But the water has moved on.
This page is not here.

Out of memory.
We wish to hold the whole sky,
But we never will.

10,000 Things
How long do any persist?
Explorer is gone.

Server: poor response
Not quick enough for browser
Time out, plum blossom.

Having been erased,
The document you're seeking
Must now be retyped.

Serious error.
All shortcuts have disappeared.
Screen. Mind. Both are blank.
EOH;
    $hais = preg_split("/\n\n/", $hai);
    return $hais[array_rand($hais)];
}
