<?php
/** Wrapper for SQL connections, allows for easy debugging and logging of queries.
  */
class SQLwrap {
    var $connection;

    function __construct($connection) {
        $this->connection = $connection;
    }

    static function connect($server, $user, $pass, $dbname) {
        Log::debug("Opening DB connection to $server, DB $dbname");
        $this->connection = mysql_connect($server, $user, $pass, true);
        if (!$this->connection) {
            Log::fail("Failed opening database connection");
            throw new Exception("Failed opening DB connection");
        }
        mysql_select_db($dbname, $this->connection) || Log::fail("Failed selecting DB '$dbname'");
    }

    /** Make sure any results we retrieve or commands we send use the same charset and collation as the database
      * (http://www.adviesenzo.nl/examples/php_mysql_charset_fix/) */
    function use_db_charset() {
        $db_charset = mysql_query( "SHOW VARIABLES LIKE 'character_set_database'" );
        $charset_row = mysql_fetch_assoc( $db_charset );
        mysql_query( "SET NAMES '" . $charset_row['Value'] . "'" );
        unset( $db_charset, $charset_row );
    }

    /** Run a plain SQL query
      * The query and the affected rows are logged to SQL.
      * If the query fails, an Exception is thrown. */
    function query($query) {
        Log::sql("Running query: ".$query);
        $result = false;
        $result = mysql_query(trim($query), $this->connection); // Trim whitespace from queries so the MySQL query-cache works properly
        if (!$result) {
                Log::fail("Failed running query: ".mysql_error($this->connection));
                throw new Exception("Failed running query: ".mysql_error($this->connection));
        } else {
            Log::sql("Affected rows: ".mysql_affected_rows($this->connection));
        }
        return $result;
    }
    
    /** Retrieve the last generated AUTO_INCREMENT id */
    function last_id() {
        return mysql_insert_id($this->connection);
    }
    
    /** Retrieve the last affected rows count */
    function affected_rows() {
        return mysql_affected_rows($this->connection);
    }

    /** Execute query and map results into object by index */
    function mapquery($index, $query) {
        $entries = array();
        $res = $this->query($query);
        while ($entry = mysql_fetch_object($res))
                $entries[$entry->$index] = $entry;
        return $entries;
    }

    /** Execute query and map results into hash by index */
    function mapqueryhash($index, $query) {
        $entries = array();
        $res = $this->query($query);
        while ($entry = mysql_fetch_assoc($res))
                $entries[$entry[$index]] = $entry;
        return $entries;
    }

    /** Execute query and map results into hash */
    function queryhash($query) {
        $entries = array();
        $res = $this->query($query);
        while ($entry = mysql_fetch_assoc($res))
                $entries[] = $entry;
        return $entries;
    }
    
    /** Execute query and put first value of each record into list. */
    function listquery($query) {
        $entries = array();
        $res = $this->query($query);
        while ($entry = mysql_fetch_row($res))
                $entries[] = array_shift($entry);
        return $entries;
    }
    
    /** Get a single value from query
      * @return first field of first row or false if there were no rows */
    function singlequery($query) {
        $result = $this->query($query);
        if (mysql_num_rows($result) < 1 || mysql_num_fields($result) < 1 ) return false;
        return mysql_result($result, 0, 0);
    }
}
?>
