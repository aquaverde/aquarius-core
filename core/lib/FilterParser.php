<?
/** Reads filter sentences and builds statements for custom filter languages.
  * Example:
  *   $parser        = new FilterParser('lib/predicates');
  *   $active_filter = $parser->interpret('is active');
  *   $context       = array('where' => 'top');
  *   $menu_filter   = $parser->interpret('has menu_position $where', $context);
  **/
class FilterParser {
    /** Create a filter parser with optional context
      *   */
    function __construct($dir=false) {
        $this->predicates = array();
        $this->predicate_dir = $dir;
        $this->predicate_cache = array();
    }

    /** Add predicate parser functions
      * Existing predicates of same name will be overridden. */
    function add_predicates(array $predicates) {
        $this->predicates = array_merge($this->predicates, $predicates);
        $this->predicate_cache = array();
    }

    function load_predicate($predicate) {
        if (!$this->valid_identifier($predicate)) {
            $this->fail("Illegal predicate name '$predicate'");
        }
        if (isset($this->predicates[$predicate])) {
            return $this->predicates[$predicate];
        }
        if ($this->predicate_dir) {
            $predicate_path = $this->predicate_dir.'/'.$predicate.'.php';
            $parser_function_source = @file_get_contents($predicate_path, FILE_USE_INCLUDE_PATH);
            if ($parser_function_source) {
                return create_function('$parser', $parser_function_source);
                if (!$parser_function) $this->fail('Error parsing '.$predicate_path);
            }
        }
        $this->fail("predicate '$predicate' is undefined");
    }

    /** Turn a filter sentence into filter statement
      * @param $sentence filter sentence to be interpreted
      * @param $context optional dictionary with variables for filter sentence
      * @return filter object or false for empty filters */
    function interpret($sentence, $context=array()) {
        $this->words             = array_filter(preg_split('/[\s]+/', $sentence));
        if (empty($this->words)) return false;

        $this->context           = $context;
        $this->current_word      = false;
        $this->current_predicate = false;
        $this->statements        = array();
        return $this->parse();
    }

    /** Parses words into statements
      * @return filter object or false if there were no more predicates */
    function parse() {
        while (!empty($this->words)) {
            $statement = $this->parse_predicate();
            if ($statement) array_push($this->statements, $statement);
            // Predicates must be joined by operators. We try to catch errors stemming from missing operators as soon as possible.
            if (count($this->statements) > 1) $this->fail('Operator expected');
        }
        if (empty($this->statements)) $this->fail("No statement");
        return array_pop($this->statements);
    }

    function parse_predicate() {
        $predicate = $this->consume_word();
        $this->current_predicate = $predicate;
        if (!isset($this->predicate_cache[$predicate])) {
            $this->predicate_cache[$predicate] = $this->load_predicate($predicate);
        }
        return call_user_func($this->predicate_cache[$predicate], $this);
    }


    /** Consume one argument from word stack
      * Performs variable substitution from context for words that start with the dollar ($) character */
    function consume_word() {
        if (empty($this->words)) $this->fail("Missing argument");
        $word = array_shift($this->words);
        $this->current_word = $word;
        if ($word[0] == '$') {
            $name = substr($word, 1);
            if (!$this->valid_identifier($name)) $this->fail('illegal variable name');
            if (!isset($this->context[$name])) $this->fail('variable not set');
            $word = $this->context[$name];
        }
        return $word;
    }

    function consume_statement() {
        if (empty($this->statements)) $this->fail("Operator error: Expected predicate before this");
        return array_pop($this->statements);
    }

    function valid_identifier($ident) {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $ident);
    }

    function fail($reason) {
        throw new FilterParsingException($reason." (predicate: ".$this->current_predicate." word: ". $this->current_word.")");
    }
}

class FilterParsingException extends Exception {}
?>