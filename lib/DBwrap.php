<?php
/** Wrapper for PEAR DB connections
  */
class DBwrap {
    var $connection;

    /** The connection will be opened as soon as it is actually used. */
    function __construct($connection) {
        $this->connection = $connection;
    }

    /** Run a plain SQL query
      * The query and the affected rows are logged to SQL.
      * If the query fails, an Exception is thrown. */
    function query($query, $data=array()) {
        $query = trim($query); // Trim whitespace from queries so the MySQL query-cache works properly
        Log::sql("Query: ".$query);
        if ($data) Log::sql($data);
        
        $result = $this->connection->execute($this->connection->prepare($query), $data);
        
        // Log resulting rows, or affected rows for non-selects
        if (strtolower($query[0]) == 's') {
            Log::sql($result->numRows()." rows in result.");
        } else {
            Log::sql($this->connection->affectedRows()." rows affected.");
        }

        return $result;
    }
    
    /** Retrieve the last generated AUTO_INCREMENT id */
    function last_id() {
        // Damn, MySQL specific
        return $this->singlequery("SELECT LAST_INSERT_ID()");
    }
    
    /** Retrieve the last affected rows count */
    function affected_rows() {
        return $this->connection->affectedRows();
    }

    /** Execute query and map results into object by index */
    function mapquery($index, $query, $data=array()) {
        $entries = array();
        $res = $this->query($query, $data);
        while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED)) {
                $entries[$entry->$index] = $entry;
        }
        return $entries;
    }

    /** Execute query and map results into hash by index */
    function mapqueryhash($index, $query, $data=array()) {
        $entries = array();
        $res = $this->query($query, $data=array());
        while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $entries[$entry->$index] = $entry;
        }
        return $entries;
    }

    /** Execute query and map results into hash */
    function queryhash($query, $data) {
        $entries = array();
        $res = $this->query($query, $data=array());
        while ($entry = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $entries []= $entry;
        }
        return $entries;
    }
    
    /** Execute query and put first value of each record into list. */
    function listquery($query, $data=array()) {
        $entries = array();
        $res = $this->query($query, $data);
        while ($entry = $res->fetchRow(DB_FETCHMODE_ORDERED))
                $entries[] = array_shift($entry);
        return $entries;
    }
    
    /** Get a single value from query
      * @return first field of first row or false if there were no rows */
    function singlequery($query, $data=array()) {
        $result = $this->query($query, $data);
        if ($result->numRows() < 1 || $result->numCols() < 1) return false;
        $firstrow = $result->fetchRow(DB_FETCHMODE_ORDERED);
        return $firstrow[0];
    }
}
