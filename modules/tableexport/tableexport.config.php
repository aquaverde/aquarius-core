<?php

// tableexport
// -----------------------------------------------------------------------
if (!defined('TABLEEXPORT_FILENAME'))    define('TABLEEXPORT_FILENAME', 'table_name.csv');
if (!defined('TABLEEXPORT_TABLE'))       define('TABLEEXPORT_TABLE', 'table_name');
if (!defined('TABLEEXPORT_ORDERCOLUMN')) define('TABLEEXPORT_ORDERCOLUMN', 'ID ASC');

// Set to true if you want 8859-1 instead of utf8 WHY EXCEL
$config['tableexport']['latin1'] = false;

// Character used to separate the fields, preset is a comma (as in COMMA-Separated-Values, but I digress)
$config['tableexport']['delimiter'] = ',';