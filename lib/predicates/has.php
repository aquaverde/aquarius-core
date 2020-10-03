<?php

/** Filter node based on content field
  * Synopsis: has <field> <value> */
return function($parser) {
    $field = $parser->consume_word();
    $value = $parser->consume_word();
    return new NodeFilter_Field($field, $value);
};