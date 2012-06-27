/** logical NOT predicate
  * Synopsis: not <negated predicate> */
$clause = $parser->parse_predicate();
return new Filter_Logic_Not($clause);