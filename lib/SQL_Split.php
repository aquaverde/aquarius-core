<?php

/** Ad-hoc stream SQL lexer to iterate over SQL statements in file
  * 
  * You can pass in a file descriptor to iterate over the SQL queries in that
  * file:
  * 
  * foreach(new SQL_Split(fopen('my_sql_file.sql')) as $statement) {
  *     mysqli_query($statement);
  * }
  * 
  * Memory requirements depend on the longest statement only, not on the total
  * length of the file. If there is no semicolon in the file, the contents of
  * the entire file will be returned as one statement.
  * 
  * Beware: It is a very simplistic lexer and might misread advanced syntax.
  * No syntax checking whatsoever!
  */
class SQL_Split implements Iterator {
    private $inf;

    private $current_statement;
    private $current_key;
    private $current_line;
    private $statement_line;
    private $valid;

    private $cur;
    private $statement_start;

    // Characters that cause alternate processing
    var $chars = array(
        "'"  => 'read_quote1',
        '"'  => 'read_quote2',
        '/'  => 'read_comment1',
        '-'  => 'read_comment2',
        ';'  => 'read_end'
    );


    /** Build an iterator over SQL statements in given input
      * @param $inf file to read */
    function __construct($inf) {
        $this->inf = $inf;
    }


    function rewind() {
        $this->current_statement = '';
        if (fseek($this->inf, 0) !== 0) throw new Exception("Unable to seek start in $this->inf");
        $this->cur = -1;
        $this->current_key = -1;
        $this->current_line = 1;
        $this->next();
    }


    function next() {
        $this->read_statement();
        $this->valid = strlen($this->current_statement) > 0;
        $this->current_key++;
    }

    function current() {
        return $this->current_statement;
    }


    function key() {
        return $this->current_key;
    }


    function valid() {
        return $this->valid;
    }
    
    
    /** Return line number where the last-read statement ends */
    function line() {
        return $this->current_line;
    }


    private function read_char() {
        $c = fgetc($this->inf);
        if ($c === false) return false;
        $this->current_statement .= $c;
        if ("\n" == $c) $this->current_line += 1;
        return $c;
    }


    private function read_statement() {
        $this->current_statement = '';
        while(false !== $c = $this->read_char()) {
            if (isset($this->chars[$c])) {
                $end = $this->{$this->chars[$c]}();
                if ($end) break;
            }
        }
        $this->current_statement = trim($this->current_statement);
    }


    function read_quote1() {
        while(false !== $c = $this->read_char()) {
            if ($c === '\\') {
                $this->read_char();
            } elseif($c === "'") {
                return false;
            } 
        }
        return false;
    }


    function read_quote2() {
        while(false !== $c = $this->read_char()) {
            if ($c === '\\') {
                $this->read_char();
            } elseif($c === '"') {
                return false;
            } 
        }
        return false;
    }


    function read_comment1() {
        $c = $this->read_char();
        if ($c === false) return false;
        if ('*' === $this->read_char()) {
            // We're inside a comment block, look for the end
            $cur = '  ';
            while ($cur !== '*/') {
                $c = $this->read_char();
                if ($c === false) return;
                $cur = $cur[1].$c;
            }
        }
        return false;
    }


    function read_comment2() {
        $c = $this->read_char();
        if ($c === false) return false;
        if ($c === '-') {
            // Line comment, find line termination
            do { $c = $this->read_char(); } while (false !== $c && "\n" !== $c);
        }
        return false;
    }


    function read_end() {
        return true;
    }
}