<?php

/** logical NOT predicate
  * Synopsis: not <negated predicate> */
return function($parser) {
    $clause = $parser->parse_predicate();
    return new Filter_Logic_Not($clause);
};