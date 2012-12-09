<?php
/** Dump database contents */
class DB_Dump {
    var $db;

    function __construct($db) {
        $this->db = $db;
    }

    function dump_all($f) {
        fwrite($f, '-- SQL dump '.$this->db->singlequery('SELECT DATABASE()').' on '.$_SERVER['SERVER_NAME']."\n");
        fwrite($f, '-- '.gmdate("Y.m.d H:i:s")." UCT\n\n");
        foreach($this->db->listquery('show tables') as $table) {
            $this->dump_table_structure($table, $f);
            $this->dump_table_data($table, $f);
        }
    }
    
    function dump_table_structure($table, $f) {
        fwrite($f, "\n-- Table `$table`: structure \n");
        fwrite($f, "DROP TABLE IF EXISTS `$table`;\n");
        $tstrs = $this->db->query("SHOW CREATE TABLE `$table`")->fetchRow(DB_FETCHMODE_ORDERED);
        fwrite($f, $tstrs[1].";\n\n");
    }
    
    function dump_table_data($table, $f) {
        $result = $this->db->query("SELECT * FROM `$table`");
        fwrite($f, "-- Table `$table`: ".$result->numRows()." rows\n");

        if ($result->numRows() < 1) return;
     
        fwrite($f, "INSERT INTO `$table` VALUES\n");
        $first = true;
        while($row = $result->fetchRow(DB_FETCHMODE_ORDERED)) {
            fwrite($f, ($first ? '  (' : ', ('));
            $last_index = count($row) - 1;
            foreach($row as $i => $value) {
                if (is_null($value)) fwrite($f, 'null');
                else fwrite($f, $this->db->quote($value));
                if ($i < $last_index) fwrite($f, ', ');
            }
            fwrite($f, ")\n");
            $first = false;
        }
        fwrite($f, ";\n");
    }
}